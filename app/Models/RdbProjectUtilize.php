<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\HasDualFilePath;

class RdbProjectUtilize extends Model
{
    use \App\Traits\HasDataShowScope, HasDualFilePath, \App\Traits\Auditable;

    protected $table = 'rdb_project_utilize';
    protected $primaryKey = 'utz_id';
    public $timestamps = true;

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function changwat()
    {
        return $this->belongsTo(RdbChangwat::class, 'chw_id', 'id');
    }

    public function utilizeType()
    {
        return $this->belongsTo(RdbProjectUtilizeType::class, 'utz_group', 'utz_type_id');
    }

    /**
     * Get all utilize types when utz_group contains multiple comma-separated IDs
     */
    public function getUtilizeTypesAttribute()
    {
        if (empty($this->utz_group)) {
            return collect();
        }
        $groupIds = array_filter(array_map('trim', explode(',', (string) $this->utz_group)));
        if (empty($groupIds)) {
            return collect();
        }
        return RdbProjectUtilizeType::whereIn('utz_type_id', $groupIds)->get();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_created', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'user_updated', 'id');
    }

    protected $fillable = [
        'pro_id',
        'utz_year_id',
        'utz_year_bud',
        'utz_year_edu',
        'utz_date',
        'utz_leading',
        'utz_leading_position',
        'utz_department_name',
        'utz_department_address',
        'chw_id',
        'utz_group',
        'utz_group_qa',
        'utz_detail',
        'utz_budget',
        'utz_count',
        'utz_countfile',
        'utz_files',
        'uf_safe_name',
        'uf_path',
        'uf_size',
        'uf_mime_type',
        'data_show',
        'workflow_status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_remarks',
        'unlock_reason',
        'unlock_requested_at',
        'user_created',
        'user_updated',
    ];

    use HasDualFilePath;

    /**
     * Get path ที่ถูกต้องสำหรับไฟล์การนำไปใช้ประโยชน์
     */
    public function getUtilizeFilePath(?string $filename = null): ?string
    {
        if (!empty($filename) && $filename !== $this->utz_files) {
            $safeName = basename(trim($filename));
            $candidates = [
                'uploads/datautilize/' . $this->utz_id . '/' . $safeName,
                'uploads/datautilize/' . $safeName,
                'utilize/' . $safeName,
                'uploads/utilize/' . $safeName,
                'datautilize/' . $this->utz_id . '/' . $safeName,
            ];
            foreach ($candidates as $cand) {
                if (Storage::disk('public')->exists($cand) || file_exists(public_path('storage/' . $cand)) || file_exists(public_path($cand))) {
                    return $cand;
                }
            }
            return 'uploads/datautilize/' . $this->utz_id . '/' . $safeName;
        }

        return $this->resolveDualFilePath('uf_path', 'utz_files', [
            'uploads/datautilize/{utz_id}',
            'uploads/datautilize',
            'utilize',
            'uploads/utilize',
            'datautilize/{utz_id}',
        ]) ?? (!empty($this->utz_files) ? 'uploads/datautilize/' . $this->utz_id . '/' . basename(trim($this->utz_files)) : null);
    }
}
