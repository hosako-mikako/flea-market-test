<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input)
    {

        // バリデーション実行
        $request = app(\App\Http\Requests\RegisterRequest::class);
        $request->replace($input);
        $validated = $request->validated();

        return User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'profile_image' => '', // 初回時は空文字、プロフィール設定で必須
            'profile_postal_code' => null, // プロフィール設定で入力
            'profile_address' => null, // プロフィール設定で入力
            'profile_building' => null, // プロフィール設定で入力
            'email_verified_at' => now(), // 即座に認証済みとする。
        ]);
    }
}
