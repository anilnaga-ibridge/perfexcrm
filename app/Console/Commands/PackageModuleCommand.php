<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class PackageModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:package 
                            {module : The alias or directory path of the plugin} 
                            {--output= : The directory to save the packaged ZIP file}';

    protected $aliases = ['plugin:package'];

    /**
     * The console command description.
     */
    protected $description = 'Run SDK validation and package a plugin into a distributable ZIP archive.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $target = $this->argument('module');
        $outputOption = $this->option('output');

        // 1. Resolve module directory
        $modulePath = null;
        $alias = null;

        if (File::isDirectory($target)) {
            $modulePath = realpath($target);
            $alias = basename($modulePath);
        } else {
            $possiblePath = base_path('Modules/' . $target);
            if (File::isDirectory($possiblePath)) {
                $modulePath = realpath($possiblePath);
                $alias = $target;
            }
        }

        if (!$modulePath) {
            $this->error("Error: Module directory not found for: '{$target}'");
            return 1;
        }

        $this->info("Packaging module '{$alias}'...");
        $this->info("Step 1: Running SDK validation gate in Strict Mode...");

        // 2. Run validator in strict mode
        $exitCode = Artisan::call('module:validate', [
            'module' => $target,
            '--strict' => true,
        ]);

        // Print validator output
        $validatorOutput = Artisan::output();
        $this->line($validatorOutput);

        if ($exitCode !== 0) {
            $this->error("Packaging aborted: SDK validation failed in Strict Mode. Please resolve all warnings and errors before packaging.");
            return 1;
        }

        $this->info("Step 2: Validation passed. Parsing manifest details...");

        // 3. Parse manifest
        $manifestPath = $modulePath . '/module.json';
        $manifest = json_decode(File::get($manifestPath), true);
        $version = $manifest['version'] ?? '1.0.0';

        // 4. Resolve output path
        $outputDir = $outputOption ? realpath($outputOption) : base_path('packages');
        if (!$outputDir) {
            $outputDir = $outputOption ?: base_path('packages');
        }
        File::ensureDirectoryExists($outputDir);

        $zipFilename = "{$alias}-{$version}.zip";
        $zipPath = rtrim($outputDir, '/') . '/' . $zipFilename;

        if (File::exists($zipPath)) {
            $this->warn("Target package file '{$zipFilename}' already exists. Overwriting...");
            File::delete($zipPath);
        }

        $this->info("Step 3: Compiling files and building ZIP package...");

        // 5. ZIP the module directory (excluding forbidden files)
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Failed to create ZIP archive at: '{$zipPath}'");
            return 1;
        }

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
        ];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modulePath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $addedCount = 0;
        foreach ($files as $file) {
            // Skip directories (they are added automatically when adding files)
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($modulePath) + 1);

            // Check if file is in an excluded directory or matches an excluded filename
            $shouldExclude = false;
            foreach ($excludedPaths as $exclude) {
                if ($relativePath === $exclude || str_starts_with($relativePath, $exclude . '/')) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                continue;
            }

            // Nest the module files inside a folder named after the alias in the ZIP
            $zipRelativePath = "{$alias}/{$relativePath}";
            $zip->addFile($filePath, $zipRelativePath);
            $addedCount++;
        }

        $zip->close();

        $size = filesize($zipPath);
        $sizeFormatted = number_format($size / 1024, 2) . ' KB';

        $this->info("=================================================");
        $this->info("Package Created Successfully!");
        $this->line("File:   <fg=green>{$zipPath}</>");
        $this->line("Size:   <fg=green>{$sizeFormatted}</>");
        $this->line("Files:  <fg=green>{$addedCount} files packaged</>");
        $this->info("=================================================");

        return 0;
    }
}
