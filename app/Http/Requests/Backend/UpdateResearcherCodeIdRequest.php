<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResearcherCodeIdRequest extends FormRequest
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
            'citizen_id' => 'required|string',
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
            'citizen_id.required' => 'กรุณาระบุหมายเลขบัตรประจำตัวประชาชน',
            'citizen_id.string' => 'หมายเลขบัตรประจำตัวประชาชนต้องเป็นตัวอักษร',
        ];
    }
}
