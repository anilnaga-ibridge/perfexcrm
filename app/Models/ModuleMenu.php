<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleMenu extends Model
{
    protected $fillable = [
        'module_id',
        'parent_id',
        'title',
        'route',
        'icon',
        'permission',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function parent()
    {
        return $this->belongsTo(ModuleMenu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ModuleMenu::class, 'parent_id');
    }
}
