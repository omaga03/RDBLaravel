<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbReviewSubmission extends Model
{
    protected $table = 'rdb_review_submission';

    protected $fillable = [
        'pro_id',
        'round_number',
        'submission_type',
        'submission_title',
        'file_name',
        'file_path',
        'file_size',
        'file_mime',
        'page_count',
        'note',
        'submitted_by',
        'submitted_at',
        'status',
        'admin_summary',
        'closed_by',
        'closed_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'round_number' => 'integer',
        'file_size' => 'integer',
        'page_count' => 'integer',
        'submitted_at' => 'datetime',
        'closed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by', 'id');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function assignments()
    {
        return $this->hasMany(RdbReviewAssignment::class, 'submission_id', 'id');
    }

    public function clarifications()
    {
        return $this->hasMany(RdbReviewClarification::class, 'submission_id', 'id')
            ->orderBy('id', 'asc');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'ส่งเล่มประเมินแล้ว',
            'under_review' => 'กำลังประเมิน',
            'round_closed' => 'แจ้งผลการประเมินแล้ว',
            'revision_required' => 'รอการแก้ไขปรับปรุง',
            'disputed' => 'ยื่นคำชี้แจง/โต้แย้ง',
            'final_submitted' => 'ส่งฉบับแก้ไขสมบูรณ์แล้ว',
            'approved' => 'อนุมัติฉบับสมบูรณ์',
            'rejected' => 'ไม่ผ่านการประเมิน',
            default => $this->status,
        };
    }

    public function getSubmissionTypeLabelAttribute(): string
    {
        return match ($this->submission_type) {
            'initial_draft' => 'ร่างรายงานฉบับแรก',
            'revised_draft' => 'ร่างรายงานฉบับแก้ไข',
            'final_report' => 'รายงานฉบับสมบูรณ์ (Final)',
            default => $this->submission_type,
        };
    }
}
