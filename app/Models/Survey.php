<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = [
        'subject', 'view_description', 'email_description', 'include_link',
        'from_email', 'redirect_url', 'disabled', 'logged_in_only',
        'total_questions', 'total_participants', 'date_created', 'active',
    ];

    protected $casts = [
        'include_link' => 'boolean',
        'disabled' => 'boolean',
        'logged_in_only' => 'boolean',
        'active' => 'boolean',
        'date_created' => 'date',
    ];
}
