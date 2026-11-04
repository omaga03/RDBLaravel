<?php

use App\Models\SystemSetting;
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

// Helper to safely get schedule settings from database or fallback
$getScheduleExpr = function (string $key, string $default) {
    try {
        return SystemSetting::get($key, $default);
    } catch (\Throwable $e) {
        return $default;
    }
};

$isTaskEnabled = function (string $key, string $default = '1') {
    try {
        return SystemSetting::get($key, $default) === '1';
    } catch (\Throwable $e) {
        return $default === '1';
    }
};

// 1. สำรองฐานข้อมูลระบบอัตโนมัติ (Database Backup)
if ($isTaskEnabled('scheduler_backup_db_enabled', '1')) {
    $dbExpr = $getScheduleExpr('scheduler_backup_db_expression', '0 2 * * *');
    Schedule::command('rdb:backup --type=db')
        ->cron($dbExpr)
        ->name('rdb-daily-database-backup')
        ->withoutOverlapping()
        ->runInBackground();
}

// 2. สำรองระบบเต็มรูปแบบ (Database + Storage Archives)
if ($isTaskEnabled('scheduler_backup_all_enabled', '1')) {
    $allExpr = $getScheduleExpr('scheduler_backup_all_expression', '0 3 * * 0');
    Schedule::command('rdb:backup --type=all')
        ->cron($allExpr)
        ->name('rdb-weekly-full-backup')
        ->withoutOverlapping()
        ->runInBackground();
}

// 3. ซิงค์ข่าวทุนวิจัยจากระบบ NRIIS อัตโนมัติ
if ($isTaskEnabled('scheduler_sync_nriis_news_enabled', '1')) {
    $nriisExpr = $getScheduleExpr('scheduler_sync_nriis_news_expression', '0 6 * * *');
    Schedule::command('rdb:sync-nriis-news')
        ->cron($nriisExpr)
        ->name('rdb-sync-nriis-funding-news')
        ->withoutOverlapping()
        ->runInBackground();
}

// 4. ซิงค์ข่าวงานประชุมวิชาการจากเพจ Facebook สถาบันวิจัยฯ อัตโนมัติ
if ($isTaskEnabled('scheduler_sync_facebook_conferences_enabled', '1')) {
    $fbExpr = $getScheduleExpr('scheduler_sync_facebook_conferences_expression', '30 6 * * *');
    Schedule::command('rdb:sync-facebook-conferences')
        ->cron($fbExpr)
        ->name('rdb-sync-facebook-conferences')
        ->withoutOverlapping()
        ->runInBackground();
}
