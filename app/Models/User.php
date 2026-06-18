<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
