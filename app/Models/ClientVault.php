<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientVault extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'server_address',
        'port',
        'username',
        'password',
        'short_description',
        'visibility',
        'share_in_projects',
        'created_by',
    ];

    protected $casts = [
        'port' => 'integer',
        'share_in_projects' => 'boolean',
    ];

    /**
     * Get the client that owns this vault entry.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the user that created this vault entry.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
