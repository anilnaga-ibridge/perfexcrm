<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailList extends Model
{
    protected $table = 'mail_lists';

    protected $fillable = [
        'name', 'custom_fields', 'creator_name', 'date_created',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'date_created' => 'date',
    ];
}
