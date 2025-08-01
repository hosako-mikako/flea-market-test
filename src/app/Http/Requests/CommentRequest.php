<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'comment' => 'required|max:255',
        ];
    }

    // カスタムエラーメッセージ
    public function messages()
    {
        return [
            'comment.required' => 'コメントを入力してください。',
            'comment.max' => 'コメントは255字以内で入力してください。',
        ];
    }


    // バリデーション項目名
    public function attributes()
    {
        return [
            'comment' => 'コメント',
        ];
    }
}
