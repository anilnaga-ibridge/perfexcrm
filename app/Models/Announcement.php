<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'message',
        'show_to_staff',
        'show_to_clients',
    ];

    protected $casts = [
        'show_to_staff' => 'boolean',
        'show_to_clients' => 'boolean',
    ];
}
