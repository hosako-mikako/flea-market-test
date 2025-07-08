<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;


class AddressController extends Controller
{
    // 送付先住所変更画面を表示（購入時専用）
    public function edit(Product $product) {
        $user =Auth::user();

        // 自分の商品は購入できない
        if ($product->user_id == Auth::id()) {
            return redirect()->route('products.show', $product)->with('error', '自分の商品は購入できません。');
        }

        // 売り切れ商品は購入できない
        if ($product->status === Product::STATUS_SOLD) {
            return redirect()->route('products.show', $product)->with('error', 'この商品は売り切れです。');
        }

        // セッションから住所を取得、なければプロフィールを使用
        $sessionAddress = session('purchase_address');

        if ($sessionAddress) {
            // セッションに住所がある場合はそれを初期値に
            $defaultAddress = $sessionAddress;
        } else {
            // プロフィール住所を初期値は空欄
            $defaultAddress = [
                'postal_code' => '',
                'address' => '',
                'building' => ''
            ];
        }

        return view('users.address', compact('user', 'defaultAddress', 'product'));
    }

    // 送付先住所を更新（セッションに保存）
    public function update(AddressRequest $request, Product $product)
    {
        $validatedData = $request->validated();

        // 商品の状態を再チェック
        if ($product->user_id == Auth::id()) {
            return redirect()->route('products.show', $product)->with('error', '自分の商品は購入できません。');
        }

        if ($product->status === Product::STATUS_SOLD) {
            return redirect()->route('products.show', $product)->with('error', 'この商品は売り切れです。');
        }

        try {
            // セッションに住所情報を保存
            session([
                'purchase_address' => [
                    'postal_code' => $validatedData['postal_code'],
                    'address' => $validatedData['address'],
                    'building' => $validatedData['building']
                ]
            ]);

            return redirect()->route('purchase.show', $product)->with('success', '配送先住所を変更しました。');
        } catch(\Exception $e) {
            return redirect()->back()->withInput()->with('error', '住所の更新中にエラーが発生しました。再度お試しください。');
        }
    }

    // セッションから住所情報を取得
    public static function getSessionAddress() {
        return session('purchase_address', null);
    } 

    // セッションの住所情報をクリア
    public static function clearSessionAddress() {
        session()->forget('purchase_address');
    }
}
