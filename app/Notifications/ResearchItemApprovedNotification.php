<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
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
            'type' => 'approved',
            'title' => '🎉 ผลงานของท่านได้รับการอนุมัติแล้ว',
            'message' => "รายการ '{$titleName}' ได้รับการตรวจสอบและเผยแพร่สู่สาธารณะเรียบร้อยแล้ว",
            'url' => route('frontend.rdb' . $this->type . '.show', $this->item->getKey()),
            'item_id' => $this->item->getKey(),
            'model_type' => $this->type,
        ];
    }
}
