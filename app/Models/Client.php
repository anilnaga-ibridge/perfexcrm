<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'company',
        'vat',
        'phonenumber',
        'website',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'default_language',
        'groups',
        'currency',
        
        // Billing
        'billing_street',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        
        // Shipping
        'shipping_street',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the contacts for the client.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
