<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $researchTeam = [];
        if ($this->relationLoaded('works')) {
            foreach ($this->works as $pw) {
                $res = $pw->researcher;
                $prefix = $res?->prefix?->prefix_nameTH ?? '';
                $fname = $res?->researcher_fname ?? '';
                $lname = $res?->researcher_lname ?? '';
                $researchTeam[] = [
                    'id' => (string) ($pw->rdb_user_id ?? $pw->researcher_id ?? ''),
                    'researcher_id' => (string) ($pw->researcher_id ?? ''),
                    'pmis' => (string) ($res?->tea_code ?? ''),
                    'prefix' => (string) $prefix,
                    'first_name' => (string) $fname,
                    'last_name' => (string) $lname,
                    'full_name' => trim("{$prefix}{$fname} {$lname}"),
                    'department_id' => (string) ($res?->department_id ?? ''),
                    'department_name' => (string) ($res?->department?->department_nameTH ?? ''),
                    'contribution_percentage' => (string) ($pw->ratio ?? '0'),
                    'project_role' => (string) ($pw->position?->position_nameTH ?? ''),
                ];
            }
        }

        $fundingSource = $this->group?->pgroup_nameTH ?? $this->type?->pt_name ?? 'โครงการวิจัยเดี่ยว';
        $fundingType = $this->type?->pt_name ?? '';
        $fundingSubType = $this->typeSub?->pts_name ?? $fundingType;

        return [
            'id' => (string) $this->pro_id,
            'project_code' => (string) ($this->pro_code ?? ''),
            'fiscal_year' => (string) ($this->year?->year_name ?? ''),
            'budget_amount' => number_format((float) $this->pro_budget, 2, '.', ''),
            'status_label' => (string) ($this->status?->ps_name ?? ''),
            'title_th' => (string) ($this->pro_nameTH ?? ''),
            'title_en' => (string) ($this->pro_nameEN ?? ''),
            'funding_source' => (string) $fundingSource,
            'funding_type' => (string) $fundingType,
            'funding_sub_type' => (string) $fundingSubType,
            'department_id' => (string) ($this->department_id ?? ''),
            'department_name' => (string) ($this->department?->department_nameTH ?? ''),
            'abstract' => (string) ($this->pro_abstract ?? ''),
            'keywords' => (string) ($this->pro_keyword ?? ''),
            'start_date' => $this->pro_date_start ? date('Y-m-d', strtotime($this->pro_date_start)) : null,
            'end_date' => $this->pro_date_end ? date('Y-m-d', strtotime($this->pro_date_end)) : null,
            'finish_date' => $this->pro_finish ? date('Y-m-d', strtotime($this->pro_finish)) : null,
            'remarks' => (string) ($this->pro_comment ?? $this->pro_note ?? ''),
            'research_team' => $researchTeam,
            'created_at' => $this->created_at ? (is_string($this->created_at) ? date('c', strtotime($this->created_at)) : $this->created_at->toISOString()) : null,
            'updated_at' => $this->updated_at ? (is_string($this->updated_at) ? date('c', strtotime($this->updated_at)) : $this->updated_at->toISOString()) : null,
        ];
    }
}
