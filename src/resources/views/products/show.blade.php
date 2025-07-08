@extends('layouts.app')

@section('title', $product->name . ' - 商品詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="product-detail-container">
    <div class="product-detail-main">
        <!-- 商品画像 -->
        <div class="product-image-section">
            <div class="product-image-wrapper">
                <img src="{{ asset('storage/' . $product->image_path) }}"
                    alt="{{ $product->name }}"
                    class="product-detail-image"
                    onerror="this.src='{{ asset('storage/images/no-image.png') }}'">
            </div>
        </div>

        <!-- 商品情報 -->
        <div class="product-info-section">
            <div class="product-header">
                <h1 class="product-title">{{ $product->name }}</h1>
                <div class="product-brand">{{ $product->brand ?: 'ブランド名なし' }}</div>
                <div class="product-price">¥<span class="price-number">{{ number_format($product->price) }}</span>（税込）</div>


                <!-- いいね・コメント -->
                <div class="product-stats">
                    @auth
                    @if($product->user_id !== Auth::id())
                    <div class="stat-item">
                        <img src="{{ asset('storage/images/icons/star-icon.png') }}" alt="いいね"
                            class="like-icon {{ $isFavorited ? 'favorited' : '' }}"
                            data-product-id="{{ $product->id }}">
                        <span class="like-count">{{ $product->favoritesCount() }}</span>
                    </div>
                    @else
                    <div class="stat-item">
                        <img src="{{ asset('storage/images/icons/star-icon.png') }}" alt="いいね" class="like-icon disabled">
                        <span class="like-count">{{ $product->favoritesCount() }}</span>
                    </div>
                    @endif
                    @else
                    <!-- 未ログイン：ログイン画面にリダイレクト -->
                    <div class="stat-item">
                        <a href="{{ route('login') }}">
                            <img src="{{ asset('storage/images/icons/star-icon.png') }}" alt="いいね" class="like-icon">
                        </a>
                        <span class="loke-count">{{ $product->favoritesCount() }}</span>
                    </div>
                    @endauth

                    <div class="stat-item">
                        <img src="{{ asset('storage/images/icons/comment-icon.png') }}" alt="コメント" class="comment-icon">
                        <span class="comment-count">{{ $product->commentsCount() }}</span>
                    </div>
                </div>
            </div>

            <!-- 購入ボタン -->
            <div class="purchase-section">
                @auth
                <a href="{{ route('purchase.show', $product) }}" class="purchase-btn">購入手続き</a>
                @else
                <a href="{{ route('login') }}" class="purchase-btn">ログインして購入</a>
                @endauth
            </div>

            <!-- 商品説明 -->
            <div class="product-description">
                <h2 class="section-title">商品説明</h2>
                <p class="description-text">{{ $product->description }}</p>
            </div>

            <!-- 商品情報 -->
            <div class="product-details">
                <h2 class="section-title">商品情報</h2>
                <div class="detail-table">
                    <div class="detail-row">
                        <div class="detail-label">カテゴリー</div>
                        @if($product->categories->count() > 0)
                        @foreach($product->categories as $category)
                        <span class="category-tag">{{ $category->name }}</span>
                        @endforeach
                        @else
                        <span class="no-category">カテゴリなし</span>
                        @endif
                    </div>
                    <div class="detail-row">
                        <div class="detail-label-status">商品の状態</div>
                        <div class="detail-value">
                            {{ $product->getConditionName() }}
                        </div>
                    </div>
                </div>
            </div>
            <!-- コメント一覧 -->
            <div class="comments-section">
                <h2 class="section-title-comment">コメント <span class="comment-count-text">({{ $product->commentsCount() }})</span></h2>

                <!-- 成功メッセージ -->
                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif
                <!-- エラーメッセージ -->
                @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <div class="comments-list" id="commentsList">
                    @if ($product->comments->count() > 0)
                    @foreach ($product->comments as $comment)
                    <div class="comment-item">
                        <div class="comment-user">
                            <!-- 実際のプロフィール画像がある場合 -->
                            @if($comment->user->profile_image && file_exists(storage_path('app/public/' . $comment->user->profile_image)))
                            <!-- 実際のプロフィール画像がある場合 -->
                            <img src="{{ asset('storage/' . $comment->user->profile_image) }}"
                                alt="{{ $comment->user->name }}"
                                class="comment-user-image">
                            @else
                            <!-- デフォルトプレースホルダー -->
                            <div class="comment-user-placeholder">
                                {{ mb_substr($comment->user->name, 0, 1) }}
                            </div>
                            @endif
                            <span class="user-name">{{ $comment->user->name }}</span>
                        </div>
                        <div class="comment-content">
                            <p>{{ $comment->comment }}</p>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="no-comments" id="noCommentsMessage">
                        <p>まだコメントがありません</p>
                    </div>
                    @endif
                </div>

                <!-- コメント投稿フォーム（ログイン時のみ表示） -->
                @auth
                <div class="comment-form">
                    <form action="{{ route('comments.store', $product) }}" method="POST" class="comment-form-inner" id="commentForm">
                        @csrf
                        <label for="comment" class="form-label">商品へのコメント</label>
                        <div class="form-group">
                            <textarea name="comment" id="comment" class="comment-textarea" placeholder="コメントを入力してください" maxlength="255"></textarea>
                        </div>
                        <button type="submit" class="comment-submit-btn">コメントを送信する</button>
                    </form>
                </div>
                @else
                <div class="login-prompt">
                    <p class="comment-submit-btn"><a href="{{ route('login') }}" class="login-btn">ログイン</a>してコメントを投稿</p>
                </div>
                @endauth
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // いいねアイコンとそのカウント要素を全て取得
        const likeIcons = document.querySelectorAll('.like-icon:not(.disabled)');


        likeIcons.forEach(likeIcon => {
            const likeCount = likeIcon.closest('.stat-item').querySelector('.like-count');

            likeIcon.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Ajax リクエスト
                fetch(`/products/${productId}/favorite`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // いいね数を更新
                            likeCount.textContent = data.favorites_count;

                            // アイコンの状態を切り替え
                            if (data.is_favorited) {
                                likeIcon.classList.add('favorited'); // アイコンが黄色に
                            } else {
                                likeIcon.classList.remove('favorited'); // アイコンが元に戻る
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });

        // コメント投稿機能
        const commentForm = document.getElementById('commentForm');
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault(); // フォームの通常送信を防ぐ

                const formData = new FormData(this);
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 新しいコメントを追加
                            addNewComment(data.comment);

                            // フォームをリセット
                            document.getElementById('comment').value = '';

                            // コメント数を更新
                            updateCommentCount();

                            // 「まだコメントがありません」メッセージを非表示
                            const noCommentsMessage = document.getElementById('noCommentsMessage');
                            if (noCommentsMessage) {
                                noCommentsMessage.style.display = 'none';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('コメントの投稿に失敗しました。');
                    });
            });
        }

        // 新しいコメントを表示に追加する関数
        function addNewComment(comment) {
            const commentsList = document.getElementById('commentsList');
            const newCommentHtml = `
                <div class="comment-item">
                    <div class="comment-user">
                        <img src="${comment.user_profile_image}" alt="${comment.user_name}" class="comment-user-image">
                        <span class="user-name">${comment.user_name}</span>
                    </div>
                    <div class="comment-content">
                        <p>${comment.comment}</p>
                    </div>
                </div>
            `;
            commentsList.insertAdjacentHTML('beforeend', newCommentHtml);
        }

        // コメント数を更新する関数
        function updateCommentCount() {
            const commentCountElements = document.querySelectorAll('.comment-count, .comment-count-text');
            commentCountElements.forEach(element => {
                const currentCount = parseInt(element.textContent.replace(/[()]/g, ''));
                element.textContent = element.classList.contains('comment-count-text') ?
                    `(${currentCount + 1})` :
                    currentCount + 1;
            });
        }
    });
</script>
@endsection