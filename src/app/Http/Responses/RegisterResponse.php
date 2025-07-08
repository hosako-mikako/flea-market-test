<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        // 会員登録語はプロフィール設定画面にダイレクト
        return redirect()->route('profile.edit')->with('success', '会員登録が完了しました。プロフィール情報を設定してください。');
    }
}