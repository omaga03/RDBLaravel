<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRdbProjectUtilizeRequest extends FormRequest
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
            'pro_id' => 'required|integer|exists:rdb_project,pro_id',
            'utz_year_id' => 'nullable|integer',
            'utz_year_bud' => 'nullable|integer',
            'utz_year_edu' => 'nullable|integer',
            'utz_date' => 'nullable|date',
            'utz_leading' => 'nullable|string|max:255',
            'utz_leading_position' => 'nullable|string|max:255',
            'utz_department_name' => 'nullable|string|max:255',
            'chw_id' => 'nullable|integer|exists:rdb_changwat,id',
            'utz_group' => 'nullable|integer',
            'utz_group_qa' => 'nullable|integer',
            'utz_detail' => 'nullable|string',
            'utz_budget' => 'nullable|numeric',
            'utz_count' => 'nullable|integer',
            'utz_countfile' => 'nullable|integer',
            'utz_files' => 'nullable|file|max:10240', // Max 10MB
            'data_show' => 'nullable|integer',
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
            'pro_id.exists' => 'โครงการไม่ถูกต้อง',
            'utz_date.date' => 'วันที่ไม่ถูกต้อง',
            'utz_budget.numeric' => 'งบประมาณต้องเป็นตัวเลข',
            'utz_files.file' => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง',
            'utz_files.max' => 'ขนาดไฟล์ต้องไม่เกิน 10MB',
        ];
    }
}
