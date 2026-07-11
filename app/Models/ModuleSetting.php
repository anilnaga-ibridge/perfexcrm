<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    protected $fillable = [
        'module_id',
        'setting_key',
        'setting_value',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
