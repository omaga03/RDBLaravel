<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbDepMajorRequest extends FormRequest
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
        // The primary key for RdbDepMajor is 'maj_code'
        // Assuming the route parameter is 'id' and represents the maj_code
        $maj_code = $this->route('id');

        return [
            'depcou_id' => 'nullable|integer',
            'department_id' => 'required|integer',
            'maj_nameTH' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('table_name', 'column_name')->ignore($id, 'id_column')
                Rule::unique('rdb_dep_major', 'maj_nameTH')->ignore($maj_code, 'maj_code'),
            ],
            'maj_nameEN' => 'nullable|string|max:255',
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
            'maj_nameTH.unique' => 'ชื่อสาขานี้มีอยู่ในระบบแล้ว',
            'maj_nameTH.required' => 'กรุณาระบุชื่อสาขา (ภาษาไทย)',
            'department_id.required' => 'กรุณาเลือกหน่วยงาน/คณะ',
        ];
    }
}
