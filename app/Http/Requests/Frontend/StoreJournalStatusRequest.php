<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJournalStatusRequest extends FormRequest
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
            'jou_name' => 'required|string|max:255',
            'jou_respon' => 'nullable|string|max:255',
            'jou_email' => 'nullable|email|max:255',
            'jou_files' => 'nullable|file|max:10240', // 10MB
            'jou_status' => 'nullable|integer',
            'jou_note' => 'nullable|string',
            'data_show' => 'nullable|integer',
            // 'user_updated', 'user_created', 'created_at', 'updated_at' are handled by controller
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
            'jou_name.required' => 'กรุณาระบุชื่อวารสาร',
            'jou_email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'jou_files.file' => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง',
            'jou_files.max' => 'ขนาดไฟล์ต้องไม่เกิน 10MB',
        ];
    }
}
