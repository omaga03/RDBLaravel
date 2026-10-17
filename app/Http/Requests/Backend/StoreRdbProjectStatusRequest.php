<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbProjectStatusRequest extends FormRequest
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
            'ps_name' => 'required|string|max:255|unique:rdb_project_status,ps_name',
            'ps_icon' => 'nullable|string|max:50',
            'ps_color' => 'nullable|string|max:7', // Hex color code
            'ps_rank' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'ps_name.unique' => 'ชื่อสถานะนี้มีอยู่ในระบบแล้ว',
        ];
    }
}
