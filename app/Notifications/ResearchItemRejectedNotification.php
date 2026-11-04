<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResearchItemRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $item,
        public string $type,
        public string $remarks
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
        $titleName = match ($this->type) {
            'project' => $this->item->pro_nameTH,
            'published' => $this->item->pub_name,
            'dip' => $this->item->dip_data2_name ?? $this->item->dip_name,
            'utilize' => 'การนำโครงการวิจัยไปใช้ประโยชน์ #' . $this->item->pro_id,
            default => 'ผลงานวิชาการ',
        };

        $routeName = match ($this->type) {
            'utilize' => 'backend.rdbprojectutilize.edit',
            default => 'backend.rdb_' . $this->type . '.edit',
        };

        $url = \Illuminate\Support\Facades\Route::has($routeName)
            ? route($routeName, $this->item->getKey())
            : url('/backend');

        return (new MailMessage)
            ->subject('⚠️ [PCRU RDB] ผลงานวิจัยของท่านมีรายการที่ต้องแก้ไขเพิ่มเติม (Revision Required)')
            ->greeting('เรียน ' . ($notifiable->name ?? 'ท่านนักวิจัย'))
            ->line("ผลงานวิจัยรายการ '{$titleName}' ได้รับการตรวจสอบจากผู้ดูแลระบบ โดยมีข้อเสนอแนะที่ต้องปรับปรุงแก้ไข ดังนี้:")
            ->line("รายละเอียดข้อเสนอแนะ: \"{$this->remarks}\"")
            ->action('เข้าสู่ระบบเพื่อแก้ไขข้อมูล', $url)
            ->line('กรุณาเข้าสู่ระบบหลังบ้านเพื่อตรวจสอบและดำเนินการแก้ไข จากนั้นกดส่งตรวจสอบอีกครั้ง')
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
            'utilize' => 'backend.rdbprojectutilize.edit',
            default => 'backend.rdb_' . $this->type . '.edit',
        };

        return [
            'type' => 'rejected',
            'title' => '⚠️ ผลงานถูกส่งกลับแก้ไข (Revision Required)',
            'message' => "รายการ '{$titleName}' ถูกส่งกลับแก้ไข เหตุผล: {$this->remarks}",
            'url' => \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName, $this->item->getKey()) : null,
            'item_id' => $this->item->getKey(),
            'model_type' => $this->type,
            'remarks' => $this->remarks,
        ];
    }
}
