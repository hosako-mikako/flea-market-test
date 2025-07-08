<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'profile_postal_code',
        'profile_address',
        'profile_building',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // リレーション: 住所
    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }

    // デフォルト送付先住所を取得する
    public function defaultShippingAddress() 
    {
        return $this->shippingAddresses()->where('is_default', true)->first();
    }

    // リレーション: 出品した商品
    public function products()
    {
        return $this->hasMany(Product::class);
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

    // リレーション:　コメント
    public function comments() {
        return $this->hasMany(Comment::class);
    }
}
