<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'module:make
        {name : The display name of the plugin (e.g. "Inventory" or "Leave Request")}
        {--crud : Generate full CRUD scaffold with model, migration, controller, requests, and Vue pages}
        {--settings : Include settings.json}';

    protected $aliases = ['plugin:make'];

    protected $description = 'Scaffold a new native module from the SDK reference structure';

    public function handle(): int
    {
        $name = trim($this->argument('name'));
        if (empty($name)) {
            $this->error('Module name is required.');
            return self::FAILURE;
        }

        $crud = $this->option('crud');
        $settings = $this->option('settings');

        $moduleAlias = $this->kebabCase($name);
        $namespace = $this->pascalCase($name);
        $model = $this->pascalCase($name);
        $permissionPrefix = $this->snakeCase($name);
        $tableName = Str::plural($this->snakeCase($name));
        $modelPlural = str_replace('_', '-', $tableName);
        $modelPluralTitle = Str::plural($name);
        $pageComponentName = $model;

        $modulePath = base_path("Modules/{$moduleAlias}");

        if (is_dir($modulePath)) {
            $this->error("Module '{$name}' already exists at Modules/{$moduleAlias}.");
            return self::FAILURE;
        }

        $stubPath = base_path('stubs/module');
        if (!is_dir($stubPath)) {
            $this->error("Stubs directory not found at {$stubPath}. Run this from the project root.");
            return self::FAILURE;
        }

        $vars = [
            'moduleName' => $name,
            'moduleAlias' => $moduleAlias,
            'namespace' => $namespace,
            'model' => $model,
            'permissionPrefix' => $permissionPrefix,
            'tableName' => $tableName,
            'modelPlural' => $modelPlural,
            'modelPluralTitle' => $modelPluralTitle,
            'pageComponentName' => $pageComponentName,
        ];

        $this->line("Creating module: {$name}");
        $this->line("  Alias:      {$moduleAlias}");
        $this->line("  Namespace:  Modules\\{$namespace}");
        $this->line("  Table:      {$tableName}");
        $this->newLine();

        $directories = [
            'Http/Controllers/Api',
            'Http/Requests',
            'Models',
            'routes',
            'Database/Migrations',
            'resources/js/pages',
            'resources/js/components',
            'tests/Feature',
            'tests/Unit',
            'Providers',
            'Widgets',
            'Events',
            'Listeners',
            'Console/Commands',
            'Policies',
            'Jobs',
            'Views/widgets',
            'Assets/js',
            'Assets/css',
            'language/en',
        ];

        foreach ($directories as $dir) {
            File::makeDirectory("{$modulePath}/{$dir}", 0755, true, true);
        }

        // Global scaffold files for all modules
        $this->processStub("{$stubPath}/module.json.stub", "{$modulePath}/module.json", $vars);
        $this->processStub(
            $crud ? "{$stubPath}/menu.crud.json.stub" : "{$stubPath}/menu.json.stub",
            "{$modulePath}/menu.json", $vars
        );
        $this->processStub(
            $crud ? "{$stubPath}/permissions.crud.json.stub" : "{$stubPath}/permissions.json.stub",
            "{$modulePath}/permissions.json", $vars
        );
        $this->processStub(
            $crud ? "{$stubPath}/routes/api.crud.php.stub" : "{$stubPath}/routes/api.php.stub",
            "{$modulePath}/routes/api.php", $vars
        );
        $this->processStub("{$stubPath}/routes/web.php.stub", "{$modulePath}/routes/web.php", $vars);

        $this->processStub(
            $crud
                ? "{$stubPath}/Http/Controllers/Api/CrudController.php.stub"
                : "{$stubPath}/Http/Controllers/Api/Controller.php.stub",
            "{$modulePath}/Http/Controllers/Api/{$model}Controller.php", $vars
        );

        // Core extensible scaffolding
        $this->processStub("{$stubPath}/Providers/PluginServiceProvider.php.stub", "{$modulePath}/Providers/PluginServiceProvider.php", $vars);
        $this->processStub("{$stubPath}/Widgets/DashboardWidget.php.stub", "{$modulePath}/Widgets/DashboardWidget.php", $vars);
        $this->processStub("{$stubPath}/Events/PluginEvent.php.stub", "{$modulePath}/Events/PluginEvent.php", $vars);
        $this->processStub("{$stubPath}/Listeners/PluginListener.php.stub", "{$modulePath}/Listeners/PluginListener.php", $vars);
        $this->processStub("{$stubPath}/Console/Commands/PluginCommand.php.stub", "{$modulePath}/Console/Commands/PluginCommand.php", $vars);
        $this->processStub("{$stubPath}/Policies/PluginPolicy.php.stub", "{$modulePath}/Policies/PluginPolicy.php", $vars);
        $this->processStub("{$stubPath}/Jobs/PluginJob.php.stub", "{$modulePath}/Jobs/PluginJob.php", $vars);
        $this->processStub("{$stubPath}/Views/index.blade.php.stub", "{$modulePath}/Views/index.blade.php", $vars);
        $this->processStub("{$stubPath}/Views/widgets/dashboard.blade.php.stub", "{$modulePath}/Views/widgets/dashboard.blade.php", $vars);
        $this->processStub("{$stubPath}/Assets/js/app.js.stub", "{$modulePath}/Assets/js/app.js", $vars);
        $this->processStub("{$stubPath}/Assets/css/app.css.stub", "{$modulePath}/Assets/css/app.css", $vars);
        $this->processStub("{$stubPath}/language/en/lang.php.stub", "{$modulePath}/language/en/{$moduleAlias}_lang.php", $vars);
        
        $this->processStub("{$stubPath}/README.md.stub", "{$modulePath}/README.md", $vars);
        $this->processStub("{$stubPath}/CHANGELOG.md.stub", "{$modulePath}/CHANGELOG.md", $vars);
        $this->processStub("{$stubPath}/LICENSE.stub", "{$modulePath}/LICENSE", $vars);

        // Tests scaffolding
        $this->processStub("{$stubPath}/Tests/Unit/UnitTest.php.stub", "{$modulePath}/tests/Unit/{$model}Test.php", $vars);

        if ($crud) {
            File::makeDirectory("{$modulePath}/resources/js/pages/{$modelPlural}", 0755, true, true);

            $this->processStub("{$stubPath}/Http/Requests/StoreRequest.php.stub",
                "{$modulePath}/Http/Requests/Store{$model}Request.php", $vars);
            $this->processStub("{$stubPath}/Http/Requests/UpdateRequest.php.stub",
                "{$modulePath}/Http/Requests/Update{$model}Request.php", $vars);
            $this->processStub("{$stubPath}/Models/Model.php.stub",
                "{$modulePath}/Models/{$model}.php", $vars);

            $timestamp = now()->format('Y_m_d_His');
            $this->processStub("{$stubPath}/Database/Migrations/create_table.php.stub",
                "{$modulePath}/Database/Migrations/{$timestamp}_create_{$tableName}_table.php", $vars);

            $this->processStub("{$stubPath}/resources/js/pages/crud/index.vue.stub",
                "{$modulePath}/resources/js/pages/{$modelPlural}/index.vue", $vars);
            $this->processStub("{$stubPath}/resources/js/pages/crud/create.vue.stub",
                "{$modulePath}/resources/js/pages/{$modelPlural}/create.vue", $vars);
            $this->processStub("{$stubPath}/resources/js/pages/crud/edit.vue.stub",
                "{$modulePath}/resources/js/pages/{$modelPlural}/edit.vue", $vars);
            $this->processStub("{$stubPath}/resources/js/components/Form.vue.stub",
                "{$modulePath}/resources/js/components/Form.vue", $vars);
            $this->processStub("{$stubPath}/Tests/Feature/FeatureTest.php.stub",
                "{$modulePath}/tests/Feature/{$model}Test.php", $vars);
        } else {
            $this->processStub("{$stubPath}/resources/js/pages/Dashboard.vue.stub",
                "{$modulePath}/resources/js/pages/Dashboard.vue", $vars);
            // Default feature test for non-crud
            $this->processStub("{$stubPath}/Tests/Feature/FeatureTest.php.stub",
                "{$modulePath}/tests/Feature/{$model}Test.php", $vars);
        }

        if ($settings) {
            $this->processStub("{$stubPath}/settings.json.stub",
                "{$modulePath}/settings.json", $vars);
        }

        $this->newLine();
        $this->info("Module '{$name}' created successfully at Modules/{$moduleAlias}.");

        $this->line("What's next:");
        $this->line("  1. composer dump-autoload");
        if ($crud) {
            $this->line("  2. Edit Database/Migrations/{$timestamp}_create_{$tableName}_table.php");
            $this->line("  3. Edit Models/{$model}.php (fillable, casts)");
            $this->line("  4. php artisan migrate");
            $this->line("  5. npm run dev");
            $this->line("  6. Build ZIP and upload via admin panel");
        } else {
            $this->line("  2. Build your module's API and pages");
            $this->line("  3. npm run dev");
            $this->line("  4. Build ZIP and upload via admin panel");
        }

        return self::SUCCESS;
    }

    private function pascalCase(string $name): string
    {
        return str_replace(['-', '_', ' '], '', Str::title($name));
    }

    private function kebabCase(string $name): string
    {
        return Str::kebab($name);
    }

    private function snakeCase(string $name): string
    {
        return Str::snake($name);
    }

    private function processStub(string $stubPath, string $destPath, array $vars): void
    {
        $content = File::get($stubPath);

        foreach ($vars as $key => $value) {
            $content = str_replace("{{ {$key} }}", $value, $content);
        }

        File::put($destPath, $content);
        $shortPath = str_replace(base_path(), '', $destPath);
        $this->line("  {$shortPath}");
    }
}
