<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    // テスト用ユーザーを作成するヘルパーメソッド
    private function createTestUser($name = 'TestUser', $email = 'test@example.com')
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'profile_image' => 'dummy.jpg',
            'profile_postal_code' => '123-4567',
            'profile_address' => 'テスト県テスト市テスト町1-1',
            'profile_building' => 'テストビル101',
        ]);
    }

    // テスト用商品を作成するヘルパーメソッド
    private function createTestProduct($user, $name = 'テスト商品', $price = 1000)
    {
        return Product::create([
            'user_id' => $user->id,
            'name' => $name,
            'description' => 'テスト商品の説明',
            'price' => $price,
            'condition' => Product::CONDITION_GOOD,
            'status' => Product::STATUS_ACTIVE,
            'image_path' => 'test-image.jpg',
            'brand' => 'テストブランド',
        ]);
    }

    // 支払方法選択画面が正常に表示されるかテスト
    public function test_payment_method_selection_screen_displays_correctly()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $response = $this->actingAs($buyer)->get("/products/{$product->id}/purchase");

        // 検証
        $response->assertStatus(200);
        $response->assertSee('支払方法');
        $response->assertSee('コンビニ払い');
        $response->assertSee('カード払い');
    }

    // 支払方法時未選択時のバリデーションテスト
    public function test_payment_method_validation_when_not_selected()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $response = $this->actingAs($buyer)->post("/products/{$product->id}/purchase", [
            'shipping_address' => '1',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
            // payment_methodを意図的に省略
        ]);

        // 検証
        $response->assertSessionHasErrors(['payment_method']);
    }

    // コンビニ払い選択時の正常処理テスト
    public function test_convenience_store_payment_method_selection()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $response = $this->actingAs($buyer)->post("/products/{$product->id}/purchase", [
            'payment_method' => 'convenience_store',
            'shipping_address' => '1',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ]);

        // 検証
        $response->assertRedirect('/profile');
        $response->assertSessionHas('success', '商品を購入しました。');
    }

    // カード払い選択時の正常処理テスト
    public function test_card_payment_method_selection()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $response = $this->actingAs($buyer)->post("/products/{$product->id}/purchase", [
            'payment_method' => 'card',
            'shipping_address' => '1',
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市テスト町1-1',
            'building' => 'テストビル101',
        ]);

        // 検証
        $response->assertRedirect('/profile');
        $response->assertSessionHas('success', '商品を購入しました。');
    }

    // 支払方法選択状態の保持テスト（old値テスト）
    public function test_payment_method_selection_preserved_on_validation_error()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $response = $this->actingAs($buyer)->post("/products/{$product->id}/purchase", [
            'payment_method' => 'card',
            // shipping_addressを意図的に省略
        ]);

        // 検証
        $response->assertSessionHasErrors(['shipping_address']);
        $response->assertSessionHasInput('payment_method', 'card');
    }
}
