<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0', 'max:9999999'],
            'condition' => ['required', 'integer', 'in:1,2,3,4'],
            'brand' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpeg,png', 'max:2048'],
            'categories' => ['required', 'array', 'min:1', 'max:2'],
            'categories.*' => ['exists:categories,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '商品名を入力してください。',
            'name.string' => '商品名は文字列で入力してください。',
            'name.max' => '商品名は255文字以下で入力してください。',
            'description.required' => '商品説明を入力してください。',
            'description.string' => '商品説明は文字列で入力してください。',
            'description.max' => '商品説明は255文字以下で入力してください。',
            'price.required' => '価格を入力してください。',
            'price.integer' => '価格は数値で入力してください。',
            'price.min' => '価格は0円以上で入力してください。',
            'price.max' => '価格は9,999,999円以下で入力してください。',
            'condition.required' => '商品の状態を選択してください。',
            'condition.integer' => '商品の状態は数値で選択してください。',
            'condition.in' => '商品の状態は正しい値を選択してください。',
            'brand.required' => 'ブランド名を入力してください。',
            'brand.string' => 'ブランド名は文字列で入力してください。',
            'brand.max' => 'ブランド名は255文字以下で入力してください。',
            'image.required' => '商品画像を選択してください。',
            'image.image' => '商品画像は画像ファイルを選択してください。',
            'image.mimes' => '商品画像はjpeg、png形式のファイルを選択してください。',
            'image.max' => '商品画像は2MB以下のファイルを選択してください。',
            'categories.required' => 'カテゴリーを選択してください。',
            'categories.array' => 'カテゴリーは配列で選択してください。',
            'categories.min' => 'カテゴリーを1つ以上選択してください。',
            'categories.max' => 'カテゴリーは2つまで選択してください。',
            'categories.*.exists' => '選択されたカテゴリーが存在しません。',
        ];
    }
}
