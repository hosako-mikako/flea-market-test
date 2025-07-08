<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;

class CommentFunctionTest extends TestCase
{
    use RefreshDatabase;

    // テスト用ユーザーを作成するヘルパーメソッド
    private function createTestUser($name = 'テストユーザー', $email = 'test@example.com') {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'profile_image' => 'dummy.jpg',
        ]);
    }

    // テスト用商品を作成するヘルパーメソッド
    private function createTestProduct($user, $name = 'テスト商品', $price = 1000, $status = 1) {
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
    
    // ログイン済みのユーザーはコメントを送信できるテスト
    public function test_authenticated_user_can_send_comment() {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'コメントテスト商品');

        $commentData = [
            'comment' => 'テスト商品のコメント'
        ];

        // 実行
        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson("/products/{$product->id}/comments", $commentData);

        // 検証
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'comment' => [
                'comment' => 'テスト商品のコメント',
                'user_name' => 'テストユーザー',
            ]
        ]);

        // データベースにコメントが保存されていることを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'comment' => 'テスト商品のコメント',
        ]);
    }

      // ログイン前のユーザーはコメントを送信できないテスト
    public function test_unauthenticated_user_cannot_send_comment()
    {
        // 準備
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'コメントテスト商品');

        $commentData = [
            'comment' => 'ログインせずにコメント送信'
        ];

        // 実行
        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/{$product->id}/comments", $commentData);

        // 検証（Assert）
        $response->assertStatus(401);

        // データベースにコメントが保存されていないことを確認
        $this->assertDatabaseMissing('comments', [
            'comment' => 'ログインせずにコメント送信',
        ]);
    }

    // コメントが入力されていない場合、バリデーションエラーが表示されるテスト
    public function test_comment_required_validation_error()
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'コメントテスト商品');

        $commentData = [
            'comment' => '' 
        ];

        // 実行
        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/{$product->id}/comments", $commentData);

        // 検証
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['comment']);
        $response->assertJson([
            'errors' => [
                'comment' => ['コメントを入力してください。']
            ]
        ]);

        // データベースにコメントが保存されていないことを確認
        $this->assertDatabaseMissing('comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    // コメントが255字以上の場合、バリデーションエラーが表示されるテスト
    public function test_comment_max_length_validation_error()
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'コメントテスト商品');

        // 256文字のコメントを作成
        $longComment = str_repeat('あ', 256);
        $commentData = [
            'comment' => $longComment
        ];

        // 実行
        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/{$product->id}/comments", $commentData);

        // 検証
        $response->assertStatus(422); 
        $response->assertJsonValidationErrors(['comment']);
        $response->assertJson([
            'errors' => [
                'comment' => ['コメントは255字以内で入力してください。']
            ]
        ]);

        // データベースにコメントが保存されていないことを確認
        $this->assertDatabaseMissing('comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'comment' => $longComment,
        ]);
    }

    // 255文字ちょうどのコメントは送信できるテスト
    public function test_comment_max_length_boundary_success()
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'コメントテスト商品');

        // 255文字ちょうどのコメントを作成
        $maxLengthComment = str_repeat('あ', 255);
        $commentData = [
            'comment' => $maxLengthComment
        ];

        // 実行
        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/{$product->id}/comments", $commentData);

        // 検証
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'comment' => [
                'comment' => $maxLengthComment,
                'user_name' => 'テストユーザー',
            ]
        ]);

        // データベースにコメントが保存されていることを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'comment' => $maxLengthComment,
        ]);
    }

    // 存在しない商品にはコメントできないテスト
    public function test_cannot_comment_on_non_existent_product()
    {
        // 準備
        $user = $this->createTestUser();

        $commentData = [
            'comment' => '存在しない商品へのコメント'
        ];

        // 実行
        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/99999/comments", $commentData);

        // 検証
        $response->assertStatus(404); 
    }

    // コメント送信後のJSONレスポンス形式確認
    public function test_comment_response_format()
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'コメントテスト商品');

        $commentData = [
            'comment' => 'レスポンス形式テスト'
        ];

        // 実行
        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/{$product->id}/comments", $commentData);

        // 検証
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'comment' => [
                'id',
                'comment',
                'user_name',
                'user_profile_image',
                'created_at'
            ]
        ]);

        // レスポンスの型確認
        $responseData = $response->json();
        $this->assertIsBool($responseData['success']);
        $this->assertIsArray($responseData['comment']);
        $this->assertIsInt($responseData['comment']['id']);
        $this->assertIsString($responseData['comment']['comment']);
        $this->assertIsString($responseData['comment']['user_name']);
        $this->assertIsString($responseData['comment']['created_at']);
    }

    // 複数のコメントが送信できるテスト
    public function test_multiple_comments_can_be_sent()
    {
        // 準備
        $user1 = $this->createTestUser('ユーザー1', 'user1@example.com');
        $user2 = $this->createTestUser('ユーザー2', 'user2@example.com');
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $product = $this->createTestProduct($seller, 'コメントテスト商品');

        // 実行
        $response1 = $this->actingAs($user1)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/{$product->id}/comments", ['comment' => '最初のコメント']);

        $response2 = $this->actingAs($user2)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/{$product->id}/comments", ['comment' => '二番目のコメント']);

        // 検証
        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // データベースに両方のコメントが保存されていることを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user1->id,
            'product_id' => $product->id,
            'comment' => '最初のコメント',
        ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user2->id,
            'product_id' => $product->id,
            'comment' => '二番目のコメント',
        ]);

        // コメント総数の確認
        $this->assertEquals(2, Comment::where('product_id', $product->id)->count());
    }

    // 売り切れ商品にもコメントできるテスト
    public function test_can_comment_on_sold_product()
    {
        // 準備
        $user = $this->createTestUser();
        $seller = $this->createTestUser('出品者', 'seller@example.com');
        $soldProduct = $this->createTestProduct($seller, '売り切れ商品', 1000, Product::STATUS_SOLD);

        $commentData = [
            'comment' => '売り切れ商品へのコメント'
        ];

        // 実行
        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/{$soldProduct->id}/comments", $commentData);

        // 検証
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'comment' => [
                'comment' => '売り切れ商品へのコメント',
                'user_name' => 'テストユーザー',
            ]
        ]);

        // データベースに保存されていることを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'product_id' => $soldProduct->id,
            'comment' => '売り切れ商品へのコメント',
        ]);
    }

    // 自分の商品にコメントできるテスト
    public function test_can_comment_on_own_product()
    {
        // 準備
        $user = $this->createTestUser();
        $product = $this->createTestProduct($user, '自分の商品'); // 自分が出品した商品

        $commentData = [
            'comment' => '自分の商品への追加説明'
        ];

        // 実行
        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson("/products/{$product->id}/comments", $commentData);

        // 検証
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'comment' => [
                'comment' => '自分の商品への追加説明',
                'user_name' => 'テストユーザー',
            ]
        ]);

        // データベースに保存されていることを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'comment' => '自分の商品への追加説明',
        ]);
    } 
}
