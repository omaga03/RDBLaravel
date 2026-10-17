<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbDipTypeRequest extends FormRequest
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
        // The primary key for RdbDipType is 'dipt_id'
        // Assuming the route parameter is 'id' and represents the dipt_id
        $dipt_id = $this->route('id');

        return [
            'dipt_name' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('table_name', 'column_name')->ignore($id, 'id_column')
                Rule::unique('rdb_dip_type', 'dipt_name')->ignore($dipt_id, 'dipt_id'),
            ],
            // 'user_updated' is handled by controller after validation
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
            'dipt_name.unique' => 'ชื่อประเภททรัพย์สินทางปัญญานี้มีอยู่ในระบบแล้ว',
            'dipt_name.required' => 'กรุณาระบุชื่อประเภททรัพย์สินทางปัญญา',
        ];
    }
}