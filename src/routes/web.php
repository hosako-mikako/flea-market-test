<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// 公開ページ（認証不要）
Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

//　認証が必要な機能
Route::middleware('auth')->group(function() {
    // マイリスト
    Route::get('/mylist', [ProductController::class, 'favorites'])->name('products.favorites');

    // いいね機能
    Route::post('/products/{product}/favorite', [ProductController::class, 'toggleFavorite'])->name('products.favorite');

    // 商品出品
    Route::get('/sell', [ProductController::class, 'create'])->name('products.create');
    Route::post('/sell', [ProductController::class, 'store'])->name('products.store');

    //プロフィール機能
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // コメント機能
    Route::post('/products/{product}/comments', [CommentController::class, 'store'])->name('comments.store');

    // 商品購入画面・処理
    Route::get('/products/{product}/purchase', [PurchaseController::class, 'show'])->name('purchase.show');
    Route::post('/products/{product}/purchase', [PurchaseController::class, 'store'])->name('purchase.store');

    //送付先住所変更(購入時専用)
    Route::get('/purchase/{product}/address', [AddressController::class, 'edit'])->name('address.edit');
    Route::patch('/purchase/{product}/address', [AddressController::class, 'update'])->name('address.update');
});
