<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRdbProjectFilesRequest extends FormRequest
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
            'rf_filesname' => 'required|string|max:255',
            'rf_files' => 'nullable|file|max:51200', // File is optional on update
            'rf_note' => 'nullable|string',
            'rf_files_show' => 'nullable|boolean',
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
            'rf_filesname.required' => 'กรุณาระบุชื่อไฟล์',
            'rf_files.file' => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง',
            'rf_files.max' => 'ขนาดไฟล์ต้องไม่เกิน 50MB',
        ];
    }
}
