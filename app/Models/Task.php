<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'priority',
        'status',
        'start_date',
        'due_date',
        'assigned_to',
        'related_to_type',
        'related_to_id',
        'client_id',
        'billable',
        'is_public',
        'hourly_rate',
        'repeat_every',
        'tags',
        'assignees',
        'followers',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'due_date'    => 'date',
        'billable'    => 'boolean',
        'is_public'   => 'boolean',
        'hourly_rate' => 'decimal:2',
        'assignees'   => 'array',
        'followers'   => 'array',
    ];

    /**
     * Get the primary staff user assigned to this task.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the client this task is related to (when related_to_type = 'Customer').
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
