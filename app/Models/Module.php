<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'description',
        'version',
        'slug',
        'is_active',
        'is_installed',
        'file_path',
        'settings_link',
        'permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_installed' => 'boolean',
        'settings_link' => 'array',
        'permissions' => 'array',
    ];
}
