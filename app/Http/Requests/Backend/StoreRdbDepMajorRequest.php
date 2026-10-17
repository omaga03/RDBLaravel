<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbDepMajorRequest extends FormRequest
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
            // Assuming maj_id is auto-increment or not directly filled
            'depcou_id' => 'nullable|integer', // Assuming it's a foreign key
            'department_id' => 'required|integer', // Assuming it's a foreign key
            'maj_nameTH' => 'required|string|max:255|unique:rdb_dep_major,maj_nameTH',
            'maj_nameEN' => 'nullable|string|max:255',
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
            'maj_nameTH.unique' => 'ชื่อสาขานี้มีอยู่ในระบบแล้ว',
            'maj_nameTH.required' => 'กรุณาระบุชื่อสาขา (ภาษาไทย)',
            'department_id.required' => 'กรุณาเลือกหน่วยงาน/คณะ',
        ];
    }
}
