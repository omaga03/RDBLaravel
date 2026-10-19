<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResearchItemUnlockRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $item,
        public string $type,
        public string $reason
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
            default => 'รายการผลงาน',
        };

        return [
            'type' => 'unlock_requested',
            'title' => '🔓 คำขอเปิดสิทธิ์แก้ไขผลงานที่เผยแพร่แล้ว',
            'message' => "รายการ '{$titleName}' ได้ยื่นขอเปิดสิทธิ์แก้ไข: {$this->reason}",
            'url' => route('backend.workflow.queue', ['tab' => 'unlocks']),
            'item_id' => $this->item->getKey(),
            'model_type' => $this->type,
        ];
    }
}
