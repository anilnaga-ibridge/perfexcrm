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
    ];

    protected $casts = [
        'budget'     => 'float',
        'start_date' => 'date',
        'deadline'   => 'date',
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
