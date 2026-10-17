<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbDepartmentRequest extends FormRequest
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
            'department_code' => 'nullable|string|max:10',
            'department_nameTH' => 'required|string|max:255',
            'department_nameEN' => 'nullable|string|max:255',
            'tdepartment_id' => 'nullable|integer|exists:rdb_department_type,tdepartment_id',
            'department_color' => 'nullable|string|max:20',
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
            'department_nameTH.required' => 'กรุณาระบุชื่อหน่วยงาน/คณะ (ภาษาไทย)',
            'tdepartment_id.exists' => 'ประเภทหน่วยงานไม่ถูกต้อง',
        ];
    }
}
