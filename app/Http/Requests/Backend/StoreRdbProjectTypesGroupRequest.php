<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbProjectTypesGroupRequest extends FormRequest
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
            'pttg_name' => 'required|string|max:255|unique:rdb_project_types_group,pttg_name',
            'pttg_group' => 'required|string|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'pttg_name.required' => 'กรุณากรอกชื่อกลุ่มงบประมาณ',
            'pttg_name.unique' => 'ชื่อกลุ่มงบประมาณนี้มีอยู่แล้วในระบบ',
            'pttg_name.max' => 'ชื่อกลุ่มงบประมาณต้องไม่เกิน 255 ตัวอักษร',
            'pttg_group.required' => 'กรุณากรอกรหัสกลุ่ม',
            'pttg_group.max' => 'รหัสกลุ่มต้องไม่เกิน 1 ตัวอักษร',
        ];
    }
}
