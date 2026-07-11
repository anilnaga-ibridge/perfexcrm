<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Registries\PluginRegistry;
use App\Plugin\Assets\AssetPipeline;
use Illuminate\Support\Facades\File;

/**
 * Class PluginPublishCommand
 * 
 * Artisan CLI command publishing static assets, localization lang files,
 * and view files from plugin directories.
 */
class PluginPublishCommand extends Command
{
    protected $signature = 'plugin:publish 
                            {--plugin= : Alias of the plugin to publish}
                            {--force : Overwrite existing published files}
                            {--tag= : Tag of the resource type to publish}';

    protected $description = 'Publish static assets, configurations, and views from plugins';

    public function handle(): int
    {
        $alias = $this->option('plugin');
        $force = $this->option('force');
        $tag = $this->option('tag');

        $registry = app(PluginRegistry::class);
        $pipeline = app(AssetPipeline::class);

        $plugins = [];
        if ($alias) {
            $plugin = $registry->getPlugin($alias);
            if ($plugin) {
                $plugins[] = $plugin;
            } else {
                $this->error("Plugin '{$alias}' not found.");
                return Command::FAILURE;
            }
        } else {
            $plugins = $registry->getPlugins();
        }

        foreach ($plugins as $plugin) {
            $this->info("Publishing resources for plugin: {$plugin->getName()}");
            
            // Check tags and publish accordingly
            if (!$tag || $tag === 'assets') {
                $assetsPath = $plugin->getPath() . '/Assets';
                if (File::isDirectory($assetsPath)) {
                    $assetClass = new class($plugin->getAlias(), $assetsPath) implements \App\Contracts\Plugins\AssetInterface {
                        protected string $alias;
                        protected string $path;
                        public function __construct(string $alias, string $path) {
                            $this->alias = $alias;
                            $this->path = $path;
                        }
                        public function getPublishAlias(): string {
                            return $this->alias;
                        }
                        public function getSourcePath(): string {
                            return $this->path;
                        }
                    };
                    $pipeline->publish($assetClass);
                    $this->line("  ✔ Published static assets to public/modules/" . strtolower($plugin->getAlias()));
                }
            }

            if (!$tag || $tag === 'views') {
                $viewsPath = $plugin->getPath() . '/Views';
                $targetViewsPath = resource_path('views/vendor/' . strtolower($plugin->getAlias()));
                if (File::isDirectory($viewsPath)) {
                    if ($force && File::exists($targetViewsPath)) {
                        File::deleteDirectory($targetViewsPath);
                    }
                    if (!File::exists($targetViewsPath)) {
                        File::copyDirectory($viewsPath, $targetViewsPath);
                        $this->line("  ✔ Published views to resources/views/vendor/" . strtolower($plugin->getAlias()));
                    } else {
                        $this->comment("  Skipped views (directory already exists). Use --force to overwrite.");
                    }
                }
            }
        }

        $this->info("Publishing operations completed successfully.");
        return Command::SUCCESS;
    }
}
