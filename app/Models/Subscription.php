<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_id',
        'amount',
        'billing_period',
        'status',
        'start_date',
        'stripe_plan',
        'quantity',
        'currency',
        'tax_1',
        'tax_2',
        'terms',
        'description',
        'include_description',
        'project_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'quantity' => 'integer',
        'include_description' => 'boolean',
    ];

    /**
     * Get the client that owns the subscription.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the project associated with the subscription.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
