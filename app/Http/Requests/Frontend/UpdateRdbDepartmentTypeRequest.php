<?php

namespace App\Http->Requests\Frontend;

use Illuminate->Foundation->Http->FormRequest;
use Illuminate->Validation->Rule;

class UpdateRdbDepartmentTypeRequest extends FormRequest
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
     * @return array<string, \Illuminate->Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // The primary key for RdbDepartmentType is 'tdepartment_id'
        // Assuming the route parameter is 'id' and represents the tdepartment_id
        $tdepartment_id = $this->route('id');

        return [
            'tdepartment_nameTH' => [
                'required',
                'string',
                'max:255',
                // Add unique rule if needed
                // Rule::unique('rdb_department_type', 'tdepartment_nameTH')->ignore($tdepartment_id, 'tdepartment_id'),
            ],
            'tdepartment_nameEN' => 'nullable|string|max:255',
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
            'tdepartment_nameTH.required' => 'กรุณาระบุชื่อประเภทหน่วยงาน (ภาษาไทย)',
            'tdepartment_nameTH.string' => 'ชื่อประเภทหน่วยงาน (ภาษาไทย) ต้องเป็นตัวอักษร',
            'tdepartment_nameTH.max' => 'ชื่อประเภทหน่วยงาน (ภาษาไทย) ต้องไม่เกิน 255 ตัวอักษร',
        ];
    }
}
