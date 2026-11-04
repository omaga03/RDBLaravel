<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\TelegramNotificationService;
use Cron\CronExpression;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

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
            'expression' => '0 2 * * *',
            'schedule_text' => 'ทุกวัน เวลา 02:00 น.',
            'category' => 'ความปลอดภัย & กู้คืนระบบ',
            'badge_class' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'icon' => 'bi-database-fill-gear',
            'description' => 'ส่งออกโครงสร้างและข้อมูลทุกตารางของระบบลงไฟล์ .sql.gz เพื่อป้องกันความเสียหายจากเหตุสุดวิสัย',
        ],
        [
            'id' => 'backup-all',
            'name' => 'สำรองระบบเต็มรูปแบบประจำสัปดาห์ (Weekly Full Backup)',
            'command' => 'rdb:backup --type=all',
            'expression' => '0 3 * * 0',
            'schedule_text' => 'ทุกวันอาทิตย์ เวลา 03:00 น.',
            'category' => 'ความปลอดภัย & กู้คืนระบบ',
            'badge_class' => 'bg-danger-subtle text-danger border border-danger-subtle',
            'icon' => 'bi-archive-fill',
            'description' => 'สำรองฐานข้อมูลพร้อมบีบอัดเอกสารงานวิจัย ไฟล์แนบ และภาพหน้าปกทั้งหมดลงคลัง Archives',
        ],
        [
            'id' => 'count-pdf-pages',
            'name' => 'นับจำนวนหน้าเอกสาร PDF อัตโนมัติ (PDF Page Counter)',
            'command' => 'rdb:count-pdf-pages --module=all --limit=100',
            'expression' => '0 4 * * *',
            'schedule_text' => 'ทุกวัน เวลา 04:00 น.',
            'category' => 'เอกสาร & คลังดิจิทัล',
            'badge_class' => 'bg-info-subtle text-info border border-info-subtle',
            'icon' => 'bi-file-earmark-pdf-fill',
            'description' => 'ตรวจสอบไฟล์ PDF โครงการวิจัยและผลงานตีพิมพ์ที่ตกค้าง พร้อมนับหน้าด้วย 4-Tier Engine แม่นยำ 99.99%',
        ],
        [
            'id' => 'generate-pdf-covers',
            'name' => 'เรนเดอร์ภาพหน้าปก WebP อัตโนมัติ (PDF Cover Rasterizer)',
            'command' => 'rdb:generate-pdf-covers --module=all --limit=50',
            'expression' => '30 4 * * *',
            'schedule_text' => 'ทุกวัน เวลา 04:30 น.',
            'category' => 'เอกสาร & คลังดิจิทัล',
            'badge_class' => 'bg-warning-subtle text-warning border border-warning-subtle',
            'icon' => 'bi-image-fill',
            'description' => 'สร้างภาพหน้าปก .webp ความละเอียดสูงจากหน้าแรกของเล่มรายงานวิจัย สำหรับหน้าการ์ดและ DSpace',
        ],
        [
            'id' => 'sync-nriis-news',
            'name' => 'ซิงค์ข่าวประกาศทุนวิจัยจากระบบ NRIIS (Funding Sync)',
            'command' => 'rdb:sync-nriis-news',
            'expression' => '0 6 * * *',
            'schedule_text' => 'ทุกวัน เวลา 06:00 น.',
            'category' => 'บูรณาการ & ข้อมูลภายนอก',
            'badge_class' => 'bg-success-subtle text-success border border-success-subtle',
            'icon' => 'bi-megaphone-fill',
            'description' => 'นำเข้าข่าวประกาศรับสมัครทุนวิจัยใหม่จากสำนักงานการวิจัยแห่งชาติ (วช. / NRIIS) เข้าสู่ระบบอัตโนมัติ',
        ],
        [
            'id' => 'sync-facebook-conferences',
            'name' => 'ซิงค์งานประชุมวิชาการจากเพจสถาบันวิจัยฯ (Event Sync)',
            'command' => 'rdb:sync-facebook-conferences',
            'expression' => '30 6 * * *',
            'schedule_text' => 'ทุกวัน เวลา 06:30 น.',
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
            try {
                $cron = new CronExpression($task['expression']);
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
                $task['next_run_thai'] = 'ไม่สามารถคำนวณได้';
                $task['next_run_diff'] = '-';
            }

            return $task;
        }, $this->tasks);

        $nowThai = TelegramNotificationService::formatThaiDateTime(now());
        $telegramConfigured = TelegramNotificationService::isConfigured();
        $telegramAdminConfigured = TelegramNotificationService::isAdminConfigured();
        $adminChatId = config('services.telegram.admin_chat_id');

        return view('backend.system_scheduler.index', compact(
            'tasksWithRunInfo',
            'nowThai',
            'telegramConfigured',
            'telegramAdminConfigured',
            'adminChatId'
        ));
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
            $log = AuditLog::record(
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
