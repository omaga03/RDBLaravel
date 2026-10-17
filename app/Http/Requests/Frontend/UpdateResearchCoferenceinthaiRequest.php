<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResearchCoferenceinthaiRequest extends FormRequest
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
        // The primary key for ResearchConferenceInThai is 'id'
        // Assuming the route parameter is 'id' and represents the id
        $id = $this->route('id');

        return [
            'con_name' => 'required|string|max:255',
            'con_detail' => 'nullable|string',
            'con_even_date' => 'nullable|date',
            'con_sub_deadline' => 'nullable|date',
            'con_venue' => 'nullable|string|max:255',
            'con_website' => 'nullable|string|max:255',
            'con_img' => 'nullable|image|max:5120', // Max 5MB
            'con_site_img' => 'nullable|image|max:5120',
            'con_count' => 'nullable|integer',
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
            'con_name.required' => 'กรุณาระบุชื่อการประชุม',
            'con_name.string' => 'ชื่อการประชุมต้องเป็นตัวอักษร',
            'con_name.max' => 'ชื่อการประชุมต้องไม่เกิน 255 ตัวอักษร',
            'con_even_date.date' => 'วันที่จัดงานประชุมไม่ถูกต้อง',
            'con_sub_deadline.date' => 'วันสุดท้ายของการส่งผลงานไม่ถูกต้อง',
            'con_img.image' => 'ไฟล์รูปภาพประชาสัมพันธ์ไม่ถูกต้อง',
            'con_img.max' => 'ขนาดไฟล์รูปภาพประชาสัมพันธ์ต้องไม่เกิน 5MB',
            'con_site_img.image' => 'ไฟล์รูปภาพประชาสัมพันธ์ภายในเว็บไม่ถูกต้อง',
            'con_site_img.max' => 'ขนาดไฟล์รูปภาพประชาสัมพันธ์ภายในเว็บต้องไม่เกิน 5MB',
            'con_count.integer' => 'จำนวนผู้เข้าชมต้องเป็นตัวเลข',
        ];
    }
}
