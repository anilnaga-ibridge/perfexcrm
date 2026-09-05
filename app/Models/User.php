<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'role_id', 'profile_image', 'active', 'phone', 'department', 'hourly_rate', 'facebook', 'linkedin', 'skype', 'default_language', 'email_signature', 'direction', 'permissions', 'last_login'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($user) {
            \Illuminate\Support\Facades\Cache::forget("user_perm_{$user->id}");
        });
    }

    public function hasPermission(string $permission, ?string $action = null): bool
    {
        if ($action !== null) {
            $permission = $permission . '.' . $action;
        }

        // 1. Super Admin bypass (explicit admin flag check)
        if ($this->admin == 1 || $this->admin === '1' || $this->is_admin == 1 || $this->is_admin === true || $this->getRawOriginal('role') === 'admin') {
            return true;
        }

        $roleRelation = $this->relationLoaded('role') ? $this->getRelation('role') : ($this->role_id ? Role::find($this->role_id) : null);
        if ($roleRelation && ($roleRelation->slug === 'admin' || strtolower($roleRelation->name) === 'admin')) {
            return true;
        }

        $normalize = function ($str) {
            return strtolower(str_replace(['_', '-'], ' ', trim((string) $str)));
        };

        $feature = $permission;
        $action = null;
        if (str_contains($permission, '.')) {
            [$feature, $action] = explode('.', $permission, 2);
        }

        $normFeature = $normalize($feature);
        $normAction = $action ? $normalize($action) : null;

        $targetAliases = \App\Permissions\PermissionRegistry::getAliases($feature);

        // Helper closure to lookup inside a permissions JSON structure
        $checkJson = function ($permsMap) use ($targetAliases, $normAction, $permission, $normalize) {
            if (!is_array($permsMap) || empty($permsMap)) {
                return null;
            }

            // Direct exact key check (e.g., 'Customers.create' => true/false)
            if (array_key_exists($permission, $permsMap) && !is_array($permsMap[$permission])) {
                return (bool) $permsMap[$permission];
            }

            $foundMatch = false;

            // Feature matching with key normalization
            foreach ($permsMap as $featKey => $actions) {
                if (in_array($normalize($featKey), $targetAliases)) {
                    // Array of action strings (e.g. ['view', 'create'])
                    if (is_array($actions) && array_is_list($actions)) {
                        if ($normAction === null) return count($actions) > 0;
                        foreach ($actions as $actVal) {
                            if ($normalize($actVal) === $normAction) return true;
                            if ($normAction === 'view' && ($normalize($actVal) === 'view global' || $normalize($actVal) === 'view own')) return true;
                        }
                        $foundMatch = true;
                        continue;
                    }

                    // Key-value array of actions (e.g. {'view_global': 1, 'create': 0})
                    if (is_array($actions)) {
                        if ($normAction !== null) {
                            foreach ($actions as $actKey => $val) {
                                if ($normalize($actKey) === $normAction) {
                                    return (bool) $val;
                                }
                                if ($normAction === 'view' && ($normalize($actKey) === 'view global' || $normalize($actKey) === 'view own')) {
                                    if ((bool) $val) return true;
                                }
                            }
                            $foundMatch = true;
                            continue;
                        }
                        if (array_sum(array_map(function($v) { return filter_var($v, FILTER_VALIDATE_BOOLEAN) ? 1 : 0; }, array_values($actions))) > 0) {
                            return true;
                        }
                        $foundMatch = true;
                        continue;
                    }

                    if (is_bool($actions)) {
                        if ($actions) return true;
                        $foundMatch = true;
                        continue;
                    }
                }
            }

            return $foundMatch ? false : null;

            return null;
        };

        // 2. Check direct user JSON permission overrides
        $userResult = $checkJson($this->permissions);
        if ($userResult !== null) {
            return $userResult;
        }

        // 3. Check Role JSON permissions array
        if ($roleRelation) {
            $roleResult = $checkJson($roleRelation->permissions);
            if ($roleResult !== null) {
                return $roleResult;
            }
        }

        // 4. Check relational permissions via roles many-to-many relationship
        foreach ($this->roles as $r) {
            if (method_exists($r, 'permissionRecords') && $r->permissionRecords()->where('name', $permission)->exists()) {
                return true;
            }
        }

        // 5. Check relational permissions via direct role
        if ($roleRelation && method_exists($roleRelation, 'permissionRecords') && $roleRelation->permissionRecords()->where('name', $permission)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Defensively decode a permissions attribute value into an array,
     * unwrapping any accidentally double-encoded JSON strings.
     */
    public static function decodePermissionsJson($value): array
    {
        $perms = $value;
        for ($i = 0; $i < 2 && is_string($perms); $i++) {
            $decoded = json_decode($perms, true);
            $perms = json_last_error() === JSON_ERROR_NONE ? $decoded : $perms;
        }
        return is_array($perms) ? $perms : [];
    }

    public static function normalizePermissionsArray($permissions): array
    {
        if (!is_array($permissions)) {
            return [];
        }

        $normalized = [];
        foreach ($permissions as $feature => $caps) {
            if (!is_array($caps)) {
                $normalized[$feature] = filter_var($caps, FILTER_VALIDATE_BOOLEAN);
                continue;
            }

            if (!array_is_list($caps)) {
                $normCaps = [];
                foreach ($caps as $cap => $value) {
                    $normCaps[$cap] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
                $normalized[$feature] = $normCaps;
                continue;
            }

            // Legacy list format: ['view', 'create'] means those capabilities are enabled.
            $normCaps = [];
            foreach ($caps as $value) {
                if (is_string($value) && $value !== '') {
                    $normCaps[$value] = true;
                }
            }
            // Boolean-only lists carry no recoverable capability names and are dropped.
            if ($normCaps !== []) {
                $normalized[$feature] = $normCaps;
            }
        }

        return $normalized;
    }

    /**
     * Extract only the capabilities that differ from the role defaults.
     * Used to store per-user permission overrides instead of full snapshots,
     * so role edits keep propagating to users without overrides.
     */
    public static function diffPermissionsAgainstRole(array $permissions, ?Role $role): array
    {
        $rolePerms = ($role && is_array($role->permissions)) ? $role->permissions : [];
        $overrides = [];

        foreach ($permissions as $feature => $caps) {
            if (!is_array($caps)) {
                $enabled = filter_var($caps, FILTER_VALIDATE_BOOLEAN);
                $default = isset($rolePerms[$feature]) && !is_array($rolePerms[$feature])
                    ? filter_var($rolePerms[$feature], FILTER_VALIDATE_BOOLEAN)
                    : false;
                if ($enabled !== $default) {
                    $overrides[$feature] = $enabled;
                }
                continue;
            }

            foreach ($caps as $cap => $value) {
                if (is_array($value)) {
                    continue;
                }
                $enabled = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $default = isset($rolePerms[$feature][$cap]) && !is_array($rolePerms[$feature][$cap])
                    ? filter_var($rolePerms[$feature][$cap], FILTER_VALIDATE_BOOLEAN)
                    : false;
                if ($enabled !== $default) {
                    $overrides[$feature][$cap] = $enabled;
                }
            }
        }

        return $overrides;
    }

    public function getPermissionsAttribute($value)
    {
        if ($this->getRawOriginal('role') === 'admin') {
            return ['all' => true];
        }

        $roleRelation = $this->relationLoaded('role') ? $this->getRelation('role') : ($this->role_id ? Role::find($this->role_id) : null);
        if ($roleRelation && ($roleRelation->slug === 'admin' || strtolower($roleRelation->name) === 'admin')) {
            return ['all' => true];
        }

        $rolePerms = ($roleRelation && is_array($roleRelation->permissions)) ? $roleRelation->permissions : [];
        $rawPerms = static::decodePermissionsJson($value);

        if (empty($rawPerms)) {
            return static::normalizePermissionsArray($rolePerms);
        }

        if (empty($rolePerms)) {
            return static::normalizePermissionsArray($rawPerms);
        }

        $merged = array_replace_recursive($rolePerms, $rawPerms);
        return static::normalizePermissionsArray($merged);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'permissions' => 'array',
            'last_login' => 'datetime',
        ];
    }

    protected $appends = [];

    public function getRoleDataAttribute()
    {
        $role = $this->relationLoaded('role') ? $this->getRelation('role') : $this->role()->getResults();
        return $role ? [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
        ] : null;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
