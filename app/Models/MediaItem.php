<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'is_directory',
        'parent_id',
        'size',
        'mime_type',
    ];

    protected $casts = [
        'is_directory' => 'boolean',
    ];

    /**
     * Get the parent directory.
     */
    public function parent()
    {
        return $this->belongsTo(MediaItem::class, 'parent_id');
    }

    /**
     * Get the files and directories inside this folder.
     */
    public function children()
    {
        return $this->hasMany(MediaItem::class, 'parent_id');
    }
}
