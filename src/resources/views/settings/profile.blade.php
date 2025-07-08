@extends('layouts.app')

@section('title', 'プロフィール設定')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">
            プロフィール設定
        </h1>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="auth-form">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <div class="image-upload-section">
                    <div class="current-image">
                        <div class="image-preview-container" id="imagePreviewContainer">
                            @if($user->profile_image)
                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="プロフィール画像" class="profile-preview" id="imagePreview">
                            @else
                            <div class="profile-placeholder" id="imagePreview">
                            </div>
                            @endif
                        </div>
                        <div class="image-upload-controls">
                            <input type="file" id="profile_image" name="profile_image" class="image-input @error('profile_image') error @enderror" accept="image/*">
                            <label for="profile_image" class="upload-btn">画像を選択する</label>
                        </div>
                    </div>
                    @error('profile_image')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">
                        ユーザー名
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-input @error('name') error @enderror">
                    @error('name')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="postal_code" class="form-label">
                        郵便番号
                    </label>
                    <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $user->profile_postal_code ?? '') }}" class="form-input @error('postal_code') error @enderror">
                    @error('postal_code')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="address" class="form-label">
                        住所
                    </label>
                    <input type="text" id="address" name="address" value="{{ old('address', $user->profile_address ?? '') }}" class="form-input @error('address') error @enderror">
                    @error('address')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="building" class="form-label">
                        建物名
                    </label>
                    <input type="text"
                        id="building"
                        name="building"
                        value="{{ old('building', $user->profile_building ?? '') }}"
                        class="form-input @error('building') error @enderror">
                    @error('building')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-actions">
                    <button type="submit" class="auth-btn">更新する</button>
                </div>
        </form>
    </div>
</div>

<script>
    // 画像プレビュー機能
    document.getElementById('profile_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const container = document.getElementById('imagePreviewContainer');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.innerHTML = '';

                // 新しい画像要素を作成
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'プロフィール画像プレビュー';
                img.className = 'profile-preview';
                img.id = 'imagePreview';

                // コンテナに追加
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        } else {
            // ファイルが選択されていない場合はプレースホルダーを表示
            container.innerHTML = '<div class="profile-placeholder" id="imagePlaceholder"></div>';
        }
    });

    // 郵便番号の自動フォーマット
    document.getElementById('postal_code').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length >= 3) {
            value = value.slice(0, 3) + '-' + value.slice(3, 7);
        }
        e.target.value = value;
    });
</script>
@endsection