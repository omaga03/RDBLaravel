<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRdbProjectWorkRequest extends FormRequest
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
        // For update, pro_id and researcher_id are part of the composite primary key
        // and usually not updated directly in the form this way.
        // Assuming the form is for updating ratio and position for an existing record.
        return [
            'pro_id' => 'required|integer|exists:rdb_project,pro_id',
            'researcher_id' => 'required|integer|exists:rdb_researcher,researcher_id',
            'ratio' => 'required|numeric|min:0|max:100',
            'position_id' => 'required|integer|exists:rdb_project_position,position_id',
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
            'pro_id.required' => 'กรุณาระบุโครงการ',
            'pro_id.exists' => 'โครงการที่เลือกไม่ถูกต้อง',
            'researcher_id.required' => 'กรุณาระบุนักวิจัย',
            'researcher_id.exists' => 'นักวิจัยที่เลือกไม่ถูกต้อง',
            'ratio.required' => 'กรุณาระบุสัดส่วน',
            'ratio.numeric' => 'สัดส่วนต้องเป็นตัวเลข',
            'ratio.min' => 'สัดส่วนต้องไม่น้อยกว่า 0',
            'ratio.max' => 'สัดส่วนต้องไม่เกิน 100',
            'position_id.required' => 'กรุณาระบุตำแหน่ง',
            'position_id.exists' => 'ตำแหน่งที่เลือกไม่ถูกต้อง',
        ];
    }
}
