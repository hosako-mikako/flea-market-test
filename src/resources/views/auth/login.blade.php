@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h1 class="auth-title">
        ログイン
    </h1>

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
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

        <div class="form-actions">
            <button type="submit" class="auth-btn">ログインする</button>
        </div>
    </form>

    <div class="auth-links">
        <a href="{{ route('register') }}" class="auth-link">会員登録はこちら</a>
    </div>
</div>
@endsection