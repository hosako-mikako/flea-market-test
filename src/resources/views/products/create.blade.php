@extends('layouts.app')

@section('title', '商品出品')

@section('css')
<link rel="stylesheet" href="{{ asset('css/exhibition.css') }}">
@endsection

@section('content')
<div class="exhibition-container">
    <h1 class="exhibition-title">
        商品の出品
    </h1>
    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="exhibition-form">
        @csrf

        <!-- 商品画像 -->
        <div class="form-section">
            <h2 class="section-title-image">
                商品画像
            </h2>
            <div class="image-upload-area">
                <div class="image-preview" id="imagePreview">
                    <span class="upload-text">画像を選択する</span>
                </div>
                <input type="file" id="image" name="image" class="image-input" accept="image/jpeg,image/png">
            </div>
            @error('image')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- 商品の詳細 -->
        <div class="form-section">
            <h2 class="section-title">商品の詳細</h2>

            <!-- カテゴリー -->
            <div class="form-group">
                <label class="form-label">
                    カテゴリー
                </label>
                <div class="category-tags">
                    @foreach($categories as $category)
                    <label class="category-tag">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
                        <span class="tag-text">{{ $category->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @error('categories')
            <span class="error-message">{{ $message }}</span>
            @enderror

            <!-- 商品の状態 -->
            <div class="form-group">
                <label for="condition" class="form-label">
                    商品の状態
                </label>
                <select name="condition" id="condition" class="form-select-hidden">
                    <option value="">選択してください</option>
                    @foreach(\App\Models\Product::getConditions() as $value => $name)
                    <option value="{{ $value }}" {{ old('condition') == $value ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>

                <!-- カスタムドロップダウン -->
                <div class="custom-select" id="customSelect">
                    <div class="custom-select-trigger">
                        <span class="custom-select-text placeholder">選択してください</span>
                        <span class="custom-select-arrow">▼</span>
                    </div>
                    <div class="custom-select-options">
                        <div class="custom-option" data-value="">
                            <span class="option-check">✓</span>
                            <span class="option-text">選択してください</span>
                        </div>
                        @foreach(\App\Models\Product::getConditions() as $value => $name)
                        <div class="custom-option" data-value="{{ $value }}">
                            <span class="option-check">✓</span>
                            <span class="option-text">{{ $name }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                @error('condition')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- 商品名と説明 -->
        <div class="form-section">
            <h2 class="section-title">商品名と説明</h2>

            <!-- 商品名 -->
            <div class="form-group">
                <label for="name" class="form-label">商品名</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-input @error('name') error @enderror">
                @error('name')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- ブランド名 -->
            <div class="form-group">
                <label for="brand" class="form-label">ブランド名</label>
                <input type="text" id="brand" name="brand" value="{{ old('brand') }}" class="form-input @error('brand') error @enderror">
                @error('brand')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- 商品の説明 -->
            <div class="form-group">
                <label for="description" class="form-label">商品の説明</label>
                <textarea id="description" name="description" class="form-textarea @error('description') error @enderror" rows="5">{{ old('description') }}</textarea>
                @error('description')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- 販売価格 -->
        <div class="form-section">
            <!-- 価格 -->
            <div class="form-group">
                <div class="price-row">
                    <span class="price-label">販売価格</span>
                    <div class="price-input-wrapper">
                        <span class="price-symbol">¥</span>
                        <input type="number" id="price" name="price" value="{{ old('price') }}" class="form-input price-input @error('price') error @enderror" min="1" max="9999999">
                    </div>
                </div>
                @error('price')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- 出品ボタン -->
        <div class="form-actions">
            <button type="submit" class="exhibition-btn">出品する</button>
        </div>
    </form>
</div>

<script>
    // 画面プレビュー機能
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="商品画像" class="preview-image">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<span class="upload-text">画像を選択する</span>';
        }
    });

    // 画像アップロードエリアクリック
    document.getElementById('imagePreview').addEventListener('click', function() {
        document.getElementById('image').click();
    });

    // カテゴリー選択制限（最大2個）
    const categoryTags = document.querySelectorAll('input[name="categories[]"]');
    const categoryLabels = document.querySelectorAll('.category-tag');

    categoryLabels.forEach((label, index) => {
        label.addEventListener('click', function(e) {
            e.preventDefault();
            const checkbox = categoryTags[index];
            const checkedBoxes = document.querySelectorAll('input[name="categories[]"]:checked');

            if (checkbox.checked) {
                // チェックを外す
                checkbox.checked = false;
                label.classList.remove('selected');
            } else if (checkedBoxes.length < 2) {
                // チェックを付ける（２個未満の場合のみ）
                checkbox.checked = true;
                label.classList.add('selected');
            }

            // 2個選択時は他を無効化
            updateCategoryState();
        });
    });

    function updateCategoryState() {
        const checkedBoxes = document.querySelectorAll('input[name="categories[]"]:checked');
        categoryLabels.forEach((label, index) => {
            const checkbox = categoryTags[index];
            if (checkedBoxes.length >= 2 && !checkbox.checked) {
                label.classList.add('disabled');
            } else {
                label.classList.remove('disabled');
            }
        });
    }

    // 初期状態の設定
    categoryTags.forEach((checkbox, index) => {
        if (checkbox.checked) {
            categoryLabels[index].classList.add('selected');
        }
    });
    updateCategoryState();

    // カスタムセレクト機能
    const customSelect = document.getElementById('customSelect');
    const hiddenSelect = document.getElementById('condition');
    const trigger = customSelect.querySelector('.custom-select-trigger');
    const options = customSelect.querySelectorAll('.custom-option');
    const textElement = customSelect.querySelector('.custom-select-text');

    // 初期状態の設定
    const initialValue = hiddenSelect.value;
    if (initialValue) {
        const initialOption = customSelect.querySelector(`[data-value="${initialValue}"]`);
        if (initialOption) {
            selectOption(initialOption);
        }
    }

    // トリガークリックでドロップダウン開閉
    trigger.addEventListener('click', function(){
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
        textElement.className = value ? 'custom-select-text' : 'custom-select-text placeholder';

        // 選択状態を更新
        options.forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
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
</script>
@endsection