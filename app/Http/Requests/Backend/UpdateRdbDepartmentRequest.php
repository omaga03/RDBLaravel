<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRdbDepartmentRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'department_code' => 'nullable|max:20|unique:rdb_department,department_code,' . $this->route('rdbdepartment') . ',department_id',
            'department_nameTH' => 'required|max:255|unique:rdb_department,department_nameTH,' . $this->route('rdbdepartment') . ',department_id',
            'tdepartment_id' => 'nullable|exists:rdb_department_type,tdepartment_id',
            'department_nameEN' => 'nullable|string|max:255',
            'department_color' => 'nullable|string|max:7', // Hex color code
        ];
    }

    public function messages(): array
    {
        return [
            'department_code.unique' => 'รหัสหน่วยงานนี้มีอยู่ในระบบแล้ว',
            'department_nameTH.unique' => 'ชื่อหน่วยงานนี้มีอยู่ในระบบแล้ว',
        ];
    }
}
