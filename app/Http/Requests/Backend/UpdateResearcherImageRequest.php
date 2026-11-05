<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResearcherImageRequest extends FormRequest
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
            'fileimg' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:51200', // Matches form request rule
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
            'fileimg.required' => 'กรุณาเลือกไฟล์รูปภาพ',
            'fileimg.image' => 'ไฟล์ต้องเป็นรูปภาพ',
            'fileimg.mimes' => 'ไฟล์รูปภาพต้องเป็นประเภท jpeg, png, jpg, gif หรือ webp',
            'fileimg.max' => 'ขนาดไฟล์รูปภาพต้องไม่เกิน 50MB',
        ];
    }
}
