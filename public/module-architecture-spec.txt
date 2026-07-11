# Perfex CRM — Complete Module System Architecture
(How ANY module works when uploaded)

## THE BIG PICTURE

```
┌─────────────────────────────────────────────────────────────┐
│                    ADMIN BROWSER                            │
│                  Vue 3 SPA (frontend)                       │
│   ┌──────────┐  ┌──────────────┐  ┌─────────────────────┐  │
│   │ Sidebar  │  │ Static Pages │  │  Module Pages       │  │
│   │(dynamic) │  │(Dashboard,   │  │  (Native Vue OR     │  │
│   │          │  │ Invoices...) │  │   SSO iframe)       │  │
│   └──────────┘  └──────────────┘  └─────────────────────┘  │
└───────────────────────┬─────────────────────────────────────┘
                        │ REST API (Axios / JSON)
┌───────────────────────▼─────────────────────────────────────┐
│                  LARAVEL BACKEND                             │
│  ┌─────────────────┐  ┌──────────────────────────────────┐  │
│  │   Core API      │  │  Module Engine                   │  │
│  │  (Clients,      │  │  (install/activate/deactivate/   │  │
│  │   Invoices,     │  │   upgrade/uninstall)             │  │
│  │   Projects...)  │  │                                  │  │
│  └─────────────────┘  └──────────────────────────────────┘  │
└───────────────────────┬─────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────┐
│          MySQL Database  +  Filesystem (Modules/)            │
└─────────────────────────────────────────────────────────────┘
```

---

## PART 1 — WHAT IS A MODULE (ZIP STRUCTURE)

When you upload a module ZIP, it must contain this structure:

```
my-module.zip
└── my-module/
    ├── module.json               ← REQUIRED: Module manifest
    ├── menu.json                 ← Sidebar menu definition
    ├── permissions.json          ← Permission keys
    │
    ├── routes/
    │   ├── web.php               ← Web routes (for legacy/iframe)
    │   └── api.php               ← API routes for this module
    │
    ├── Controllers/              ← PHP Controller classes
    │   └── Api/
    │       └── MyController.php
    │
    ├── Database/
    │   └── Migrations/           ← Laravel migration files
    │       └── 2024_01_01_create_my_table.php
    │
    ├── Views/                    ← Blade templates (legacy, for iframe)
    │
    ├── resources/
    │   └── js/
    │       └── pages/            ← Native Vue pages (modern approach)
    │           ├── dashboard.vue
    │           ├── manage_items.vue
    │           └── reports.vue
    │
    └── install.php               ← Optional legacy install script
    └── uninstall.php             ← Optional cleanup script
    └── upgrade.php               ← Optional upgrade script
```

### module.json — The Manifest (REQUIRED)
Every module MUST have this file. It identifies the module:

```json
{
  "name": "My Custom Module",
  "alias": "my-custom-module",
  "version": "1.0.0",
  "minimum_core_version": "2.3.0",
  "sdk_version": "1.0",
  "description": "Description of what this module does.",
  "author": "Your Name",
  "author_uri": "https://yoursite.com",
  "depends": [],
  "settings_route": null
}
```

| Field | Required | Notes |
| :--- | :--- | :--- |
| **name** | ✅ | Display name shown in UI |
| **alias** | ✅ | kebab-case, used as folder name & URL slug |
| **version** | ✅ | Semver format: 1.0.0 |
| **minimum_core_version** | ✅ | Minimum system version required |
| **sdk_version** | Recommended | Use "1.0" for modern native Vue modules |
| **description** | Optional | Shown in modules list |
| **author** | Optional | Author name |
| **depends** | Optional | Array of alias strings of required modules |
| **settings_route** | Optional | Path to settings page e.g. "settings" |

### menu.json — Sidebar Navigation
Controls what appears in the sidebar when module is active:

```json
{
  "title": "my_module_menu_key",
  "route": "",
  "icon": "fa fa-cog",
  "permission": null,
  "children": [
    {
      "title": "my_module_dashboard",
      "route": "/dashboard",
      "permission": "my_module_dashboard_view"
    },
    {
      "title": "my_module_manage_items",
      "route": "/manage_items",
      "permission": "my_module_items"
    },
    {
      "title": "settings",
      "route": "/settings",
      "permission": "my_module_settings"
    }
  ]
}
```
*Route resolution:* Each child route becomes `/admin/module/{alias}{route}` $\rightarrow$ e.g., `/admin/module/my-custom-module/manage_items`.

