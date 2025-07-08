<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ShippingAddress;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ProfileController extends Controller
{
    // プロフィール設定画面表示
    public function edit() 
    {
        $user = Auth::user();
        return view('users.edit', compact('user'));
    }

    // プロフィール更新処理
    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

        // プロフィール画像の処理（必須）
        if ($request->hasFile('profile_image')) {
            //　既存の画像の削除（デフォルト画像以外の場合）
            if ($user->profile_image && $user->profile_image !== 'images/default-profile.png') {
                Storage::disk('public')->delete($user->profile_image);
            }

            // 新しい画像の保存
            $profileImage = $request->file('profile_image')->store('profile', 'public');
        } else {
            $profileImage = $user->profile_image;
        }

        // ユーザー情報更新
        $user->update([
            'name' => $request->name,
            'profile_image' => $profileImage,
            'profile_postal_code' => $request->postal_code,
            'profile_address' => $request->address,
            'profile_building' => $request->building,
        ]);

        return redirect()->route('products.index')->with('success', 'プロフィール情報を更新しました。');
    }


    // プロフィール表示画面
    public function show() 
    {
        $user = Auth::user();

        // 購入した商品
        $purchasedProducts = $user->purchases()->with('product')->latest()->get()->pluck('product');

        // 出品した商品
        $listedProducts = $user->products()->latest()->get();

        return view('users.profile', compact('user', 'purchasedProducts', 'listedProducts'));
    }
}
