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

        // Map workflow type → public route (utilize module is named "rdbprojectutilize")
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
