<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbProjectUtilizeTypeRequest extends FormRequest
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
        // The primary key for RdbProjectUtilizeType is 'utz_type_id'
        // Assuming the route parameter is 'id' and represents the utz_type_id
        $utz_type_id = $this->route('id');

        return [
            'utz_typr_name' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('table_name', 'column_name')->ignore($id, 'id_column')
                Rule::unique('rdb_project_utilize_type', 'utz_typr_name')->ignore($utz_type_id, 'utz_type_id'),
            ],
            'utz_type_index' => 'nullable|integer',
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
            'utz_typr_name.unique' => 'ชื่อประเภทการนำไปใช้ประโยชน์นี้มีอยู่ในระบบแล้ว',
            'utz_typr_name.required' => 'กรุณาระบุชื่อประเภทการนำไปใช้ประโยชน์',
        ];
    }
}