<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbProjectTypeRequest extends FormRequest
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
            'pt_name' => 'required|string|max:255',
            'pt_for' => 'nullable|string|max:50',
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
            'pt_name.required' => 'กรุณาระบุชื่อประเภทโครงการ',
            'pt_name.string' => 'ชื่อประเภทโครงการต้องเป็นตัวอักษร',
            'pt_name.max' => 'ชื่อประเภทโครงการต้องไม่เกิน 255 ตัวอักษร',
        ];
    }
}
