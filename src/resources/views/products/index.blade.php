@extends('layouts.app')

@section('title', 'フリマアプリ - 商品一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<section class="products-section">
    <nav class="filter-tabs">
        @if(request()->routeIs('products.index'))
        <h1 class="filter-tab active">
            <a href="{{ route('products.index') }}">おすすめ</a>
        </h1>
        @auth
        <a href="{{ route('products.favorites') }}" class="filter-tab">マイリスト</a>
        @else
        <a href="{{ route('login') }}" class="filter-tab">マイリスト</a>
        @endauth
        @else
        <a href="{{ route('products.index') }}" class="filter-tab">おすすめ</a>
        <h1 class="filter-tab active">
            <a href="{{ route('products.favorites') }}">マイリスト</a>
        </h1>
        @endif
    </nav>

    <hr class="section-divider">

    @if($products->count() > 0)
    <div class="products-grid">
        @foreach($products as $product)
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
    <p>商品がありません</p>
    @endif
</section>
@endsection