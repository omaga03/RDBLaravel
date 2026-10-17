<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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

        // 2. Handle Thai Date Conversion (if needed) - Simple check if string contains Thai month
        // Assuming format "d Month Y" (Thai) or "Y-m-d"
        // Actually, if passing from Flatpickr without altInput, it might be Y-m-d.
        // But let's be safe. If it contains Thai chars, try to parse.
        // For now, let's assume standard Y-m-d is intended but if comma issue is fixed, maybe dates work? 
        // User said "Start date required", meaning it might be empty or invalid format.
        // Let's rely on standard Y-m-d first. If validation fails, we verify frontend config.
        
        // 3. Auto-fill dep_id from researcher_id if validated
        // 3. Auto-fill department_id and depcat_id from researcher_id if validated
        if ($this->has('researcher_id')) {
            $researcher = \App\Models\RdbResearcher::find($this->researcher_id);
            if ($researcher) {
                // Prepare merge array
                $mergeData = [];
                
                if (!$this->has('department_id')) {
                    $mergeData['department_id'] = $researcher->department_id;
                }
                if (!$this->has('depcat_id')) {
                    $mergeData['depcat_id'] = $researcher->depcat_id;
                }
                
                if (!empty($mergeData)) {
                    $this->merge($mergeData);
                }
            }
        }

        // 4. Convert Thai Dates to Y-m-d
        if ($this->has('pro_date_start')) {
            $this->merge(['pro_date_start' => $this->parseThaiDate($this->pro_date_start)]);
        }
        if ($this->has('pro_date_end')) {
            $this->merge(['pro_date_end' => $this->parseThaiDate($this->pro_date_end)]);
        }

        // 5. Handle Abstract Concatenation
        if ($this->has('pro_abstract_th') || $this->has('pro_abstract_en')) {
            $th = $this->input('pro_abstract_th', '');
            $en = $this->input('pro_abstract_en', '');
            $this->merge([
                'pro_abstract' => $th . '<br><br><br><br>' . $en
            ]);
        }

        // 6. Set defaults for data_show and created_at
        $this->merge([
            'data_show' => 1,
            'created_at' => now()->toDateTimeString(),
        ]);
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
            'pro_code' => 'nullable|string|max:50|unique:rdb_project,pro_code',
            'pro_nameTH' => 'required|string|max:255',
            'pro_nameEN' => 'nullable|string|max:255',
            'pro_detail' => 'nullable|string',
            'pro_budget' => 'nullable|numeric|min:0',
            'pro_date_start' => 'required|date',
            'pro_date_end' => 'required|date|after:pro_date_start',
            'pt_id' => 'required|integer|exists:rdb_project_types,pt_id',
            'pts_id' => 'nullable|integer|exists:rdb_project_types_sub,pts_id',
            'ps_id' => 'nullable|integer|exists:rdb_project_status,ps_id',
            'year_id' => 'required|integer|exists:rdb_year,year_id',
            'department_id' => 'required|integer|exists:rdb_department,department_id',
            'depcat_id' => 'nullable|integer|exists:rdb_department_category,depcat_id',
            'pgroup_id' => 'nullable|integer|exists:rdb_groupproject,pgroup_id',
            'pro_group' => 'nullable|integer',
            'pro_abstract' => 'nullable|string',
            'pro_keyword' => 'nullable|string',
            'data_show' => 'nullable|integer',
            'created_at' => 'nullable|date',
            // Researcher fields (required for create)
            'researcher_id' => 'required|integer|exists:rdb_researcher,researcher_id',
            'position_id' => 'required|integer|exists:rdb_project_position,position_id',
            'ratio' => 'required|numeric|min:0|max:100',
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
            'pro_date_end.after' => 'วันสิ้นสุดต้องหลังจากวันเริ่มต้น',
        ];
    }
}
