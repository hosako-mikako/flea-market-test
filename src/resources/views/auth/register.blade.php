@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h1 class="auth-title">
        会員登録
    </h1>

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <div class="form-group">
            <label for="name" class="form-label">
                ユーザー名
            </label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-input @error('name') error @enderror">
            @error('name')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">
                メールアドレス
            </label>
            <input type="text" id="email" name="email" value="{{ old('email') }}" class="form-input @error('email') error @enderror">
            @error('email')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">
                パスワード
            </label>
            <input type="password" id="password" name="password" class="form-input @error('password') error @enderror">
            @error('password')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">
                パスワード（確認）
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input @error('password_confirmation') error @enderror">
            @error('password_confirmation')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="auth-btn">登録する</button>
        </div>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}" class="auth-link">ログインはこちら</a>
    </div>
</div>
@endsection