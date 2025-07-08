<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Purchase;
use Illuminate\Support\Facades\Hash;


class MyListTest extends TestCase
{
    use RefreshDatabase;

    // テスト用ユーザーを作成するヘルパーメソッド

    private function createTestUser($name = 'テストユーザー', $email = 'test@example.com') 
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'profile_image' => 'dummy.jpg',
        ]);
    }

    // テスト用商品を作成するヘルパーメソッド
    private function createTestProduct($user, $name = 'テスト商品', $price = 1000, $status = 1) 
    {
        // カテゴリーを作成
        $category = Category::create([
            'name' => 'テストカテゴリー',
        ]);

        return Product::create([
            'name' => $name,
            'description' => 'テスト商品の説明',
            'price' => $price,
            'condition' => 1,
            'status' => $status,
            'brand' => 'テストブランド',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'image_path' => 'dummy.jpg',
        ]);
    }

    // いいねを作成するヘルパーメソッド
    private function createFavorite($user, $product) 
    {
        return \DB::table('favorites')->insert([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // 購入を作成するヘルパーメソッド
    private function createPurchase($user, $product) 
    {
        return Purchase::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'price' => $product->price,
            'payment_method' => 'card',
            'shipping_postal_code' => '123-4567',
            'shipping_address' => 'テスト住所',
            'shipping_building' => ('テストビル'),
            'purchased_at' => now(),
        ]);
    }

    // 未認証の場合は何も表示されないテスト
    public function test_unauthenticated_user_sees_nothing() 
    {
        // 準備
        $user = $this->createTestUser();
        $product = $this->createTestProduct($user);
        $this->createFavorite($user, $product);

        // 実行
        $response = $this->get('/mylist');

        // 検証
        $response->assertStatus(302);
        $response->assertDontSee($product->name);
    }

    // いいねした商品だけが表示されるテスト
    public function test_only_favorited_products_are_displayed() 
    {
        // 準備
        $user = $this->createTestUser();
        $otherUser = $this->createTestUser('他のユーザー', 'other@example.com');

        // 商品を作成
        $favoriteProduct = $this->createTestProduct($otherUser, 'いいねした商品');
        $normalProduct = $this->createTestProduct($otherUser, 'いいねしていない商品');

        // 1つの商品にいいねする
        $this->createFavorite($user, $favoriteProduct);

        // 実行
        $response = $this->actingAs($user)->get('/mylist');

        // 検証
        $response->assertStatus(200);
        $response->assertSee($favoriteProduct->name);
        $response->assertDontSee($normalProduct->name);   
    }

    // 購入済み商品は「Sold」と表示されるテスト
    public function test_purchased_products_show_sold_status() 
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('商品出品者', 'seller@example.com');
        $buyer = $this->createTestUser('商品購入者', 'buyer@example.com');

        // 商品を作成（売り切れ状態）
        $soldProduct = $this->createTestProduct($seller, '売り切れ商品', 1000, 2);

        // いいねする
        $this->createFavorite($user, $soldProduct);

        // 購入する
        $this->createPurchase($buyer, $soldProduct);

        // 実行
        $response = $this->actingAs($user)->get('/mylist');

        // 検証
        $response->assertStatus(200);
        $response->assertSee($soldProduct->name);
        $response->assertSee('Sold');
    }

    // 自分が出品した商品は表示されない
    public function test_own_products_are_not_displayed() 
    {
        // 準備
        $user = $this->createTestUser();
        $otherUser = $this->createTestUser('他のユーザー', 'other@example.com');
        
        // 自分の商品と他人の商品を作成
        $myProduct = $this->createTestProduct($user, '自分の商品');
        $otherProduct = $this->createTestProduct($otherUser, '他人の商品');

        // 両方にいいねする
        $this->createFavorite($user, $myProduct);
        $this->createFavorite($user, $otherProduct);

        // 実行
        $response = $this->actingAs($user)->get('/mylist');

        // 検証
        $response->assertStatus(200);
        $response->assertDontSee($myProduct->name);
        $response->assertSee($otherProduct->name);
    }
}
