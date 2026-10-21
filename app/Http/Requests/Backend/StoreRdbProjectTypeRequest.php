<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRdbProjectTypeRequest extends FormRequest
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
            'pt_name' => [
                'required',
                'max:255',
                \Illuminate\Validation\Rule::unique('rdb_project_types')->where(function ($query) {
                    return $query->where('year_id', $this->year_id);
                }),
            ],
            'year_id' => 'required|exists:rdb_year,year_id',
            'pt_for' => 'nullable|string|max:1',
            'pt_created' => 'nullable|string|max:1',
            'pt_type' => 'nullable|string|max:1',
            'pt_utz' => 'nullable|string|max:1',
            'pttg_id' => 'nullable|exists:rdb_project_types_group,pttg_id',
            'master_pt_id' => 'nullable|exists:rdb_project_types_master,master_pt_id',
            'pt_note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'pt_name.unique' => 'ชื่อประเภททุนนี้มีอยู่ในปีงบประมาณนี้แล้ว',
            'year_id.required' => 'กรุณาเลือกปีงบประมาณ',
            'year_id.exists' => 'ไม่พบปีงบประมาณที่เลือก',
            'pttg_id.exists' => 'ไม่พบกลุ่มประเภททุนที่เลือก',
            'master_pt_id.exists' => 'ไม่พบหมวดประเภททุนมาตรฐานที่เลือก',
        ];
    }
}
