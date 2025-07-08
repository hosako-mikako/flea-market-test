<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    // 会員登録画面が正常に表示されるテスト 
    public function test_registration_page_can_be_displayed()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('会員登録');
    }

    // 名前が未入力の場合、バリデーションエラーが表示されるテスト
    public function test_name_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // バリデーションエラーでリダイレクトされることを確認
        $response->assertStatus(302);

        // セッションにエラーメッセージがあることを確認
        $response->assertSessionHasErrors(['name']);
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください。']);
    }

    // メールアドレスが未入力の場合、バリデーションメッセージが表示されるテスト
    public function test_email_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示されるテスト
    public function test_password_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
    }

    // パスワードが7文字以下の場合、バリデーションメッセージが表示されるテスト
    public function test_password_must_be_at_least_8_characters() 
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください。']);
    }

    // パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示されるテスト
    public function test_password_confirmation_must_match() 
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => '1234567',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません。']);
    }

    // 正常なデータで会員登録が成功するテスト
    public function test_user_can_register_with_valid_data() 
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // プロフィール設定画面にリダイレクトされることを確認
        $response->assertStatus(302);

        // データベースにユーザーが作成されていることを確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }
}
