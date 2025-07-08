<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserProfileEditTest extends TestCase
{
    use RefreshDatabase;

    // テスト用ユーザーを作成するヘルパーメソッド
    private function createTestUser($name = 'テストユーザー', $email = 'test@example.com')
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'profile_image' => 'profile/test-user.jpg',
            'profile_postal_code' => '123-4567',
            'profile_address' => 'テスト県テスト市テスト町1-1',
            'profile_building' => 'テストビル101',
        ]);
    }

    // プロフィール編集画面が正常に表示されるかテスト
    public function test_profile_edit_screen_displays_correctly()
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertSee('プロフィール設定');
        $response->assertSee('ユーザー名');
        $response->assertSee('郵便番号');
        $response->assertSee('住所');
        $response->assertSee('建物名');
        $response->assertSee('更新する');
    }

    // ユーザー名が初期値として設定されているかテスト
    public function test_user_name_displays_as_initial_value()
    {
        $user = $this->createTestUser('テストユーザー');

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertSee('value="テストユーザー"', false);
    }

    // 郵便番号が初期値として設定されているかテスト
    public function test_postal_code_displays_as_initial_value()
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertSee('value="123-4567"', false);
    }

    // 住所が初期値として設定されているかテスト
    public function test_address_displays_as_initial_value()
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertSee('value="テスト県テスト市テスト町1-1"', false);
    }

    // 建物名が初期値として設定されているかテスト
    public function test_building_displays_as_initial_value()
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertSee('value="テストビル101"', false);
    }



    //  プロフィール画像がない場合のプレースホルダー表示テスト
    public function test_profile_placeholder_displays_correctly()
    {
        // 存在しない画像パスを設定したユーザーを作成
        $user = User::create([
            'name' => '山田花子',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password123'),
            'profile_image' => 'profile/nonexistent-image.jpg', // 存在しない画像
            'profile_postal_code' => '456-7890',
            'profile_address' => 'テスト県テスト市テスト町2-2',
            'profile_building' => 'テストマンション202',
        ]);

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        // プレースホルダーに名前の最初の文字が表示されているか確認
        $response->assertSee('山', false);
        $response->assertSee('profile-placeholder', false);
    }

    // プロフィール画像のアップロード用フィールドが正しく設定されているかテスト
    public function test_profile_image_upload_field_configured_correctly()
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        // 画像アップロード用のinput要素が正しく設定されているか確認
        $response->assertSee('type="file"', false);
        $response->assertSee('name="profile_image"', false);
        $response->assertSee('accept="image/*"', false);
        $response->assertSee('画像を選択する', false);
    }

    // 空の住所情報も正しく表示されるかテスト
    public function test_empty_address_fields_display_correctly()
    {
        $user = User::create([
            'name' => '佐藤次郎',
            'email' => 'sato@example.com',
            'password' => Hash::make('password123'),
            'profile_image' => 'dummy.jpg',
            'profile_postal_code' => null, // 空の値
            'profile_address' => null,     // 空の値
            'profile_building' => null,    // 空の値
        ]);

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        // 空の値でもエラーにならず、空のフィールドが表示される
        $response->assertSee('name="postal_code"', false);
        $response->assertSee('name="address"', false);
        $response->assertSee('name="building"', false);
    }

    //  未認証ユーザーはプロフィール編集画面にアクセスできないテスト
    public function test_unauthenticated_user_cannot_access_profile_edit()
    {
        $response = $this->get('/profile/edit');

        // 認証が必要なため、ログイン画面にリダイレクトされる
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    //  フォームのCSRF保護とメソッドが正しく設定されているかテスト
    public function test_form_has_correct_csrf_and_method()
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->get('/profile/edit');

        $response->assertStatus(200);
        // CSRF トークンが含まれている
        $response->assertSee('name="_token"', false);
        // PATCHメソッドが設定されている
        $response->assertSee('name="_method" value="PATCH"', false);
        // フォームのaction属性が正しく設定されている
        $response->assertSee('action="' . route('profile.update') . '"', false);
        // enctype が設定されている（画像アップロード用）
        $response->assertSee('enctype="multipart/form-data"', false);
    }
}
