<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    // Turn off Eloquent automatic updated_at for this table
    const UPDATED_AT = null;

    protected $fillable = [
        'description',
        'user_id',
        'user_name',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the staff member who performed this action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to write logs.
     */
    public static function log(string $description, ?int $userId = null)
    {
        $request = request();
        self::create([
            'description' => $description,
            'user_id' => $userId ?? (auth()->check() ? auth()->id() : null),
            'user_name' => auth()->check() ? auth()->user()->name : 'System',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
