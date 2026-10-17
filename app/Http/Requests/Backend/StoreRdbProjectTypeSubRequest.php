<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbProjectTypeSubRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pt_id' => 'required|exists:rdb_project_types,pt_id',
            'pts_name' => [
                'required',
                'max:255',
                \Illuminate\Validation\Rule::unique('rdb_project_types_sub')->where(function ($query) {
                    return $query->where('pt_id', $this->pt_id);
                }),
            ],
            'pts_file' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,7z|max:51200', // 50MB max
        ];
    }

    public function messages(): array
    {
        return [
            'pt_id.required' => 'กรุณาเลือกประเภททุนสนับสนุน',
            'pt_id.exists' => 'ไม่พบข้อมูลประเภททุนสนับสนุนที่เลือก',
            'pts_name.required' => 'กรุณากรอกชื่อโครงการทุนสนับสนุน',
            'pts_name.unique' => 'ชื่อโครงการนี้มีอยู่แล้วในประเภททุนสนับสนุนนี้',
            'pts_name.max' => 'ชื่อโครงการต้องไม่เกิน 255 ตัวอักษร',
            'pts_file.mimes' => 'ไฟล์แนบต้องเป็นนามสกุล pdf, doc, docx, zip, rar, 7z เท่านั้น',
            'pts_file.max' => 'ขนาดไฟล์ต้องไม่เกิน 50MB',
        ];
    }
}
