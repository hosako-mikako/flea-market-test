<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Hash;

class ShippingAddressTest extends TestCase
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

    // 配送先変更画面が正常に表示されるかテスト
    public function test_shipping_address_edit_screen_displays_correctly()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $response = $this->actingAs($buyer)->get("/purchase/{$product->id}/address");

        // 検証
        $response->assertStatus(200);
        $response->assertSee('配送先');
    }

    // 配送先住所変更処理が正常に動作するかテスト
    public function test_shipping_address_update_works_correctly()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $newAddress = [
            'postal_code' => '999-8888',
            'address' => '変更県変更市変更町2-2',
            'building' => '変更ビル202',
        ];

        $response = $this->actingAs($buyer)->patch("/purchase/{$product->id}/address", $newAddress);

        // 検証
        $response->assertRedirect("/products/{$product->id}/purchase");
        $response->assertSessionHas('success', '配送先住所を変更しました。');
    }

    // 変更した住所が購入画面に反映されるかテスト
    public function test_changed_address_reflects_on_purchase_screen()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 配送先住所を変更
        $newAddress = [
            'postal_code' => '999-8888',
            'address' => '変更県変更市変更町2-2',
            'building' => '変更ビル202',
        ];

        $this->actingAs($buyer)->patch("/purchase/{$product->id}/address", $newAddress);

        // 購入画面にアクセス
        $response = $this->actingAs($buyer)->get("/products/{$product->id}/purchase");

        // 検証
        $response->assertStatus(200);
        $response->assertSee('999-8888');
        $response->assertSee('変更県変更市変更町2-2');
        $response->assertSee('変更ビル202');
    }

    // 購入完了時に配送先住所がデータベースに保存されるかテスト
    public function test_shipping_address_saved_to_database_on_purchase()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 配送先住所を変更
        $newAddress = [
            'postal_code' => '999-8888',
            'address' => '変更県変更市変更町2-2',
            'building' => '変更ビル202',
        ];

        $this->actingAs($buyer)->patch("/purchase/{$product->id}/address", $newAddress);

        // 商品を購入
        $purchaseData = [
            'payment_method' => 'card',
            'shipping_address' => '1',
            'postal_code' => '999-8888',
            'address' => '変更県変更市変更町2-2',
            'building' => '変更ビル202',
        ];

        $response = $this->actingAs($buyer)->post("/products/{$product->id}/purchase", $purchaseData);

        // 購入が正常に完了する
        $response->assertRedirect('/profile');
        $response->assertSessionHas('success', '商品を購入しました。');

        // 検証
        $this->assertDatabaseHas('shipping_addresses', [
            'user_id' => $buyer->id,
            'postal_code' => '999-8888',
            'address' => '変更県変更市変更町2-2',
            'building' => '変更ビル202',
        ]);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
        ]);
    }

    // 配送先住所のバリデーションテスト
    public function test_shipping_address_validation_required_fields()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $response = $this->actingAs($buyer)->patch("/purchase/{$product->id}/address", [
            'postal_code' => '',
            'address' => '',
            'building' => '',
        ]);

        // 検証
        $response->assertSessionHasErrors(['postal_code', 'address']);
        // buildingは任意項目なのでエラーなし
        $response->assertSessionDoesntHaveErrors(['building']);
    }

    // 郵便番号形式のバリデーションテスト
    public function test_postal_code_format_validation()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $response = $this->actingAs($buyer)->patch("/purchase/{$product->id}/address", [
            'postal_code' => '1234567', // ハイフンなし
            'address' => 'テスト住所',
            'building' => 'テストビル',
        ]);

        // 検証
        $response->assertSessionHasErrors(['postal_code']);
    }

    // 正しい郵便番号形式のテスト
    public function test_valid_postal_code_format()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 実行
        $response = $this->actingAs($buyer)->patch("/purchase/{$product->id}/address", [
            'postal_code' => '123-4567', // 正しい形式
            'address' => 'テスト住所',
            'building' => 'テストビル',
        ]);

        // 検証
        $response->assertRedirect("/products/{$product->id}/purchase");
        $response->assertSessionHas('success', '配送先住所を変更しました。');
    }

    // 自分の商品に対する配送先変更制限テスト
    public function test_cannot_change_address_for_own_product()
    {
        // 準備
        $user = $this->createTestUser();
        $product = $this->createTestProduct($user); // 自分の商品

        // 実行
        $response = $this->actingAs($user)->get("/purchase/{$product->id}/address");

        // 検証
        $response->assertRedirect("/products/{$product->id}");
        $response->assertSessionHas('error', '自分の商品は購入できません。');

    }

    

    // 売り切れ商品に対する配送先変更制限テスト
    public function test_cannot_change_address_for_sold_product()
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');
        $product = $this->createTestProduct($seller);

        // 商品を売り切れ状態にする
        $product->update(['status' => Product::STATUS_SOLD]);

        // 実行
        $response = $this->actingAs($buyer)->get("/purchase/{$product->id}/address");

        // 検証
        $response->assertRedirect("/products/{$product->id}");
        $response->assertSessionHas('error', 'この商品は売り切れです。');
    }
}
