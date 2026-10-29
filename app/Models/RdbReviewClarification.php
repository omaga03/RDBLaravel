<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbReviewClarification extends Model
{
    protected $table = 'rdb_review_clarification';

    protected $fillable = [
        'submission_id',
        'user_id',
        'sender_role',
        'action_type',
        'message',
        'attachment_name',
        'attachment_path',
    ];

    public function submission()
    {
        return $this->belongsTo(RdbReviewSubmission::class, 'submission_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getActionTypeLabelAttribute(): string
    {
        return match ($this->action_type) {
            'clarification' => 'คำชี้แจงการปรับปรุงแก้ไข',
            'dispute' => 'คำชี้แจง/ขอทบทวนผลการประเมิน (Dispute)',
            'admin_reply' => 'ข้อความตอบกลับจากเจ้าหน้าที่',
            default => $this->action_type,
        };
    }
}
