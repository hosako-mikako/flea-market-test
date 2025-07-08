@extends('layouts.app')

@section('title', '配送先住所変更')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form.css') }}">
@endsection

@section('content')
<div class="form-container">
    <h1 class="form-title">住所の変更</h1>

    <form method="POST" action="{{ route('address.update', $product) }}" class="address-form">
        @csrf
        @method('PATCH')

        <!-- 郵便番号 -->
        <div class="form-group">
            <label for="postal_code" class="form-label">郵便番号</label>
            <input type="text" id="postal_code" name="postal_code" class="form-input" value="">
            @error('postal_code')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- 住所 -->
        <div class="form-group">
            <label for="address" class="form-label">住所</label>
            <input type="text" id="address" name="address" class="form-input" value="">
            @error('address')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- 建物名 -->
        <div class="form-group">
            <label for="building" class="form-label">建物名</label>
            <input type="text" id="building" name="building" class="form-input" value="">
            @error('building')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- 送信ボタン -->
        <div class="form-actions">
            <button type="submit" class="form-submit-btn">
                更新する
            </button>
        </div>
    </form>
</div>
@endsection