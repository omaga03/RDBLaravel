<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbDepartmentCourseRequest extends FormRequest
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
        // The primary key for RdbDepartmentCourse is 'depcou_id'
        // Assuming the route parameter is 'id' and represents the depcou_id
        $depcou_id = $this->route('id');

        return [
            'department_id' => 'nullable|integer|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|integer|exists:rdb_department_category,depcat_id',
            'cou_name' => [
                'required',
                'string',
                'max:255',
                // Add unique rule if cou_name needs to be unique (it's not unique in current validate)
                // Rule::unique('rdb_department_course', 'cou_name')->ignore($depcou_id, 'depcou_id'),
            ],
            'cou_name_sh' => 'nullable|string|max:100',
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
