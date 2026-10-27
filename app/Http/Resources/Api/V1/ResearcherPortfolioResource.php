<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResearcherPortfolioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $prefix = $this->prefix?->prefix_nameTH ?? '';
        $fname = $this->researcher_fname ?? '';
        $lname = $this->researcher_lname ?? '';

        return [
            'id' => (string) $this->researcher_id,
            'teacher_code' => (string) ($this->tea_code ?? ''),
            'prefix' => (string) $prefix,
            'first_name' => (string) $fname,
            'last_name' => (string) $lname,
            'full_name' => trim("{$prefix}{$fname} {$lname}"),
            'email' => (string) ($this->researcher_email ?? ''),
            'tel' => (string) ($this->researcher_tel ?? ''),
            'department_id' => (string) ($this->department_id ?? ''),
            'department_name' => (string) ($this->department?->department_nameTH ?? ''),
            'status' => (string) ($this->status?->restatus_nameTH ?? ''),
            'h_index' => (float) ($this->researcher_hindex ?? $this->hindex ?? 0),
            'scopus_author_id' => (string) ($this->scopus_authorId ?? ''),
            'orcid_id' => (string) ($this->orcid ?? ''),
            'created_at' => $this->created_at ? (is_string($this->created_at) ? date('c', strtotime($this->created_at)) : $this->created_at->toISOString()) : null,
            'updated_at' => $this->updated_at ? (is_string($this->updated_at) ? date('c', strtotime($this->updated_at)) : $this->updated_at->toISOString()) : null,
        ];
    }
}
