<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleEvent extends Model
{
    protected $fillable = [
        'module_id',
        'module_alias',
        'event_name',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
