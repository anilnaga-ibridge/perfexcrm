<?php

namespace App\Services\Validation;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginScaffolder
{
    private string $path;
    private string $alias;
    private bool $dryRun = false;
    private array $writtenFiles = [];

    public function __construct(string $path, string $alias, bool $dryRun = false)
    {
        $this->path = rtrim($path, '/');
        $this->alias = $alias;
        $this->dryRun = $dryRun;
    }

    /**
     * Get files that were (or would be) created.
     */
    public function getWrittenFiles(): array
    {
        return $this->writtenFiles;
    }

    /**
     * Scaffold missing components for a given model based on the target level.
     */
    public function scaffold(string $modelName, array $modelInfo, string $level, array $currentAudit): void
    {
        $pluralKebab = Str::plural(Str::kebab($modelName));
        $pluralSnake = Str::plural(Str::snake($modelName));
        $nsPrefix = Str::studly($this->alias);

        // 1. Migration
        if ($currentAudit['migration']['status'] !== 'ok' && in_array($level, ['all', 'crud', 'backend'])) {
            $this->scaffoldMigration($modelName, $modelInfo);
        }

        // 2. Factory
        if ($currentAudit['factory']['status'] !== 'ok' && in_array($level, ['all', 'backend', 'tests'])) {
            $this->scaffoldFactory($modelName, $modelInfo);
        }

        // 3. Seeder
        if ($currentAudit['seeder']['status'] !== 'ok' && in_array($level, ['all', 'backend'])) {
            $this->scaffoldSeeder($modelName, $modelInfo);
        }

        // 4. Requests (Store & Update)
        if (($currentAudit['store_request']['status'] !== 'ok' || $currentAudit['update_request']['status'] !== 'ok') && in_array($level, ['all', 'crud', 'backend'])) {
            $this->scaffoldRequests($modelName, $modelInfo);
        }

        // 5. Policy
        if ($currentAudit['policy']['status'] !== 'ok' && in_array($level, ['all', 'backend'])) {
            $this->scaffoldPolicy($modelName, $modelInfo);
        }

        // 6. Controller
        if ($currentAudit['controller']['status'] !== 'ok' && in_array($level, ['all', 'crud', 'backend'])) {
            $this->scaffoldController($modelName, $modelInfo, $nsPrefix);
        }

        // 7. API & Web Routes
        if (($currentAudit['api_routes']['status'] !== 'ok' || $currentAudit['web_routes']['status'] !== 'ok') && in_array($level, ['all', 'crud', 'backend'])) {
            $this->scaffoldRoutes($modelName, $modelInfo);
        }

        // 8. Permissions & Menu
        if (($currentAudit['permissions']['status'] !== 'ok' || $currentAudit['menu']['status'] !== 'ok') && in_array($level, ['all', 'crud'])) {
            $this->scaffoldMenuAndPermissions($modelName, $modelInfo);
        }

        // 9. Vue Pages (Index, Create, Edit, Show)
        if (in_array($level, ['all', 'crud', 'frontend'])) {
            $this->scaffoldVuePages($modelName, $modelInfo, $currentAudit);
        }

        // 10. Tests
        if ($currentAudit['test']['status'] !== 'ok' && in_array($level, ['all', 'tests'])) {
            $this->scaffoldTests($modelName, $modelInfo);
        }
    }

