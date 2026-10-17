<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbDepartmentTypeRequest extends FormRequest
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
            'tdepartment_nameTH' => 'required|string|max:255|unique:rdb_department_type,tdepartment_nameTH',
            'tdepartment_nameEN' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'tdepartment_nameTH.unique' => 'ชื่อประเภทนี้มีอยู่ในระบบแล้ว',
        ];
    }
}
