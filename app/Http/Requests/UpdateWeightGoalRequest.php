<?php

namespace App\Http\Requests;

use App\Models\WeightGoal;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWeightGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $goal = $this->route('goal');
        return $goal && $goal->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'target_weight' => 'required|numeric|min:20|max:300',
            'target_date' => 'required|date|after:today',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'target_weight.required' => '目標體重為必填項目',
            'target_weight.numeric' => '目標體重必須為數字',
            'target_weight.min' => '目標體重不能少於 20 公斤',
            'target_weight.max' => '目標體重不能超過 300 公斤',
            'target_date.required' => '目標日期為必填項目',
            'target_date.date' => '請輸入有效的日期',
            'target_date.after' => '目標日期必須是未來日期',
            'description.max' => '描述不能超過 500 個字元',
        ];
    }
}