### permissions.json — Access Control
Defines what permissions this module needs:

```json
[
  { "key": "my_module_dashboard_view", "description": "View Dashboard" },
  { "key": "my_module_items",          "description": "Manage Items" },
  { "key": "my_module_settings",       "description": "Module Settings" }
]
```

---

## PART 2 — COMPLETE MODULE LIFECYCLE

### STEP 1: User Uploads ZIP
1. Admin goes to **Setup** $\rightarrow$ **Plugins** $\rightarrow$ **Upload Module** button.
2. Sends request: `POST /api/modules` (multipart/form-data, `module_file=my-module.zip`).
3. Handled by: `ModuleController@store`.

### STEP 2: Install Process (ModuleManager::install)
1. ZIP extracted to temp dir (`storage/app/temp_module_extract_{timestamp}/`).
2. System searches for `module.json` (recursively):
   * **Found?** Decodes JSON and extracts info.
   * **Not Found?** 
     * Tries WordPress-style comment headers in main PHP file (legacy CodeIgniter support).
     * Tries nested ZIP files (CodeCanyon double-zip distribution pattern).
     * If still missing $\rightarrow$ Error: "Invalid module ZIP".
3. Alias normalized to kebab-case format.
4. Checks if a module with this alias already exists in the database:
   * **Yes?** Runs the `upgrade()` flow instead (the uploaded version must be higher).
   * **No?** Continues with a fresh installation.
5. Verifies core system compatibility using `minimum_core_version`.
6. Copies module files from temp path to `Modules/{alias}/`.
7. Auto-migrates legacy Vue views:
   * Translates legacy files in `skeleton/resources/js/views/module/*.vue` to `resources/js/pages/*.vue` (PascalCase converted to snake_case matching menu routes).
8. Creates a database record in the `modules` table with status `'installed'`.
9. Executes legacy CodeIgniter `install.php` and runs any files inside the `migrations/` directory (if present, using the CI compatibility layer).
10. Runs modern Laravel migrations:
    `php artisan migrate --path=Modules/{alias}/Database/Migrations`
11. Auto-activates the module immediately.
12. Fires `ModuleInstalled` event.

### STEP 3: Activate Process (ModuleManager::activate)
1. **Dependency check**: All dependencies listed in `depends` must be installed and active.
2. **Filesystem checks**: Checks directory structure and files health.
3. **Register Permissions**: Parses `permissions.json`, creates records in `permissions` and `module_permissions` tables, and auto-assigns the permissions to the `'admin'` role in `role_permissions`.
4. **Register Sidebar Menus**: Parses `menu.json`, registers root and child menu nodes in `module_menus` table. If missing $\rightarrow$ auto-generates menu routes by scanning the Views folder.
5. Sets status to `'active'` in DB.
6. Fires `ModuleActivated` event.
*Result:* Module appears instantly in the sidebar navigation.

### STEP 4: Deactivate
1. **Cascade**: Recursively deactivates all other active modules that depend on this module.
2. Removes all menu nodes from `module_menus` table (items disappear from sidebar).
3. Removes all module permission associations and deletes role assignments from `role_permissions`.
4. Sets status to `'inactive'`.
5. Fires `ModuleDeactivated` event.

### STEP 5: Upgrade (Upload higher version ZIP)
1. System matches alias to an existing module record $\rightarrow$ calls `upgrade()`.
2. Verifies uploaded version is strictly greater than current installed version.
3. Snapshots all dependent modules.
4. Cascades deactivation down the dependency tree.
5. Overwrites `Modules/{alias}/` with the new codebase.
6. Executes `upgrade.php` or scripts in `Upgrades/` folder (if present).
7. Runs any new Laravel migrations.
8. Updates database version number.
9. Re-activates the module.
10. Re-activates dependent modules in topological order.
11. Fires `ModuleUpgraded` event.

### STEP 6: Uninstall
1. Deactivates module (with cascade).
2. Runs custom cleanup script `uninstall.php`.
3. If `delete_data=true`:
   * Identifies table names created by migrations.
   * Backups module data to `storage/app/backups/modules/{alias}_backup_{time}.json`.
   * Rolls back migrations.
   * Runs `Schema::dropIfExists()` for each module table.
