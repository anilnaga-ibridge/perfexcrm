<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'content', 'category_id', 'status', 'views_count',
    ];

    protected $casts = [
        'views_count' => 'integer',
    ];
}
