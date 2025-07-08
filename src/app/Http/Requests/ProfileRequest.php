<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileRequest extends FormRequest
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
        $userId = Auth::id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'profile_image' => ['required', 'image', 'mimes:jpeg,png'],
            'postal_code' => ['required', 'string', 'regex:/^\d{3}-\d{4}$|^\d{7}$/'],
            'address' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages() {
        return [
            'name.required' => 'お名前を入力してください。',
            'name.string' => 'お名前は文字列で入力してください。',
            'name.max' => 'お名前は255文字以下で入力してください。',
            'profile_image.required' => 'プロフィール画像を選択してください。',
            'profile_image.image' => 'プロフィール画像は画像ファイルを選択してください。',
            'profile_image.mimes' => 'プロフィール画像はjpeg、png形式のファイルを選択してください。',
            'postal_code.required' => '郵便番号を入力してください。',
            'postal_code.regex' => '郵便番号は正しい形式で入力してください（例：123-4567）。',
            'address.required' => '住所を入力してください。',
            'address.string' => '住所は文字列で入力してください。',
            'address.max' => '住所は255文字以下で入力してください。',
            'building.string' => '建物名は文字列で入力してください。',
            'building.max' => '建物名は255文字以下で入力してください。',
        ];
    }
}
