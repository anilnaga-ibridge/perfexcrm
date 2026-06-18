<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KbCategory extends Model
{
    use HasFactory;

    protected $table = 'kb_categories';

    protected $fillable = [
        'name', 'slug', 'color', 'description', 'order_num', 'disabled',
    ];

    protected function casts(): array
    {
        return [
            'disabled' => 'boolean',
            'order_num' => 'integer',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(KbArticle::class, 'category_id');
    }
}
