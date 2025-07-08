<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'フリマアプリ')</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">

    @yield('css')

<body>
    <header class="header">
        <div class="header-container">
            <img src="{{ asset('storage/images/logo-images/logo.svg') }}" alt="フリマアプリ" class="logo-image">
            <form action="{{ route('products.index') }}" method="get" class="search-form">
                <div class="search-bar">
                    <input type="text" name="search" class="search-input" placeholder="何をお探しですか？" value="{{ request('search') }}">
                </div>
            </form>
            <nav class="nav-links">
                @guest
                <!-- 未ログイン時 -->
                <a href="{{ route('login') }}" class="nav-link">ログイン</a>
                <a href="{{ route('login') }}" class="nav-link">マイページ</a>
                <a href="{{ route('login') }}" class="nav-link">出品</a>
                @else
                <!-- ログイン時 -->
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-link logout-btn">ログアウト</button>
                </form>
                <a href="{{ route('profile.show') }}" class="nav-link">マイページ</a>
                <a href="{{ route('products.create') }}" class="nav-link">出品</a>
                @endguest
            </nav>
        </div>
    </header>
    <main class="main-container">
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </main>
</body>

</html>