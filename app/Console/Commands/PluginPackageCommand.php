<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Upgrade\PluginPackageManager;
use App\Plugin\Registries\PluginRegistry;
use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * Class PluginPackageCommand
 * 
 * Artisan command compressing a plugin directory into a standard release ZIP.
 */
class PluginPackageCommand extends Command
{
    protected $signature = 'plugin:package {plugin : The alias of the plugin to package}';
    protected $description = 'Package a plugin directory into a distributable ZIP package with checksums';

    public function handle(): int
    {
        $alias = $this->argument('plugin');
        $registry = app(PluginRegistry::class);
        $packageManager = app(PluginPackageManager::class);

        $plugin = $registry->getPlugin($alias);
        if (!$plugin) {
            $this->error("Plugin '{$alias}' not found.");
            return Command::FAILURE;
        }

        $path = $plugin->getPath();
        $this->info("Packaging plugin: {$plugin->getName()} from {$path}...");

        try {
            // 1. Validate layout format
            $packageManager->validatePackageLayout($path);

            // 2. Generate file checksums
            $checksums = [];
            $files = File::allFiles($path);
            $excludedPaths = [
                'node_modules',
                'vendor',
                '.git',
                '.github',
                '.env',
                '.DS_Store',
                'composer.lock',
                'package-lock.json',
                'yarn.lock',
                '__MACOSX',
                'Thumbs.db',
                '.gitignore',
                '.gitattributes',
                'signature.json',
                'checksums.json',
                'signature.pem',
            ];

            $validFiles = [];
            foreach ($files as $file) {
                $relPath = str_replace($path . '/', '', $file->getPathname());
                
                $shouldExclude = false;
                foreach ($excludedPaths as $exclude) {
                    if ($relPath === $exclude || str_starts_with($relPath, $exclude . '/')) {
                        $shouldExclude = true;
                        break;
                    }
                }
                if ($shouldExclude) {
                    continue;
                }

                // Exclude hidden files and macOS AppleDouble metadata files (._*)
                $segments = explode('/', str_replace('\\', '/', $relPath));
                $isMetadataOrHidden = false;
                foreach ($segments as $segment) {
                    if ($segment !== '' && (str_starts_with($segment, '._') || str_starts_with($segment, '.'))) {
                        if ($segment !== '.htaccess') {
                            $isMetadataOrHidden = true;
                            break;
                        }
                    }
                }
                if ($isMetadataOrHidden) {
                    continue;
                }

                $checksums[$relPath] = hash_file('sha256', $file->getPathname());
                $validFiles[] = $file;
            }

            File::put($path . '/checksums.json', json_encode($checksums, JSON_PRETTY_PRINT));
            $this->line("  ✔ Checksums generated inside checksums.json.");

            // 3. Compress folder to ZIP
            $zipPath = storage_path("app/temp/plugins/{$alias}_v" . $plugin->getVersion() . ".zip");
            File::ensureDirectoryExists(dirname($zipPath));

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($validFiles as $file) {
                    $relPath = str_replace($path . '/', '', $file->getPathname());
                    $zip->addFile($file->getPathname(), $relPath);
                }
                // Add the newly created checksums file
                $zip->addFile($path . '/checksums.json', 'checksums.json');
                $zip->close();
            }

            $this->info("Successfully compiled package: " . basename($zipPath));
        } catch (\Throwable $e) {
            $this->error("Packaging failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
