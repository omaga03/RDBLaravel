<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRdbProjectPersonnelDepRequest extends FormRequest
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
        // The primary key for RdbProjectPersonnelDep is 'ppd_id'
        // Assuming the route parameter is 'id' and represents the ppd_id
        $ppd_id = $this->route('id');

        return [
            'year_id' => 'nullable|integer|exists:rdb_year,year_id',
            'department_id' => 'nullable|integer|exists:rdb_department,department_id',
            'depcou_id' => 'nullable|integer|exists:rdb_department_course,depcou_id',
            'major_id' => 'nullable|string|max:255|exists:rdb_dep_major,maj_code', // maj_code is string PK
            'depcat_id' => 'nullable|integer|exists:rdb_department_category,depcat_id',
            'pp_num' => 'nullable|integer',
            'pp_standard' => 'nullable|integer',
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
            'year_id.exists' => 'ปีงบประมาณไม่ถูกต้อง',
            'department_id.exists' => 'หน่วยงาน/คณะไม่ถูกต้อง',
            'depcou_id.exists' => 'หลักสูตรไม่ถูกต้อง',
            'major_id.exists' => 'สาขาวิชาไม่ถูกต้อง',
            'depcat_id.exists' => 'ประเภทหน่วยงานไม่ถูกต้อง',
            'pp_num.integer' => 'จำนวนบุคลากรต้องเป็นตัวเลข',
            'pp_standard.integer' => 'จำนวนบุคลากรมาตรฐานต้องเป็นตัวเลข',
        ];
    }
}
