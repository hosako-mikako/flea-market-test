<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;

class ProductController extends Controller
{
    // 商品一覧画面（トップ画面）
    public function index(Request $request)
    {
        $search = $request->get('search');

        // 商品データ取得（リレーション含む）
        $query = Product::with(['user', 'categories']);

        // 検索機能
        if ($search) {
            // 大文字小文字を区別しない部分一致検索
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        // ログイン時：自分が出品した商品を除外
        if (Auth::check()) {
            $query->where('user_id', '!=', Auth::id());
        }

        // ページネーションなしで新着順で取得
        $products = $query->orderBy('created_at', 'desc')->get();

        return view('products.index', compact('products', 'search'));
    }

    // マイリスト画面
    public function favorites(Request $request)
    {
        $search = $request->get('search');

        // 認証チェック：未ログインの場合は空表示
        if (!Auth::check()) {
            $products = collect();
            return view('products.index', compact('products', 'search'));
        }

        $query = Product::with(['user', 'categories']);

        // お気に入り商品のみ取得
        $query->whereHas('favorites', function ($q) {
            $q->where('user_id', Auth::id());
        });

        // 検索機能：お気に入り商品の中から商品名で部分一致検索
        if ($search) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        // 自分の商品を除外
        $query->where('user_id', '!=', Auth::id());

        // 新着順で取得
        $products = $query->orderBy('created_at', 'desc')->get();

        return view('products.index', compact('products', 'search'));
    }

    // 商品詳細画面
    public function show(Product $product)
    {
        // 商品データを関連データと一緒に取得
        $product->load(['user', 'categories', 'comments.user']);

        // ログイン時のお気に入り状態のチェック
        $isFavorited = false;
        if (Auth::check()) {
            $isFavorited = $product->favorites()->where('user_id', Auth::id())->exists();
        }

        return view('products.show', compact('product', 'isFavorited'));
    }

    // いいねの切り替え機能 (Ajax対応)
    public function toggleFavorite(Product $product)
    {
        $user = Auth::user();

        // 既にお気に入り登録がされているかチェック
        $favorite = $user->favorites()->where('product_id', $product->id)->first();

        if ($favorite) {
            // いいね済みの場合は削除
            $favorite->delete();
            $isFavorited = false;
            $message = 'お気に入りから削除しました。';
        } else {
            // いいねしていない場合は追加
            $user->favorites()->create([
                'product_id' => $product->id
            ]);
            $isFavorited = true;
            $message = 'お気に入りに追加しました。';
        }

        // 最新のお気に入り数を取得
        $favoritesCount = $product->favoritesCount();

        // Ajaxリクエストの場合はJSONレスポンス
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_favorited' => $isFavorited,
                'favorites_count' => $favoritesCount,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    // 商品出品画面表示
    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    // 商品出品処理
    public function store(ExhibitionRequest $request)
    {
        try {
            // 画像ファイルの保存
            $imagePath = $request->file('image')->store('products', 'public');

            // 商品データの作成
            $product = Product::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'condition' => $request->condition,
                'brand' => $request->brand,
                'status' => Product::STATUS_ACTIVE, // 販売中
                'image_path' => $imagePath,
            ]);

            // カテゴリーの関連付け(最大2つまで制限)
            if ($request->has('categories')) {
                $categories = array_slice($request->categories, 0, 2); // 最大2つまで
                $product->categories()->attach($categories);
            }

            // プロフィール画面にリダイレクト
            return redirect()->route('profile.show')->with('success', '商品を出品しました。');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', '商品の出品に失敗しました。再度お試しください。');
        }
    }
}
