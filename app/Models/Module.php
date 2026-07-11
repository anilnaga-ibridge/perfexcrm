<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Module extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'alias',
        'version',
        'minimum_core_version',
        'depends',
        'description',
        'status',
        'author',
        'settings_route',
        'icon',
        'homepage',
        'license',
        'is_core',
        'file_path',
        'installed_at',
        'activated_at',
    ];

    protected $casts = [
        'depends' => 'array',
        'is_core' => 'boolean',
        'installed_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function menus()
    {
        return $this->hasMany(ModuleMenu::class);
    }

    public function modulePermissions()
    {
        return $this->hasMany(ModulePermission::class);
    }

    public function settings()
    {
        return $this->hasMany(ModuleSetting::class);
    }

    public function events()
    {
        return $this->hasMany(ModuleEvent::class);
    }
}
