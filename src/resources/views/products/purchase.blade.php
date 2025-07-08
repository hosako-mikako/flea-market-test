@extends('layouts.app')

@section('title', '商品購入')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
<div class="purchase-container">
    <form method="POST" action="{{ route('purchase.store', $product) }}" class="purchase-form">
        @csrf

        <!-- 左側：商品情報・配送先 -->
        <div class="purchase-form-left">
            <!-- 商品情報セクション -->
            <div class="product-info-section">
                <div class="product-image">
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-image-small" onerror="this.src='{{ asset('storage/images/no-image.png') }}'">
                </div>
                <div class="product-details">
                    <h2 class="product-name">{{ $product->name }}</h2>
                    <div class="product-price">¥<span class="price-number">{{ number_format((int)$product->price) }}</span></div>
                </div>
            </div>

            <hr class="section-divider">

            <!-- 支払方法セクション -->
            <div class="payment-section">
                <h3 class="section-title">
                    支払方法
                </h3>

                <!-- 元のselect（隠す) -->
                <select name="payment_method" id="payment_method" class="form-select-hidden">
                    <option value="">選択してください</option>
                    <option value="convenience_store" {{ old('payment_method') == 'convenience_store' ? 'selected' : '' }}>コンビニ払い</option>
                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>カード払い</option>
                </select>

                <!-- カスタムドロップダウン -->
                <div class="custom-select" id="paymentSelect">
                    <div class="custom-select-trigger">
                        <span class="custom-select-text placeholder">選択してください</span>
                        <span class="custom-select-arrow">▼</span>
                    </div>
                    <div class="custom-select-options">
                        <div class="custom-option" data-value="convenience_store">
                            <span class="option-check">✓</span>
                            <span class="option-text">コンビニ払い</span>
                        </div>
                        <div class="custom-option" data-value="card">
                            <span class="option-check">✓</span>
                            <span class="option-text">カード払い</span>
                        </div>
                    </div>
                </div>
                @error('payment_method')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <hr class="section-divider">

            <!-- 配送先セクション -->
            <div class="shipping-section">
                <div class="shipping-header">
                    <h3 class="section-title">配送先</h3>
                    <a href="{{ route('address.edit', $product) }}" class="change-address-btn">変更する</a>
                </div>

                <div class="address-info">
                    @if ($defaultAddress['postal_code'] && $defaultAddress['address'])
                    <div class="postal-code">〒{{ $defaultAddress['postal_code'] }}</div>
                    <div class="address">{{ $defaultAddress['address'] }}</div>
                    @if ($defaultAddress['building'])
                    <div class="building">{{ $defaultAddress['building'] }}</div>
                    @endif
                    @else
                    <div class="no-address">
                        <p>配送先住所が登録されていません</p>
                        <a href="{{ route('address.edit', $product) }}" class="register-address-btn">住所を登録する</a>
                    </div>
                    @endif
                </div>

                <!-- shipping_addressバリデーション用エラーメッセージ -->
                @error('shipping_address')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- 隠しフィールドで住所情報を送信 -->
            <input type="hidden" name="postal_code" value="{{ $defaultAddress['postal_code'] ?? '' }}">
            <input type="hidden" name="address" value="{{ $defaultAddress['address'] ?? '' }}">
            <input type="hidden" name="building" value="{{ $defaultAddress['building'] ?? '' }}">

            <!-- shipping_addressフィールド（バリデーション用） -->
            <input type="hidden" name="shipping_address" value="{{ ($defaultAddress['postal_code'] && $defaultAddress['address']) ? '1' : '' }}">


            <hr class="section-divider">

        </div>


        <!-- 右側：購入確認サマリー -->
        <div class="purchase-right-area">
            <!-- 購入確認サマリー -->
            <div class="purchase-summary">
                <!-- 購入内容確認 -->
                <div class="summary-section">
                    <div class="summary-row">
                        <span class="summary-label">商品代金</span>
                        <span class="summary-value">¥{{ number_format((int)$product->price) }}</span>
                    </div>
                    <div class="summary-row payment-method-display">
                        <span class="summary-label">支払方法</span>
                        <span class="summary-value" id="selectedPaymentMethod">-</span>
                    </div>
                </div>
            </div>

            <!-- 購入ボタン -->
            <div class="purchase-button-section">
                <button type="submit" class="purchase-btn" id="purchaseBtn">購入する</button>
            </div>
        </div>
    </form>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {

        // カスタムセレクト機能
        const customSelect = document.getElementById('paymentSelect');
        const hiddenSelect = document.getElementById('payment_method');
        const trigger = customSelect.querySelector('.custom-select-trigger');
        const options = customSelect.querySelectorAll('.custom-option');
        const textElement = customSelect.querySelector('.custom-select-text');
        const selectedPaymentDisplay = document.getElementById('selectedPaymentMethod');

        // 初期状態の設定
        const initialValue = hiddenSelect.value;
        if (initialValue) {
            const initialOption = customSelect.querySelector(`[data-value="${initialValue}"]`);
            if (initialOption) {
                selectOption(initialOption);
            }
        }

        // トリガークリックでドロップダウン開閉
        trigger.addEventListener('click', function() {
            customSelect.classList.toggle('open');
        });

        // オプション選択
        options.forEach(option => {
            option.addEventListener('click', function() {
                selectOption(this);
                customSelect.classList.remove('open');
            });
        });

        // オプション選択処理
        function selectOption(option) {
            const value = option.dataset.value;
            const text = option.querySelector('.option-text').textContent;

            // 隠しselectの値を更新
            hiddenSelect.value = value;

            // 表示テキストを更新
            textElement.textContent = text;
            textElement.className = 'custom-select-text';

            // 選択状態を更新
            options.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');

            // サマリーに支払方法を表示
            selectedPaymentDisplay.textContent = text;

        }

        // 外側クリックで閉じる
        document.addEventListener('click', function(e) {
            if (!customSelect.contains(e.target)) {
                customSelect.classList.remove('open');
            }
        });

        // キーボード操作対応
        customSelect.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                customSelect.classList.toggle('open');
            } else if (e.key === 'Escape') {
                customSelect.classList.remove('open');
            }
        });
    });
</script>
@endsection