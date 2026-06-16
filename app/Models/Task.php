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
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Get the user (staff) assigned to this task.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
