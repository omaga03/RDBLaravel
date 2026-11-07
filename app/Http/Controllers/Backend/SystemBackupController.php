<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SystemBackupService;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemBackupController extends Controller
{
    protected SystemBackupService $backupService;

    public function __construct(SystemBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Enforce strict authorization: The Creator only
     */
    protected function ensureTheCreatorOnly(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('The Creator') && $user->id !== 129)) {
            abort(403, 'สงวนสิทธิ์การเข้าถึงและสั่งการเฉพาะ The Creator (ผู้ดูแลระบบสูงสุด) เท่านั้น');
        }
    }

    /**
     * Display the System Backup & Disaster Recovery Hub
     */
    public function index()
    {
        $this->ensureTheCreatorOnly();

        $backupList = $this->backupService->getBackupList();
        $dbSizeFormatted = $this->backupService->formatBytes($this->backupService->getDatabaseSize());
        $storageSizeFormatted = $this->backupService->formatBytes($this->backupService->getStorageSize());
        $mysqldumpAvailable = !empty($this->backupService->getMysqldumpBinary());
        $mysqlAvailable = !empty($this->backupService->getMysqlBinary());
        $currentProgress = SystemBackupService::getProgress();
        $isBackupRunning = SystemBackupService::isBackupRunning();

        // If completed/failed and was finished more than 60 seconds ago, auto-clear stale card
        if (!$isBackupRunning && $currentProgress && in_array($currentProgress['status'] ?? '', ['completed', 'failed'])) {
            $updatedAt = $currentProgress['updated_timestamp'] ?? 0;
            if ((time() - $updatedAt) > 60) {
                SystemBackupService::clearProgress();
                $currentProgress = null;
            }
        }

        return view('backend.system_backup.index', compact(
            'backupList',
            'dbSizeFormatted',
            'storageSizeFormatted',
            'mysqldumpAvailable',
            'mysqlAvailable',
            'currentProgress',
            'isBackupRunning'
        ));
    }

    /**
     * Trigger backup creation (Asynchronous Background Execution)
     */
    public function create(Request $request)
    {
        $this->ensureTheCreatorOnly();

        $request->validate([
            'type' => 'required|string|in:all,db,files',
        ]);

        $type = $request->input('type');

        // Prevent overlapping backups
        if (SystemBackupService::isBackupRunning()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'มีกระบวนการสำรองข้อมูลกำลังดำเนินการอยู่แล้ว กรุณารอสักครู่',
                    'progress' => SystemBackupService::getProgress(),
                ], 429);
            }
            return redirect()->route('backend.system_backup.index')
                             ->with('warning', 'มีกระบวนการสำรองข้อมูลกำลังดำเนินการอยู่แล้ว');
        }

        // Dispatch background worker
        $this->backupService->dispatchBackgroundBackup($type);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'เริ่มต้นกระบวนการสำรองข้อมูลในเบื้องหลังเรียบร้อยแล้ว',
                'type' => $type,
                'progress' => SystemBackupService::getProgress(),
            ]);
        }

        return redirect()->route('backend.system_backup.index')
                         ->with('success', 'ระบบกำลังดำเนินการสำรองข้อมูลในเบื้องหลัง พร้อมแสดงแถบสถานะการทำงาน');
    }

    /**
     * Get real-time backup progress (AJAX polling)
     */
    public function progress()
    {
        $this->ensureTheCreatorOnly();

        $progress = SystemBackupService::getProgress();
        $isRunning = SystemBackupService::isBackupRunning();

        return response()->json([
            'running' => $isRunning,
            'progress' => $progress ?: [
                'status' => 'idle',
                'percent' => 0,
                'stage_label' => 'ไม่มีงานสำรองข้อมูลที่กำลังทำงาน',
            ],
        ]);
    }

    /**
     * Cancel or clear backup progress state
     */
    public function cancelProgress()
    {
        $this->ensureTheCreatorOnly();

        SystemBackupService::clearProgress();

        return response()->json([
            'success' => true,
            'message' => 'ล้างสถานะการสำรองข้อมูลเรียบร้อยแล้ว',
        ]);
    }

    /**
     * Download backup archive securely
     */
    public function download(string $filename): BinaryFileResponse
    {
        $this->ensureTheCreatorOnly();

        $path = $this->backupService->getBackupFilePath($filename);
        if (!$path) {
            abort(404, 'ไม่พบไฟล์สำรองที่ต้องการดาวน์โหลด');
        }

        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'backup_downloaded',
                'model_type' => 'SystemBackup',
                'model_id' => 0,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'N/A',
                'details' => json_encode(['filename' => basename($filename), 'size' => filesize($path)], JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            // ignore audit log error
        }

        return response()->download($path, basename($filename), [
            'Content-Type' => str_ends_with($filename, '.gz') ? 'application/gzip' : 'application/zip',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * Delete backup archive via POST body (avoids browser download extensions intercepting .zip/.gz URLs)
     */
    public function destroyPost(Request $request)
    {
        $this->ensureTheCreatorOnly();

        $filename = trim((string) $request->input('filename', ''));
        if ($filename === '') {
            return redirect()->route('backend.system_backup.index')
                             ->with('error', 'ไม่ได้ระบุชื่อไฟล์สำรองที่ต้องการลบ');
        }

        return $this->destroy($filename);
    }

    /**
     * Delete backup archive
     */
    public function destroy(string $filename)
    {
        $this->ensureTheCreatorOnly();

        $clean = basename($filename);
        $deleted = $this->backupService->deleteBackup($clean);
        if ($deleted) {
            return redirect()->route('backend.system_backup.index')
                             ->with('success', "ลบไฟล์สำรอง {$clean} เรียบร้อยแล้ว");
        }

        return redirect()->route('backend.system_backup.index')
                         ->with('error', "ไม่สามารถลบไฟล์ {$clean} ได้ หรือไม่พบไฟล์");
    }

    /**
     * Request Restore OTP via AJAX/Form
     */
    public function requestRestoreOtp(Request $request)
    {
        $this->ensureTheCreatorOnly();

        $request->validate([
            'filename' => 'required|string',
        ]);

        $filename = $request->input('filename');

        try {
            $creator = auth()->user();
            $res = $this->backupService->sendRestoreOtp($creator, $filename, $request->ip(), $request->userAgent());

            $msg = $res['mail_sent'] 
                ? "ระบบได้จัดส่งรหัสความปลอดภัย OTP 6 หลักไปยังอีเมล {$creator->email} แล้ว กรุณานำรหัสมากรอกยืนยันภายใน 10 นาที"
                : "สร้างรหัสความปลอดภัย OTP สำเร็จแล้ว (บันทึกลงในระบบ Log สำหรับทดสอบ)";

            return response()->json([
                'success' => true,
                'message' => $msg,
                'recipient' => $creator->email,
                'dev_otp' => $res['dev_otp'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถส่ง OTP ได้: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Confirm Restore with OTP and Confirmation Phrase
     */
    public function confirmRestore(Request $request)
    {
        $this->ensureTheCreatorOnly();

        $request->validate([
            'filename' => 'required|string',
            'otp' => 'required|string|size:6',
            'confirm_text' => 'required|string',
        ]);

        $filename = $request->input('filename');
        $otp = $request->input('otp');
        $confirmText = trim($request->input('confirm_text'));

        if (strtoupper($confirmText) !== 'CONFIRM RESTORE') {
            return redirect()->route('backend.system_backup.index')
                             ->with('error', 'ข้อความยืนยันไม่ถูกต้อง กรุณาพิมพ์ CONFIRM RESTORE ให้ตรงตามที่ระบบระบุ');
        }

        $validOtp = $this->backupService->verifyRestoreOtp($filename, $otp);
        if (!$validOtp) {
            return redirect()->route('backend.system_backup.index')
                             ->with('error', 'รหัสความปลอดภัย OTP ไม่ถูกต้องหรือหมดอายุแล้ว (กรุณากดขอ OTP ใหม่)');
        }

        try {
            $res = $this->backupService->restoreBackup($filename);

            // Clear system caches after restore
            try {
                Artisan::call('cache:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
            } catch (\Throwable $e) {
                // ignore
            }

            $successMsg = "กู้คืนระบบจากไฟล์ {$res['restored_file']} สำเร็จสมบูรณ์! (ระบบได้สร้าง Safety Snapshot ไว้ที่ {$res['safety_snapshot']} โดยอัตโนมัติก่อนเริ่มกู้คืน)";

            return redirect()->route('backend.system_backup.index')
                             ->with('success', $successMsg);
        } catch (\Throwable $e) {
            return redirect()->route('backend.system_backup.index')
                             ->with('error', 'การกู้คืนระบบล้มเหลว: ' . $e->getMessage());
        }
    }
}
