<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ps_id' => 'required|integer|exists:rdb_project_status,ps_id',
        ];
    }

    public function messages(): array
    {
        return [
            'ps_id.required' => 'กรุณาเลือกสถานะโครงการ',
            'ps_id.integer' => 'รหัสสถานะโครงการต้องเป็นตัวเลข',
            'ps_id.exists' => 'ไม่พบข้อมูลสถานะโครงการที่เลือก',
        ];
    }
}
