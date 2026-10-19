<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResearchItemSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $item,
        public string $type
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $titleName = match ($this->type) {
            'project' => $this->item->pro_nameTH,
            'published' => $this->item->pub_name,
            'dip' => $this->item->dip_data2_name ?? $this->item->dip_name,
            'utilize' => 'การนำโครงการวิจัยไปใช้ประโยชน์ #' . $this->item->pro_id,
            default => 'รายการผลงานใหม่',
        };

        return [
            'type' => 'submitted',
            'title' => '📥 มีผลงานใหม่ส่งเข้าตรวจสอบ',
            'message' => "รายการ '{$titleName}' รอการตรวจสอบความถูกต้อง",
            'url' => route('backend.workflow.queue', ['tab' => $this->type . 's']),
            'item_id' => $this->item->getKey(),
            'model_type' => $this->type,
        ];
    }
}
