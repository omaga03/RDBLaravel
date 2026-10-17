<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbProjectUtilizationRequest extends FormRequest
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
        // The primary key for RdbProjectUtilization is 'uti_id'
        // Assuming the route parameter is 'id' and represents the uti_id
        $uti_id = $this->route('id');

        return [
            'uti_nameTH' => 'required|string|max:255',
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
