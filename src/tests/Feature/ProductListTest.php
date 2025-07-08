<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Purchase;
use Illuminate\Support\Facades\Hash;

class ProductListTest extends TestCase
{
    use RefreshDatabase;

    // テスト用データベース作成のヘルパーメソッド
    private function createTestUser($name = 'テストユーザー', $email = 'test@example.com') 
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'profile_image' => 'dummy.jpg',
        ]);
    }

    private function createTestProduct($user, $name = 'テスト商品', $status = Product::STATUS_ACTIVE)
    {
        return Product::create([
            'user_id' => $user->id,
            'name' => $name,
            'description' => '商品説明',
            'price' => 1000,
            'condition' => Product::CONDITION_GOOD,
            'brand' => 'テストブランド',
            'status' => $status,
            'image_path' => 'dummy.jpg',
        ]);
    }

    private function createTestCategory($name = 'テストカテゴリ')
    {
        return Category::create([
            'name' => $name,
        ]);
    }

    // 全商品を取得できるテスト
    public function test_can_display_all_products()
    {
        // テスト用ユーザーを作成（商品閲覧者）
        $viewer = $this->createTestUser('商品閲覧者', 'viewer@example.com');

        // テスト用ユーザーを作成（商品出品者）
        $seller = $this->createTestUser('商品出品者', 'seller@example.com');

        // テスト用商品を作成
        $product1 = $this->createTestProduct($seller, '表示される商品1');
        $product2 = $this->createTestProduct($seller, '表示される商品2');

        // 商品閲覧者としてログイン
        $this->actingAs($viewer);

        // 商品一覧画面にアクセス
        $response = $this->get('/');

        // 商品が表示されているか確認
        $response->assertStatus(200);
        $response->assertSee('表示される商品1');
        $response->assertSee('表示される商品2');
    }

    // 購入済み商品に「Sold」と表示される
    public function test_sold_products_display_sold_mark() 
    {
        // テスト用ユーザーを作成
        $viewer = $this->createTestUser('商品閲覧者', 'viewer@example.com');
        $seller = $this->createTestUser('商品出品者', 'seller@example.com');
        $buyer = $this->createTestUser('商品購入者', 'buyer@example.com');

        // テスト用商品を作成（出品中）
        $product = $this->createTestProduct($seller, '売り切れ商品'); // 売り切れになる予定の商品のため、商品名を売り切れ商品にしている

        // 商品を購入済みにする（purchasesテーブルにレコード追加）
        Purchase::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'price' => $product->price,
            'payment_method' => 'card',
            'shipping_postal_code' => '123-4567',
            'shipping_address' => 'テスト住所',
            'shipping_building' => 'テストビル',
            'purchased_at' => now(),
        ]);

        // 商品のステータスを売り切れに更新
        $product->update(['status' => Product::STATUS_SOLD]); // ここで売り切れ商品になる

        // 商品閲覧者としてログイン
        $this->actingAs($viewer);

        // 商品一覧画面にアクセス
        $response = $this->get('/');

        // 売り切れ商品が表示され、「Sold」マークが表示されることを確認
        $response->assertStatus(200);
        $response->assertSee('売り切れ商品');
        $response->assertSee('Sold');
    }

    // 自分が出品した商品は表示されない
    public function test_own_products_are_not_displayed() 
    {
        // テスト用ユーザーを作成（商品閲覧者兼出品者）
        $user = $this->createTestUser('ユーザー', 'user@examplecom');

        // 他のユーザーを作成
        $otherUser = $this->createTestUser('他のユーザー', 'other@example.com');

        // 自分の商品を作成
        $myProduct = $this->createTestProduct($user, '自分の商品');

        // 他人の商品を作成
        $otherProduct = $this->createTestProduct($otherUser, '他人の商品');

        // ユーザーとしてログイン
        $this->actingAs($user);

        // 商品一覧画面にアクセス
        $response = $this->get('/');

        // 他人の商品は表示され、自分の商品は表示されないことを確認
        $response->assertStatus(200);
        $response->assertSee('他人の商品');
        $response->assertDontSee('自分の商品');
    }
}