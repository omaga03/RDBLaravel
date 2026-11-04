<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Services\TelegramNotificationService;
use Cron\CronExpression;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SystemSchedulerController extends Controller
{
    /**
     * Whitelist of automated scheduled tasks
     */
    protected array $tasks = [
        [
            'id' => 'backup-db',
            'name' => 'สำรองฐานข้อมูลระบบประจำวัน (Daily Database Backup)',
            'command' => 'rdb:backup --type=db',
            'default_expression' => '0 2 * * *',
            'default_time' => '02:00',
            'default_day' => '0',
            'default_freq_type' => 'daily',
            'default_schedule_text' => 'ทุกวัน เวลา 02:00 น.',
            'category' => 'ความปลอดภัย & กู้คืนระบบ',
            'badge_class' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'icon' => 'bi-database-fill-gear',
            'description' => 'ส่งออกโครงสร้างและข้อมูลทุกตารางของระบบลงไฟล์ .sql.gz เพื่อป้องกันความเสียหายจากเหตุสุดวิสัย',
        ],
        [
            'id' => 'backup-all',
            'name' => 'สำรองระบบเต็มรูปแบบประจำสัปดาห์ (Weekly Full Backup)',
            'command' => 'rdb:backup --type=all',
            'default_expression' => '0 3 * * 0',
            'default_time' => '03:00',
            'default_day' => '0',
            'default_freq_type' => 'weekly',
            'default_schedule_text' => 'ทุกวันอาทิตย์ เวลา 03:00 น.',
            'category' => 'ความปลอดภัย & กู้คืนระบบ',
            'badge_class' => 'bg-danger-subtle text-danger border border-danger-subtle',
            'icon' => 'bi-archive-fill',
            'description' => 'สำรองฐานข้อมูลพร้อมบีบอัดเอกสารงานวิจัย ไฟล์แนบ และภาพหน้าปกทั้งหมดลงคลัง Archives',
        ],
        [
            'id' => 'sync-nriis-news',
            'name' => 'ซิงค์ข่าวประกาศทุนวิจัยจากระบบ NRIIS (Funding Sync)',
            'command' => 'rdb:sync-nriis-news',
            'default_expression' => '0 6 * * *',
            'default_time' => '06:00',
            'default_day' => '0',
            'default_freq_type' => 'daily',
            'default_schedule_text' => 'ทุกวัน เวลา 06:00 น.',
            'category' => 'บูรณาการ & ข้อมูลภายนอก',
            'badge_class' => 'bg-success-subtle text-success border border-success-subtle',
            'icon' => 'bi-megaphone-fill',
            'description' => 'นำเข้าข่าวประกาศรับสมัครทุนวิจัยใหม่จากสำนักงานการวิจัยแห่งชาติ (วช. / NRIIS) เข้าสู่ระบบอัตโนมัติ',
        ],
        [
            'id' => 'sync-facebook-conferences',
            'name' => 'ซิงค์งานประชุมวิชาการจากเพจสถาบันวิจัยฯ (Event Sync)',
            'command' => 'rdb:sync-facebook-conferences',
            'default_expression' => '30 6 * * *',
            'default_time' => '06:30',
            'default_day' => '0',
            'default_freq_type' => 'daily',
            'default_schedule_text' => 'ทุกวัน เวลา 06:30 น.',
            'category' => 'บูรณาการ & ข้อมูลภายนอก',
            'badge_class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            'icon' => 'bi-calendar-event-fill',
            'description' => 'ติดตามและนำเข้าข่าวการจัดงานประชุมวิชาการและสัมมนาจาก Facebook Fanpage สถาบันวิจัยฯ อัตโนมัติ',
        ],
    ];

    /**
     * Display Task Scheduler Hub
     */
    public function index()
    {
        $this->ensureAuthorized();

        $tasksWithRunInfo = array_map(function ($task) {
            $key = str_replace('-', '_', $task['id']);
            $enabled = SystemSetting::get("scheduler_{$key}_enabled", '1') === '1';
            $expression = SystemSetting::get("scheduler_{$key}_expression", $task['default_expression']);
            $time = SystemSetting::get("scheduler_{$key}_time", $task['default_time']);
            $dayOfWeek = SystemSetting::get("scheduler_{$key}_day_of_week", $task['default_day']);
            $freqType = SystemSetting::get("scheduler_{$key}_frequency_type", $task['default_freq_type']);
            $scheduleText = SystemSetting::get("scheduler_{$key}_schedule_text", $task['default_schedule_text']);

            $task['enabled'] = $enabled;
            $task['expression'] = $expression;
            $task['time'] = $time;
            $task['day_of_week'] = $dayOfWeek;
            $task['frequency_type'] = $freqType;
            $task['schedule_text'] = $scheduleText;

            if ($enabled) {
                try {
                    $cron = new CronExpression($expression);
                    $nextRun = Carbon::instance($cron->getNextRunDate());
                    $nextRunThai = TelegramNotificationService::formatThaiDateTime($nextRun);
                    $diffForHumans = $nextRun->diffForHumans([
                        'parts' => 2,
                        'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                    ]);

                    $task['next_run_carbon'] = $nextRun;
                    $task['next_run_thai'] = $nextRunThai;
                    $task['next_run_diff'] = $diffForHumans;
                } catch (\Throwable $e) {
                    $task['next_run_thai'] = 'รูปแบบเวลาไม่ถูกต้อง';
                    $task['next_run_diff'] = '-';
                }
            } else {
                $task['next_run_carbon'] = null;
                $task['next_run_thai'] = 'ปิดการทำงานอัตโนมัติ';
                $task['next_run_diff'] = 'ระงับชั่วคราว';
            }

            return $task;
        }, $this->tasks);

        $nowThai = TelegramNotificationService::formatThaiDateTime(now());
        $telegramConfigured = TelegramNotificationService::isConfigured();
        $telegramAdminConfigured = TelegramNotificationService::isAdminConfigured();
        $adminChatId = TelegramNotificationService::getAdminChatId();

        return view('backend.system_scheduler.index', compact(
            'tasksWithRunInfo',
            'nowThai',
            'telegramConfigured',
            'telegramAdminConfigured',
            'adminChatId'
        ));
    }

    /**
     * Update schedule for a specific task
     */
    public function updateSchedule(Request $request)
    {
        $this->ensureAuthorized();

        $validated = $request->validate([
            'task_id' => 'required|string',
            'enabled' => 'nullable',
            'frequency_type' => 'required|in:daily,weekly,custom',
            'time' => 'nullable|string',
            'day_of_week' => 'nullable|integer|between:0,6',
            'custom_expression' => 'nullable|string',
        ]);

        $task = collect($this->tasks)->firstWhere('id', $validated['task_id']);
        if (!$task) {
            return redirect()->route('backend.system_scheduler.index')
                ->with('error', 'ไม่พบงานที่ต้องการแก้ไขการตั้งเวลา');
        }

        $key = str_replace('-', '_', $task['id']);
        $isEnabled = !empty($validated['enabled']) ? '1' : '0';
        $freqType = $validated['frequency_type'];

        $expression = $task['default_expression'];
        $scheduleText = $task['default_schedule_text'];
        $time = $validated['time'] ?: $task['default_time'];
        $dayOfWeek = isset($validated['day_of_week']) ? (string) $validated['day_of_week'] : $task['default_day'];

        $thaiDays = [
            0 => 'วันอาทิตย์',
            1 => 'วันจันทร์',
            2 => 'วันอังคาร',
            3 => 'วันพุธ',
            4 => 'วันพฤหัสบดี',
            5 => 'วันศุกร์',
            6 => 'วันเสาร์',
        ];

        if ($freqType === 'daily') {
            $parts = explode(':', $time);
            $hour = (int) ($parts[0] ?? 2);
            $minute = (int) ($parts[1] ?? 0);
            $expression = "{$minute} {$hour} * * *";
            $scheduleText = "ทุกวัน เวลา {$time} น.";
        } elseif ($freqType === 'weekly') {
            $parts = explode(':', $time);
            $hour = (int) ($parts[0] ?? 3);
            $minute = (int) ($parts[1] ?? 0);
            $expression = "{$minute} {$hour} * * {$dayOfWeek}";
            $dayName = $thaiDays[(int) $dayOfWeek] ?? 'วันอาทิตย์';
            $scheduleText = "ทุก{$dayName} เวลา {$time} น.";
        } elseif ($freqType === 'custom') {
            $custom = trim($validated['custom_expression'] ?? '');
            if (!CronExpression::isValidExpression($custom)) {
                return redirect()->route('backend.system_scheduler.index')
                    ->with('error', "Cron Expression '{$custom}' ไม่ถูกต้องตามมาตรฐาน (ต้องประกอบด้วย 5 ส่วน เช่น 0 2 * * *)");
            }
            $expression = $custom;
            $scheduleText = "กำหนดเอง ({$custom})";
        }

        // Save into SystemSetting
        SystemSetting::set("scheduler_{$key}_enabled", $isEnabled, 'scheduler', "เปิด/ปิดงาน {$task['name']}");
        SystemSetting::set("scheduler_{$key}_expression", $expression, 'scheduler', "Cron Expression สำหรับ {$task['name']}");
        SystemSetting::set("scheduler_{$key}_time", $time, 'scheduler', "เวลาที่รันสำหรับ {$task['name']}");
        SystemSetting::set("scheduler_{$key}_day_of_week", $dayOfWeek, 'scheduler', "วันในสัปดาห์สำหรับ {$task['name']}");
        SystemSetting::set("scheduler_{$key}_frequency_type", $freqType, 'scheduler', "ประเภทความถี่สำหรับ {$task['name']}");
        SystemSetting::set("scheduler_{$key}_schedule_text", $scheduleText, 'scheduler', "ข้อความความถี่สำหรับ {$task['name']}");

        Cache::forget("sys_setting_scheduler_{$key}_enabled");
        Cache::forget("sys_setting_scheduler_{$key}_expression");
        Cache::forget("sys_setting_scheduler_{$key}_time");
        Cache::forget("sys_setting_scheduler_{$key}_day_of_week");
        Cache::forget("sys_setting_scheduler_{$key}_frequency_type");
        Cache::forget("sys_setting_scheduler_{$key}_schedule_text");

        // Record Audit Log
        AuditLog::record(
            event: 'update',
            auditable: 'SystemScheduler',
            oldValues: null,
            newValues: [
                'task_id' => $task['id'],
                'enabled' => $isEnabled,
                'expression' => $expression,
                'schedule_text' => $scheduleText,
            ],
            remarks: "ปรับปรุงช่วงเวลาตั้งรันงานอัตโนมัติ: {$task['name']}",
            diffSummary: "แก้ไขช่วงเวลาเป็น: {$scheduleText} (Cron: {$expression}, สถานะ: " . ($isEnabled === '1' ? 'เปิด' : 'ปิด') . ")"
        );

        return redirect()->route('backend.system_scheduler.index')
            ->with('success', "บันทึกการตั้งค่าช่วงเวลาสำหรับ '{$task['name']}' เรียบร้อยแล้ว ({$scheduleText})");
    }

    /**
     * Execute a scheduled task immediately on demand
     */
    public function run(Request $request)
    {
        $this->ensureAuthorized();

        $validated = $request->validate([
            'task_id' => 'required|string',
        ]);

        $task = collect($this->tasks)->firstWhere('id', $validated['task_id']);

        if (!$task) {
            return redirect()->route('backend.system_scheduler.index')
                ->with('error', 'ไม่พบงานที่ต้องการสั่งรัน หรือคำสั่งไม่อยู่ในรายการที่อนุญาต');
        }

        try {
            @set_time_limit(300); // 5 minutes maximum for manual runs

            $startTime = microtime(true);
            $exitCode = Artisan::call($task['command']);
            $output = trim(Artisan::output());
            $duration = round(microtime(true) - $startTime, 2);

            if (empty($output)) {
                $output = "คำสั่ง {$task['command']} ทำงานเสร็จสมบูรณ์ (ไม่มีข้อความเอาต์พุตเพิ่มเติม)";
            }

            // Record Audit Log
            AuditLog::record(
                event: 'system_scheduler.manual_run',
                auditable: 'SystemScheduler',
                oldValues: null,
                newValues: [
                    'task_id' => $task['id'],
                    'command' => $task['command'],
                    'duration_sec' => $duration,
                    'exit_code' => $exitCode,
                ],
                remarks: "สั่งรันงานอัตโนมัติ: {$task['name']} (ใช้เวลา {$duration} วินาที)",
                diffSummary: "รัน {$task['command']} สำเร็จ (Code: {$exitCode})"
            );

            return redirect()->route('backend.system_scheduler.index')
                ->with('executed_task_name', $task['name'])
                ->with('executed_command', $task['command'])
                ->with('command_output', $output)
                ->with('command_status', $exitCode === 0 ? 'success' : 'warning')
                ->with('success', "รันงาน '{$task['name']}' เสร็จสิ้นแล้ว (ใช้เวลา {$duration} วินาที)");

        } catch (\Throwable $e) {
            $output = "เกิดข้อผิดพลาดขณะรันคำสั่ง:\n" . $e->getMessage() . "\n" . $e->getTraceAsString();

            AuditLog::record(
                event: 'system_scheduler.manual_run_failed',
                auditable: 'SystemScheduler',
                oldValues: null,
                newValues: [
                    'task_id' => $task['id'],
                    'command' => $task['command'],
                    'error' => $e->getMessage(),
                ],
                remarks: "สั่งรันงานอัตโนมัติล้มเหลว: {$task['name']}",
                diffSummary: "Error: " . $e->getMessage()
            );

            return redirect()->route('backend.system_scheduler.index')
                ->with('executed_task_name', $task['name'])
                ->with('executed_command', $task['command'])
                ->with('command_output', $output)
                ->with('command_status', 'danger')
                ->with('error', "เกิดข้อผิดพลาดขณะรันงาน: " . $e->getMessage());
        }
    }

    /**
     * Test Telegram notification to Admin
     */
    public function testTelegram(Request $request)
    {
        $this->ensureAuthorized();

        $chatId = $request->input('chat_id');
        $result = TelegramNotificationService::testConnection($chatId);

        if ($result['success']) {
            return redirect()->route('backend.system_scheduler.index')
                ->with('success', 'ส่งข้อความทดสอบไปยัง Telegram เรียบร้อยแล้ว! กรุณาตรวจสอบในแอป Telegram ของท่าน');
        }

        return redirect()->route('backend.system_scheduler.index')
            ->with('error', 'ส่งข้อความไม่สำเร็จ: ' . ($result['error'] ?? 'ไม่ทราบสาเหตุ'));
    }

    /**
     * Ensure current user has superadmin or admin research role
     */
    protected function ensureAuthorized(): void
    {
        $user = auth()->user();
        $isAuthorized = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        if (!$isAuthorized) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงศูนย์จัดการระบบตั้งเวลาอัตโนมัติ (Super Admin Only)');
        }
    }
}
