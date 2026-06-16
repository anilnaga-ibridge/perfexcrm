<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    use HasFactory;

    protected $table = 'lead_statuses';

    protected $fillable = [
        'name',
        'color',
        'order_num'
    ];

    /**
     * Get the leads with this status.
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'status_id');
    }
}
