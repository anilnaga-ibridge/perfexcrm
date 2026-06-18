<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KbArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'content', 'category_id', 'status', 'views_count', 'internal', 'disabled',
    ];

    protected function casts(): array
    {
        return [
            'views_count' => 'integer',
            'internal' => 'boolean',
            'disabled' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'category_id');
    }
}
