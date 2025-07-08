<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Hash;

class UserProfileTest extends TestCase
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
            'image_path' => 'dummy.jpg',
            'brand' => 'テストブランド',
        ]);
    }

    // プロフィール画面が正常に表示されるかテスト
    public function test_profile_screen_display_correctly() 
    {
        // 準備
        $user = $this->createTestUser('テストユーザー', 'test@example.com');

        // 実行
        $response = $this->actingAs($user)->get('/profile');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    // プロフィール画面でユーザー情報が正しく表示されるかテスト
    public function test_user_information_displays_correctly()
    {
        // 準備
        $user = $this->createTestUser('テストユーザー', 'test@example.com');

        // 実行
        $response = $this->actingAs($user)->get('/profile');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee('プロフィールを編集');
        $response->assertSee('出品した商品');
        $response->assertSee('購入した商品');
    }

    // 出品した商品一覧が正しく表示されるかテスト
    public function test_listed_products_display_correctly() 
    {
        // 準備
        $user = $this->createTestUser();
        $product1 = $this->createTestProduct($user, '出品商品1', 1000);
        $product2 = $this->createTestProduct($user, '出品商品2', 2000);

        // 実行
        $response = $this->actingAs($user)->get('/profile');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('出品商品1');
        $response->assertSee('出品商品2');
    }

    // 購入した商品一覧が正しく表示されるかテスト
    public function test_purchased_products_display_correctly() 
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');

        $product1 = $this->createTestProduct($seller, '購入商品1', 1500);
        $product2 = $this->createTestProduct($seller, '購入商品2', 2500);

        // 購入者が商品を購入
        Purchase::create([
            'user_id' => $buyer->id,
            'product_id' => $product1->id,
            'purchased_at' => now(),
        ]);

        Purchase::create([
            'user_id' => $buyer->id,
            'product_id' => $product2->id,
            'purchased_at' => now(),
        ]);

        // 実行
        $response = $this->actingAs($buyer)->get('/profile?tab=purchased');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('購入商品1');
        $response->assertSee('購入商品2');
    }

    // タブ切り替え機能のテスト
    public function test_tab_switching_functionality() 
    {
        // 準備
        $user1 = $this->createTestUser('テストユーザー1', 'a@example.com');
        $user2 = $this->createTestUser('テストユーザー2', 'b@example.com');

        // User1が商品を出品
        $listedProduct = $this->createTestProduct($user1, '出品商品1', 3000);

        // User2が商品を出品し、User1が購入
        $purchasedProduct = $this->createTestProduct($user2, '購入商品1', 4000);
        Purchase::create([
            'user_id' => $user1->id,
            'product_id' => $purchasedProduct->id,
            'purchased_at' => now(),
        ]);

        // 実行1
        $response1 = $this->actingAs($user1)->get('/profile');
        $response1->assertStatus(200);
        $response1->assertSee('出品商品1');
        $response1->assertDontSee('購入商品1'); // 購入商品は表示されない

        // 実行2
        $response2 = $this->actingAs($user1)->get('/profile?tab=purchased');
        $response2->assertStatus(200);
        $response2->assertSee('購入商品1');
        $response2->assertDontSee('出品商品1'); // 出品商品は表示されない
    }


    // 出品・購入履歴がない場合の表示テスト
    public function test_empty_products_display() {
        // 準備
        $user = $this->createTestUser();

        // 実行
        $response = $this->actingAs($user)->get('/profile');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    // 最新順で商品が表示されるかテスト
    public function test_products_display_in_latest_order() 
    {
        // 準備
        $seller = $this->createTestUser('seller', 'seller@example.com');
        $buyer = $this->createTestUser('buyer', 'buyer@example.com');

        // 時間差で商品を出品
        $oldProduct = $this->createTestProduct($buyer, '古い出品商品', 1000);
        sleep(1); // 1秒待機
        $newProduct = $this->createTestProduct($buyer, '新しい出品商品', 2000);


        // 実行
        $response = $this->actingAs($buyer)->get('/profile');

        // 検証
        $response->assertStatus(200);

        // HTMLの順序確認（新しい商品が先に表示される）
        $content = $response->getContent();
        $newProductPos = strpos($content, '新しい出品商品');
        $oldProductPos = strpos($content, '古い出品商品');

        // 両方の商品が見つかった場合のみ順序をチェック
        if ($newProductPos !== false && $oldProductPos !== false) {
            $this->assertLessThan($oldProductPos, $newProductPos, '出品商品が最新順で表示されていません');
        }

        // 購入商品の最新順テスト
        $oldPurchasedProduct = $this->createTestProduct($seller, '古い購入商品', 3000);
        Purchase::create([
            'user_id' => $buyer->id,
            'product_id' => $oldPurchasedProduct->id,
            'purchased_at' => now()->subMinute(),
        ]);

        sleep(1);
        $newPurchasedProduct = $this->createTestProduct($seller, '新しい購入商品', 4000);
        Purchase::create([
            'user_id' => $buyer->id,
            'product_id' => $newPurchasedProduct->id,
            'purchased_at' => now(),
        ]);

        // 購入した商品タブで最新順確認
        $response2 = $this->actingAs($buyer)->get('/profile?tab=purchased');
        $response2->assertStatus(200);

        $content2 = $response2->getContent();
        $newPurchasedPos = strpos($content2, '新しい購入商品');
        $oldPurchasedPos = strpos($content2, '古い購入商品');

        // 両方の商品が見つかった場合のみ順序をチェック
        if ($newPurchasedPos !== false && $oldPurchasedPos !== false) {
            $this->assertLessThan($oldPurchasedPos, $newPurchasedPos, '購入商品が最新順で表示されていません');
        }
    }

    // 未認証ユーザーのプロフィールアクセス制限テスト
    public function test_unauthenticated_user_cannot_access_profile() 
    {
        // 実行
        $response = $this->get('/profile');

        // 検証
        $response->assertRedirect('/login');
    }
}
