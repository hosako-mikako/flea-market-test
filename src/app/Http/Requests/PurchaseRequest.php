<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment_method' => 'required|in:convenience_store,card',
            'shipping_address' => 'required',
        ];
    }

    // カスタムエラーメッセージ
    public function messages() {
        return [
            'payment_method.required' => '支払方法を選択してください。',
            'payment_method.in' => '有効な支払方法を選択してください。',
            'shipping_address.required' => '配送先を選択してください。',
        ];
    }

    // バリデーション項目名
    public function attributes()
    {
        return [
            'payment_method' => '支払方法',
            'shipping_address' => '配送先',
        ];
    }
}