    private function writeFile(string $relativePath, string $content): void
    {
        $fullPath = $this->path . '/' . ltrim($relativePath, '/');
        $this->writtenFiles[] = $relativePath;

        if (!$this->dryRun) {
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }
            File::put($fullPath, $content);
        }
    }

    private function scaffoldMigration(string $modelName, array $modelInfo): void
    {
        $tableName = $modelInfo['table'];
        $timestamp = now()->format('Y_m_d_His');
        $filename = "Database/Migrations/{$timestamp}_create_{$tableName}_table.php";

        $columns = "";
        foreach ($modelInfo['fillable'] as $field) {
            if ($field === 'user_id') {
                $columns .= "            \$table->foreignId('user_id')->constrained('users')->onDelete('cascade');\n";
            } elseif (str_ends_with($field, '_id')) {
                $relationTable = Str::plural(str_replace('_id', '', $field));
                $columns .= "            \$table->foreignId('{$field}')->constrained('{$relationTable}')->onDelete('cascade');\n";
            } elseif ($field === 'email') {
                $columns .= "            \$table->string('email')->unique();\n";
            } elseif (in_array($field, ['salary', 'amount', 'price', 'bonus', 'deductions'])) {
                $columns .= "            \$table->decimal('{$field}', 10, 2)->default(0.00);\n";
            } elseif (in_array($field, ['date', 'joining_date', 'leaving_date'])) {
                $columns .= "            \$table->date('{$field}')->nullable();\n";
            } elseif ($field === 'status') {
                $columns .= "            \$table->string('status')->default('active');\n";
            } else {
                $columns .= "            \$table->string('{$field}')->nullable();\n";
            }
        }

        $content = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
{$columns}            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
";
        $this->writeFile($filename, $content);
    }

    private function scaffoldFactory(string $modelName, array $modelInfo): void
    {
        $filename = "Database/Factories/{$modelName}Factory.php";
        $nsPrefix = Str::studly($this->alias);

        $fields = "";
        foreach ($modelInfo['fillable'] as $field) {
            if ($field === 'user_id') {
                $fields .= "            'user_id' => \\App\\Models\\User::factory(),\n";
            } elseif (str_ends_with($field, '_id')) {
                $relatedModel = Str::studly(str_replace('_id', '', $field));
                $fields .= "            '{$field}' => \\Modules\\{$nsPrefix}\\Models\\{$relatedModel}::factory(),\n";
            } elseif ($field === 'email') {
                $fields .= "            'email' => \$this->faker->unique()->safeEmail(),\n";
            } elseif (in_array($field, ['salary', 'amount', 'price', 'bonus', 'deductions'])) {
                $fields .= "            '{$field}' => \$this->faker->randomFloat(2, 1000, 5000),\n";
            } elseif (in_array($field, ['date', 'joining_date', 'leaving_date'])) {
                $fields .= "            '{$field}' => \$this->faker->date(),\n";
            } elseif ($field === 'status') {
                $fields .= "            'status' => 'active',\n";
            } else {
                $fields .= "            '{$field}' => \$this->faker->word(),\n";
            }
        }

        $content = "<?php

namespace Modules\\{$nsPrefix}\\Database\\Factories;

use Illuminate\\Database\\Eloquent\\Factories\\Factory;
use Modules\\{$nsPrefix}\\Models\\{$modelName};

class {$modelName}Factory extends Factory
{
    protected \$model = {$modelName}::class;

    public function definition(): array
    {
        return [
{$fields}        ];
    }
}
";
        $this->writeFile($filename, $content);
    }

    private function scaffoldSeeder(string $modelName, array $modelInfo): void
    {
        $filename = "Database/Seeders/{$modelName}Seeder.php";
        $nsPrefix = Str::studly($this->alias);

        $content = "<?php

namespace Modules\\{$nsPrefix}\\Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Modules\\{$nsPrefix}\\Models\\{$modelName};

class {$modelName}Seeder extends Seeder
{
    public function run(): void
    {
        {$modelName}::factory()->count(10)->create();
    }
}
";
        $this->writeFile($filename, $content);
    }

    private function scaffoldRequests(string $modelName, array $modelInfo): void
    {
        $nsPrefix = Str::studly($this->alias);

        $rules = "";
        foreach ($modelInfo['fillable'] as $field) {
            if ($field === 'user_id') {
                $rules .= "            'user_id' => 'required|exists:users,id',\n";
            } elseif (str_ends_with($field, '_id')) {
                $relatedTable = Str::plural(str_replace('_id', '', $field));
                $rules .= "            '{$field}' => 'required|exists:{$relatedTable},id',\n";
            } elseif ($field === 'email') {
                $rules .= "            'email' => 'required|email|max:255',\n";
            } elseif (in_array($field, ['salary', 'amount', 'price', 'bonus', 'deductions'])) {
                $rules .= "            '{$field}' => 'required|numeric|min:0',\n";
            } elseif (in_array($field, ['date', 'joining_date', 'leaving_date'])) {
                $rules .= "            '{$field}' => 'required|date',\n";
            } elseif ($field === 'status') {
                $rules .= "            'status' => 'required|string|in:active,inactive',\n";
            } else {
                $rules .= "            '{$field}' => 'required|string|max:255',\n";
            }
        }

        $storeContent = "<?php

namespace Modules\\{$nsPrefix}\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class Store{$modelName}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
{$rules}        ];
    }
}
";
        $this->writeFile("Http/Requests/Store{$modelName}Request.php", $storeContent);

        // Update request: similar, but fields are 'sometimes|...'
        $updateRules = str_replace("'required|", "'sometimes|required|", $rules);
        $updateContent = "<?php

