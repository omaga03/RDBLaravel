<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbDepartmentCategoryRequest extends FormRequest
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
        // The primary key for RdbDepartmentCategory is 'depcat_id'
        // Assuming the route parameter is 'id' and represents the depcat_id
        $depcat_id = $this->route('id');

        return [
            'depcat_name' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('table_name', 'column_name')->ignore($id, 'id_column')
                Rule::unique('rdb_department_category', 'depcat_name')->ignore($depcat_id, 'depcat_id'),
            ],
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
            'depcat_name.unique' => 'ชื่อประเภทหน่วยงานนี้มีอยู่ในระบบแล้ว',
            'depcat_name.required' => 'กรุณาระบุชื่อประเภทหน่วยงาน',
        ];
    }
}
