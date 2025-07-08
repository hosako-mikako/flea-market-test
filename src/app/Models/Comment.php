<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'comment',
    ];

    // コメントを投稿したユーザー 
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // コメントされた商品
    public function product() {
        return $this->belongsTo(Product::class);
    }
}
