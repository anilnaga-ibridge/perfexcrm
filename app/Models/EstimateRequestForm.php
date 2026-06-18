<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateRequestForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'tags', 'assigned_to', 'status', 'language', 'recaptcha_enabled',
        'submit_btn_text', 'submit_btn_bg_color', 'submit_btn_text_color',
        'submission_action', 'submission_message', 'submission_redirect_url',
        'notify_enabled', 'notify_type', 'notify_staff_ids',
    ];

    protected function casts(): array
    {
        return [
            'recaptcha_enabled' => 'boolean',
            'notify_enabled' => 'boolean',
        ];
    }

    public function assigned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
