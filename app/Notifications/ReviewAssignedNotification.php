<?php

namespace App\Notifications;

use App\Models\RdbReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public RdbReviewAssignment $assignment
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $submission = $this->assignment->submission;
        $project = $submission?->project;
        $projectTitle = strip_tags($project?->pro_nameTH ?? $submission?->submission_title ?? 'โครงการวิจัย');
        $round = $submission?->round_number ?? 1;

        return [
            'type' => 'review_assigned',
            'title' => '📋 มอบหมายงานประเมินเล่มรายงานวิจัย',
            'message' => "ท่านได้รับมอบหมายให้เป็นผู้ทรงคุณวุฒิประเมินเล่มรายงานวิจัย (รอบที่ {$round}) เรื่อง '{$projectTitle}'",
            'url' => route('backend.project_review.evaluate', $this->assignment->id),
            'item_id' => $this->assignment->id,
            'model_type' => 'project_review_assignment',
        ];
    }
}
