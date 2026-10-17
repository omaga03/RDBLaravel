<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRdbStrategicRequest extends FormRequest
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
            'strategic_nameTH' => 'required|string|max:255|unique:rdb_strategic,strategic_nameTH,' . $this->route('rdbstrategic') . ',strategic_id',
            'year_id' => 'required|exists:rdb_year,year_id',
            'strategic_nameEN' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'strategic_nameTH.unique' => 'ชื่อยุทธศาสตร์นี้มีอยู่ในระบบแล้ว',
            'year_id.required' => 'กรุณาเลือกปีงบประมาณ',
            'year_id.exists' => 'ไม่พบปีงบประมาณที่เลือก',
        ];
    }
}
