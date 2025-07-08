<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'condition',
        'brand',
        'status',
        'image_path',
    ];

    protected $casts = [
        'status' => 'integer',
        'condition' => 'integer',
        'price' => 'integer',
    ];

    // 商品の状態定数
    const CONDITION_VERY_GOOD = 1;    // 良好
    const CONDITION_GOOD = 2;         // 目立った傷や汚れなし
    const CONDITION_FAIR = 3;         // やや傷や汚れあり
    const CONDITION_POOR = 4;         // 状態が悪い

    // 商品のステータス定数
    const STATUS_ACTIVE = 1;          // 出品中
    const STATUS_SOLD = 2;            // 売り切れ

    // リレーション: 出品者
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // リレーション: カテゴリ
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    // リレーション: お気に入り
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // リレーション: 購入履歴
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    // リレーション: コメント
    public function comments() {
        return $this->hasMany(Comment::class)->latest();
    }

    // いいね数を取得
    public function favoritesCount() {
        return $this->favorites()->count();
    }

    // コメント数を取得
    public function commentsCount() {
        return $this->comments()->count();
    }

    //既存ユーザーがいいねしているかをチェック
    public function isFavoritedBy($userId) {
        if (!$userId) {
            return false;
        }
        return $this->favorites()->where('user_id', $userId)->exists();
    }

    // 商品の状態一覧を取得
    public static function getConditions()
    {
        return  [
            self::CONDITION_VERY_GOOD => '良好',
            self::CONDITION_GOOD => '目立った傷や汚れなし',
            self::CONDITION_FAIR => 'やや傷や汚れあり',
            self::CONDITION_POOR => '状態が悪い',
        ];
    }

    // 商品のステータス一覧を取得
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => '販売中',
            self::STATUS_SOLD => '売り切れ',
        ];
    }

    // 商品の状態名を取得
    public function getConditionName()
    {
        return self::getConditions()[$this->condition] ?? '不明';
    }

    // 商品のステータス名を取得
    public function getStatusName()
    {
        return self::getStatuses()[$this->status] ?? '不明';
    }
}

