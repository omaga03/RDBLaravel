<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProjectRequest extends FormRequest
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
            'title_th' => ['required', 'string', 'max:500'],
            'title_en' => ['nullable', 'string', 'max:500'],
            'fiscal_year' => ['required'],
            'budget_amount' => ['required', 'numeric', 'min:0'],
            'department_id' => ['required', 'integer'],
            'pt_id' => ['nullable', 'integer'],
            'pts_id' => ['nullable', 'integer'],
            'pgroup_id' => ['nullable', 'integer'],
            'status_id' => ['nullable', 'integer'],
            'abstract' => ['nullable', 'string'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'finish_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
            'research_team' => ['required', 'array', 'min:1'],
            'research_team.*.researcher_id' => ['required', 'integer'],
            'research_team.*.contribution_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'research_team.*.position_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * Configure the validator instance for custom business logic.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $team = $this->input('research_team', []);
            if (is_array($team) && !empty($team)) {
                $totalPercentage = 0.0;
                foreach ($team as $member) {
                    $totalPercentage += (float) ($member['contribution_percentage'] ?? 0);
                }

                // Tolerance for float comparison
                if ($totalPercentage > 100.01) {
                    $validator->errors()->add(
                        'research_team',
                        "ผลรวมสัดส่วนการมีส่วนร่วมในโครงการวิจัยต้องไม่เกิน 100% (ปัจจุบัน: {$totalPercentage}%)"
                    );
                }
            }
        });
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title_th.required' => 'กรุณาระบุชื่อโครงการวิจัย (ภาษาไทย)',
            'fiscal_year.required' => 'กรุณาระบุปีงบประมาณ',
            'budget_amount.required' => 'กรุณาระบุงบประมาณโครงการ',
            'department_id.required' => 'กรุณาระบุสังกัด/หน่วยงาน',
            'research_team.required' => 'กรุณาระบุทีมนักวิจัยอย่างน้อย 1 คน',
            'research_team.min' => 'กรุณาระบุทีมนักวิจัยอย่างน้อย 1 คน',
            'end_date.after_or_equal' => 'วันที่สิ้นสุดต้องมากกว่าหรือเท่ากับวันที่เริ่มต้น',
        ];
    }
}