4. Deletes module files at `Modules/{alias}/`.
5. Deletes settings and drops database records (automatically cascades to menus, permissions, and settings tables).
6. Fires `ModuleUninstalled` event.

---

## PART 3 — HOW DYNAMIC REGISTRATION WORKS (Laravel Boot)

On every single page load, the `ModuleServiceProvider` boots all active modules:

### `register()`
Loads the Composer class loader at runtime to dynamically define PSR-4 namespaces:
```php
$loader->addPsr4("Modules\\MyCustomModule\\", "Modules/my-custom-module/");
```
*No `composer dump-autoload` required.*

### `boot()`
Enables core integrations dynamically for active modules:
```php
// 1. Web Routes
Route::middleware('web')
     ->namespace('Modules\MyCustomModule\Controllers')
     ->group($webRoutesPath);
  
// 2. API Routes (prefixed with /api/)
Route::middleware('api')
     ->prefix('api')
     ->namespace('Modules\MyCustomModule\Controllers\Api')
     ->group($apiRoutesPath);
  
// 3. Blade View Namespace
$this->loadViewsFrom("Modules/my-custom-module/Views", 'my-custom-module');
  
// 4. Migration Files Loader
$this->loadMigrationsFrom("Modules/my-custom-module/Database/Migrations");
```

---

## PART 4 — FRONTEND — HOW SIDEBAR WORKS

### Static Items
Always rendered on layout boot:
* Dashboard
* Customers
* Sales (Proposals, Estimates, Invoices, Payments, Credit Notes, Items)
* Subscriptions
* Expenses
* Contracts
* Projects
* Tasks
* Support (Tickets)
* Leads
* Estimate Request
* Knowledge Base
* Utilities (Media, Calendar, Announcements, Activity Log, Surveys...)
* Reports (Sales, Expenses, Finance, Leads, Timesheets, KB Articles)
* Setup

### Dynamic Module Items
Fetched on layout initialization:
* Trigger: `GET /plugins/menus`
* Returns active module menu trees from database (filtered by current user role permissions).
* Appended below a separator line in the sidebar layout.
* Renders as a collapsible submenu if child routes are present, or a single link if none.

---

## PART 5 — HOW MODULE PAGES RENDER (ModuleView.vue)

When an admin clicks a dynamic module sidebar item, Vue Router navigates to `/admin/module/{alias}/{...path}` and resolves to `ModuleView.vue`:

### STEP 1: Try Native Vue (ModulePageLoader.js)
System uses Vite dynamic imports at compilation:
`Modules/{alias}/resources/js/pages/{path}.vue`
If a file matches, it is imported and rendered as a native Vue component.

### STEP 2: Custom Settings Route
If the path matches the declared `settings_route` inside `module.json`, it renders the generic settings view (`GenericModuleSettings.vue`).

### STEP 3: SSO Iframe Fallback (Legacy CodeIgniter Support)
1. Browser requests a secure URL: `GET /modules/sso-url?redirect=/plugins/{alias}/{path}`.
2. Backend creates a one-time token (expires in 1 minute, stored in cache) and returns the link:
   `/plugins/sso?token={token}`
3. `ModuleView.vue` renders an `<iframe>` pointing to the SSO URL.
4. Laravel logs in the user session on token match, regenerates the session ID, and redirects the iframe to `/plugins/{alias}/{path}` where the CodeIgniter legacy compatibility layer serves the HTML page.

---

## PART 6 — MODULE SETTINGS SYSTEM

Declare schema details inside `module.json`:
```json
{
  "settings": {
    "schema": "settings-schema.json"
  }
}
```

Define sections and field inputs inside `settings-schema.json`:
```json
{
  "sections": [
    {
      "title": "General",
      "fields": [
        {
          "key": "api_key",
          "label": "API Key",
          "type": "password",
          "default": "",
          "validation": { "required": true }
        },
        {
          "key": "notify_on_complete",
          "label": "Notify on Complete",
          "type": "boolean",
          "default": true
        },
        {
          "key": "max_records",
          "label": "Max Records",
          "type": "number",
          "default": 100,
          "width": 12
        }
      ]
    }
  ]
}
```

