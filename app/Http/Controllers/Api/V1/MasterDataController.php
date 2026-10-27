<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RdbDepartment;
use App\Models\RdbProjectType;
use App\Models\RdbYear;
use Illuminate\Http\JsonResponse;

class MasterDataController extends Controller
{
    /**
     * Get list of PCRU faculties and departments (1-13).
     */
    public function departments(): JsonResponse
    {
        $departments = RdbDepartment::select([
            'department_id',
            'department_nameTH',
            'department_nameEN',
            'department_code',
        ])
        ->orderBy('department_id')
        ->get();

        return response()->json([
            'data' => $departments->map(function ($d) {
                return [
                    'id' => (string) $d->department_id,
                    'name_th' => (string) $d->department_nameTH,
                    'name_en' => (string) ($d->department_nameEN ?? ''),
                    'code' => (string) ($d->department_code ?? ''),
                ];
            }),
        ]);
    }

    /**
     * Get list of fiscal years.
     */
    public function years(): JsonResponse
    {
        $years = RdbYear::select(['year_id', 'year_name'])
            ->orderBy('year_name', 'desc')
            ->get();

        return response()->json([
            'data' => $years->map(function ($y) {
                return [
                    'id' => (string) $y->year_id,
                    'year_name' => (string) $y->year_name,
                ];
            }),
        ]);
    }

    /**
     * Get list of project types.
     */
    public function projectTypes(): JsonResponse
    {
        $types = RdbProjectType::with('projectTypeSubs')
            ->get();

        return response()->json([
            'data' => $types->map(function ($t) {
                return [
                    'id' => (string) $t->pt_id,
                    'name' => (string) $t->pt_name,
                    'sub_types' => $t->projectTypeSubs->map(function ($st) {
                        return [
                            'id' => (string) $st->pts_id,
                            'name' => (string) $st->pts_name,
                        ];
                    }),
                ];
            }),
        ]);
    }
}
