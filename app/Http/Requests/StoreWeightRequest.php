<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeightRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'weight' => 'required|numeric|min:20|max:300',
            'record_at' => 'required|date|before_or_equal:today',
            'note' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'weight.required' => '體重為必填項目',
            'weight.numeric' => '體重必須為數字',
            'weight.min' => '體重不能少於 20 公斤',
            'weight.max' => '體重不能超過 300 公斤',
            'record_at.required' => '記錄日期為必填項目',
            'record_at.date' => '請輸入有效的日期',
            'record_at.before_or_equal' => '記錄日期不能是未來日期',
            'note.max' => '備註不能超過 500 個字元',
        ];
    }

}
