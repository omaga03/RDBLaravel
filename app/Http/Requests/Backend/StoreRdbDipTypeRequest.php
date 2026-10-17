<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbDipTypeRequest extends FormRequest
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
            'dipt_name' => 'required|string|max:255|unique:rdb_dip_type,dipt_name',
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
            'dipt_name.unique' => 'ชื่อประเภททรัพย์สินทางปัญญานี้มีอยู่ในระบบแล้ว',
            'dipt_name.required' => 'กรุณาระบุชื่อประเภททรัพย์สินทางปัญญา',
        ];
    }
}