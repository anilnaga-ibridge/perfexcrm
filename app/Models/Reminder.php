<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'remind_to',
        'description',
        'date',
        'send_email',
        'is_notified',
        'created_by',
    ];

    protected $casts = [
        'send_email'   => 'boolean',
        'is_notified'  => 'boolean',
        'date'         => 'date:Y-m-d',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function remindTo()
    {
        return $this->belongsTo(User::class, 'remind_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
