<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbGroupprojectRequest extends FormRequest
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
            'pgroup_nameTH' => 'required|max:255|unique:rdb_groupproject,pgroup_nameTH',
            'pgroup_nameEN' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'pgroup_nameTH.unique' => 'ชื่อกลุ่มโครงการนี้มีอยู่ในระบบแล้ว',
        ];
    }
}
