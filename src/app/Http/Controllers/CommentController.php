<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;

class CommentController extends Controller
{
    // コメントを投稿する
    public function store(CommentRequest $request, Product $product) {

        try {
            // コメントを作成
            $comment = Comment::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'comment' => $request->comment,
            ]);


            // Ajaxリクエストの場合はJSONレスポンス
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'comment' => [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'user_name' => $comment->user->name,
                        'user_profile_image' => $comment->user->profile_image ? asset('storage/' . $comment->user->profile_image) : null, // デフォルト画像はフロントエンド側で制御
                        'created_at' => $comment->created_at->format('Y-m-d H:i'),
                    ]
                ]);
            }

            // 通常のフォーム送信の場合
            return redirect()->route('products.show', $product)->with('success', 'コメントを投稿しました。');
        } catch (\Exception $e) {
            // Ajaxリクエストの場合
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'コメントの投稿に失敗しました。',
                ], 500);
            }

            // 通常のフォーム送信の場合
            return redirect()->back()->withInput()->with('error', 'コメントの投稿に失敗しました。');
        }
    }
}
