<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    // メールアドレスが未入力の場合、バリデーションエラーが表示されるテスト
    public function test_email_is_required_for_login() 
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
    }

    // パスワードが未入力の場合、バリデーションエラーが表示されるテスト
    public function test_password_is_required_for_login() 
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
    }

    // 存在しないメールアドレスの場合、認証エラーが表示されるテスト
    public function test_login_fails_with_invalid_email() 
    {
        // 存在しないメールアドレスでログイン試行
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません。']);
        $this->assertGuest();

    }

    // 間違ったパスワードの場合、認証エラーが表示されるテスト
    public function test_login_fails_invalid_password() 
    {
        // テスト用ユーザーを事前に作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'profile_image' => 'dummy.jpg',
        ]);

        // 間違ったパスワードでログイン試行
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']); // passwordと書いてしまうとメールアドレスが存在することがばれてしまうためemailにする
        $this->assertGuest();
    }

    // 正しい情報が入力された場合、ログイン処理が実行されるテスト
    public function test_user_can_login_with_valid_credentials() 
    {
        // テスト用ユーザーを事前に作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'profile_image' => 'dummy.jpg',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/?type=favorite');
        $this->assertAuthenticated(); // 認証状態になったことを確認
    }

    // ログアウトができるテスト

    public function test_authenticated_user_can_logout() 
    {
        // テスト用ユーザーを作成、ログイン
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'profile_image' => 'dummy.jpg',
        ]);

        $this->actingAs($user);  // ログイン状態にする

        // ログアウト処理
        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertGuest(); // 未認証状態になったことを確認
    }

}