<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductExhibitionTest extends TestCase
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

    // テスト用カテゴリを作成するヘルパーメソッド
    private function createTestCategories()
    {
        return [
            Category::create(['name' => 'ファッション']),
            Category::create(['name' => '家電']),
            Category::create(['name' => 'スポーツ']),
        ];
    }


    // 商品出品用の基本データを生成するヘルパーメソッド
    private function getValidProductData($categories = null)
    {
        if (!$categories) {
            $categories = $this->createTestCategories();
        }

        Storage::fake('public');
        $image = UploadedFile::fake()->create('test-product.jpg', 1024, 'image/jpeg'); // 1MB

        return [
            'name' => 'テスト商品',
            'description' => 'これはテスト用の商品説明です。',
            'price' => 5000,
            'condition' => Product::CONDITION_GOOD,
            'brand' => 'テストブランド',
            'image' => $image,
            'categories' => [$categories[0]->id],
        ];
    }

    // 商品出品画面が正常に表示されるかテスト
    public function test_product_exhibition_screen_displays_correctly()
    {
        $user = $this->createTestUser();
        $categories = $this->createTestCategories();

        $response = $this->actingAs($user)->get('/sell');

        $response->assertStatus(200);
        $response->assertSee('商品の出品');
        $response->assertSee('商品画像');
        $response->assertSee('カテゴリー');
        $response->assertSee('商品の状態');
        $response->assertSee('商品名');
        $response->assertSee('ブランド名');
        $response->assertSee('商品の説明');
        $response->assertSee('販売価格');
        $response->assertSee('出品する');

        // カテゴリが表示されているか確認
        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }
    }

    // 未認証ユーザーは商品出品画面にアクセスできないテスト
    public function test_unauthenticated_user_cannot_access_exhibition_screen()
    {
        $response = $this->get('/sell');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }


    // 商品名が必須であることをテスト
    public function test_product_name_is_required()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();
        $data['name'] = '';

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name' => '商品名を入力してください。']);
    }

    // 商品説明が必須であることをテスト
    public function test_product_description_is_required()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();
        $data['description'] = '';

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['description' => '商品説明を入力してください。']);
    }

    // 販売価格が必須であることをテスト
    public function test_product_price_is_required()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();
        unset($data['price']);

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['price' => '価格を入力してください。']);
    }

    // 商品の状態が必須であることをテスト
    public function test_product_condition_is_required()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();
        unset($data['condition']);

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['condition' => '商品の状態を選択してください。']);
    }

    // ブランド名が必須であることをテスト
    public function test_product_brand_is_required()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();
        $data['brand'] = '';

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['brand' => 'ブランド名を入力してください。']);
    }

    // 商品画像が必須であることをテスト
    public function test_product_image_is_required()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();
        unset($data['image']);

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['image' => '商品画像を選択してください。']);
    }

    // カテゴリが必須であることをテスト
    public function test_product_categories_are_required()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();
        $data['categories'] = [];

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['categories' => 'カテゴリーを選択してください。']);
    }

    // 商品の状態の値制限テスト
    public function test_product_condition_value_validation()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();
        $data['condition'] = 99; // 無効な値

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['condition' => '商品の状態は正しい値を選択してください。']);
    }

    // 画像形式の制限テスト
    public function test_product_image_format_validation()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();

        Storage::fake('public');
        $data['image'] = UploadedFile::fake()->create('test.txt', 100); // txtファイル

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['image' => '商品画像はjpeg、png形式のファイルを選択してください。']);
    }

    // 画像サイズの制限テスト
    public function test_product_image_size_validation()
    {
        $user = $this->createTestUser();
        $data = $this->getValidProductData();

        Storage::fake('public');
        $data['image'] = UploadedFile::fake()->create('test-product.jpg', 1024, 'image/jpeg')->size(3000); // 3MB（制限を超える）

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['image' => '商品画像は2MB以下のファイルを選択してください。']);
    }

    // カテゴリ数の上限制限テスト
    public function test_categories_maximum_limit()
    {
        $user = $this->createTestUser();
        $categories = $this->createTestCategories();
        $data = $this->getValidProductData($categories);
        $data['categories'] = [$categories[0]->id, $categories[1]->id, $categories[2]->id]; // 3個（上限を超える）

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['categories' => 'カテゴリーは2つまで選択してください。']);
    }

    // 商品情報の正常登録テスト
    public function test_product_can_be_successfully_created()
    {
        $user = $this->createTestUser();
        $categories = $this->createTestCategories();
        $data = $this->getValidProductData($categories);

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);
        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');

        // データベースに正しく保存されているか確認
        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'name' => 'テスト商品',
            'description' => 'これはテスト用の商品説明です。',
            'price' => 5000,
            'condition' => Product::CONDITION_GOOD,
            'brand' => 'テストブランド',
            'status' => Product::STATUS_ACTIVE,
        ]);

        // 画像ファイルが保存されているか確認
        $product = Product::where('user_id', $user->id)->first();
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    // カテゴリ関連付けの正常動作テスト
    public function test_product_categories_are_properly_attached()
    {
        $user = $this->createTestUser();
        $categories = $this->createTestCategories();
        $data = $this->getValidProductData($categories);
        $data['categories'] = [$categories[0]->id, $categories[1]->id]; // 2つのカテゴリを選択

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);

        // 商品とカテゴリの関連付けが正しく保存されているか確認
        $product = Product::where('user_id', $user->id)->first();
        $this->assertCount(2, $product->categories);
        $this->assertTrue($product->categories->contains($categories[0]));
        $this->assertTrue($product->categories->contains($categories[1]));
    }

    // 単一カテゴリ選択時の正常動作テスト
    public function test_single_category_selection_works()
    {
        $user = $this->createTestUser();
        $categories = $this->createTestCategories();
        $data = $this->getValidProductData($categories);
        $data['categories'] = [$categories[0]->id]; // 1つのカテゴリのみ選択

        $response = $this->actingAs($user)->post('/sell', $data);

        $response->assertStatus(302);

        $product = Product::where('user_id', $user->id)->first();
        $this->assertCount(1, $product->categories);
        $this->assertTrue($product->categories->contains($categories[0]));
    }

    // フォーム構造とCSRF保護の確認テスト
    public function test_exhibition_form_structure_and_csrf()
    {
        $user = $this->createTestUser();
        $this->createTestCategories();

        $response = $this->actingAs($user)->get('/sell');

        $response->assertStatus(200);
        // CSRF保護
        $response->assertSee('name="_token"', false);
        // フォームアクション
        $response->assertSee('action="' . route('products.store') . '"', false);
        // enctype設定
        $response->assertSee('enctype="multipart/form-data"', false);
        // 各フィールドの存在確認
        $response->assertSee('name="name"', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('name="price"', false);
        $response->assertSee('name="condition"', false);
        $response->assertSee('name="brand"', false);
        $response->assertSee('name="image"', false);
        $response->assertSee('name="categories[]"', false);
    }
}
