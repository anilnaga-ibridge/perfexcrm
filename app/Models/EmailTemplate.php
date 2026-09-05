<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $table = 'email_templates';

    protected $fillable = [
        'key',
        'name',
        'type',
        'audience',
        'subject',
        'body',
        'from_name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
