<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbProjectUtilizationRequest extends FormRequest
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
            'uti_nameTH' => 'required|string|max:255',
            // 'user_created', 'user_updated', 'created_at', 'updated_at' are handled by controller
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
            'uti_nameTH.required' => 'กรุณาระบุชื่อการนำไปใช้ประโยชน์',
            'uti_nameTH.string' => 'ชื่อการนำไปใช้ประโยชน์ต้องเป็นตัวอักษร',
            'uti_nameTH.max' => 'ชื่อการนำไปใช้ประโยชน์ต้องไม่เกิน 255 ตัวอักษร',
        ];
    }
}
