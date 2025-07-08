<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PurchaseFunctionTest extends TestCase {
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
            'image_path' => 'dummy.jpg',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
    }

    // 「購入する」ボタンを押下すると購入が完了するテスト
    public function test_can_complete_purchase_by_clicking_purchase_button() 
    {
        // 準備
        $buyer = $this->createTestUser('購入者', 'buyer@example.com');
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, '購入テスト商品', 5000);

        $purchaseData = [
            'payment_method' => 'card',
            'shipping_address' => 'profile',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ];

        // 実行
        $response = $this->actingAs($buyer)->post("/products/{$product->id}/purchase", $purchaseData);

        // 検証
        $response->assertStatus(302);

        // データベースに購入記録が保持されていることを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        // 配送先住所が作成されていることを確認
        $this->assertDatabaseHas('shipping_addresses', [
            'user_id' => $buyer->id,
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ]);

        // 商品のステータスが売り切れに変更されていることを確認
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => Product::STATUS_SOLD,
        ]);
    }

    // 購入した商品は商品一覧画面にて「Sold」と表示されるテスト
    public function test_purchased_product_shows_sold_status_in_product_list() 
    {
        // 準備
        $buyer = $this->createTestUser('購入者', 'buyer@example.com');
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, '購入テスト商品', 5000);

        // 購入処理を実行
        Purchase::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'purchased_at' => now(),
        ]);

        // 商品を売り切れ状態に変更
        $product->update(['status' => Product::STATUS_SOLD]);

        // 実行
        $response = $this->get('/');

        // 検証
        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('Sold');
    }

    // 「プロフィール/購入した商品一覧」に追加されているかの確認テスト
    public function test_purchased_product_appears_in_profile_purchased_list() 
    {
        // 準備
        $buyer = $this->createTestUser('購入者', 'buyer@example.com');
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, '購入テスト商品', 5000);

        // 購入記録を作成
        Purchase::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'purchased_at' => now(),
        ]);

        // 実行
        $response = $this->actingAs($buyer)->get('/profile?tab=purchased');

        // 検証
        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('購入テスト商品');
    }

    // 自分の商品は購入できないテスト
    public function test_cannot_purchase_own_product() 
    {
        // 準備
        $user = $this->createTestUser();
        $product = $this->createTestProduct($user, '自分の商品');

        $purchaseData = [
            'payment_method' => 'card',
            'shipping_address' => 'profile',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ];

        // 実行
        $response = $this->actingAs($user)->post("/products/{$product->id}/purchase", $purchaseData);

        // 検証
        $response->assertStatus(302);
        $response->assertRedirect("/products/{$product->id}");
        $response->assertSessionHas('error', '自分の商品は購入できません。');

        // データベースに購入記録が保持されていないことを確認
        $this->assertDatabaseMissing('purchases', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // 商品のステータスが変更されていないことを確認
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => Product::STATUS_ACTIVE,
        ]);
    }

    // 売り切れ商品は購入できないテスト/
    public function test_cannot_purchase_sold_product() 
    {
        // 準備
        $buyer = $this->createTestUser('購入者', 'buyer@example.com');
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $soldProduct = $this->createTestProduct($seller, '売り切れ商品', 5000, Product::STATUS_SOLD);

        $purchaseData = [
            'payment_method' => 'card',
            'shipping_address' => 'profile',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ];

        // 実行
        $response = $this->actingAs($buyer)->post("/products/{$soldProduct->id}/purchase", $purchaseData);

        // 検証
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'この商品は売り切れです。');

        // データベースに購入記録が保存されていないことを確認
        $this->assertDatabaseMissing('purchases', [
            'user_id' => $buyer->id,
            'product_id' => $soldProduct->id,
        ]);
    }

    // 支払方法が未選択の場合のバリデーションエラーテスト
    public function test_payment_method_required_validation_error() 
    {
        // 準備
        $buyer = $this->createTestUser('購入者', 'buyer@example.com');
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, '購入テスト商品');

        $purchaseData = [
            // 'payment_method' => 'card', // 支払方法を省略
            'shipping_address' => 'profile',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ];

        // 実行
        $response = $this->actingAs($buyer)->post("/products/{$product->id}/purchase", $purchaseData);

        // 検証
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['payment_method']);

        // データベースに購入記録が保持されていないことを確認
        $this->assertDatabaseMissing('purchases', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
        ]);
    }

    // 配送先が未選択の場合バリデーションエラーテスト
    public function test_shipping_address_required_validation_error() 
    {
        //準備
        $buyer = $this->createTestUser('購入者', 'buyer@example.com');
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, '購入テスト商品');

        $purchaseData = [
            'payment_method' => 'card',
            // 'shipping_address' => 'profile', // 配送先を省略
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ];

        // 実行
        $response = $this->actingAs($buyer)->post("/products/{$product->id}/purchase", $purchaseData);

        // 検証
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['shipping_address']);

        // データベースに購入記録が保存されていないことを確認
        $this->assertDatabaseMissing('purchases', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
        ]);
    }

    // 未認証ユーザーは購入できないテスト
    public function test_unauthenticated_user_cannot_purchase()
    {
        // 準備
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, '購入テスト商品');

        $purchaseData = [
            'payment_method' => 'card',
            'shipping_address' => 'profile',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ];

        // 実行
        $response = $this->post("/products/{$product->id}/purchase", $purchaseData);

        // 検証（Assert）
        $response->assertStatus(302);

        // データベースに購入記録が保存されていないことを確認
        $this->assertDatabaseMissing('purchases', [
            'product_id' => $product->id,
        ]);
    }

    // 存在しない商品は購入できないテスト
    public function test_cannot_purchase_non_existent_product()
    {
        // 準備
        $buyer = $this->createTestUser('購入者', 'buyer@example.com');

        $purchaseData = [
            'payment_method' => 'card',
            'shipping_address' => 'profile',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ];

        // 実行
        $response = $this->actingAs($buyer)
            ->post("/products/99999/purchase", $purchaseData);

        // 検証
        $response->assertStatus(404);
    }
}
