<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // リレーション: カテゴリに属する商品
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }
}
