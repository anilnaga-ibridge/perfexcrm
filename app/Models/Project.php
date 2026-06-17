<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'client_id', 'billing_type',
        'budget', 'start_date', 'deadline', 'status',
        'progress_from_tasks', 'progress', 'estimated_hours',
        'send_created_email', 'tags', 'settings',
    ];

    protected $casts = [
        'budget'              => 'float',
        'start_date'          => 'date',
        'deadline'            => 'date',
        'progress_from_tasks' => 'boolean',
        'progress'            => 'integer',
        'estimated_hours'     => 'float',
        'send_created_email'  => 'boolean',
        'settings'            => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members');
    }
}
