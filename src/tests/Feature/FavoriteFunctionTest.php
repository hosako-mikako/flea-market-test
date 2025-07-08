<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;


class FavoriteFunctionTest extends TestCase
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
            'image_path' => 'dummy.jpg',
            'user_id' => $user->id,
            'category_id' => $category->id,
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

    // いいねアイコンを押下していいねを追加できるテスト
    public function test_can_add_favorite_by_clicking_icon() 
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'いいねテスト商品');

        // 実行
        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/favorite");

        // 検証
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'favorites_count' => 1,
            'is_favorited' => true,
            'message' => 'お気に入りに追加しました。',
        ]);

        // データベースにいいねが保存されていることを確認
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    // いいね済の商品のアイコンを押下していいねを解除できるテスト
    public function test_can_remove_favorite_by_clicking_icon_again() 
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'いいねテスト商品');

        // 事前にいいねを追加
        $this->createFavorite($user, $product);

        // 実行
        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/favorite");

        // 検証
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'favorites_count' => 0,
            'is_favorited' => false,
            'message' => 'お気に入りから削除しました。',
        ]);

        // データベースからいいねが削除されていることを確認
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    // いいね数が正しく計算されるテスト
    public function test_favorite_count_is_calculated_correctly() 
    {
        // 準備
        $user1 = $this->createTestUser('ユーザー1', 'user1@example.com');
        $user2 = $this->createTestUser('ユーザー2', 'user2@example.com');
        $user3 = $this->createTestUser('ユーザー3', 'user3@example.com');
        $seller = $this->createTestUser('出品者', 'seller@example.com');

        $product = $this->createTestProduct($seller, 'いいねテスト商品');

        // 二人が事前にいいね済
        $this->createFavorite($user2, $product);
        $this->createFavorite($user3, $product);

        // 実行　- 3人目がいいね
        $response = $this->actingAs($user1)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/favorite");

        // 検証
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'favorites_count' => 3,
            'is_favorited' => true,
        ]);
    }

    // 同じユーザーが複数回いいねしても1回分のみカウントされる
    public function test_same_user_can_only_favorite_once() 
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'いいねテスト商品');

        // 実行 - 1回目のいいね
        $response1 = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/favorite");

        // 実行 - 2回目のいいね（解除）
        $response2 = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/favorite");

        // 実行 - 3回目のいいね（再追加）
        $response3 = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/favorite");

        // 検証
        $response1->assertJson(['favorites_count' => 1, 'is_favorited' => true, 'message' => 'お気に入りに追加しました。']);
        $response2->assertJson(['favorites_count' => 0, 'is_favorited' => false, 'message' => 'お気に入りから削除しました。']);
        $response3->assertJson(['favorites_count' => 1, 'is_favorited' => true, 'message' => 'お気に入りに追加しました。']);

        // データベースには1件のみ存在
        $this->assertEquals(1, \DB::table('favorites')->where('user_id', $user->id)->where('product_id', $product->id)->count());
    }

    // 未ログインユーザーはいいねできない
    public function test_unauthenticated_user_cannot_add_favorite() 
    {
        // 準備
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'いいね商品テスト');

        // 実行
        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/favorite");

        // 検証
        $response->assertStatus(401);  // 未認証エラー
    }


    // 自分の商品にもいいねできる
    public function test_user_can_favorite_own_product() 
    {
        // 準備
        $user = $this->createTestUser();
        $product = $this->createTestProduct($user, '自分の商品');

        // 実行
        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/favorite");

        // 検証
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'favorites_count' => 1,
            'is_favorited' => true,
            'message' => 'お気に入りに追加しました。',
        ]);
    }

    // 存在しない商品にいいね出来ない
    public function test_cannot_favorite_non_existent_product() 
    {
        // 準備
        $user = $this->createTestUser();

        // 実行
        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/99999/favorite");

        // 検証
        $response->assertStatus(404);
    }

    // いいね状態の確認
    public function test_can_check_current_favorite_status() 
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'いいねテスト商品');

        // 事前にいいねを追加
        $this->createFavorite($user, $product);

        // 実行
        $response = $this->actingAs($user)->get("/products/{$product->id}");

        // 検証
        $response->assertStatus(200);
    }

    // 売り切れ商品にもいいね出来る
    public function test_can_favorite_sold_product() 
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $soldProduct = $this->createTestProduct($seller, '売り切れ商品', 1000, Product::STATUS_SOLD);

        // 実行
        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$soldProduct->id}/favorite");

        // 検証
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'favorites_count' => 1,
            'is_favorited' => true,
        ]);

        // データベースに保存されていることを確認
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $soldProduct->id,
        ]);
    }

    // JSONレスポンスの形式確認
    public function test_favorite_response_format() 
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'いいねテスト商品');

        // 実行
        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/favorite");

        // 検証
        $response->assertJsonStructure([
            'success',
            'favorites_count',
            'is_favorited',
            'message'
        ]);

        // レスポンスの型確認
        $responseData = $response->json();
        $this->assertIsBool($responseData['success']);
        $this->assertIsInt($responseData['favorites_count']);
        $this->assertIsBool($responseData['is_favorited']);
        $this->assertIsString($responseData['message']);
    }

}
