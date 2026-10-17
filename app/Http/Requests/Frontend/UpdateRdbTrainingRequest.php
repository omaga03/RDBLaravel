<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbTrainingRequest extends FormRequest
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
        // The primary key for RdbTraining is 'tra_id'
        // Assuming the route parameter is 'id' and represents the tra_id
        $tra_id = $this->route('id');

        return [
            'tra_name' => 'required|string|max:255',
            'tra_description' => 'nullable|string',
            'tra_property' => 'nullable|string',
            'tra_fee' => 'nullable|numeric',
            'tra_datetimestart' => 'nullable|date',
            'tra_datetimeend' => 'nullable|date',
            'tra_place' => 'nullable|string',
            'tra_applicant' => 'nullable|string',
            'tra_note' => 'nullable|string',
            'tra_url' => 'nullable|string',
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
            'tra_name.required' => 'กรุณาระบุชื่อการอบรม',
            'tra_name.string' => 'ชื่อการอบรมต้องเป็นตัวอักษร',
            'tra_name.max' => 'ชื่อการอบรมต้องไม่เกิน 255 ตัวอักษร',
            'tra_fee.numeric' => 'ค่าธรรมเนียมต้องเป็นตัวเลข',
            'tra_datetimestart.date' => 'วันที่เริ่มต้นไม่ถูกต้อง',
            'tra_datetimeend.date' => 'วันที่สิ้นสุดไม่ถูกต้อง',
        ];
    }
}
