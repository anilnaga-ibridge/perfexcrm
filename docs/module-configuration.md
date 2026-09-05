# Module Configuration System — Design Document

**Status:** Draft  
**Version:** 1  
**Applies to:** Module System v1.0+

---

## 1. Overview

Provide a generic, declarative configuration framework so every module can define
its own settings via a schema file, and the core renders the UI, validates
input, and persists values — without core knowing about any specific module.

---

## 2. Folder Structure

```
Modules/{alias}/
├── module.json
├── settings.json              <-- new
├── permissions.json
├── menu.json
├── routes/web.php
└── ...

app/
├── Http/
│   └── Controllers/
│       └── Api/
│           └── ModuleSettingsController.php    <-- new
├── Models/
│   └── ModuleSetting.php                       <-- new
├── Services/
│   ├── ModuleManager.php
│   ├── ModuleSettingsService.php               <-- new — central abstraction
│   └── ModuleSettingValidator.php              <-- new

resources/js/
├── main/
│   ├── components/
│   │   └── ModuleSettingsForm.vue      <-- new — generic form renderer
│   └── views/
│       └── Setup.vue                   ( + settings modal/routing )

database/
└── migrations/
    └── xxxx_xx_xx_create_module_settings_table.php   <-- new

tests/
└── Feature/
    └── ModuleSettingsTest.php          <-- new
```

---

## 3. Manifest (`module.json`)

Add an optional `settings` pointer:

```json
{
  "name": "Payroll",
  "alias": "payroll",
  "version": "1.0.0",
  "settings": {
    "schema": "settings.json"
  }
}
```

- `settings.schema` — relative path inside the module directory.
- Absent → module has no configurable settings (backward compatible).
- `settings_route` in `module.json` continues to work for modules that need a
  fully custom settings page.

---

## 4. Settings Schema (`settings.json`)

### 4.1 Top-level

```json
{
  "schema_version": 1,
  "sections": [ ... ]
}
```

`schema_version` — integer. Stored so the renderer can select the correct
interpreter if the format evolves.

### 4.2 Section

```json
{
  "title": "General",
  "description": "Optional description text",
  "fields": [ ... ]
}
```

### 4.3 Field

```json
{
  "key": "currency",
  "label": "Currency",
  "type": "select",
  "default": "USD",
  "required": true,
  "placeholder": "Select currency",
  "help": "Display text below the field",
  "options": ["USD", "EUR", "INR"],
  "validation": {
    "required": true,
    "min": null,
    "max": null,
    "pattern": null,
    "options": null
  }
}
```

All fields except `key` and `type` are optional.

### 4.4 Supported Field Types (v1)

| Type          | HTML/Component   | Value stored as |
|---------------|------------------|-----------------|
| `text`        | `<input>`        | string          |
| `textarea`    | `<textarea>`     | string          |
| `number`      | `<input type=number>` | float       |
| `boolean`     | `<a-switch>`     | int 0/1         |
| `select`      | `<a-select>`     | string          |
| `multiselect` | `<a-select mode=multiple>` | JSON array |
| `color`       | `<input type=color>` | string      |
| `email`       | `<input type=email>` | string      |
| `url`         | `<input type=url>`   | string      |
| `password`    | `<a-input-password>` | encrypted string |
| `file`        | file upload      | stored path (v1.1) |

### 4.5 Validation Rules (embedded in field)

```json
{
  "key": "tax_rate",
  "type": "number",
  "validation": {
    "required": true,
    "min": 0,
    "max": 100
  }
}
```

Rules are evaluated server-side before persist. Frontend mirrors relevant rules
for instant feedback.

Rule reference for v1:

| Rule       | Applies to           | Behaviour                         |
|------------|----------------------|-----------------------------------|
| required   | all                  | Rejects empty/null                |
| min        | number               | Minimum numeric value             |
| max        | number               | Maximum numeric value             |
| min_length | text, textarea      | Min string length                 |
| max_length | text, textarea      | Max string length                 |
| pattern    | text, email, url    | Regex match                       |
| options    | select, multiselect | Value must be in allowed set      |
| encrypted  | password            | Stored encrypted at rest          |

---

## 5. Database

### 5.1 `module_settings` table

| Column          | Type              | Notes                          |
|-----------------|-------------------|--------------------------------|
| id              | bigint AI PK      |                                |
| module_id       | bigint FK         | `-> modules(id) ON DELETE CASCADE` |
| setting_key     | varchar(255)      |                                |
| setting_value   | text              | JSON-encoded unless scalar     |
| created_at      | timestamp         |                                |
| updated_at      | timestamp         |                                |

**Unique constraint:** `UNIQUE(module_id, setting_key)`

### 5.2 Why key-value instead of JSON column

- Allows partial updates (single key).
- Queryable by key without parsing JSON.
- No schema migration when a module adds new settings.
- Encrypted fields can be stored transparently (Laravel casts).

