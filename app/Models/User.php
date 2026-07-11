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

    public function hasPermission(string $permission): bool
    {
        // 1. Admin role has full access (check both column and relation slug)
        if ($this->role === 'admin') {
            return true;
        }

        $roleRelation = $this->relationLoaded('role') ? $this->getRelation('role') : $this->role()->getResults();
        if ($roleRelation && $roleRelation->slug === 'admin') {
            return true;
        }

        // 2. Check legacy JSON permissions array on Role
        if ($roleRelation && is_array($roleRelation->permissions)) {
            if (isset($roleRelation->permissions[$permission]) && $roleRelation->permissions[$permission]) {
                return true;
            }
            foreach ($roleRelation->permissions as $feature => $actions) {
                if (strtolower($feature) === strtolower($permission)) {
                    return true;
                }
                if (is_array($actions) && isset($actions[$permission]) && $actions[$permission]) {
                    return true;
                }
            }
        }

        // 3. Check relational permissions via roles many-to-many relationship
        foreach ($this->roles as $r) {
            if ($r->permissions()->where('name', $permission)->exists()) {
                return true;
            }
        }

        // 4. Check relational permissions via direct role
        if ($roleRelation && $roleRelation->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        return false;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'permissions' => 'array',
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
}
