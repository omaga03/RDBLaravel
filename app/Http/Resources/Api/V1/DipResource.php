<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $project = $this->project;
        $researcher = $this->researcher;

        $resPrefix = $researcher?->prefix?->prefix_nameTH ?? '';
        $resFname = $researcher?->researcher_fname ?? '';
        $resLname = $researcher?->researcher_lname ?? '';
        $resFullName = trim("{$resPrefix}{$resFname} {$resLname}");

        return [
            'id' => (string) $this->dip_id,
            'ip_title' => (string) ($this->dip_data2_name ?? ''),
            'ip_type' => [
                'id' => (string) ($this->dipt_id ?? ''),
                'name' => (string) ($this->dipType?->dipt_name ?? ''),
            ],
            'registration_number' => (string) ($this->dip_number ?? ''),
            'request_number' => (string) ($this->dip_request_number ?? ''),
            'request_date' => $this->dip_request_date ? date('Y-m-d', strtotime($this->dip_request_date)) : null,
            'publication_number' => (string) ($this->dip_publication_no ?? ''),
            'publication_date' => $this->dip_publication_date ? date('Y-m-d', strtotime($this->dip_publication_date)) : null,
            'patent_number' => (string) ($this->dip_patent_number ?? ''),
            'status' => (string) ($this->dip_data2_status ?? ''),
            'conclusion' => (string) ($this->dip_data2_conclusion ?? ''),
            'assertion' => (string) ($this->dip_data2_assertion ?? ''),
            'external_url' => (string) ($this->dip_url ?? ''),
            'project' => $project ? [
                'id' => (string) $project->pro_id,
                'code' => (string) ($project->pro_code ?? ''),
                'title_th' => (string) ($project->pro_nameTH ?? ''),
                'title_en' => (string) ($project->pro_nameEN ?? ''),
            ] : null,
            'researcher' => $researcher ? [
                'id' => (string) $researcher->researcher_id,
                'pmis' => (string) ($researcher->tea_code ?? ''),
                'prefix' => (string) $resPrefix,
                'first_name' => (string) $resFname,
                'last_name' => (string) $resLname,
                'full_name' => (string) $resFullName,
                'department_id' => (string) ($researcher->department_id ?? ''),
                'department_name' => (string) ($researcher->department?->department_nameTH ?? ''),
            ] : null,
            'file_name' => (string) ($this->dip_files ?? $this->dip_data1_files ?? ''),
            'file_url' => $this->dip_path ? asset('storage/' . $this->dip_path) : null,
            'workflow_status' => (string) ($this->workflow_status ?? 'published'),
        ];
    }
}
