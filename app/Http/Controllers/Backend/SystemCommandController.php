<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\RdbProject;
use App\Models\RdbPublished;
use App\Models\AuditLog;
use App\Services\PdfCoverService;
use App\Services\DSpaceExportService;
use App\Services\SystemBackupService;

class SystemCommandController extends Controller
{
    /**
     * Display the System Commands & Maintenance Dashboard
     */
    public function index(PdfCoverService $coverService, DSpaceExportService $dspaceService, SystemBackupService $backupService)
    {
        $this->ensureAuthorized();

        // 1. PDF Cover Statistics
        $projectTotalWithFile = RdbProject::where('data_show', 1)
            ->where(function ($q) {
                $q->whereNotNull('pro_file_path')->where('pro_file_path', '<>', '')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('pro_file')->where('pro_file', '<>', '')->where('pro_file', '<>', '0');
                  });
            })->count();

        $projectWithCover = RdbProject::where('data_show', 1)
            ->whereNotNull('pro_cover_path')
            ->where('pro_cover_path', '<>', '')
            ->count();

        $publishedTotalWithFile = RdbPublished::where('data_show', 1)
            ->where(function ($q) {
                $q->whereNotNull('pf_path')->where('pf_path', '<>', '')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('pub_file')->where('pub_file', '<>', '')->where('pub_file', '<>', '0');
                  });
            })->count();

        $publishedWithCover = RdbPublished::where('data_show', 1)
            ->whereNotNull('pub_cover_path')
            ->where('pub_cover_path', '<>', '')
            ->count();

        // 2. Page Counting Statistics
        $projectWithPageNum = RdbProject::where('data_show', 1)
            ->whereNotNull('pro_page_num')
            ->where('pro_page_num', '>', 0)
            ->count();

        $publishedWithPageNum = RdbPublished::where('data_show', 1)
            ->whereNotNull('pub_numfile')
            ->where('pub_numfile', '>', 0)
            ->count();

        // 3. Engine & Environment Status Check
        $pdftotextBin = $dspaceService->getPdftotextBinary();
        $pdftoppmBin = $coverService->getPdftoppmBinary();

        $engineInfo = [
            'active_cover_engine' => $coverService->getActiveEngineDescription(),
            'python_executable' => $this->detectPythonPath(),
            'pymupdf_available' => $this->checkPyMuPdfAvailable(),
            'pdftotext_binary' => $pdftotextBin,
            'pdftotext_available' => !empty($pdftotextBin),
            'pdftoppm_binary' => $pdftoppmBin,
            'pdftoppm_available' => !empty($pdftoppmBin),
            'smalot_available' => class_exists(\Smalot\PdfParser\Parser::class),
            'fpdi_available' => class_exists('\setasign\Fpdi\Fpdi'),
            'gd_loaded' => extension_loaded('gd'),
            'gd_webp' => function_exists('imagewebp'),
            'storage_symlink_ok' => is_dir(public_path('storage')),
            'free_disk_space' => $this->getFormattedDiskSpace(),
        ];

        // 4. Advanced Server Health & Production Diagnostics
        $dbStatus = 'Connected';
        $dbVersion = 'Unknown';
        try {
            \DB::connection()->getPdo();
            $dbVersion = \DB::selectOne("SELECT VERSION() as v")?->v ?? 'Unknown';
        } catch (\Throwable $e) {
            $dbStatus = 'Disconnected: ' . $e->getMessage();
        }

        $pendingJobsCount = 0;
        $failedJobsCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('jobs')) {
                $pendingJobsCount = \DB::table('jobs')->count();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('failed_jobs')) {
                $failedJobsCount = \DB::table('failed_jobs')->count();
            }
        } catch (\Throwable $e) {}

        // 4. Resolve Active Scheduler Events Dynamically
        $activeScheduleEvents = [];
        try {
            $activeScheduleEvents = app()->make(\Illuminate\Console\Scheduling\Schedule::class)->events();
        } catch (\Throwable $e) {}

        $healthInfo = [
            'db_status' => $dbStatus,
            'db_version' => $dbVersion,
            'db_name' => \DB::getDatabaseName(),
            'queue_driver' => config('queue.default', 'sync'),
            'pending_jobs' => $pendingJobsCount,
            'failed_jobs' => $failedJobsCount,
            'app_env' => config('app.env', 'production'),
            'app_debug' => (bool)config('app.debug', false),
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'schedule_count' => count($activeScheduleEvents),
            'db_backup_time' => \App\Models\SystemSetting::get('scheduler_backup_db_schedule_text', '02:00 น. ทุกวัน'),
            'full_backup_time' => \App\Models\SystemSetting::get('scheduler_backup_all_schedule_text', 'อาทิตย์ 03:00 น.'),
            'nriis_time' => \App\Models\SystemSetting::get('scheduler_sync_nriis_news_schedule_text', '06:00 น.'),
        ];

        // 5. System Backup & Restore Information (Strictly The Creator Only)
        $isCreator = auth()->user()?->hasRole('The Creator') || auth()->user()?->id === 129;
        $backupList = $isCreator ? $backupService->getBackupList() : [];
        $dbSizeFormatted = $backupService->formatBytes($backupService->getDatabaseSize());
        $storageSizeFormatted = $backupService->formatBytes($backupService->getStorageSize());
        $mysqldumpAvailable = !empty($backupService->getMysqldumpBinary());
        $mysqlAvailable = !empty($backupService->getMysqlBinary());

        return view('backend.system_commands.index', compact(
            'projectTotalWithFile',
            'projectWithCover',
            'publishedTotalWithFile',
            'publishedWithCover',
            'projectWithPageNum',
            'publishedWithPageNum',
            'engineInfo',
            'healthInfo',
            'isCreator',
            'backupList',
            'dbSizeFormatted',
            'storageSizeFormatted',
            'mysqldumpAvailable',
            'mysqlAvailable'
        ));
    }

    /**
     * Run an Artisan system maintenance command
     */
    public function run(Request $request)
    {
        $this->ensureAuthorized();

        $request->validate([
            'command' => 'required|string|in:generate_covers,count_pages,clear_cache,optimize_all',
            'module' => 'nullable|string|in:all,project,published',
            'force' => 'nullable|boolean',
            'limit' => 'nullable|string',
        ]);

        $commandKey = $request->input('command');
        $module = $request->input('module', 'all');
        $force = $request->boolean('force');
        $limitInput = $request->input('limit');
        $limit = ($limitInput && is_numeric($limitInput) && (int)$limitInput > 0) ? (int)$limitInput : null;

        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);

        $output = '';
        $commandName = '';
        $status = 'success';

        try {
            switch ($commandKey) {
                case 'generate_covers':
                    $commandName = 'rdb:generate-pdf-covers';
                    $params = [
                        '--module' => $module,
                    ];
                    if ($force) {
                        $params['--force'] = true;
                    }
                    if ($limit) {
                        $params['--limit'] = $limit;
                    }

                    Artisan::call($commandName, $params);
                    $output = Artisan::output();
                    break;

                case 'count_pages':
                    $commandName = 'rdb:count-pdf-pages';
                    $params = [
                        '--module' => $module,
                    ];
                    if ($force) {
                        $params['--force'] = true;
                    }
                    if ($limit) {
                        $params['--limit'] = $limit;
                    }

                    Artisan::call($commandName, $params);
                    $output = Artisan::output();
                    break;

                case 'clear_cache':
                    $commandName = 'cache:clear & optimize';
                    Artisan::call('cache:clear');
                    $output .= "[cache:clear]\n" . Artisan::output() . "\n";
                    Artisan::call('view:clear');
                    $output .= "[view:clear]\n" . Artisan::output() . "\n";
                    Artisan::call('route:clear');
                    $output .= "[route:clear]\n" . Artisan::output() . "\n";
                    Artisan::call('config:clear');
                    $output .= "[config:clear]\n" . Artisan::output();
                    break;

                case 'optimize_all':
                    $commandName = 'optimize:clear';
                    Artisan::call('optimize:clear');
                    $output = Artisan::output();
                    break;
            }

            // Record to Audit Log
            AuditLog::record(
                'execute_system_command',
                auth()->user(),
                null,
                ['command' => $commandName, 'module' => $module, 'force' => $force, 'limit' => $limit],
                "รันคำสั่งระบบ: {$commandName} (สถานะ: สำเร็จ)"
            );

            return redirect()->route('backend.system_commands.index')
                ->with('command_name', $commandName)
                ->with('command_output', $output)
                ->with('command_status', 'success')
                ->with('success', "รันคำสั่ง {$commandName} เสร็จสมบูรณ์แล้ว");

        } catch (\Throwable $e) {
            $output = "เกิดข้อผิดพลาด: " . $e->getMessage() . "\n" . $e->getTraceAsString();

            AuditLog::record(
                'execute_system_command_failed',
                auth()->user(),
                null,
                ['command' => $commandKey, 'error' => $e->getMessage()],
                "รันคำสั่งระบบล้มเหลว: {$commandKey}"
            );

            return redirect()->route('backend.system_commands.index')
                ->with('command_name', $commandKey)
                ->with('command_output', $output)
                ->with('command_status', 'danger')
                ->with('error', "เกิดข้อผิดพลาดขณะรันคำสั่ง: " . $e->getMessage());
        }
    }

    /**
     * Ensure current user has superadmin or admin research role
     */
    protected function ensureAuthorized(): void
    {
        $user = auth()->user();
        $isAuthorized = $user && ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Research'));
        if (!$isAuthorized) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงศูนย์คำสั่งและบำรุงรักษาระบบ (Super Admin Only)');
        }
    }

    /**
     * Detect Python path in Laragon or system
     */
    protected function detectPythonPath(): ?string
    {
        $candidates = [
            'F:\\laragon\\bin\\python\\python-3.10\\python.exe',
            'C:\\laragon\\bin\\python\\python-3.10\\python.exe',
            'python',
            'python3',
        ];
        foreach ($candidates as $cand) {
            if (is_file($cand) && is_executable($cand)) {
                return $cand;
            }
        }
        return 'python (system path)';
    }

    /**
     * Check if PyMuPDF (fitz) is available
     */
    protected function checkPyMuPdfAvailable(): bool
    {
        $python = $this->detectPythonPath() ?: 'python';
        $cmd = escapeshellarg($python) . ' -c "import fitz, PIL; print(\'OK\')" 2>&1';
        $out = @shell_exec($cmd);
        return is_string($out) && str_contains($out, 'OK');
    }

    /**
     * Get free disk space
     */
    protected function getFormattedDiskSpace(): string
    {
        try {
            $bytes = @disk_free_space(storage_path());
            if ($bytes === false) {
                return 'ไม่สามารถตรวจสอบได้';
            }
            $gb = round($bytes / 1024 / 1024 / 1024, 2);
            return "{$gb} GB ว่าง";
        } catch (\Throwable $e) {
            return 'N/A';
        }
    }
}
