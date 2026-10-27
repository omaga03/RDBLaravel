<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_th' => ['sometimes', 'required', 'string', 'max:500'],
            'title_en' => ['nullable', 'string', 'max:500'],
            'fiscal_year' => ['sometimes', 'required'],
            'budget_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'department_id' => ['sometimes', 'required', 'integer'],
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
            'research_team' => ['nullable', 'array'],
            'research_team.*.researcher_id' => ['required_with:research_team', 'integer'],
            'research_team.*.contribution_percentage' => ['required_with:research_team', 'numeric', 'min:0', 'max:100'],
            'research_team.*.position_id' => ['nullable', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $team = $this->input('research_team');
            if (is_array($team) && !empty($team)) {
                $totalPercentage = 0.0;
                foreach ($team as $member) {
                    $totalPercentage += (float) ($member['contribution_percentage'] ?? 0);
                }

                if ($totalPercentage > 100.01) {
                    $validator->errors()->add(
                        'research_team',
                        "ผลรวมสัดส่วนการมีส่วนร่วมในโครงการวิจัยต้องไม่เกิน 100% (ปัจจุบัน: {$totalPercentage}%)"
                    );
                }
            }
        });
    }
}