namespace Modules\\{$nsPrefix}\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class Update{$modelName}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
{$updateRules}        ];
    }
}
";
        $this->writeFile("Http/Requests/Update{$modelName}Request.php", $updateContent);
    }

    private function scaffoldPolicy(string $modelName, array $modelInfo): void
    {
        $filename = "Policies/{$modelName}Policy.php";
        $nsPrefix = Str::studly($this->alias);
        $permPrefix = Str::snake($modelName);

        $content = "<?php

namespace Modules\\{$nsPrefix}\\Policies;

use App\\Models\\User;
use Illuminate\\Auth\\Access\\HandlesAuthorization;

class {$modelName}Policy
{
    use HandlesAuthorization;

    public function viewAny(User \$user): bool
    {
        return \$user->hasPermission('{$permPrefix}.view');
    }

    public function view(User \$user): bool
    {
        return \$user->hasPermission('{$permPrefix}.view');
    }

    public function create(User \$user): bool
    {
        return \$user->hasPermission('{$permPrefix}.create');
    }

    public function update(User \$user): bool
    {
        return \$user->hasPermission('{$permPrefix}.edit');
    }

    public function delete(User \$user): bool
    {
        return \$user->hasPermission('{$permPrefix}.delete');
    }
}
";
        $this->writeFile($filename, $content);
    }

    private function scaffoldController(string $modelName, array $modelInfo, string $nsPrefix): void
    {
        $filename = "Http/Controllers/Api/{$modelName}Controller.php";
        $pluralVar = lcfirst(Str::plural($modelName));
        $singularVar = lcfirst($modelName);

        $withRelations = "";
        if (!empty($modelInfo['relations'])) {
            $relNames = array_column($modelInfo['relations'], 'name');
            $withRelations = "->with([" . implode(', ', array_map(fn($r) => "'{$r}'", $relNames)) . "])";
        }

        $content = "<?php

namespace Modules\\{$nsPrefix}\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Modules\\{$nsPrefix}\\Models\\{$modelName};
use Modules\\{$nsPrefix}\\Http\\Requests\\Store{$modelName}Request;
use Modules\\{$nsPrefix}\\Http\\Requests\\Update{$modelName}Request;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;

class {$modelName}Controller extends Controller
{
    public function __construct()
    {
        \$this->middleware('auth:sanctum');
    }

    public function index(Request \$request): JsonResponse
    {
        \$query = {$modelName}::query(){$withRelations};
        if (\$request->has('search') && \$request->search !== '') {
            \$search = \$request->search;
            // Basic search on fillable string columns
            \$query->where(function (\$q) use (\$search) {
                \$q->where('id', 'like', \"%\$search%\");
            });
        }
        \${$pluralVar} = \$query->paginate(15);
        return response()->json(['success' => true, 'data' => \${$pluralVar}]);
    }

    public function store(Store{$modelName}Request \$request): JsonResponse
    {
        \${$singularVar} = {$modelName}::create(\$request->validated());
        return response()->json(['success' => true, 'message' => '{$modelName} created successfully.', 'data' => \${$singularVar}], 201);
    }

    public function show(\$id): JsonResponse
    {
        \${$singularVar} = {$modelName}::{$withRelations}->findOrFail(\$id);
        return response()->json(['success' => true, 'data' => \${$singularVar}]);
    }

    public function update(Update{$modelName}Request \$request, \$id): JsonResponse
    {
        \${$singularVar} = {$modelName}::findOrFail(\$id);
        \${$singularVar}->update(\$request->validated());
        return response()->json(['success' => true, 'message' => '{$modelName} updated successfully.', 'data' => \${$singularVar}]);
    }

    public function destroy(\$id): JsonResponse
    {
        \${$singularVar} = {$modelName}::findOrFail(\$id);
        \${$singularVar}->delete();
        return response()->json(['success' => true, 'message' => '{$modelName} deleted successfully.']);
    }
}
";
        $this->writeFile($filename, $content);
    }

    private function scaffoldRoutes(string $modelName, array $modelInfo): void
    {
        $pluralKebab = Str::plural(Str::kebab($modelName));
        $nsPrefix = Str::studly($this->alias);

        // API routes
        $apiFile = 'routes/api.php';
        $apiContent = "";
        if (File::exists($this->path . '/' . $apiFile)) {
            $apiContent = File::get($this->path . '/' . $apiFile);
        }

        if (!str_contains($apiContent, $pluralKebab)) {
            if (empty(trim($apiContent))) {
                $apiContent = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n";
            }
            $apiContent .= "Route::apiResource('{$pluralKebab}', {$modelName}Controller::class);\n";
            $this->writeFile($apiFile, $apiContent);
        }

        // Web routes (for frontend pages redirect)
        $webFile = 'routes/web.php';
        $webContent = "";
        if (File::exists($this->path . '/' . $webFile)) {
            $webContent = File::get($this->path . '/' . $webFile);
        }

        if (!str_contains($webContent, $pluralKebab)) {
            if (empty(trim($webContent))) {
                $webContent = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n";
            }
            // Direct redirects or views
            $webContent .= "Route::middleware(['web', 'auth'])->prefix('modules/{$this->alias}')->group(function () {\n";
            $webContent .= "    Route::get('{$pluralKebab}', function() { return view('{$this->alias}::layout'); });\n";
            $webContent .= "});\n";
            $this->writeFile($webFile, $webContent);
        }
    }

    private function scaffoldMenuAndPermissions(string $modelName, array $modelInfo): void
    {
        $pluralKebab = Str::plural(Str::kebab($modelName));
        $titlePlural = Str::title(str_replace('-', ' ', $pluralKebab));
        $permPrefix = Str::snake($modelName);

        // 1. permissions.json
        $perms = [];
        if (File::exists($this->path . '/permissions.json')) {
            $perms = json_decode(File::get($this->path . '/permissions.json'), true) ?: [];
        }

        $expectedPerms = [
            ['name' => "{$permPrefix}.view", 'description' => "View {$titlePlural}"],
            ['name' => "{$permPrefix}.create", 'description' => "Create {$titlePlural}"],
            ['name' => "{$permPrefix}.edit", 'description' => "Edit {$titlePlural}"],
            ['name' => "{$permPrefix}.delete", 'description' => "Delete {$titlePlural}"],
        ];

        foreach ($expectedPerms as $ep) {
            $exists = false;
            foreach ($perms as $p) {
                if (($p['name'] ?? $p['key'] ?? '') === $ep['name']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $perms[] = $ep;
            }
        }
        $this->writeFile('permissions.json', json_encode($perms, JSON_PRETTY_PRINT));

        // 2. menu.json
        $menu = [];
        if (File::exists($this->path . '/menu.json')) {
            $menu = json_decode(File::get($this->path . '/menu.json'), true) ?: [];
        }

        if (empty($menu)) {
            $menu = [
                'title' => Str::title(str_replace('-', ' ', $this->alias)),
                'icon' => 'appstore',
                'children' => []
            ];
        }

        $routePath = '/' . $pluralKebab;
        $childExists = false;
        if (isset($menu['children'])) {
            foreach ($menu['children'] as $child) {
                if (($child['route'] ?? '') === $routePath) {
                    $childExists = true;
                    break;
                }
            }
            if (!$childExists) {
                $menu['children'][] = [
                    'title' => $titlePlural,
                    'route' => $routePath,
                    'permission' => "{$permPrefix}.view"
                ];
            }
        }
        $this->writeFile('menu.json', json_encode($menu, JSON_PRETTY_PRINT));
    }

    private function scaffoldVuePages(string $modelName, array $modelInfo, array $currentAudit): void
    {
        $pluralKebab = Str::plural(Str::kebab($modelName));
        $singularKebab = Str::kebab($modelName);
        $titlePlural = Str::title(str_replace('-', ' ', $pluralKebab));
        $titleSingular = Str::title(str_replace('-', ' ', $singularKebab));

        // Index page with DataTable
        if ($currentAudit['vue_index']['status'] !== 'ok') {
            $this->scaffoldVueIndex($modelName, $modelInfo, $pluralKebab, $titlePlural);
        }

        // Create page
        if ($currentAudit['vue_create']['status'] !== 'ok') {
            $this->scaffoldVueCreate($modelName, $modelInfo, $pluralKebab, $titleSingular);
        }

        // Edit page
        if ($currentAudit['vue_edit']['status'] !== 'ok') {
            $this->scaffoldVueEdit($modelName, $modelInfo, $pluralKebab, $titleSingular);
        }

        // Show page
        if ($currentAudit['vue_show']['status'] !== 'ok') {
            $this->scaffoldVueShow($modelName, $modelInfo, $pluralKebab, $titleSingular);
        }
    }

    private function getFormInputs(array $modelInfo): array
    {
        $inputs = [];
        foreach ($modelInfo['fillable'] as $field) {
            $label = Str::title(str_replace(['_', '-'], ' ', $field));
            if ($field === 'user_id') {
                $inputs[] = [
                    'field' => 'user_id',
                    'label' => 'User',
                    'type' => 'select',
                    'options_var' => 'users',
                ];
            } elseif (str_ends_with($field, '_id')) {
                $relationName = str_replace('_id', '', $field);
                $pluralRelation = Str::plural($relationName);
                $inputs[] = [
                    'field' => $field,
                    'label' => Str::title(str_replace(['_', '-'], ' ', $relationName)),
                    'type' => 'select',
                    'options_var' => $pluralRelation,
                ];
            } elseif ($field === 'email') {
                $inputs[] = [
                    'field' => 'email',
                    'label' => 'Email Address',
                    'type' => 'email',
                ];
            } elseif (in_array($field, ['salary', 'amount', 'price', 'bonus', 'deductions'])) {
                $inputs[] = [
                    'field' => $field,
                    'label' => $label,
                    'type' => 'number',
                ];
            } elseif (in_array($field, ['date', 'joining_date', 'leaving_date'])) {
                $inputs[] = [
                    'field' => $field,
                    'label' => $label,
                    'type' => 'date',
                ];
            } elseif ($field === 'status') {
                $inputs[] = [
                    'field' => 'status',
                    'label' => 'Status',
                    'type' => 'status',
                ];
            } else {
                $inputs[] = [
                    'field' => $field,
                    'label' => $label,
                    'type' => 'text',
                ];
            }
        }
        return $inputs;
    }

    private function scaffoldVueIndex(string $modelName, array $modelInfo, string $pluralKebab, string $titlePlural): void
    {
        $filename = "resources/js/pages/{$pluralKebab}/index.vue";
        $columns = "";
        foreach ($modelInfo['fillable'] as $field) {
            $label = Str::title(str_replace(['_', '-'], ' ', $field));
            $columns .= "  { title: '{$label}', dataIndex: '{$field}', key: '{$field}' },\n";
        }

        $content = "<template>
  <div class=\"p-6 space-y-6\">
    <div class=\"flex justify-between items-center\">
      <div>
        <h1 class=\"text-2xl font-bold text-slate-800\">{$titlePlural}</h1>
        <p class=\"text-sm text-slate-500\">View and manage list of {$pluralKebab}</p>
      </div>
      <router-link :to=\"'/admin/module/{$this->alias}/{$pluralKebab}/create'\" class=\"px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm\">
        Add New
      </router-link>
    </div>

    <div class=\"bg-white rounded-xl border border-slate-150 p-6 shadow-sm\">
      <div class=\"flex justify-between items-center mb-4\">
        <input v-model=\"search\" @input=\"fetchData\" type=\"text\" placeholder=\"Search...\" class=\"text-sm border border-slate-200 rounded-lg px-3 py-2 w-64 outline-none focus:ring-1 focus:ring-indigo-500\" />
      </div>

      <div class=\"overflow-x-auto\">
        <table class=\"w-full text-left border-collapse text-sm\">
          <thead>
            <tr class=\"border-b border-slate-100 text-slate-400 font-semibold\">
              <th class=\"py-3 px-4\">ID</th>
" . implode("", array_map(fn($f) => "              <th class=\"py-3 px-4\">" . Str::title(str_replace(['_', '-'], ' ', $f)) . "</th>\n", $modelInfo['fillable'])) . "              <th class=\"py-3 px-4 text-right\">Actions</th>
            </tr>
          </thead>
          <tbody class=\"divide-y divide-slate-100 text-slate-600\">
            <tr v-for=\"item in items\" :key=\"item.id\" class=\"hover:bg-slate-50/50 transition-colors\">
              <td class=\"py-3 px-4 font-mono\">#{{ item.id }}</td>
" . implode("", array_map(fn($f) => "              <td class=\"py-3 px-4\">{{ item.{$f} }}</td>\n", $modelInfo['fillable'])) . "              <td class=\"py-3 px-4 text-right space-x-2\">
                <router-link :to=\"'/admin/module/{$this->alias}/{$pluralKebab}/' + item.id\" class=\"text-xs text-slate-500 hover:text-slate-800\">View</router-link>
                <router-link :to=\"'/admin/module/{$this->alias}/{$pluralKebab}/' + item.id + '/edit'\" class=\"text-xs text-indigo-600 hover:text-indigo-800\">Edit</router-link>
                <button @click=\"deleteItem(item.id)\" class=\"text-xs text-rose-600 hover:text-rose-800\">Delete</button>
              </td>
            </tr>
            <tr v-if=\"items.length === 0\">
              <td colspan=\"" . (count($modelInfo['fillable']) + 2) . "\" class=\"py-8 text-center text-slate-400\">No records found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);
