<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbDepartmentCourseRequest extends FormRequest
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
            'department_id' => 'nullable|integer|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|integer|exists:rdb_department_category,depcat_id',
            'cou_name' => 'required|string|max:255',
            'cou_name_sh' => 'nullable|string|max:100',
            // 'user_created', 'user_updated', 'created_at', 'updated_at' are handled by controller
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
            'cou_name.required' => 'กรุณาระบุชื่อหลักสูตร',
            'department_id.exists' => 'หน่วยงาน/คณะไม่ถูกต้อง',
            'depcat_id.exists' => 'ประเภทหน่วยงานไม่ถูกต้อง',
        ];
    }
}
