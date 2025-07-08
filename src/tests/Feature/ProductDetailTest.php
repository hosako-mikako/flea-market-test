<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;


class ProductDetailTest extends TestCase
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
    private function createTestProduct($user, $name = 'テスト商品', $price = 1000, $condition = 1, $status = 1)
    {
        return Product::create([
            'name' => $name,
            'description' => 'これはテスト商品の詳細説明です。',
            'price' => $price,
            'condition' => $condition,
            'status' => $status,
            'brand' => 'テストブランド',
            'image_path' => 'test_product.jpg',
            'user_id' => $user->id,
        ]);
    }

    // 商品にカテゴリを追加するヘルパーメソッド
    private function attachCategoryToProduct($product, $category)
    {
        return \DB::table('product_categories')->insert([
            'product_id' => $product->id,
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

    // コメントを作成するヘルパーメソッド
    private function createComment($user, $product, $comment = 'テストコメントです')
    {
        return Comment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'comment' => $comment,
        ]);
    }

    // 商品詳細ページで基本情報が表示されるテスト
    public function test_product_detail_shows_basic_information()
    {
        // 準備
        $user = $this->createTestUser();
        $product = $this->createTestProduct(
            $user,
            'iPhone 14 Pro',
            150000,
            Product::CONDITION_GOOD,
            Product::STATUS_ACTIVE
        );

        // 実行
        $response = $this->get("/products/{$product->id}");

        // 検証
        $response->assertStatus(200);

        $response->assertSee('iPhone 14 Pro');
        $response->assertSee('これはテスト商品の詳細説明です。');
        $response->assertSee('150,000');
        $response->assertSee('テストブランド');
        $response->assertSee('test_product.jpg');
    }

    // 商品の状態が正しく表示されるテスト
    public function test_product_condition_is_display_correctly()
    {
        // 準備
        $user = $this->createTestUser();

        // 異なる状態の商品を作成
        $goodProduct = $this->createTestProduct($user, '良好な商品', 1000, Product::CONDITION_GOOD);
        $fairProduct = $this->createTestProduct($user, '傷ありの商品', 800, Product::CONDITION_FAIR);

        // 実行・検証
        $goodResponse = $this->get("/products/{$goodProduct->id}");
        $goodResponse->assertStatus(200);
        $goodResponse->assertSee('目立った傷や汚れなし');

        $fairResponse = $this->get("/products/{$fairProduct->id}");
        $fairResponse->assertStatus(200);
        $fairResponse->assertSee('やや傷や汚れあり');
    }

    // カテゴリが表示されるテスト（最大2つ）
    public function test_product_categories_are_displayed()
    {
        // 準備
        $user = $this->createTestUser();
        $product = $this->createTestProduct($user, 'カテゴリテスト商品');

        // 2つのカテゴリを作成
        $category1 = Category::create(['name' => '家電']);
        $category2 = Category::create(['name' => 'スマートフォン']);

        // 商品にカテゴリを関連付け
        $this->attachCategoryToProduct($product, $category1);
        $this->attachCategoryToProduct($product, $category2);

        // 実行
        $response = $this->get("/products/{$product->id}");

        // 検証
        $response->assertStatus(200);
        $response->assertSee('家電');
        $response->assertSee('スマートフォン');
    }

    // いいね数が表示されるテスト
    public function test_product_shows_favorite_count()
    {
        // 準備
        $user = $this->createTestUser();
        $user2 = $this->createTestUser('ユーザー2', 'user2@example.com');
        $user3 = $this->createTestUser('ユーザー3', 'user3@example.com');

        $product = $this->createTestProduct($user, 'いいねテスト商品');

        // 3人がいいね
        $this->createFavorite($user, $product);
        $this->createFavorite($user2, $product);
        $this->createFavorite($user3, $product);

        // 実行
        $response = $this->get("/products/{$product->id}");

        // 検証
        $response->assertStatus(200);
        $response->assertSee('3');
    }

    // コメント内容とユーザー情報が表示されるテスト
    public function test_product_shows_comments_and_user_info()
    {
        // 準備
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $commenter = $this->createTestUser('コメント者', 'commenter@example.com');

        $product = $this->createTestProduct($seller, 'コメント表示テスト商品');

        $this->createComment($commenter, $product, '商品の状態はいかがですか？');
        $this->createComment($seller, $product, '非常に良好です！');

        // 実行
        $response = $this->get("/products/{$product->id}");

        // 検証
        $response->assertStatus(200);

        // コメントした内容が表示される
        $response->assertSee('商品の状態はいかがですか？');
        $response->assertSee('非常に良好です！');

        // コメントしたユーザー名が表示される
        $response->assertSee('コメント者');
        $response->assertSee('出品者');
    }

    // 売り切れ商品は「Sold」表示されるテスト
    public function test_sold_product_shows_sold_status()
    {
        // 準備
        $user = $this->createTestUser();
        $soldProduct = $this->createTestProduct(
            $user,
            '売り切れ商品',
            5000,
            Product::CONDITION_GOOD,
            Product::STATUS_SOLD
        );

        // 実行
        $response = $this->get("/products/{$soldProduct->id}");

        // 検証
        $response->assertStatus(200);
        $response->assertSee('売り切れ商品');
    }

    // 存在しない商品にアクセスした場合404エラーのテスト
    public function test_non_existent_product_returns_404()
    {
        // 実行
        $response = $this->get('/products/99999');

        // 検証
        $response->assertStatus(404);
    }

    // 商品詳細ページでいいね機能が表示されるテスト
    public function test_product_detail_shows_favorite_functionality()
    {
        // 準備
        $user = $this->createTestUser();
        $product = $this->createTestProduct($user, 'いいね機能テスト商品');

        // 実行
        $response = $this->actingAs($user)->get("/products/{$product->id}");

        // 検証
        $response->assertStatus(200);
        $response->assertSee('favorite');
    }

    // 商品詳細ページでコメント投稿フォームが表示されるテスト
    public function test_product_detail_shows_comment_form()
    {
        // 準備
        $user = $this->createTestUser();
        $product = $this->createTestProduct($user, 'コメントフォームテスト商品');

        // 実行
        $response = $this->actingAs($user)->get("/products/{$product->id}");

        // 検証
        $response->assertStatus(200);
        $response->assertSee('textarea');
        $response->assertSee('comment');
    }
}
