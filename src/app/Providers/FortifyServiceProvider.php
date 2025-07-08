<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        // ログイン用のカスタムRequest
        $this->app->bind(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            \App\Http\Requests\LoginRequest::class
        );

        // 会員登録用のカスタムRequest
        $this->app->when(\Laravel\Fortify\Http\Controllers\RegisteredUserController::class)
            ->needs(\Laravel\Fortify\Http\Requests\RegisterRequest::class)
            ->give(\App\Http\Requests\RegisterRequest::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // ビューのカスタマイズ
        Fortify::loginView(function() {
            return view('auth.login');
        });

        Fortify::registerView(function() {
            return view('auth.register');
        });

        // 認証エラーメッセージのカスタマイズ
        Fortify::authenticateUsing(function (Request $request) {
            $email = $request->email;
            $password = $request->password;

            $user = \App\Models\User::where('email', $email)->first();

            if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                return $user;
            }

            // 認証失敗時にカスタムメッセージを設定
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ['ログイン情報が登録されていません。'],
            ]);
        });

        // ログイン後のリダイレクト先を設定
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );

        // 会員登録後のリダイレクト先を設定
        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Http\Responses\RegisterResponse::class
        );

        // ログアウト後のリダイレクト先の設定
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );
    }
}