### Available Input Field Types
* `text`: Plain text input
* `textarea`: Multiline text
* `password`: Encrypted storage
* `number`: Numeric input
* `boolean`: Toggle switch
* `select`: Dropdown
* `multiselect`: Multi-select dropdown
* `color`: Color picker
* `date`: Date picker
* `datetime`: DateTime picker
* `file`: File upload
* `image`: Image upload
* `markdown`: Markdown editor
* `richtext`: Rich text editor

---

## PART 7 — DATABASE TABLES (Core Module System)

### `modules` table
* `id` (UUID): Primary key.
* `name` (string): Display name.
* `alias` (string): Unique identifier slug.
* `version` (string): Version code.
* `minimum_core_version` (string): Core compatibility.
* `description` (text): Summary info.
* `status` (enum): `'installed'`, `'active'`, or `'inactive'`.
* `author` (string): Developer name.
* `depends` (JSON): Dependency list.

### `module_menus` table
* `id` (bigint): Primary key.
* `module_id` (UUID): Foreign key mapping to modules.
* `parent_id` (bigint): Submenu parent relationship.
* `title` (string): Translation key or display text.
* `route` (string): URL suffix path (e.g. `/manage_items`).
* `icon` (string): Sidebar icon CSS class.
* `permission` (string): Auth permission key.

### `module_permissions` table
* `id` (bigint): Primary key.
* `module_id` (UUID): Foreign key.
* `permission_name` (string): Associated permission string.

### `module_settings` table
* `id` (bigint): Primary key.
* `module_id` (UUID): Foreign key.
* `setting_key` (string): Setting key name.
* `setting_value` (text): Storage value (JSON arrays or Laravel-encrypted strings).

### `module_events` table
* `id` (bigint): Primary key.
* `module_id` (UUID): Foreign key.
* `module_alias` (string): Module slug.
* `event_name` (string): Installed, Activated, etc.
* `payload` (JSON): Context parameters.

---

## PART 8 — ALL API ENDPOINTS FOR MODULES

| Method | Endpoint | Purpose |
| :--- | :--- | :--- |
| **GET** | `/api/modules` | List all installed modules |
| **POST** | `/api/modules` | Upload & install ZIP |
| **GET** | `/api/modules/{id}` | Get single module |
| **PUT** | `/api/modules/{id}/activate` | Activate module |
| **PUT** | `/api/modules/{id}/deactivate` | Deactivate module |
| **PUT** | `/api/modules/{id}/toggle-status` | Toggle active/inactive |
| **DELETE** | `/api/modules/{id}?delete_data=true` | Uninstall module |
| **POST** | `/api/modules/{id}/repair` | Repair module configs |
| **POST** | `/api/modules/{id}/rollback` | Rollback migrations |
| **GET** | `/plugins/active` | Active modules (sidebar check) |
| **GET** | `/plugins/menus` | Module sidebar menus |
| **GET** | `/modules/sso-url?redirect=...` | Get SSO token URL |
| **GET** | `/plugins/sso?token=...` | SSO login + redirect |
| **GET** | `/api/modules/{alias}/settings` | Get settings schema + values |
| **POST** | `/api/modules/{alias}/settings` | Save settings |
| **DELETE** | `/api/modules/{alias}/settings` | Reset settings to defaults |

---

## PART 9 — LEGACY CODEIGNITER SUPPORT

### Legacy Compatibility Layer
Upon initialization/activation:
* Evaluates dynamic CodeIgniter core functions (`BASEPATH`, `db_prefix()`, `get_instance()`).
* Provides stub database class `$CI->db` translating legacy CI active record queries to Laravel Query Builder.
* Executes module's `install.php` and runs migrations found in the `migrations/` directory.
* Intercepts `tbl_modules` queries and shifts columns dynamically (`active=1` $\rightarrow$ `status='active'`).

### Rendering Modes
* **Native Vue**: Lazy-loaded page components inside `/resources/js/pages/`.
* **SSO Iframe**: Secure session redirect rendering CodeIgniter layouts inside iframe.

---

## PART 10 — DEPENDENCY MANAGEMENT

Modules declare requirements in their manifest:
```json
{
  "alias": "advanced-reports",
  "depends": ["hr-payroll", "another-module"]
}
```

* **Guard Rails**:
  * Cannot activate if dependencies are missing or inactive.
  * Deactivation cascades down (deactivating a module turns off all dependents).
  * Upgrades automatically resolve dependency trees using Kahn's topological sort.
  * Checks for circular dependencies and fails if detected.
