<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbProjectTypeRequest extends FormRequest
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
        // The primary key for RdbProjectType is 'pt_id'
        // Assuming the route parameter is 'id' and represents the pt_id
        $pt_id = $this->route('id');

        return [
            'pt_name' => [
                'required',
                'string',
                'max:255',
                // Add unique rule if needed
                // Rule::unique('rdb_project_types', 'pt_name')->ignore($pt_id, 'pt_id'),
            ],
            'pt_for' => 'nullable|string|max:50',
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
