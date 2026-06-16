<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'company',
        'description',
        'email',
        'website',
        'phonenumber',
        'lead_value',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'status_id',
        'source_id',
        'assigned_id',
        'last_contacted'
    ];

    protected $casts = [
        'lead_value' => 'decimal:2',
        'last_contacted' => 'datetime',
    ];

    /**
     * Get the status that the lead belongs to.
     */
    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    /**
     * Get the source that the lead belongs to.
     */
    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    /**
     * Get the staff user assigned to the lead.
     */
    public function assigned()
    {
        return $this->belongsTo(User::class, 'assigned_id');
    }
}
