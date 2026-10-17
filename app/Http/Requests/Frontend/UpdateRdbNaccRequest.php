<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbNaccRequest extends FormRequest
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
        // The primary key for RdbNacc is 'nacc_id'
        // Assuming the route parameter is 'id' and represents the nacc_id
        $nacc_id = $this->route('id');

        return [
            'pro_id' => 'required|integer|exists:rdb_project,pro_id',
            'nacc_files' => 'nullable|file|max:10240', // Max 10MB
            'nacc_note' => 'nullable|string',
            'nacc_download' => 'nullable|integer',
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
            'nacc_files.file' => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง',
            'nacc_files.max' => 'ขนาดไฟล์ต้องไม่เกิน 10MB',
        ];
    }
}
