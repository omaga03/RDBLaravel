<?php

namespace App\Notifications;

use App\Models\RdbReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public RdbReviewAssignment $assignment
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (!empty($notifiable->email) && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $submission = $this->assignment->submission;
        $project = $submission?->project;
        $projectTitle = strip_tags($project?->pro_nameTH ?? $submission?->submission_title ?? 'โครงการวิจัย');
        $round = $submission?->round_number ?? 1;
        $url = route('backend.project_review.evaluate', $this->assignment->id);

        return (new MailMessage)
            ->subject('📋 [PCRU RDB] แจ้งมอบหมายงานประเมินเล่มรายงานวิจัยฉบับสมบูรณ์')
            ->greeting('เรียน ท่านผู้ทรงคุณวุฒิ ' . ($notifiable->name ?? ''))
            ->line("ท่านได้รับมอบหมายจากสถาบันวิจัยและพัฒนา ให้ทำหน้าที่เป็นผู้ทรงคุณวุฒิประเมินเล่มรายงานวิจัยฉบับสมบูรณ์ (รอบการประเมินที่ {$round})")
            ->line("ชื่อโครงการ: \"{$projectTitle}\"")
            ->action('เปิดแบบฟอร์มประเมินและดาวน์โหลดเล่มรายงาน', $url)
            ->line('ท่านสามารถล็อกอินเข้าสู่ระบบเพื่อดาวน์โหลดเอกสารเล่มรายงานวิจัย บันทึกผลคะแนนและข้อเสนอแนะตามแบบประเมินได้ทันที')
            ->salutation("ด้วยความเคารพอย่างสูง,\nสถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์");
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
