<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbReviewAssignment extends Model
{
    protected $table = 'rdb_review_assignment';

    protected $fillable = [
        'submission_id',
        'reviewer_id',
        'researcher_id',
        'template_id',
        'assigned_by',
        'assigned_at',
        'due_date',
        'status',
        'decline_reason',
        'responded_at',
        'result_decision',
        'total_score',
        'max_possible_score',
        'score_percentage',
        'general_comment',
        'annotated_file_name',
        'annotated_file_path',
        'submitted_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_date' => 'date',
        'responded_at' => 'datetime',
        'submitted_at' => 'datetime',
        'total_score' => 'decimal:2',
        'max_possible_score' => 'decimal:2',
        'score_percentage' => 'decimal:2',
    ];

    public function submission()
    {
        return $this->belongsTo(RdbReviewSubmission::class, 'submission_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'id');
    }

    public function researcher()
    {
        return $this->belongsTo(RdbResearcher::class, 'researcher_id', 'researcher_id');
    }

    public function template()
    {
        return $this->belongsTo(RdbReviewTemplate::class, 'template_id', 'id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'id');
    }

    public function scores()
    {
        return $this->hasMany(RdbReviewScore::class, 'assignment_id', 'id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'รอตอบรับ',
            'accepted' => 'ตอบรับกำลังประเมิน',
            'declined' => 'ปฏิเสธการประเมิน',
            'completed' => 'ส่งผลประเมินแล้ว',
            default => $this->status,
        };
    }

    public function getResultDecisionLabelAttribute(): string
    {
        return match ($this->result_decision) {
            'pass' => 'เห็นชอบให้ผ่าน (Pass)',
            'minor_revision' => 'เห็นชอบให้ผ่านโดยมีแก้ไขเล็กน้อย (Minor Revision)',
            'major_revision' => 'ให้แก้ไขปรับปรุงใหม่และส่งตรวจซ้ำ (Major Revision)',
            'reject' => 'ไม่เห็นชอบให้ผ่าน (Reject)',
            default => '-',
        };
    }
}
