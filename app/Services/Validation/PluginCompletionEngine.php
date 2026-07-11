<?php

namespace App\Services\Validation;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginCompletionEngine
{
    private string $path;
    private string $alias;
    private array $models = [];
    private array $dependencies = [];

    // Weights for compliance scoring
    private array $weights = [
        'migration' => 4,
        'controller' => 4,
        'api_routes' => 4,
        'web_routes' => 2,
        'store_request' => 2,
        'update_request' => 2,
        'policy' => 1,
        'permissions' => 2,
        'menu' => 2,
        'vue_index' => 2,
        'vue_create' => 2,
        'vue_edit' => 2,
        'vue_show' => 1,
        'factory' => 1,
        'seeder' => 1,
        'test' => 1,
        'localization' => 1,
        'settings' => 1,
        'documentation' => 1,
        'assets' => 1,
    ];

    public function __construct(string $path, string $alias)
    {
        $this->path = rtrim($path, '/');
        $this->alias = $alias;
    }

    /**
     * Run audit check on the plugin and generate the compliance report.
     */
    public function audit(): array
    {
        $this->discoverModels();
        $this->checkDependencies();

        $report = [
            'module' => $this->alias,
            'models_count' => count($this->models),
            'dependencies' => $this->dependencies,
            'compliance_score' => 100,
            'total_components' => 0,
            'passed_components' => 0,
            'missing_components' => 0,
            'details' => [],
            'suggestions' => [],
        ];

        if (empty($this->models)) {
            $report['suggestions'][] = "No models found. Add Laravel Eloquent models under Models/ directory.";
            return $report;
        }

        $totalDeducted = 0;
        $totalPossibleWeight = 0;

        foreach ($this->models as $modelName => $modelInfo) {
            $checks = $this->runModelChecks($modelName, $modelInfo);
            $report['details'][$modelName] = $checks;

            foreach ($checks as $component => $checkInfo) {
                $weight = $this->weights[$component] ?? 1;
                $totalPossibleWeight += $weight;
                $report['total_components']++;

                if ($checkInfo['status'] === 'ok') {
                    $report['passed_components']++;
                } else {
                    $report['missing_components']++;
                    $totalDeducted += $weight;
                    $report['suggestions'][] = "[{$modelName}] Missing {$checkInfo['label']}: {$checkInfo['suggestion']}";
                }
            }
        }

        if ($totalPossibleWeight > 0) {
            $report['compliance_score'] = max(0, 100 - (int)round(($totalDeducted / $totalPossibleWeight) * 100));
        }

        return $report;
    }

