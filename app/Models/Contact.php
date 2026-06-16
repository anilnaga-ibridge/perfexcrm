<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'firstname',
        'lastname',
        'email',
        'phonenumber',
        'title',
        'password',
        'active',
        'is_primary'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the client that owns the contact.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
