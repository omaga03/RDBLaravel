<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbChangwatRequest extends FormRequest
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
            'ta_id' => 'nullable|string|max:10',
            'tambon_t' => 'nullable|string|max:255',
            'tambon_e' => 'nullable|string|max:255',
            'am_id' => 'nullable|string|max:10',
            'amphoe_t' => 'nullable|string|max:255',
            'amphoe_e' => 'nullable|string|max:255',
            'ch_id' => 'nullable|string|max:10',
            'changwat_t' => 'required|string|max:255',
            'changwat_e' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
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
            'changwat_t.required' => 'กรุณาระบุชื่อจังหวัด',
            'changwat_t.string' => 'ชื่อจังหวัดต้องเป็นตัวอักษร',
            'changwat_t.max' => 'ชื่อจังหวัดต้องไม่เกิน 255 ตัวอักษร',
            'lat.numeric' => 'ละติจูดต้องเป็นตัวเลข',
            'long.numeric' => 'ลองจิจูดต้องเป็นตัวเลข',
        ];
    }
}
