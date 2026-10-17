<?php

namespace App\Http->Requests\Frontend;

use Illuminate->Foundation->Http->FormRequest;
use Illuminate->Validation->Rule;

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
     * @return array<string, \Illuminate->Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // The primary key for RdbDepMajor is 'maj_code'
        // Assuming the route parameter is 'id' and represents the maj_code
        $maj_code = $this->route('id');

        return [
            'maj_id' => 'nullable|string|max:20',
            'depcou_id' => 'nullable|integer|exists:rdb_department_course,depcou_id',
            'department_id' => 'nullable|integer|exists:rdb_department,department_id',
            'maj_nameTH' => 'required|string|max:255',
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
            'maj_nameTH.required' => 'กรุณาระบุชื่อสาขาวิชา (ภาษาไทย)',
            'depcou_id.exists' => 'หลักสูตรไม่ถูกต้อง',
            'department_id.exists' => 'หน่วยงาน/คณะไม่ถูกต้อง',
        ];
    }
}
