<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbProjectPositionRequest extends FormRequest
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
            'position_nameTH' => 'required|string|max:255|unique:rdb_project_position,position_nameTH',
            'position_desc' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'position_nameTH.unique' => 'ชื่อตำแหน่งนี้มีอยู่ในระบบแล้ว',
        ];
    }
}
