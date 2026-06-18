<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'goal_type',
        'staff_member',
        'start_date',
        'end_date',
        'target_value',
        'current_value',
        'description',
        'notify_when_achieve',
        'notify_when_fail',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'notify_when_achieve' => 'boolean',
        'notify_when_fail' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_member');
    }
}
