<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ShippingAddress;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    // 商品購入画面を表示
    public function show(Product $product)
    {

        $user = auth()->user();

        // 自分の商品は購入できない
        if ($product->user_id === Auth::id()) {
            return redirect()->route('products.show', $product)->with('error', '自分の商品は購入できません。');
        }

        // 売り切れ商品は購入できない
        if ($product->status === Product::STATUS_SOLD) {
            return redirect()->route('products.show', $product)->with('error', 'この商品は売り切れです。');
        }

        // 商品詳細情報を取得（リレーション含む）
        $product->load(['user', 'categories']);

        // セッションから住所を取得、なければプロフィール住所を使用
        $sessionAddress = session('purchase_address');

        if ($sessionAddress) {
            // セッションに住所がある場合
            $defaultAddress = $sessionAddress;
            $userHasAddress = true; // セッション住所があれば有効とする
        } else {
            // プロフィール住所を使用
            $defaultAddress = [
                'postal_code' => $user->profile_postal_code,
                'address' => $user->profile_address,
                'building' => $user->profile_building
            ];
            $userHasAddress = $user->profile_postal_code && $user->profile_address;
        }


        return view('products.purchase', compact('product', 'user', 'defaultAddress', 'userHasAddress'));
    }

    // 商品購入処理
    public function store(PurchaseRequest $request, Product $product)
    {

        $user = auth()->user();


        // 自分の商品は購入できない
        if ($product->user_id == Auth::id()) {
            return redirect()->route('products.show', $product)->with('error', '自分の商品は購入できません。');
        }

        // 売り切れ商品は購入できない
        if ($product->status === Product::STATUS_SOLD) {
            return redirect()->route('products.show', $product)->with('error', 'この商品は売り切れです。');
        }

        try {

            // セッション住所を使用する場合の処理を追加
            $sessionAddress = session('purchase_address');

            if ($sessionAddress) {
                // セッション住所を使用
                $addressData = $sessionAddress;
                // セッションをクリア
                session()->forget('purchase_address');
            } else {
                // フォームから送信された住所を使用
                $addressData = [
                    'postal_code' => $request->input('postal_code'),
                    'address' => $request->input('address'),
                    'building' => $request->input('building')
                ];
            }

            // 配送先住所を作成（購入時に必ず新規作成）
            $shippingAddress = ShippingAddress::create([
                'user_id' => $user->id,
                'postal_code' => $addressData['postal_code'],
                'address' => $addressData['address'],
                'building' => $addressData['building'],
                'is_default' => false,
            ]);

            // 購入記録を作成
            Purchase::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'purchased_at' => now(),
            ]);

            // 商品を売り切れにする
            $product->update([
                'status' => Product::STATUS_SOLD
            ]);

            // 購入完了後にプロフィール画面にリダイレクト
            return redirect()->route('profile.show')->with('success', '商品を購入しました。');
        } catch(\Exception $e) {
            return redirect()->back()->withInput()->with('error', '購入処理中にエラーが発生しました。再度お試しください。');
        }


    }
    
    // 購入完了画面
    public function complete(Product $product)
    {
        $user = Auth::user();

        // 自分が購入した商品かチェック
        $purchase = Purchase::where('user_id', $user->id)->where('product_id', $product->id)->with(['product.user'])->first();

        if (!$purchase) {
            return redirect()->route('products.index')->with('error', '購入情報が見つかりません。');
        }

        return view('products.index');
    }

    // ユーザーの購入履歴を取得（プロフィール画面用）
    public function index()
    {
        $user = Auth::user();

        $purchases = Purchase::where('user_id', $user->id)->with(['product.user', 'product.categories'])->orderBy('purchased_at', 'desc')->get();

        return view('profile.purchases', compact('purchases'));
    }

    // 特定の購入詳細を表示
    public function showPurchase(Purchase $purchase)
    {
        $user = Auth::user();

        // 自分の購入履歴かチェック
        if ($purchase->user_id !== $user->id) {
            return redirect()->route('profile.show')
                ->with('error', '購入履歴が見つかりません。');
        }

        $purchase->load(['products.user', 'product.categories']);

        return view('profile.purchase-detail', compact('purchase'));
    }
}