const search = ref('');

const fetchData = async () => {
  try {
    const response = await axios.get('/api/{$pluralKebab}', { params: { search: search.value } });
    items.value = response.data.data.data || response.data.data;
  } catch (err) {
    console.error('Failed to load items:', err);
  }
};

const deleteItem = async (id) => {
  if (confirm('Are you sure you want to delete this item?')) {
    try {
      await axios.delete('/api/{$pluralKebab}/' + id);
      fetchData();
    } catch (err) {
      console.error('Failed to delete item:', err);
    }
  }
};

onMounted(fetchData);
</script>
";
        $this->writeFile($filename, $content);
    }

    private function scaffoldVueCreate(string $modelName, array $modelInfo, string $pluralKebab, string $titleSingular): void
    {
        $filename = "resources/js/pages/{$pluralKebab}/create.vue";
        $singularKebab = Str::kebab($modelName);
        $inputs = $this->getFormInputs($modelInfo);

        $formFields = "";
        $formInit = "";
        $optionsFetch = "";
        $optionsRefs = "";

        foreach ($inputs as $inp) {
            $formInit .= "  {$inp['field']}: '',\n";
            $requiredLabel = "* {$inp['label']}";

            if ($inp['type'] === 'select') {
                $optionsRefs .= "const {$inp['options_var']} = ref([]);\n";
                $optionsFetch .= "    const {$inp['options_var']}Res = await axios.get('/api/{$inp['options_var']}');\n";
                $optionsFetch .= "    {$inp['options_var']}.value = {$inp['options_var']}Res.data.data.data || {$inp['options_var']}Res.data.data;\n";

                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <select v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500 bg-white\">
          <option value=\"\">Select Option</option>
          <option v-for=\"opt in {$inp['options_var']}\" :key=\"opt.id\" :value=\"opt.id\">{{ opt.name || opt.title || opt.email }}</option>
        </select>
      </div>\n";
            } elseif ($inp['type'] === 'date') {
                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <input type=\"date\" v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500\" />
      </div>\n";
            } elseif ($inp['type'] === 'number') {
                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <input type=\"number\" step=\"0.01\" v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500\" placeholder=\"Enter amount\" />
      </div>\n";
            } elseif ($inp['type'] === 'status') {
                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <select v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500 bg-white\">
          <option value=\"active\">Active</option>
          <option value=\"inactive\">Inactive</option>
        </select>
      </div>\n";
            } else {
                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <input type=\"{$inp['type']}\" v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500\" placeholder=\"Enter value\" />
      </div>\n";
            }
        }

        $content = "<template>
  <div class=\"p-6 max-w-2xl mx-auto space-y-6\">
    <div>
      <h1 class=\"text-2xl font-bold text-slate-800\">Create {$titleSingular}</h1>
      <p class=\"text-sm text-slate-500\">Add a new {$singularKebab} record</p>
    </div>

    <form @submit.prevent=\"submitForm\" class=\"bg-white rounded-xl border border-slate-150 p-6 shadow-sm space-y-4\">
{$formFields}      <div class=\"flex justify-end gap-3 pt-2\">
        <router-link :to=\"'/admin/module/{$this->alias}/{$pluralKebab}'\" class=\"px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-sm transition font-medium\">
          Cancel
        </router-link>
        <button type=\"submit\" class=\"px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm\">
          Save Record
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const form = ref({
{$formInit}});

{$optionsRefs}

const loadOptions = async () => {
  try {
{$optionsFetch}  } catch (err) {
    console.error('Failed to load relation options:', err);
  }
};

const submitForm = async () => {
  try {
    await axios.post('/api/{$pluralKebab}', form.value);
    router.push('/admin/module/{$this->alias}/{$pluralKebab}');
  } catch (err) {
    console.error('Failed to submit form:', err);
  }
};

onMounted(loadOptions);
</script>
";
        $this->writeFile($filename, $content);
    }

    private function scaffoldVueEdit(string $modelName, array $modelInfo, string $pluralKebab, string $titleSingular): void
    {
        $filename = "resources/js/pages/{$pluralKebab}/edit.vue";
        $inputs = $this->getFormInputs($modelInfo);

        $formFields = "";
        $formInit = "";
        $optionsFetch = "";
        $optionsRefs = "";

        foreach ($inputs as $inp) {
            $formInit .= "  {$inp['field']}: '',\n";
            $requiredLabel = "* {$inp['label']}";

            if ($inp['type'] === 'select') {
                $optionsRefs .= "const {$inp['options_var']} = ref([]);\n";
                $optionsFetch .= "    const {$inp['options_var']}Res = await axios.get('/api/{$inp['options_var']}');\n";
                $optionsFetch .= "    {$inp['options_var']}.value = {$inp['options_var']}Res.data.data.data || {$inp['options_var']}Res.data.data;\n";

                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <select v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500 bg-white\">
          <option value=\"\">Select Option</option>
          <option v-for=\"opt in {$inp['options_var']}\" :key=\"opt.id\" :value=\"opt.id\">{{ opt.name || opt.title || opt.email }}</option>
        </select>
      </div>\n";
            } elseif ($inp['type'] === 'date') {
                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <input type=\"date\" v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500\" />
      </div>\n";
            } elseif ($inp['type'] === 'number') {
                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <input type=\"number\" step=\"0.01\" v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500\" />
      </div>\n";
            } elseif ($inp['type'] === 'status') {
                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <select v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500 bg-white\">
          <option value=\"active\">Active</option>
          <option value=\"inactive\">Inactive</option>
        </select>
      </div>\n";
            } else {
                $formFields .= "      <div>
        <label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5\">{$requiredLabel}</label>
        <input type=\"{$inp['type']}\" v-model=\"form.{$inp['field']}\" required class=\"w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500\" />
      </div>\n";
            }
        }

        $content = "<template>
  <div class=\"p-6 max-w-2xl mx-auto space-y-6\">
    <div>
      <h1 class=\"text-2xl font-bold text-slate-800\">Edit {$titleSingular}</h1>
      <p class=\"text-sm text-slate-500\">Modify record details</p>
    </div>

    <form @submit.prevent=\"submitForm\" class=\"bg-white rounded-xl border border-slate-150 p-6 shadow-sm space-y-4\">
{$formFields}      <div class=\"flex justify-end gap-3 pt-2\">
        <router-link :to=\"'/admin/module/{$this->alias}/{$pluralKebab}'\" class=\"px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-sm transition font-medium\">
          Cancel
        </router-link>
        <button type=\"submit\" class=\"px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition shadow-sm\">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const form = ref({
{$formInit}});

