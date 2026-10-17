<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbProjectUtilizeTypeRequest extends FormRequest
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
            'utz_typr_name' => 'required|string|max:255|unique:rdb_project_utilize_type,utz_typr_name',
            'utz_type_index' => 'nullable|integer',
            // 'user_created', 'user_updated' are handled by controller after validation
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