<?php

namespace App\Notifications;

use App\Models\RdbReviewSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewFinalApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public RdbReviewSubmission $submission
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $project = $this->submission->project;
        $projectTitle = strip_tags($project?->pro_nameTH ?? $this->submission->submission_title ?? 'โครงการวิจัย');

        return [
            'type' => 'review_final_approved',
            'title' => '✅ อนุมัติเล่มรายงานวิจัยฉบับสมบูรณ์เรียบร้อยแล้ว',
            'message' => "รายงานวิจัยฉบับสมบูรณ์ของโครงการ '{$projectTitle}' ได้รับการอนุมัติและเชื่อมโยงเข้าสู่ระบบคลังข้อมูลโครงการเรียบร้อยแล้ว",
            'url' => route('backend.project_review.project', $this->submission->pro_id),
            'item_id' => $this->submission->pro_id,
            'model_type' => 'project_review_submission',
        ];
    }
}
