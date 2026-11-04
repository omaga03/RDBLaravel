<?php

namespace App\Notifications;

use App\Models\RdbReviewSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewFinalApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public RdbReviewSubmission $submission
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
        $project = $this->submission->project;
        $projectTitle = strip_tags($project?->pro_nameTH ?? $this->submission->submission_title ?? 'โครงการวิจัย');
        $url = route('backend.project_review.project', $this->submission->pro_id);

        return (new MailMessage)
            ->subject('✅ [PCRU RDB] อนุมัติเล่มรายงานวิจัยฉบับสมบูรณ์เรียบร้อยแล้ว')
            ->greeting('เรียน ' . ($notifiable->name ?? 'ท่านนักวิจัย'))
            ->line("รายงานวิจัยฉบับสมบูรณ์ของโครงการ '{$projectTitle}' ได้รับการอนุมัติและเชื่อมโยงเข้าสู่ระบบคลังข้อมูลโครงการวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ (PCRU RDB) เรียบร้อยแล้ว")
            ->action('เปิดดูรายละเอียดโครงการวิจัย', $url)
            ->line('ขอบพระคุณที่ร่วมส่งมอบผลงานวิจัยฉบับสมบูรณ์เพื่อประโยชน์ทางวิชาการและการพัฒนาสังคม')
            ->salutation("ด้วยความเคารพอย่างสูง,\nสถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์");
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
