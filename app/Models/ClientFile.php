<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'visible_to_customer',
    ];

    protected $casts = [
        'visible_to_customer' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the client that owns this file.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
