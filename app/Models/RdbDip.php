<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\HasDualFilePath;

class RdbDip extends Model
{
    use \App\Traits\HasDataShowScope, HasDualFilePath, \App\Traits\Auditable;

    protected $table = 'rdb_dip';
    protected $primaryKey = 'dip_id';
    public $timestamps = true;

    protected $casts = [
        'dip_request_date' => 'date',
        'dip_data2_dateend' => 'date',
    ];

    public function dipType()
    {
        return $this->belongsTo(RdbDipType::class, 'dipt_id', 'dipt_id');
    }

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function researcher()
    {
        return $this->belongsTo(RdbResearcher::class, 'researcher_id', 'researcher_id');
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
        'dip_type',
        'dipt_id',
        'dip_files',
        'dip_safe_name',
        'dip_path',
        'dip_size',
        'dip_mime_type',
        'dip_startdate',
        'dip_enddate',
        'pro_id',
        'dip_request_number',
        'dip_request_date',
        'dip_request_dateget',
        'dip_number',
        'dip_publication_date',
        'dip_publication_no',
        'dip_patent_number',
        'dip_data1_datestart',
        'dip_data1_files',
        'dip_data2_patent',
        'researcher_id',
        'dip_data2_name',
        'dip_data2_agent',
        'dip_data2_status',
        'dip_data2_dateend',
        'dip_data2_conclusion',
        'dip_data2_files_con',
        'dip_data2_assertion',
        'dip_data2_tag',
        'dip_data3_allassertion',
        'dip_data3_filesass1',
        'dip_data3_filesass2',
        'dip_data3_filesass3',
        'dip_data3_drawing_picture',
        'dip_data_forms_request',
        'dip_url',
        'dip_note',
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

    /**
     * Get path ที่ถูกต้องสำหรับไฟล์ทรัพย์สินทางปัญญา (ทั้งไฟล์หลักและไฟล์แนบเพิ่มเติม)
     */
    public function getDipFilePath(?string $field = 'dip_files'): ?string
    {
        $targetField = $field ?: 'dip_files';

        // 1. กรณีไฟล์หลัก
        if ($targetField === 'dip_files') {
            if (!empty($this->dip_path)) {
                return $this->dip_path;
            }

            if (!empty($this->dip_files)) {
                $filename = $this->dip_files;
                if (Storage::disk('public')->exists('dips/' . $filename) || file_exists(public_path('storage/dips/' . $filename))) {
                    return 'dips/' . $filename;
                }
                if (Storage::disk('public')->exists('uploads/dips/' . $filename) || file_exists(public_path('storage/uploads/dips/' . $filename)) || file_exists(public_path('uploads/dips/' . $filename))) {
                    return 'uploads/dips/' . $filename;
                }
                if (file_exists(public_path('datadip/' . $this->dip_id . '/' . $filename)) || file_exists(base_path('yii2_rdb-main/frontend/web/datadip/' . $this->dip_id . '/' . $filename))) {
                    return 'datadip/' . $this->dip_id . '/' . $filename;
                }
                return 'uploads/dips/' . $filename;
            }
        } else {
            // 2. กรณีไฟล์แนบเพิ่มเติม (dip_data1_files, dip_data2_files_con, ฯลฯ)
            if (!empty($this->{$targetField})) {
                $filename = $this->{$targetField};
                if (Storage::disk('public')->exists('dips/' . $filename) || file_exists(public_path('storage/dips/' . $filename))) {
                    return 'dips/' . $filename;
                }
                if (Storage::disk('public')->exists('uploads/dips/' . $filename) || file_exists(public_path('storage/uploads/dips/' . $filename)) || file_exists(public_path('uploads/dips/' . $filename))) {
                    return 'uploads/dips/' . $filename;
                }
                if (file_exists(public_path('datadip/' . $this->dip_id . '/' . $filename)) || file_exists(base_path('yii2_rdb-main/frontend/web/datadip/' . $this->dip_id . '/' . $filename))) {
                    return 'datadip/' . $this->dip_id . '/' . $filename;
                }
                return 'uploads/dips/' . $filename;
            }
        }

        return null;
    }
}
