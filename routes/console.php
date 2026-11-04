<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Automated University Research Database (PCRU RDB) Task Scheduling
|--------------------------------------------------------------------------
| These tasks run automatically when the server cron triggers:
| * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
*/

// 1. สำรองฐานข้อมูลระบบอัตโนมัติ (Database Backup) ทุกวัน เวลา 02:00 น.
Schedule::command('rdb:backup --type=db')
    ->dailyAt('02:00')
    ->name('rdb-daily-database-backup')
    ->withoutOverlapping()
    ->runInBackground();

// 2. สำรองระบบเต็มรูปแบบ (Database + Storage Archives) สัปดาห์ละ 1 ครั้ง ทุกคืนวันอาทิตย์ เวลา 03:00 น.
Schedule::command('rdb:backup --type=all')
    ->weeklyOn(0, '03:00')
    ->name('rdb-weekly-full-backup')
    ->withoutOverlapping()
    ->runInBackground();

// 3. ตรวจสอบและนับจำนวนหน้าเอกสาร PDF ที่ตกค้างอัตโนมัติ ทุกวัน เวลา 04:00 น.
Schedule::command('rdb:count-pdf-pages --module=all --limit=100')
    ->dailyAt('04:00')
    ->name('rdb-batch-count-pdf-pages')
    ->withoutOverlapping()
    ->runInBackground();

// 4. ตรวจสอบและเรนเดอร์ภาพหน้าปก WebP จากหน้าแรกของไฟล์ PDF อัตโนมัติ ทุกวัน เวลา 04:30 น.
Schedule::command('rdb:generate-pdf-covers --module=all --limit=50')
    ->dailyAt('04:30')
    ->name('rdb-batch-generate-pdf-covers')
    ->withoutOverlapping()
    ->runInBackground();

// 5. ซิงค์ข่าวทุนวิจัยจากระบบ NRIIS อัตโนมัติ ทุกเช้า เวลา 06:00 น.
Schedule::command('rdb:sync-nriis-news')
    ->dailyAt('06:00')
    ->name('rdb-sync-nriis-funding-news')
    ->withoutOverlapping()
    ->runInBackground();

// 6. ซิงค์ข่าวงานประชุมวิชาการจากเพจ Facebook สถาบันวิจัยฯ อัตโนมัติ ทุกเช้า เวลา 06:30 น.
Schedule::command('rdb:sync-facebook-conferences')
    ->dailyAt('06:30')
    ->name('rdb-sync-facebook-conferences')
    ->withoutOverlapping()
    ->runInBackground();
