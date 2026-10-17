<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbProjectFilesRequest extends FormRequest
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
            'rf_filesname' => 'required|string|max:255', // User-provided name for the file
            'rf_files' => 'required|file|max:51200', // The actual file upload (max 50MB)
            'rf_note' => 'nullable|string',
            'rf_files_show' => 'nullable|boolean',
            // 'prf_safe_name', 'prf_path', 'prf_size', 'prf_mime_type', 'rf_download', 'user_created', 'user_updated' are managed internally
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
            'rf_files.required' => 'กรุณาเลือกไฟล์ที่ต้องการอัปโหลด',
            'rf_files.file' => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง',
            'rf_files.max' => 'ขนาดไฟล์ต้องไม่เกิน 50MB',
        ];
    }
}
