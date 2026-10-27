<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RdbProjectUtilizeType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectUtilizeResource extends JsonResource
{
    /**
     * Cache for utilize types mapping.
     */
    protected static ?array $utilizeTypesMap = null;

    protected static function getUtilizeTypeMap(): array
    {
        if (self::$utilizeTypesMap === null) {
            self::$utilizeTypesMap = RdbProjectUtilizeType::all()
                ->keyBy('utz_type_id')
                ->map(fn($t) => [
                    'id' => (string) $t->utz_type_id,
                    'name' => (string) $t->utz_typr_name,
                ])
                ->toArray();
        }
        return self::$utilizeTypesMap;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $typeMap = self::getUtilizeTypeMap();
        $types = [];
        if (!empty($this->utz_group)) {
            $ids = explode(',', (string) $this->utz_group);
            foreach ($ids as $id) {
                $id = trim($id);
                if (isset($typeMap[$id])) {
                    $types[] = $typeMap[$id];
                }
            }
        }

        $project = $this->project;
        $dept = $project?->department;

        return [
            'id' => (string) $this->utz_id,
            'project_id' => (string) ($this->pro_id ?? ''),
            'project' => $project ? [
                'id' => (string) $project->pro_id,
                'code' => (string) ($project->pro_code ?? ''),
                'title_th' => (string) ($project->pro_nameTH ?? ''),
                'title_en' => (string) ($project->pro_nameEN ?? ''),
                'department_id' => (string) ($project->department_id ?? ''),
                'department_name' => (string) ($dept?->department_nameTH ?? ''),
            ] : null,
            'fiscal_year' => (string) ($this->utz_year_bud ?? $project?->year?->year_name ?? ''),
            'utilize_date' => $this->utz_date ? date('Y-m-d', strtotime($this->utz_date)) : null,
            'target_organization' => (string) ($this->utz_department_name ?? ''),
            'target_address' => (string) ($this->utz_department_address ?? ''),
            'target_leader' => (string) ($this->utz_leading ?? ''),
            'target_leader_position' => (string) ($this->utz_leading_position ?? ''),
            'location' => [
                'province' => (string) ($this->changwat?->changwat_t ?? ''),
                'amphoe' => (string) ($this->changwat?->amphoe_t ?? ''),
                'tambon' => (string) ($this->changwat?->tambon_t ?? ''),
                'latitude' => $this->changwat?->lat ? (float) $this->changwat->lat : null,
                'longitude' => $this->changwat?->long ? (float) $this->changwat->long : null,
            ],
            'utilize_types' => $types,
            'detail' => (string) ($this->utz_detail ?? ''),
            'budget' => $this->utz_budget ? number_format((float) $this->utz_budget, 2, '.', '') : null,
            'file_name' => (string) ($this->utz_files ?? ''),
            'file_url' => $this->uf_path ? asset('storage/' . $this->uf_path) : null,
            'workflow_status' => (string) ($this->workflow_status ?? 'published'),
        ];
    }
}
