<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbProjectStatusRequest extends FormRequest
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
        // The primary key for RdbProjectStatus is 'ps_id'
        // Assuming the route parameter is 'id' and represents the ps_id
        $ps_id = $this->route('id');

        return [
            'ps_name' => 'required|string|max:100',
            'ps_color' => 'nullable|string|max:20',
            'ps_icon' => 'nullable|string|max:50',
            'ps_rank' => 'nullable|integer',
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
            'ps_name.required' => 'กรุณาระบุชื่อสถานะ',
            'ps_name.max' => 'ชื่อสถานะต้องไม่เกิน 100 ตัวอักษร',
            'ps_rank.integer' => 'ลำดับต้องเป็นตัวเลข',
        ];
    }
}
