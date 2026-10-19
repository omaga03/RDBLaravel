<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
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
        return ['database'];
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

        return [
            'type' => 'rejected',
            'title' => '⚠️ ผลงานถูกส่งกลับแก้ไข (Revision Required)',
            'message' => "รายการ '{$titleName}' ถูกส่งกลับแก้ไข เหตุผล: {$this->remarks}",
            'url' => route('backend.rdb_' . $this->type . '.edit', $this->item->getKey()),
            'item_id' => $this->item->getKey(),
            'model_type' => $this->type,
            'remarks' => $this->remarks,
        ];
    }
}
