<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResearchItemApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $item,
        public string $type
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (method_exists($notifiable, 'wantsMailFor') ? $notifiable->wantsMailFor('research_approved') : (!empty($notifiable->email) && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL))) {
            $channels[] = 'mail';
        }
        if (method_exists($notifiable, 'wantsTelegramFor') ? $notifiable->wantsTelegramFor('research_approved') : !empty($notifiable->telegram_chat_id)) {
            if (\App\Services\TelegramNotificationService::isConfigured()) {
                $channels[] = \App\Notifications\Channels\TelegramChannel::class;
            }
        }
        return $channels;
    }

    public function toTelegram($notifiable): string
    {
        $titleName = match ($this->type) {
            'project' => $this->item->pro_nameTH,
            'published' => $this->item->pub_name,
            'dip' => $this->item->dip_data2_name ?? $this->item->dip_name,
            'utilize' => 'การนำโครงการวิจัยไปใช้ประโยชน์ #' . $this->item->pro_id,
            default => 'ผลงานวิชาการ',
        };

        $routeName = match ($this->type) {
            'utilize' => 'frontend.rdbprojectutilize.show',
            default => 'frontend.rdb' . $this->type . '.show',
        };

        $url = \Illuminate\Support\Facades\Route::has($routeName)
            ? route($routeName, $this->item->getKey())
            : url('/');

        $greeting = htmlspecialchars($notifiable->name ?? 'ท่านนักวิจัย');
        $escapedTitle = htmlspecialchars($titleName);

        return "🎉 <b>[PCRU RDB] ผลงานวิจัยได้รับการอนุมัติเผยแพร่แล้ว</b>\n\n"
             . "เรียน {$greeting}\n"
             . "ผลงานวิจัยรายการ <b>'{$escapedTitle}'</b> ได้รับการตรวจสอบและอนุมัติให้เผยแพร่สู่ระบบฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ เรียบร้อยแล้ว\n\n"
             . "🔗 <a href=\"{$url}\">เปิดดูผลงานบนระบบหน้าบ้าน</a>";
    }

    public function toMail($notifiable): MailMessage
    {
        $titleName = match ($this->type) {
            'project' => $this->item->pro_nameTH,
            'published' => $this->item->pub_name,
            'dip' => $this->item->dip_data2_name ?? $this->item->dip_name,
            'utilize' => 'การนำโครงการวิจัยไปใช้ประโยชน์ #' . $this->item->pro_id,
            default => 'ผลงานวิชาการ',
        };

        $routeName = match ($this->type) {
            'utilize' => 'frontend.rdbprojectutilize.show',
            default => 'frontend.rdb' . $this->type . '.show',
        };

        $url = \Illuminate\Support\Facades\Route::has($routeName)
            ? route($routeName, $this->item->getKey())
            : url('/');

        return (new MailMessage)
            ->subject('🎉 [PCRU RDB] ผลงานวิจัยของท่านได้รับการอนุมัติเผยแพร่แล้ว')
            ->greeting('เรียน ' . ($notifiable->name ?? 'ท่านนักวิจัย'))
            ->line("ผลงานวิจัยรายการ '{$titleName}' ได้รับการตรวจสอบและอนุมัติให้เผยแพร่สู่ระบบฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ (PCRU RDB) เรียบร้อยแล้ว")
            ->action('เปิดดูผลงานบนระบบหน้าบ้าน', $url)
            ->line('ขอบพระคุณที่ร่วมสร้างสรรค์ผลงานวิจัยและนวัตกรรมเพื่อการพัฒนาทางวิชาการและสังคมอย่างยั่งยืน')
            ->salutation("ด้วยความเคารพอย่างสูง,\nสถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์");
    }

    public function toArray($notifiable): array
    {
        $titleName = match ($this->type) {
            'project' => $this->item->pro_nameTH,
            'published' => $this->item->pub_name,
            'dip' => $this->item->dip_data2_name ?? $this->item->dip_name,
            'utilize' => 'การนำโครงการวิจัยไปใช้ประโยชน์ #' . $this->item->pro_id,
            default => 'ผลงานวิชาการ',
        };

        $routeName = match ($this->type) {
            'utilize' => 'frontend.rdbprojectutilize.show',
            default => 'frontend.rdb' . $this->type . '.show',
        };

        return [
            'type' => 'approved',
            'title' => '🎉 ผลงานของท่านได้รับการอนุมัติแล้ว',
            'message' => "รายการ '{$titleName}' ได้รับการตรวจสอบและเผยแพร่สู่สาธารณะเรียบร้อยแล้ว",
            'url' => \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName, $this->item->getKey()) : null,
            'item_id' => $this->item->getKey(),
            'model_type' => $this->type,
        ];
    }
}
