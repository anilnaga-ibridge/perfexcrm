<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseBackup extends Model
{
    protected $fillable = [
        'filename',
        'file_path',
        'backup_size',
        'is_auto_flag',
        'auto_backup',
    ];

    protected $casts = [
        'is_auto_flag' => 'boolean',
        'auto_backup' => 'boolean',
    ];
}
