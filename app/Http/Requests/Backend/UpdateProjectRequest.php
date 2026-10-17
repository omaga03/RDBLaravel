<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        // 1. Remove commas from budget
        if ($this->has('pro_budget')) {
            $this->merge([
                'pro_budget' => str_replace(',', '', $this->pro_budget),
            ]);
        }

        // 2. Convert Thai Dates to Y-m-d
        if ($this->has('pro_date_start')) {
            $this->merge(['pro_date_start' => $this->parseThaiDate($this->pro_date_start)]);
        }
        if ($this->has('pro_date_end')) {
            $this->merge(['pro_date_end' => $this->parseThaiDate($this->pro_date_end)]);
        }
        if ($this->has('pro_finish')) {
            $this->merge(['pro_finish' => $this->parseThaiDate($this->pro_finish)]);
        }
    }

    private function parseThaiDate($date)
    {
        if (!$date) return null;
        
        // If already Y-m-d, return
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        $thaiMonths = [
            'มกราคม' => '01', 'กุมภาพันธ์' => '02', 'มีนาคม' => '03', 'เมษายน' => '04',
            'พฤษภาคม' => '05', 'มิถุนายน' => '06', 'กรกฎาคม' => '07', 'สิงหาคม' => '08',
            'กันยายน' => '09', 'ตุลาคม' => '10', 'พฤศจิกายน' => '11', 'ธันวาคม' => '12'
        ];

        // Try format "d Month Y" (Thai) e.g., "5 มกราคม 2569"
        $parts = preg_split('/\s+/', trim($date));
        if (count($parts) === 3) {
            $d = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $mStr = $parts[1];
            $y = $parts[2];

            if (isset($thaiMonths[$mStr])) {
                $m = $thaiMonths[$mStr];
                $yAD = (int)$y - 543;
                return "{$yAD}-{$m}-{$d}";
            }
        }
        
        return $date; // Fallback
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pro_code' => 'nullable|string|max:50|unique:rdb_project,pro_code,' . $this->route('rdb_project') . ',pro_id',
            'pro_nameTH' => 'required|string|max:255',
            'pro_nameEN' => 'nullable|string|max:255',
            'pro_detail' => 'nullable|string',
            'pro_budget' => 'nullable|numeric|min:0',
            'pro_date_start' => 'required|date',
            'pro_date_end' => 'required|date|after:pro_date_start',
            'pro_finish' => 'nullable|date',
            'pt_id' => 'required|integer|exists:rdb_project_types,pt_id',
            'ps_id' => 'required|integer|exists:rdb_project_status,ps_id',
            'year_id' => 'required|integer|exists:rdb_year,year_id',
            'department_id' => 'required|integer|exists:rdb_department,department_id',
            'pgroup_id' => 'nullable|integer|exists:rdb_groupproject,pgroup_id',
            'pro_group' => 'nullable|integer',
            'data_show' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pro_nameTH.required' => 'ชื่อโครงการ (ไทย) จำเป็นต้องกรอก',
            'pro_date_start.required' => 'วันเริ่มต้น จำเป็นต้องกรอก',
            'pro_date_end.required' => 'วันสิ้นสุด จำเป็นต้องกรอก',
        ];
    }
}