    /**
     * Check if dependencies listed in module.json are installed and active.
     */
    private function checkDependencies(): void
    {
        $manifestPath = $this->path . '/module.json';
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
            if (is_array($manifest) && isset($manifest['depends'])) {
                foreach ((array)$manifest['depends'] as $dep) {
                    $exists = \App\Models\Module::where('alias', $dep)->where('status', 'active')->exists();
                    $this->dependencies[$dep] = [
                        'status' => $exists ? 'active' : 'missing',
                    ];
                }
            }
        }
    }

    /**
     * Discover models inside Modules/{Alias}/Models/.
     */
    private function discoverModels(): void
    {
        $modelsDir = $this->path . '/Models';
        if (!is_dir($modelsDir)) {
            $modelsDir = $this->path . '/models';
        }

        if (is_dir($modelsDir)) {
            $files = glob($modelsDir . '/*.php');
            foreach ($files as $file) {
                $className = pathinfo($file, PATHINFO_FILENAME);
                $content = File::get($file);
                if (preg_match('/class\s+' . preg_quote($className, '/') . '\s+extends\s+/', $content)) {
                    $this->models[$className] = $this->parseModel($content, $className);
                }
            }
        }
    }

    /**
     * Extract attributes, fillables, and relationship patterns from model content.
     */
    private function parseModel(string $content, string $className): array
    {
        // Table name: protected $table = '...';
        $table = null;
        if (preg_match('/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            $table = $matches[1];
        } else {
            $table = Str::snake(Str::plural($className));
        }

        // Fillable fields: protected $fillable = [...];
        $fillable = [];
        if (preg_match('/protected\s+\$fillable\s*=\s*\[([^\]]+)\]/s', $content, $matches)) {
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $matches[1], $fieldMatches);
            $fillable = $fieldMatches[1];
        }

        // Relationships: belongsTo, hasMany etc.
        $relations = [];
        if (preg_match_all('/public\s+function\s+(\w+)\s*\([^\)]*\)\s*\{(?:[^{}]*|\{(?:[^{}]*|\{[^{}]*\})*\})*\}/s', $content, $matches)) {
            foreach ($matches[0] as $methodBody) {
                if (preg_match('/\$this->(belongsTo|hasMany|hasOne|belongsToMany)\s*\(\s*([\w\\\]+)::class/', $methodBody, $relMatches)) {
                    $relationType = $relMatches[1];
                    $relatedClass = class_basename($relMatches[2]);
                    $relationName = '';
                    if (preg_match('/public\s+function\s+(\w+)/', $methodBody, $nameMatches)) {
                        $relationName = $nameMatches[1];
                    }
                    $relations[] = [
                        'name' => $relationName,
                        'type' => $relationType,
                        'related_class' => $relatedClass,
                    ];
                }
            }
        }

        return [
            'name' => $className,
            'table' => $table,
            'fillable' => $fillable,
            'relations' => $relations,
        ];
    }

    /**
     * Audit validation checks for a single model.
     */
    private function runModelChecks(string $modelName, array $info): array
    {
        $pluralKebab = Str::plural(Str::kebab($modelName));
        $pluralSnake = Str::plural(Str::snake($modelName));

        // Read routes to inspect coverage
        $routesContent = '';
        foreach (['routes/api.php', 'Routes/api.php', 'routes/web.php', 'Routes/web.php'] as $r) {
            if (File::exists($this->path . '/' . $r)) {
                $routesContent .= File::get($this->path . '/' . $r) . "\n";
            }
        }

        // Read permissions & menus to inspect mapping
        $permissions = [];
        if (File::exists($this->path . '/permissions.json')) {
            $permissions = (array)json_decode(File::get($this->path . '/permissions.json'), true);
        }

        $menu = [];
        if (File::exists($this->path . '/menu.json')) {
            $menu = (array)json_decode(File::get($this->path . '/menu.json'), true);
        }

        // 1. Migration
        $hasMigration = false;
        $migrationsDir = $this->path . '/Database/Migrations';
        if (is_dir($migrationsDir)) {
            foreach (glob($migrationsDir . '/*.php') as $migFile) {
                $mContent = File::get($migFile);
                if (str_contains($mContent, "Schema::create('{$info['table']}'") || str_contains($mContent, 'Schema::create("' . $info['table'] . '"')) {
                    $hasMigration = true;
                    break;
                }
            }
        }

        // 2. Factory
        $hasFactory = File::exists($this->path . "/Database/Factories/{$modelName}Factory.php");

        // 3. Seeder
        $hasSeeder = File::exists($this->path . "/Database/Seeders/{$modelName}Seeder.php");

        // 4. Controller
        $hasController = File::exists($this->path . "/Http/Controllers/Api/{$modelName}Controller.php") ||
                         File::exists($this->path . "/Controllers/{$modelName}Controller.php");

        // 5. Store Request
        $hasStoreReq = File::exists($this->path . "/Http/Requests/Store{$modelName}Request.php") ||
                       File::exists($this->path . "/Requests/Store{$modelName}Request.php");

        // 6. Update Request
        $hasUpdateReq = File::exists($this->path . "/Http/Requests/Update{$modelName}Request.php") ||
                        File::exists($this->path . "/Requests/Update{$modelName}Request.php");

        // 7. Policy
        $hasPolicy = File::exists($this->path . "/Policies/{$modelName}Policy.php");

        // 8. API Routes (Resource coverage check)
        $hasApiRoutes = false;
        $missingApiMethods = [];
        if (!empty($routesContent)) {
            // Check if matches standard resource declaration
            $resourcePattern = '/(Route::apiResource|Route::resource)\s*\(\s*[\'"]' . preg_quote($pluralKebab, '/') . '[\'"]/';
            $resourcePatternSnake = '/(Route::apiResource|Route::resource)\s*\(\s*[\'"]' . preg_quote($pluralSnake, '/') . '[\'"]/';
            if (preg_match($resourcePattern, $routesContent) || preg_match($resourcePatternSnake, $routesContent)) {
                $hasApiRoutes = true;
            } else {
                // Manually check routes
                $getPattern = '/Route::get\s*\(\s*[\'"]' . preg_quote($pluralKebab, '/') . '[\'"]/';
                $postPattern = '/Route::post\s*\(\s*[\'"]' . preg_quote($pluralKebab, '/') . '[\'"]/';
                $putPattern = '/(Route::put|Route::patch)\s*\(\s*[\'"]' . preg_quote($pluralKebab, '/') . '\/\{/';
                $deletePattern = '/Route::delete\s*\(\s*[\'"]' . preg_quote($pluralKebab, '/') . '\/\{/';
                $showPattern = '/Route::get\s*\(\s*[\'"]' . preg_quote($pluralKebab, '/') . '\/\{/';

                $hasGet = preg_match($getPattern, $routesContent);
                $hasPost = preg_match($postPattern, $routesContent);
                $hasPut = preg_match($putPattern, $routesContent);
                $hasDelete = preg_match($deletePattern, $routesContent);
                $hasShow = preg_match($showPattern, $routesContent);

                if ($hasGet && $hasPost && $hasPut && $hasDelete && $hasShow) {
                    $hasApiRoutes = true;
                } else {
                    if (!$hasGet) $missingApiMethods[] = 'GET';
                    if (!$hasPost) $missingApiMethods[] = 'POST';
                    if (!$hasPut) $missingApiMethods[] = 'PUT/PATCH';
                    if (!$hasDelete) $missingApiMethods[] = 'DELETE';
                    if (!$hasShow) $missingApiMethods[] = 'GET (Show)';
                }
            }
        }

        // 9. Web Routes (checks if matching page routes exist)
        $hasWebRoutes = str_contains($routesContent, $pluralKebab) || str_contains($routesContent, $pluralSnake);

        // 10. Permissions mapping in permissions.json
        $hasPermissions = false;
        $expectedPerms = [
            Str::snake($modelName) . '.view',
            Str::snake($modelName) . '.create',
            Str::snake($modelName) . '.edit',
            Str::snake($modelName) . '.delete'
        ];
        $foundPerms = 0;
        foreach ($permissions as $p) {
            $pName = $p['name'] ?? $p['key'] ?? '';
            if (in_array($pName, $expectedPerms)) {
                $foundPerms++;
            }
        }
        if ($foundPerms >= 2) { // checks if at least view & create/edit are present
            $hasPermissions = true;
        }

        // 11. Menu mapping in menu.json
        $hasMenu = false;
        $menuString = json_encode($menu);
        if (str_contains($menuString, $pluralKebab) || str_contains($menuString, $pluralSnake) || str_contains(strtolower($menuString), strtolower($modelName))) {
            $hasMenu = true;
        }

        // 12. Vue Index Page with DataTable verification
        $vueIndexFile = $this->path . "/resources/js/pages/{$pluralKebab}/index.vue";
        if (!File::exists($vueIndexFile)) {
            $vueIndexFile = $this->path . "/resources/js/pages/{$pluralSnake}/index.vue";
        }
        $hasVueIndex = File::exists($vueIndexFile);
        $vueIndexOk = false;
        if ($hasVueIndex) {
            $vueContent = File::get($vueIndexFile);
            if (str_contains($vueContent, 'DataTable') || str_contains($vueContent, '<table') || str_contains($vueContent, 'handsontable')) {
                $vueIndexOk = true;
            }
        }

        // 13. Vue Create Page
        $vueCreateFile = $this->path . "/resources/js/pages/{$pluralKebab}/create.vue";
        if (!File::exists($vueCreateFile)) {
            $vueCreateFile = $this->path . "/resources/js/pages/{$pluralSnake}/create.vue";
        }
        $hasVueCreate = File::exists($vueCreateFile);

        // 14. Vue Edit Page
        $vueEditFile = $this->path . "/resources/js/pages/{$pluralKebab}/edit.vue";
        if (!File::exists($vueEditFile)) {
            $vueEditFile = $this->path . "/resources/js/pages/{$pluralSnake}/edit.vue";
        }
        $hasVueEdit = File::exists($vueEditFile);

        // 15. Vue Show Page
        $vueShowFile = $this->path . "/resources/js/pages/{$pluralKebab}/show.vue";
        if (!File::exists($vueShowFile)) {
            $vueShowFile = $this->path . "/resources/js/pages/{$pluralSnake}/show.vue";
        }
        $hasVueShow = File::exists($vueShowFile);

        // 16. Unit/Feature Test
        $hasTest = File::exists($this->path . "/tests/Feature/{$modelName}Test.php") ||
                   File::exists($this->path . "/Tests/Feature/{$modelName}Test.php");

        // 17. Localization
        $hasLocalization = is_dir($this->path . '/language') || is_dir($this->path . '/Language');

        // 18. Settings
        $hasSettings = File::exists($this->path . '/settings.json');

        // 19. Documentation
        $hasDocs = File::exists($this->path . '/README.md') || is_dir($this->path . '/docs');

        // 20. Assets
        $hasAssets = is_dir($this->path . '/Assets') || is_dir($this->path . '/assets');

        return [
            'migration' => [
                'label' => 'Migration',
                'status' => $hasMigration ? 'ok' : 'missing',
                'suggestion' => "Create migration file for table '{$info['table']}' in Database/Migrations.",
            ],
            'factory' => [
                'label' => 'Model Factory',
                'status' => $hasFactory ? 'ok' : 'missing',
                'suggestion' => "Scaffold factory Database/Factories/{$modelName}Factory.php.",
            ],
            'seeder' => [
                'label' => 'Model Seeder',
                'status' => $hasSeeder ? 'ok' : 'missing',
                'suggestion' => "Scaffold seeder Database/Seeders/{$modelName}Seeder.php.",
            ],
            'controller' => [
                'label' => 'Controller',
                'status' => $hasController ? 'ok' : 'missing',
                'suggestion' => "Create controller Http/Controllers/Api/{$modelName}Controller.php.",
            ],
            'store_request' => [
                'label' => 'Store Request',
                'status' => $hasStoreReq ? 'ok' : 'missing',
                'suggestion' => "Create store form request Http/Requests/Store{$modelName}Request.php.",
            ],
            'update_request' => [
                'label' => 'Update Request',
                'status' => $hasUpdateReq ? 'ok' : 'missing',
                'suggestion' => "Create update form request Http/Requests/Update{$modelName}Request.php.",
            ],
            'policy' => [
                'label' => 'Access Policy',
                'status' => $hasPolicy ? 'ok' : 'missing',
                'suggestion' => "Create Policies/{$modelName}Policy.php for permission enforcement.",
            ],
            'api_routes' => [
                'label' => 'API CRUD Routes',
                'status' => $hasApiRoutes ? 'ok' : 'missing',
                'suggestion' => empty($missingApiMethods) 
                    ? "Declare Route::apiResource('{$pluralKebab}') inside routes/api.php." 
                    : "Add missing endpoints: " . implode(', ', $missingApiMethods) . " inside routes/api.php.",
            ],
            'web_routes' => [
                'label' => 'Web Routes',
                'status' => $hasWebRoutes ? 'ok' : 'missing',
                'suggestion' => "Add page entry routing inside routes/web.php.",
            ],
            'permissions' => [
                'label' => 'Permissions',
                'status' => $hasPermissions ? 'ok' : 'missing',
                'suggestion' => "Add permission hooks for " . implode(', ', $expectedPerms) . " in permissions.json.",
            ],
            'menu' => [
                'label' => 'Sidebar Menu Link',
                'status' => $hasMenu ? 'ok' : 'missing',
                'suggestion' => "Register the route path for '{$pluralKebab}' in menu.json.",
            ],
            'vue_index' => [
                'label' => 'Vue Page - Index',
                'status' => $hasVueIndex ? ($vueIndexOk ? 'ok' : 'warning') : 'missing',
                'suggestion' => !$hasVueIndex 
                    ? "Create resource list view resources/js/pages/{$pluralKebab}/index.vue."
                    : "Ensure resources/js/pages/{$pluralKebab}/index.vue contains a data table grid.",
            ],
            'vue_create' => [
                'label' => 'Vue Page - Create',
                'status' => $hasVueCreate ? 'ok' : 'missing',
                'suggestion' => "Create form editor view resources/js/pages/{$pluralKebab}/create.vue.",
            ],
            'vue_edit' => [
                'label' => 'Vue Page - Edit',
                'status' => $hasVueEdit ? 'ok' : 'missing',
                'suggestion' => "Create form updater view resources/js/pages/{$pluralKebab}/edit.vue.",
            ],
            'vue_show' => [
                'label' => 'Vue Page - Detail',
                'status' => $hasVueShow ? 'ok' : 'missing',
                'suggestion' => "Create read-only detail view resources/js/pages/{$pluralKebab}/show.vue.",
            ],
            'test' => [
                'label' => 'Feature Tests',
                'status' => $hasTest ? 'ok' : 'missing',
                'suggestion' => "Implement tests/Feature/{$modelName}Test.php.",
            ],
            'localization' => [
                'label' => 'Localizations',
                'status' => $hasLocalization ? 'ok' : 'missing',
                'suggestion' => "Add dynamic UI translations in language/ folder.",
            ],
            'settings' => [
                'label' => 'Settings Manifest',
                'status' => $hasSettings ? 'ok' : 'missing',
                'suggestion' => "Create settings.json configuration template.",
            ],
            'documentation' => [
                'label' => 'Documentation',
                'status' => $hasDocs ? 'ok' : 'missing',
                'suggestion' => "Write README.md file in the plugin root.",
            ],
            'assets' => [
                'label' => 'Static Assets',
                'status' => $hasAssets ? 'ok' : 'missing',
                'suggestion' => "Declare static assets inside Assets/ folder.",
            ],
        ];
    }

    public function getModels(): array
    {
        if (empty($this->models)) {
            $this->discoverModels();
        }
        return $this->models;
    }
}
