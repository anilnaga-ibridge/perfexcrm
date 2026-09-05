<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'touserid',
        'description',
        'link',
        'isread',
        'read',
        'date',
    ];

    protected $casts = [
        'isread' => 'boolean',
        'read' => 'boolean',
    ];
}
