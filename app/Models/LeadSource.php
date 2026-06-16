<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    use HasFactory;

    protected $table = 'lead_sources';

    protected $fillable = [
        'name'
    ];

    /**
     * Get the leads from this source.
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'source_id');
    }
}
