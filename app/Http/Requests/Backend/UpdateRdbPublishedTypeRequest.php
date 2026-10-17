<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbPublishedTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller's Gate
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // The primary key for RdbPublishedType is 'pubtype_id'
        // Assuming the route parameter is 'id' and represents the pubtype_id
        $pubtype_id = $this->route('id');

        return [
            'pubtype_group' => 'required|string|max:255',
            'pubtype_grouptype' => 'nullable|string|max:255',
            'pubtype_subgroup' => 'nullable|string|max:255',
            'pubtype_score' => 'nullable|numeric|between:0,99.99',
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'pubtype_group.required' => 'กรุณาระบุกลุ่มประเภท',
            'pubtype_group.string' => 'กลุ่มประเภทต้องเป็นตัวอักษร',
            'pubtype_group.max' => 'กลุ่มประเภทต้องไม่เกิน 255 ตัวอักษร',
            'pubtype_score.numeric' => 'คะแนนต้องเป็นตัวเลข',
            'pubtype_score.between' => 'คะแนนต้องอยู่ระหว่าง 0 ถึง 99.99',
        ];
    }
}