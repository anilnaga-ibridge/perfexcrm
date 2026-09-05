<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $table = 'todo_items';
    protected $fillable = ['staff_id', 'assigned_to', 'assigned_by', 'description', 'priority', 'due_date', 'done', 'sort_order'];
    protected $casts = [
        'done' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function staff()
    {
        return $this->belongsTo(\App\Models\User::class, 'staff_id', 'id');
    }

    public function assignedToStaff()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to', 'id');
    }

    public function assignedByStaff()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_by', 'id');
    }
}
