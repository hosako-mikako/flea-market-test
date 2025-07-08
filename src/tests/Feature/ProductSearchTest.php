<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class ProductSearchTest extends TestCase
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

    // 商品名で部分検索ができる（商品一覧ページ）テスト
    public function test_can_search_products_by_partial_name_match() 
    {
        // 準備
        $user = $this->createTestUser();

        // 様々な商品を作成
        $this->createTestProduct($user, 'タンブラー シルバー');
        $this->createTestProduct($user, 'ステンレスタンブラー');
        $this->createTestProduct($user, 'マグカップ');
        $this->createTestProduct($user, 'プラスチックコップ');
        
        // 実行
        $response = $this->get('/?search=タンブラー');

        // 検証
        $response->assertStatus(200);

        // 一致する商品が表示される
        $response->assertSee('タンブラー シルバー');
        $response->assertSee('ステンレスタンブラー');

        // 一致しない商品は表示されない
        $response->assertDontSee('マグカップ');
        $response->assertDontSee('プラスチックコップ');
    }

    // 大文字小文字を区別しない検索テスト
    public function test_search_is_case_insensitive() 
    {
        // 準備
        $user = $this->createTestUser();

        $this->createTestProduct($user, 'iPhone ケース');
        $this->createTestProduct($user, 'Android スマホ');

        // 実行
        $response = $this->get('/?search=iPhone');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('iPhone ケース');
        $response->assertDontSee('Android スマホ');
    }

    // 検索キーワードが空の場合は全商品表示テスト
    public function test_empty_search_shows_all_products() 
    {
        // 準備
        $user = $this->createTestUser();

        $this->createTestProduct($user, '商品1');
        $this->createTestProduct($user, '商品2');
        $this->createTestProduct($user, '商品3');

        // 実行
        $response = $this->get('/?search=');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('商品1');
        $response->assertSee('商品2');
        $response->assertSee('商品3');
    }

    // 検索キーワードがビューに渡されている（商品一覧）テスト
    public function test_search_keyword_is_passed_to_view() 
    {
        // 準備
        $user = $this->createTestUser();
        $this->createTestProduct($user, 'テスト商品');

        // 実行
        $response = $this->get('/?search=テスト');

        // 検証
        $response->assertStatus(200);
        // 検索キーワードがビューに表示されている
        $response->assertSee('value="テスト"', false);
    }

    // マイリストでも検索機能が動作する
    public function test_search_works_on_mylist_page() 
    {
        // 準備
        $user = $this->createTestUser();
        $otherUser = $this->createTestUser('他のユーザー', 'other@example.com');

        // 商品を作成
        $tumblerProduct = $this->createTestProduct($otherUser, 'タンブラー');
        $cupProduct = $this->createTestProduct($otherUser, 'マグカップ');
        $phoneProduct = $this->createTestProduct($otherUser, 'スマートフォン');

        // すべての商品にいいねする
        $this->createFavorite($user, $tumblerProduct);
        $this->createFavorite($user, $cupProduct);
        $this->createFavorite($user, $phoneProduct);

        // 実行
        $response = $this->actingAs($user)->get('/mylist?search=タンブラー');

        $response->assertStatus(200);
        $response->assertSee('タンブラー');
        $response->assertDontSee('マグカップ');
        $response->assertDontSee('スマートフォン');
    }

    // マイリストでも検索キーワードが保持される
    public function test_search_keyword_is_preserved_on_mylist() 
    {
        // 準備
        $user = $this->createTestUser();
        $otherUser = $this->createTestUser('他のユーザー', 'other@example.com');

        $product = $this->createTestProduct($otherUser, 'テスト商品');
        $this->createFavorite($user, $product);

        // 実行
        $response = $this->actingAs($user)->get('/mylist?search=テスト');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('value="テスト"', false);
    }

    // 該当商品がない場合の検索結果テスト
    public function test_no_results_when_no_matching_products() 
    {
        // 準備
        $user = $this->createTestUser();
        $this->createTestProduct($user, 'タンブラー');
        $this->createTestProduct($user, 'マグカップ');

        // 実行
        $response = $this->get('/?search=存在しない商品名');

        // 検証
        $response->assertStatus(200);
        $response->assertDontSee('タンブラー');
        $response->assertDontSee('マグカップ');
    }

    // URLエンコードされた検索キーワードの処理テスト
    public function test_handles_url_encoded_search_keywords() 
    {
        // 準備
        $user = $this->createTestUser();
        $this->createTestProduct($user, 'タンブラー');

        // 実行
        $response = $this->get('/?search=' . urlencode('タンブラー'));

        // 検証
        $response->assertStatus(200);
        $response->assertSee('タンブラー');
    }

}
