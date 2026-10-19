<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResearchItemUnlockApprovedNotification extends Notification
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
            default => 'รายการผลงาน',
        };

        $editUrl = match ($this->type) {
            'project' => route('backend.rdb_project.edit', $this->item->getKey()),
            'published' => route('backend.rdb_published.edit', $this->item->getKey()),
            'dip' => route('backend.rdb_dip.edit', $this->item->getKey()),
            'utilize' => route('backend.rdbprojectutilize.edit', $this->item->getKey()),
            default => route('backend.site.index'),
        };

        return [
            'type' => 'unlock_approved',
            'title' => '📝 คำขอแก้ไขผลงานได้รับการอนุมัติ (ย้ายไปฉบับร่างแล้ว)',
            'message' => "รายการ '{$titleName}' ได้รับการอนุมัติให้แก้ไขแล้ว และถูกย้ายไปยัง 'ฉบับร่าง' ท่านสามารถแก้ไขและส่งตรวจใหม่ได้",
            'url' => $editUrl,
            'item_id' => $this->item->getKey(),
            'model_type' => $this->type,
        ];
    }
}