{$optionsRefs}

const loadData = async () => {
  try {
    const id = route.params.id;
    const response = await axios.get('/api/{$pluralKebab}/' + id);
    form.value = response.data.data;
  } catch (err) {
    console.error('Failed to load record details:', err);
  }
};

const loadOptions = async () => {
  try {
{$optionsFetch}  } catch (err) {
    console.error('Failed to load relation options:', err);
  }
};

const submitForm = async () => {
  try {
    const id = route.params.id;
    await axios.put('/api/{$pluralKebab}/' + id, form.value);
    router.push('/admin/module/{$this->alias}/{$pluralKebab}');
  } catch (err) {
    console.error('Failed to submit form:', err);
  }
};

onMounted(async () => {
  await loadOptions();
  await loadData();
});
</script>
";
        $this->writeFile($filename, $content);
    }

    private function scaffoldVueShow(string $modelName, array $modelInfo, string $pluralKebab, string $titleSingular): void
    {
        $filename = "resources/js/pages/{$pluralKebab}/show.vue";

        $rows = "";
        foreach ($modelInfo['fillable'] as $field) {
            $label = Str::title(str_replace(['_', '-'], ' ', $field));
            $rows .= "        <div class=\"flex justify-between items-center py-3\">\n";
            $rows .= "          <span class=\"text-xs font-semibold text-slate-400 uppercase tracking-wider\">{$label}</span>\n";
            $rows .= "          <span class=\"text-sm text-slate-800 font-medium\">{{ item.{$field} }}</span>\n";
            $rows .= "        </div>\n";
        }

        $content = "<template>
  <div class=\"p-6 max-w-2xl mx-auto space-y-6\">
    <div class=\"flex justify-between items-center\">
      <div>
        <h1 class=\"text-2xl font-bold text-slate-800\">{$titleSingular} Details</h1>
        <p class=\"text-sm text-slate-500\">Detailed profile values for record #{{ item.id }}</p>
      </div>
      <router-link :to=\"'/admin/module/{$this->alias}/{$pluralKebab}'\" class=\"px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-sm font-semibold transition\">
        Back to List
      </router-link>
    </div>

    <div class=\"bg-white rounded-xl border border-slate-150 p-6 shadow-sm divide-y divide-slate-100\">
      <div class=\"flex justify-between items-center py-3\">
        <span class=\"text-xs font-semibold text-slate-400 uppercase tracking-wider\">Record ID</span>
        <span class=\"text-sm font-mono text-slate-800 font-medium\">#{{ item.id }}</span>
      </div>
{$rows}    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const item = ref({});

const loadData = async () => {
  try {
    const id = route.params.id;
    const response = await axios.get('/api/{$pluralKebab}/' + id);
    item.value = response.data.data;
  } catch (err) {
    console.error('Failed to load item:', err);
  }
};

onMounted(loadData);
</script>
";
        $this->writeFile($filename, $content);
    }

    private function scaffoldTests(string $modelName, array $modelInfo): void
    {
        $filename = "tests/Feature/{$modelName}Test.php";
        $nsPrefix = Str::studly($this->alias);
        $pluralKebab = Str::plural(Str::kebab($modelName));
        $tableName = $modelInfo['table'];

        $content = "<?php

namespace Modules\\{$nsPrefix}\\Tests\\Feature;

use Tests\\TestCase;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use App\\Models\\User;
use Modules\\{$nsPrefix}\\Models\\{$modelName};

class {$modelName}Test extends TestCase
{
    use RefreshDatabase;

    private User \$admin;

    protected function setUp(): void
    {
        parent::setUp();
        \$this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_list_records(): void
    {
        {$modelName}::factory()->count(3)->create();

        \$response = \$this->actingAs(\$this->admin)
            ->getJson('/api/{$pluralKebab}');

        \$response->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_can_create_record(): void
    {
        \$payload = {$modelName}::factory()->make()->toArray();

        \$response = \$this->actingAs(\$this->admin)
            ->postJson('/api/{$pluralKebab}', \$payload);

        \$response->assertStatus(201);
        \$this->assertDatabaseHas('{$tableName}', [
            'id' => \$response->json('data.id')
        ]);
    }

    public function test_can_read_record(): void
    {
        \$record = {$modelName}::factory()->create();

        \$response = \$this->actingAs(\$this->admin)
            ->getJson('/api/{$pluralKebab}/' . \$record->id);

        \$response->assertStatus(200)
            ->assertJsonPath('data.id', \$record->id);
    }

    public function test_can_update_record(): void
    {
        \$record = {$modelName}::factory()->create();
        \$payload = ['status' => 'inactive'];

        \$response = \$this->actingAs(\$this->admin)
            ->putJson('/api/{$pluralKebab}/' . \$record->id, \$payload);

        \$response->assertStatus(200);
        \$this->assertDatabaseHas('{$tableName}', [
            'id' => \$record->id,
            'status' => 'inactive'
        ]);
    }

    public function test_can_delete_record(): void
    {
        \$record = {$modelName}::factory()->create();

        \$response = \$this->actingAs(\$this->admin)
            ->deleteJson('/api/{$pluralKebab}/' . \$record->id);

        \$response->assertStatus(200);
        \$this->assertDatabaseMissing('{$tableName}', [
            'id' => \$record->id
        ]);
    }
}
";
        $this->writeFile($filename, $content);
    }
}
