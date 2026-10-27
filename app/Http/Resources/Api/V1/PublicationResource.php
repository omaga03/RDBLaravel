<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $projectDetails = null;
        if ($this->relationLoaded('project') && $this->project) {
            $projectDetails = [
                'id' => (string) $this->project->pro_id,
                'fiscal_year' => (string) ($this->project->year?->year_name ?? ''),
                'funding_type' => (string) ($this->project->type?->pt_name ?? ''),
                'title_th' => (string) ($this->project->pro_nameTH ?? ''),
                'budget_amount' => number_format((float) $this->project->pro_budget, 2, '.', ''),
                'remarks' => (string) ($this->project->pro_comment ?? $this->project->pro_note ?? ''),
            ];
        }

        $pt = $this->pubtype;
        $pubTypeSubgroup = '';
        if ($pt) {
            $groupStr = array_filter([$pt->pubtype_group, $pt->pubtype_grouptype]);
            $suffix = !empty($groupStr) ? ' (' . implode(' - ', $groupStr) . ')' : '';
            $pubTypeSubgroup = ($pt->pubtype_subgroup ?? '') . $suffix;
        }

        $yearName = (string) ($this->year?->year_name ?? $this->year_bud ?? $this->year_edu ?? '');

        $authors = [];
        if ($this->relationLoaded('publishedWorks')) {
            foreach ($this->publishedWorks as $pw) {
                $res = $pw->researcher;
                $prefix = $res?->prefix?->prefix_nameTH ?? '';
                $fname = $res?->researcher_fname ?? '';
                $lname = $res?->researcher_lname ?? '';
                $authors[] = [
                    'publication_id' => (string) $this->id,
                    'researcher_id' => (string) ($pw->researcher_id ?? ''),
                    'pmis' => (string) ($res?->tea_code ?? ''),
                    'full_name' => trim("{$prefix}{$fname} {$lname}"),
                    'department_name' => (string) ($res?->department?->department_nameTH ?? ''),
                    'author_role' => (string) ($pw->authorType?->pubta_nameTH ?? ''),
                    'is_main_affiliation' => (string) ($pw->pubw_main ?? '0'),
                    'allocated_budget' => (string) ($pw->pubw_bud ?? '0'),
                ];
            }
        } elseif ($this->relationLoaded('authors')) {
            foreach ($this->authors as $res) {
                $pivot = $res->pivot;
                $authors[] = [
                    'publication_id' => (string) $this->id,
                    'researcher_id' => (string) $res->researcher_id,
                    'pmis' => (string) ($res->tea_code ?? ''),
                    'full_name' => trim("{$res->researcher_fname} {$res->researcher_lname}"),
                    'department_name' => (string) ($res->department?->department_nameTH ?? ''),
                    'author_role' => (string) ($pivot?->typeAuthor?->pubta_nameTH ?? ''),
                    'is_main_affiliation' => (string) ($pivot?->pubw_main ?? '0'),
                    'allocated_budget' => (string) ($pivot?->pubw_bud ?? '0'),
                ];
            }
        } elseif (isset($this->authors_data)) {
            $authors = $this->authors_data;
        }

        return [
            'id' => (string) $this->id,
            'project_id' => (string) ($this->pro_id ?? ''),
            'project_details' => $projectDetails,
            'year_name' => $yearName,
            'pubtypesubgroup' => $pubTypeSubgroup,
            'publication_title' => (string) ($this->pub_name ?? ''),
            'publication_abstract' => (string) ($this->pub_abstract ?? ''),
            'publication_keywords' => (string) ($this->pub_keyword ?? ''),
            'authors' => $authors,
            'department_id' => (string) ($this->department_id ?? ''),
            'department_name' => (string) ($this->department?->department_nameTH ?? ''),
            'department_nameTH' => (string) ($this->department?->department_nameTH ?? ''),
            'journal_source' => (string) ($this->pub_name_journal ?? ''),
            'issue_date_start' => $this->pub_date ? date('Y-m-d', strtotime($this->pub_date)) : '',
            'issue_date_end' => $this->pub_date_end ? date('Y-m-d', strtotime($this->pub_date_end)) : '',
            'quality_score' => number_format((float) ($this->pub_score ?? 0), 2, '.', ''),
            'internal_note' => (string) ($this->pub_note ?? ''),
            'created_at' => $this->created_at ? (is_string($this->created_at) ? date('c', strtotime($this->created_at)) : $this->created_at->toISOString()) : null,
            'updated_at' => $this->updated_at ? (is_string($this->updated_at) ? date('c', strtotime($this->updated_at)) : $this->updated_at->toISOString()) : null,
        ];
    }
}
