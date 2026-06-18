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
        
        'latitude',
        'longitude',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Get the contacts for the client.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the files linked to this client.
     */
    public function files()
    {
        return $this->hasMany(ClientFile::class);
    }

    /**
     * Get the vault entries for the client.
     */
    public function vaults()
    {
        return $this->hasMany(ClientVault::class);
    }

    /**
     * Get the invoices for this client.
     */
    public function invoices()
    {
        return $this->hasMany(\App\Models\Invoice::class, 'client_id');
    }
}