### 5.3 Model

```php
class ModuleSetting extends Model
{
    protected $fillable = ['module_id', 'setting_key', 'setting_value'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
```

For password fields, a custom cast `$casts = ['setting_value' => 'encrypted']`
can be applied per-key at the service layer.

---

## 6. Service Layer (`ModuleSettingsService`)

All business logic lives here — the controller is a thin pass-through.

```php
class ModuleSettingsService
{
    public function getSchema(Module $module): ?array
    public function getValues(Module $module): array
    public function getSettings(Module $module): ?array  // schema + values
    public function hasValue(Module $module, string $key): bool
    public function save(Module $module, array $values): array
    public function resetToDefaults(Module $module): void
    public function hasSettings(Module $module): bool
}
```

**Data flow:**

```
Controller
    ↓
ModuleSettingsService   ← owns schema parsing, caching, validation,
    ↓                      encryption, defaults, DB reads/writes
ModuleSettingValidator  ← validates values against schema rules
ModuleSetting (Model)   ← thin Eloquent model, no business logic
```

### 6.1 `getSettings()` — merged schema + values

Reads `settings.json` from the module's filesystem, parses it, loads stored
values, merges defaults, and returns both in a single response.

Returns `null` (or throws 404) if the module has no `settings.json`.

### 6.2 `save()` — validate and persist

1. Loads and parses `settings.json`.
2. Passes submitted values + schema to `ModuleSettingValidator::validate()`.
3. Persists validated key-value pairs via `ModuleSetting::updateOrCreate()`.
4. Encrypts `password` type fields before storage.
5. Returns the updated key-value map.

### 6.3 `resetToDefaults()`

Deletes all persisted rows for the module from `module_settings`.
Subsequent `getSettings()` calls will return only the schema defaults.

### 6.4 `hasSettings()`

Returns `true` when `module.json` contains `settings.schema` pointing to an
existing file.

---

## 7. API Contract

Base URL: `/api/modules/{module}/settings`

### 7.1 Get Settings (schema + values merged)

```
GET /api/modules/{module}/settings
```

**Response 200**

```json
{
  "schema": {
    "schema_version": 1,
    "sections": [
      {
        "title": "General",
        "description": null,
        "fields": [
          {
            "key": "currency",
            "label": "Currency",
            "type": "select",
            "default": "USD",
            "required": true,
            "options": ["USD", "EUR", "INR"],
            "validation": { "required": true },
            "help": null
          }
        ]
      }
    ]
  },
  "values": {
    "currency": "USD",
    "tax_rate": 18
  }
}
```

Keys in `values` that have no persisted row but have a `default` in the schema
are returned with their default value.

**404** — module has no `settings.json`.

### 7.2 Save Values

```
PUT /api/modules/{module}/settings
```

**Request body**

```json
{
  "currency": "EUR",
  "tax_rate": 15
}
```

Only submitted keys are updated. Keys absent from the body are left unchanged.

**Validation:** Delegated to `ModuleSettingsService::save()`, which calls the
validator. Returns 422 with per-key errors on failure.

**Response 200**

```json
{
  "message": "Settings saved",
  "values": {
    "currency": "EUR",
    "tax_rate": 15
  }
}
```

### 7.3 Reset to Defaults

```
POST /api/modules/{module}/settings/reset
```

**Response 200**

```json
{
  "message": "Settings reset to defaults",
  "values": {
    "currency": "USD",
    "tax_rate": null
  }
}
```

Deletes all stored rows. Response includes schema defaults merged in.

### 7.4 Module index integration

The existing `GET /api/modules` response gains a `has_settings` boolean:

```json
{
  "id": 1,
  "alias": "payroll",
  "name": "Payroll",
  "has_settings": true,
  "settings_link": null,
  ...
}
```

When both `settings_link` (legacy custom page) and `has_settings` (generic) are
present, the Settings button opens the generic form unless the module also has
`settings_route` in its manifest (which takes precedence, i.e. the legacy custom
page wins).

---

## 8. Vue Rendering Flow

### 8.1 Component Tree

```
Setup.vue (modules section)
│
└── <a-modal> or inline section
    └── <ModuleSettingsForm
           :schema="schema"
           :values="values"
           @save="saveSettings"
         />
```

### 8.2 `ModuleSettingsForm.vue`

**Props**

| Prop     | Type   | Description                      |
|----------|--------|----------------------------------|
| schema   | Object | Parsed settings.json             |
| values   | Object | Current key-value pairs          |
| loading  | Bool   | Loading state                    |
| saving   | Bool   | Save in progress                 |

**Events**

| Event | Payload | Description          |
|-------|---------|----------------------|
| save  | Object  | Emitted on form submit |

**Behaviour**

