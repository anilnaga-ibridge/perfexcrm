<?php

namespace App\Plugin\Kernel;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class PackageManager
{
    protected string $publicKeyPath;

    public function __construct()
    {
        $this->publicKeyPath = storage_path('keys/platform_public.pem');
    }

    /**
     * Verify the digital signature of a plugin ZIP package.
     *
     * @param string $zipPath
     * @return bool
     * @throws \RuntimeException
     */
    public function verifySignature(string $zipPath): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException("Failed to open plugin ZIP package.");
        }

        // Collect and filter ZIP entries
        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = str_replace('\\', '/', $zip->getNameIndex($i));

            // Skip directories
            if (str_ends_with($name, '/')) {
                continue;
            }

            // Skip macOS metadata
            if (str_starts_with($name, '__MACOSX/')) {
                continue;
            }

            // Skip hidden files anywhere in the archive
            $segments = explode('/', $name);
            $hidden = false;
            foreach ($segments as $segment) {
                if ($segment !== '' && str_starts_with($segment, '.')) {
                    $hidden = true;
                    break;
                }
            }
            if ($hidden) {
                continue;
            }

            $files[] = [
                'index' => $i,
                'name' => $name,
            ];
        }

        $manifestData = null;
        $prefix = '';
        $signatureData = null;

        // Prioritize manifest.json then module.json, and validate expected manifest structure
        foreach (['manifest.json', 'module.json'] as $targetFilename) {
            foreach ($files as $file) {
                if (basename($file['name']) === $targetFilename) {
                    $content = $zip->getFromIndex($file['index']);
                    $manifest = json_decode($content, true);

                    if (is_array($manifest) && isset($manifest['name']) && isset($manifest['alias']) && isset($manifest['version'])) {
                        $manifestData = $content;
                        $prefix = rtrim(substr($file['name'], 0, -strlen(basename($file['name']))), '/');
                        break 2;
                    }
                }
            }
        }

        if ($manifestData === null) {
            // Nested ZIP pattern (CodeCanyon double-zip): look for .zip files inside the archive
            // and check if they contain the manifest.
            $nestedZips = [];
            foreach ($files as $file) {
                if (str_ends_with(strtolower($file['name']), '.zip')) {
                    $nestedZips[] = $file;
                }
            }

            foreach ($nestedZips as $nestedEntry) {
                $tempDir = storage_path('app/temp_sig_verify_' . microtime(true) . '_' . uniqid());
                File::ensureDirectoryExists($tempDir);
                $nestedContent = $zip->getFromIndex($nestedEntry['index']);
                $nestedPath = $tempDir . '/nested.zip';
                File::put($nestedPath, $nestedContent);

                $nestedZip = new ZipArchive();
                if ($nestedZip->open($nestedPath) === true) {
                    for ($j = 0; $j < $nestedZip->numFiles; $j++) {
                        $nName = str_replace('\\', '/', $nestedZip->getNameIndex($j));
                        if (str_ends_with($nName, '/') || str_starts_with($nName, '__MACOSX/')) {
                            continue;
                        }
                        $baseName = basename($nName);
                        if ($baseName === 'manifest.json' || $baseName === 'module.json') {
                            $content = $nestedZip->getFromIndex($j);
                            $parsed = json_decode($content, true);
                            if (is_array($parsed) && isset($parsed['name']) && isset($parsed['alias']) && isset($parsed['version'])) {
                                $manifestData = $content;
                                // Re-derive prefix: the nested entry's directory + inner entry's directory
                                $outerPrefix = rtrim(dirname($nestedEntry['name']), '/');
                                $innerPrefix = rtrim(dirname($nName), '/');
                                $prefix = ($outerPrefix ? $outerPrefix . '/' : '') . $innerPrefix;
                                break;
                            }
                        }
                    }
                    $nestedZip->close();
                }

                // Also search for signature.pem in the nested ZIP
                if ($manifestData !== null) {
                    $nestedZip2 = new ZipArchive();
                    if ($nestedZip2->open($nestedPath) === true) {
                        for ($j = 0; $j < $nestedZip2->numFiles; $j++) {
                            $nName = str_replace('\\', '/', $nestedZip2->getNameIndex($j));
                            // Check both with and without the nested prefix
                            if (basename($nName) === 'signature.pem') {
                                $signatureData = $nestedZip2->getFromIndex($j);
                                break;
                            }
                        }
                        $nestedZip2->close();
                    }
                }

                File::deleteDirectory($tempDir);

                if ($manifestData !== null) {
                    break;
                }
            }
        }

        if ($manifestData === null) {
            $allEntries = array_column($files, 'name');
            Log::warning("PackageManager: No manifest found (legacy module?), bypassing signature check", [
                'zip_path' => $zipPath,
                'total_entries' => count($allEntries),
                'sample_entries' => array_slice($allEntries, 0, 20),
            ]);
            $zip->close();
            return true;
        }

        // Search for signature.pem in the same directory as the selected manifest (skip if already found in nested ZIP)
        if ($signatureData === null) {
            $signatureTarget = ($prefix === '') ? 'signature.pem' : $prefix . '/signature.pem';

            foreach ($files as $file) {
                if ($file['name'] === $signatureTarget) {
                    $signatureData = $zip->getFromIndex($file['index']);
                    break;
                }
            }
        }

        $zip->close();

        // For local development, if no signature or public key is set, we bypass check with warning
        if ($signatureData === null || !File::exists($this->publicKeyPath)) {
            Log::warning("PackageManager: Digital signature verification bypassed for dev package: " . basename($zipPath));
            return true;
        }

        $pubKey = File::get($this->publicKeyPath);
        $ok = openssl_verify($manifestData, base64_decode($signatureData), $pubKey, OPENSSL_ALGO_SHA256);

        if ($ok !== 1) {
            throw new \RuntimeException("Digital signature verification failed. The plugin package has been tampered with or is untrusted.");
        }

        // Verify checksum hashes of all packaged files if defined in the manifest
        $manifest = json_decode($manifestData, true);
        if ($manifest && isset($manifest['files']) && is_array($manifest['files'])) {
            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                foreach ($manifest['files'] as $relativePath => $expectedHash) {
                    $fileContent = $zip->getFromName($relativePath);
                    if ($fileContent === false && $prefix !== '') {
                        $fileContent = $zip->getFromName($prefix . '/' . ltrim($relativePath, '/'));
                    }
                    if ($fileContent === false) {
                        $zip->close();
                        throw new \RuntimeException("File declared in manifest checksums is missing from ZIP package: {$relativePath}");
                    }
                    $actualHash = hash('sha256', $fileContent);
                    if ($actualHash !== $expectedHash) {
                        $zip->close();
                        throw new \RuntimeException("File integrity check failed for {$relativePath}. Expected hash: {$expectedHash}, actual: {$actualHash}");
                    }
                }
                $zip->close();
            }
        }

        return true;
    }
    
    /**
     * Create a backup snapshot of a plugin before performing an upgrade.
     *
     * @param string $alias
     * @param string $version
     * @return string Path to the created snapshot ZIP
     */
    public function createSnapshot(string $alias, string $version): string
    {
        $modulePath = base_path("Modules/{$alias}");
        if (!File::isDirectory($modulePath)) {
            return '';
        }

        $snapshotDir = storage_path("plugins/snapshots");
        File::ensureDirectoryExists($snapshotDir);
        
        $snapshotPath = "{$snapshotDir}/{$alias}_{$version}.zip";
        if (File::exists($snapshotPath)) {
            File::delete($snapshotPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($snapshotPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = File::allFiles($modulePath);
            foreach ($files as $file) {
                $relativePath = substr($file->getRealPath(), strlen($modulePath) + 1);
                $zip->addFile($file->getRealPath(), $relativePath);
            }

            // Backup settings and migrations state
            $moduleRecord = DB::table('modules')->where('alias', $alias)->first();
            if ($moduleRecord) {
                $settings = DB::table('module_settings')->where('module_id', $moduleRecord->id)->get();
                $backupData = [
                    'module'   => (array) $moduleRecord,
                    'settings' => $settings->toArray(),
                ];
                $zip->addFromString('snapshot_db_config.json', json_encode($backupData, JSON_PRETTY_PRINT));
            }
            $zip->close();
        }

        Log::info("PackageManager: Created module snapshot for [{$alias}] version {$version}");
        return $snapshotPath;
    }

    /**
     * Rollback a module to a previously backed-up snapshot ZIP.
     *
     * @param string $alias
     * @param string $snapshotPath
     */
    public function rollback(string $alias, string $snapshotPath): void
    {
        if (!File::exists($snapshotPath)) {
            throw new \RuntimeException("Failed to rollback: Snapshot file not found.");
        }

        $modulePath = base_path("Modules/{$alias}");
        if (File::isDirectory($modulePath)) {
            File::deleteDirectory($modulePath);
        }

        File::ensureDirectoryExists($modulePath);

        $zip = new ZipArchive();
        if ($zip->open($snapshotPath) === true) {
            $zip->extractTo($modulePath);

            // Restore DB settings
            $backupJson = $zip->getFromName('snapshot_db_config.json');
            if ($backupJson !== false) {
                $backupData = json_decode($backupJson, true);
                if (isset($backupData['module'])) {
                    $mod = $backupData['module'];
                    DB::table('modules')->updateOrInsert(['alias' => $alias], [
                        'name'    => $mod['name'],
                        'version' => $mod['version'],
                        'status'  => $mod['status'],
                    ]);

                    if (isset($backupData['settings'])) {
                        $moduleRecord = DB::table('modules')->where('alias', $alias)->first();
                        if ($moduleRecord) {
                            DB::table('module_settings')->where('module_id', $moduleRecord->id)->delete();
                            foreach ($backupData['settings'] as $setting) {
                                DB::table('module_settings')->insert([
                                    'module_id' => $moduleRecord->id,
                                    'key'       => $setting['key'],
                                    'value'     => $setting['value'],
                                ]);
                            }
                        }
                    }
                }
            }

            $zip->close();
            Log::info("PackageManager: Rolled back module [{$alias}] using snapshot: " . basename($snapshotPath));
        } else {
            throw new \RuntimeException("Failed to extract snapshot ZIP during rollback.");
        }
    }
}
