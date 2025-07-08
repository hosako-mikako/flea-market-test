@extends('layouts.app')

@section('title', 'プロフィール')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<!-- プロフィール情報セクション -->
<section class="profile-header">
    <div class="profile-info">
        <div class="profile-image-container">
            @if($user->profile_image && file_exists(storage_path('app/public/' . $user->profile_image)))
            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="profile-image">
            @else
            <div class="profile-placeholder">
                {{ mb_substr($user->name, 0,1) }}
            </div>
            @endif
        </div>
        <h1 class="profile-name">
            {{ $user->name }}
        </h1>
        <a href="{{ route('profile.edit') }}" class="profile-edit-btn">
            プロフィールを編集
        </a>
    </div>
</section>

<!-- 商品タブセクション -->
<section class="products-section">
    <nav class="filter-tabs">
        @if(request('tab') === 'purchased')
        <a href="{{ route('profile.show') }}" class="filter-tab">出品した商品</a>
        <h1 class="filter-tab active">
            <a href="{{ route('profile.show', ['tab' => 'purchased']) }}">購入した商品</a>
        </h1>
        @else
        <h1 class="filter-tab active">
            <a href="{{ route('profile.show') }}">出品した商品</a>
        </h1>
        <a href="{{ route('profile.show', ['tab' => 'purchased']) }}" class="filter-tab">購入した商品</a>
        @endif
    </nav>

    <hr class="section-divider">

    @if(request('tab') === 'purchased')
    <!-- 購入した商品一覧 -->
    @if($purchasedProducts->count() > 0)
    <div class="products-grid">
        @foreach($purchasedProducts as $product)
        <a href="{{ route('products.show', $product->id) }}" class="product-card">
            <div class="product-image-wrapper">
                <img src="{{ asset('storage/' . ($product->image_path ?? 'images/no-image.png')) }}" alt="{{ $product->name }}" class="product-image" onerror="this.src='{{ asset('storage/images/no-image.png') }}'">
                <!-- 売り切れ表示 -->
                @if($product->status === 2)
                <div class="sold-overlay">
                    <span class="sold-text">Sold</span>
                </div>
                @endif
            </div>

            <div class="product-info">
                <h2 class="product-name">{{ $product->name }}</h2>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <p>購入した商品はありません</p>
    @endif
    @else
    <!-- 出品した商品一覧 -->
    @if($listedProducts->count() > 0)
    <div class="products-grid">
        @foreach($listedProducts as $product)
        <a href="{{ route('products.show', $product->id) }}" class="product-card">
            <div class="product-image-wrapper">
                <img src="{{ asset('storage/' . ($product->image_path ?? 'images/no-image.png')) }}" alt="{{ $product->name }}" class="product-image" onerror="this.src='{{ asset('storage/images/no-image.png') }}'">

                <!-- 売り切れ表示 -->
                @if($product->status === 2)
                <div class="sold-overlay">
                    <span class="sold-text">Sold</span>
                </div>
                @endif
            </div>
            <div class="product-info">
                <h2 class="product-name">{{ $product->name }}</h2>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <p>出品した商品はありません</p>
    @endif
    @endif
</section>
@endsection