1. Receives schema + values.
2. Groups fields into sections with `<h3>` section titles.
3. Renders each field via a `<component :is="fieldComponent">` switcher.
4. Manages local draft state via `reactive({})`.
5. Mirrors validation rules from schema:
   - required fields show `*`.
   - number fields clamp min/max on change.
   - pattern fields show inline error.
6. Submit button calls `emit('save', draft)`.
7. Disables form during `saving`.

The frontend makes a single `GET /api/modules/{id}/settings` call on mount to
receive both the schema and current values in one round trip.

### 8.3 Route / Navigation

Two entry points from the modules table:

- **Legacy:** `mod.settings_link` → `router.push()` as implemented.
- **Generic:** `mod.has_settings` → open settings modal/inline editor with
  schema fetched from `GET /api/modules/{id}/settings/schema`.

Both can coexist. If a module has both, the manifest's `settings_route` takes
priority (legacy custom page), falling back to generic.

---

## 9. Validation

### 9.1 Server-side (`ModuleSettingValidator`)

Called by `ModuleSettingsService::save()` before persisting.

```php
class ModuleSettingValidator
{
    public function validate(array $schema, array $values): array
    {
        // Iterate sections → fields.
        // For each field:
        //   1. If required and missing → error.
        //   2. If type=number, min/max → range check.
        //   3. If type in (select, multiselect) and options → in_array check.
        //   4. If pattern → preg_match.
        //   5. Return validated/flattened key-value pairs.
    }
}
```

Returns validated array or throws `ValidationException`.

### 9.2 Client-side (Vue)

- Required fields are marked and checked on blur.
- Number fields clamp on input.
- Select/multiselect options are constrained by schema options.
- Pattern validation on blur.
- Form-level summary errors on submit.

---

## 10. Storage & Encryption

| Setting type  | `setting_value` storage     |
|---------------|-----------------------------|
| text, select  | Plain string                |
| number        | Float as string             |
| boolean       | "0" or "1"                  |
| multiselect   | JSON-encoded array          |
| password      | Laravel `Crypt::encrypt()`  |
| file          | Stored path (future)        |

All values are stored as `text` in the database. JSON-encoded values are decoded
on read when the field type is `multiselect`.

---

## 11. Migration Strategy

### 11.1 From current state to v1

| Step | Action |
|------|--------|
| 1    | Create `module_settings` table migration |
| 2    | Add `ModuleSetting` model |
| 3    | Add `ModuleSettingsService` (owns all logic) |
| 4    | Add `ModuleSettingValidator` |
| 5    | Add `ModuleSettingsController` + routes |
| 6    | Create `ModuleSettingsForm.vue` component |
| 7    | Integrate into `Setup.vue` modules section |
| 8    | Add `has_settings` to `ModuleController@index` response |
| 9    | Wire Settings button to smart-routing (legacy vs generic) |
| 10   | Ship — no existing module is affected |

### 11.2 Module authors

- Add `settings.schema` pointer to `module.json`.
- Create `settings.json` with sections/fields.
- Read settings via `ModuleSetting::where('module_id', $id)->pluck(...)` or
  a future `Module::getSettings()` helper.

---

## 12. Extensibility Points

### 12.1 Custom field components (v1.1)

Deferred from v1. Once the core built-in types (text, number, select, etc.) are
stable, modules can register custom field components:

```js
ModuleSettingsForm.registerFieldType('payroll-tax-table', PayrollTaxTable);
```

Schema field:

```json
{
  "key": "tax_brackets",
  "type": "custom",
  "component": "payroll-tax-table"
}
```

### 12.2 Post-save hook (future)

A `ModuleSettingsSaved` event fired after persist, allowing modules or other
subscribers to react to configuration changes.

### 12.3 Value transformation

Before returning values (GET) the service can apply transforms — decrypt
passwords, decode JSON, apply defaults. Transformers are field-type-aware
so the logic stays centralised.

---

## 13. Backward Compatibility

| Scenario | Behaviour |
|----------|-----------|
| Module has no `settings.json` | `has_settings` = false, Settings button uses legacy `settings_link` or is hidden |
| Module has `settings_route` in `module.json` | Legacy custom page opened (router.push), generic form ignored |
| Module has both `settings` and `settings_route` | `settings_route` takes priority |
| Old `module.json` without `settings` key | No change — Settings button behaviour unchanged |
| `schema_version` mismatch | Renderer checks version; future renderers can support multiple versions |

---

## 14. Open Questions

1. **Should settings be scoped per-environment?** (dev/staging/prod) — Not in v1.
2. **Should we support file uploads in v1?** — No, defer to v1.1.
3. **Should the generic settings form be a modal or inline page?** — Modal is
   simpler for v1, can be promoted to full page later.
4. **Should events fire on setting change?** — Yes, add in Phase 2 (event system).
   Not in v1.
