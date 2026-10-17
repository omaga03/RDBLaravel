<?php

namespace App\Http\Requests\Frontend;

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
            'utz_typr_name' => 'required|string|max:255',
            'utz_type_index' => 'nullable|integer',
            // 'user_created', 'user_updated' are handled by controller
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
            'utz_typr_name.required' => 'กรุณาระบุชื่อประเภทการนำไปใช้ประโยชน์',
            'utz_typr_name.string' => 'ชื่อประเภทการนำไปใช้ประโยชน์ต้องเป็นตัวอักษร',
            'utz_typr_name.max' => 'ชื่อประเภทการนำไปใช้ประโยชน์ต้องไม่เกิน 255 ตัวอักษร',
            'utz_type_index.integer' => 'ลำดับต้องเป็นตัวเลข',
        ];
    }
}
