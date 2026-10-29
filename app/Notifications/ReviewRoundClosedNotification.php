<?php

namespace App\Notifications;

use App\Models\RdbReviewSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewRoundClosedNotification extends Notification
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
        $round = $this->submission->round_number ?? 1;
        $statusLabel = $this->submission->status_label;

        return [
            'type' => 'review_round_closed',
            'title' => '📊 แจ้งผลการประเมินเล่มรายงานวิจัย',
            'message' => "แจ้งผลการประเมินเล่มรายงานวิจัย (รอบที่ {$round}: {$statusLabel}) โครงการ '{$projectTitle}'",
            'url' => route('backend.project_review.project', $this->submission->pro_id),
            'item_id' => $this->submission->pro_id,
            'model_type' => 'project_review_submission',
        ];
    }
}
