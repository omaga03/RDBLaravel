<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbPublishedTypeAuthorRequest extends FormRequest
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
        return [
            'pubta_nameTH' => 'required|string|max:255|unique:rdb_published_type_author,pubta_nameTH',
            'pubta_nameEN' => 'nullable|string|max:255|unique:rdb_published_type_author,pubta_nameEN',
            'pubta_score' => 'nullable|numeric',
            // 'created_at', 'updated_at' are handled by controller
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
            'pubta_nameTH.unique' => 'ชื่อประเภทผู้แต่ง (ไทย) นี้มีอยู่ในระบบแล้ว',
            'pubta_nameTH.required' => 'กรุณาระบุชื่อประเภทผู้แต่ง (ไทย)',
            'pubta_nameEN.unique' => 'ชื่อประเภทผู้แต่ง (อังกฤษ) นี้มีอยู่ในระบบแล้ว',
            'pubta_score.numeric' => 'คะแนนต้องเป็นตัวเลข',
        ];
    }
}
