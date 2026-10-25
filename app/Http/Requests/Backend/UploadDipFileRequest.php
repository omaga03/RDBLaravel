<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UploadDipFileRequest extends FormRequest
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
            'field' => 'nullable|string|in:dip_files,dip_data1_files,dip_data2_files_con,dip_data3_drawing_picture,dip_data_forms_request,dip_data3_filesass1,dip_data3_filesass2,dip_data3_filesass3',
            'dip_files' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:51200', // 50MB
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
            'dip_files.required' => 'กรุณาเลือกไฟล์ที่ต้องการอัปโหลด',
            'dip_files.file' => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง',
            'dip_files.mimes' => 'ไฟล์ต้องเป็นประเภท PDF',
            'dip_files.max' => 'ขนาดไฟล์ต้องไม่เกิน 50MB',
        ];
    }
}
