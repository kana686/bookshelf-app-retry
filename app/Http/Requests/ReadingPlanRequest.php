<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => [
                'required',
                'exists:books,id',
            ],
            'target_date' => [
                'required',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください',
            'book_id.exists' => '選択された書籍が存在しません',
            'target_date.required' => '目標日を入力してください',
            'target_date.date' => '有効な日付形式で入力してください',
        ];
    }
}
