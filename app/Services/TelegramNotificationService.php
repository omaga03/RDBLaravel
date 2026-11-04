<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    /**
     * Get active Telegram Bot Token (from SystemSetting or .env fallback)
     */
    public static function getBotToken(): ?string
    {
        try {
            $token = \App\Models\SystemSetting::get('telegram_bot_token');
            if (!empty($token)) {
                return trim($token);
            }
        } catch (\Throwable $e) {}

        return config('services.telegram.bot_token');
    }

    /**
     * Get active Super Admin Chat ID (from SystemSetting or .env fallback)
     */
    public static function getAdminChatId(): ?string
    {
        try {
            $chatId = \App\Models\SystemSetting::get('telegram_admin_chat_id');
            if (!empty($chatId)) {
                return trim($chatId);
            }
        } catch (\Throwable $e) {}

        return config('services.telegram.admin_chat_id');
    }

    /**
     * Check if Telegram bot token and admin chat ID are configured.
     */
    public static function isConfigured(): bool
    {
        $enabled = config('services.telegram.enabled', true);
        $token = self::getBotToken();

        return $enabled && !empty($token);
    }

    /**
     * Check if Admin notifications are configured.
     */
    public static function isAdminConfigured(): bool
    {
        $adminChatId = self::getAdminChatId();
        return self::isConfigured() && !empty($adminChatId);
    }

    /**
     * Send raw message to a specific Telegram chat.
     */
    public static function sendMessage(string $chatId, string $message, string $parseMode = 'HTML'): array
    {
        $token = self::getBotToken();
        $enabled = config('services.telegram.enabled', true);
        $timeout = (int) config('services.telegram.timeout', 4);

        if (!$enabled || empty($token) || empty($chatId)) {
            return [
                'success' => false,
                'error' => 'Telegram service is not configured or disabled.',
            ];
        }

        try {
            $response = Http::timeout($timeout)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->json(),
                ];
            }

            Log::warning('Telegram API error response: ' . $response->body());
            return [
                'success' => false,
                'error' => 'Telegram API returned status: ' . $response->status() . ' - ' . $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::warning('Telegram notification failed to send: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send notification to the Super Admin (The Creator / Admin Research).
     */
    public static function notifyAdmin(string $title, string $body, array $meta = []): bool
    {
        $adminChatId = self::getAdminChatId();
        if (!self::isAdminConfigured() || empty($adminChatId)) {
            return false;
        }

        $nowThai = self::formatThaiDateTime(now());

        $lines = [];
        $lines[] = "🏛️ <b>[PCRU RDB] การแจ้งเตือนระบบ</b>";
        $lines[] = "⚡ <b>" . htmlspecialchars($title) . "</b>";
        $lines[] = "";
        $lines[] = htmlspecialchars($body);

        if (!empty($meta)) {
            $lines[] = "";
            $lines[] = "📋 <b>รายละเอียดเพิ่มเติม:</b>";
            foreach ($meta as $k => $v) {
                if (is_array($v) || is_object($v)) {
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                }
                $lines[] = "• <i>" . htmlspecialchars($k) . "</i>: " . htmlspecialchars((string) $v);
            }
        }

        $lines[] = "";
        $lines[] = "⏱️ <i>" . $nowThai . "</i>";

        $result = self::sendMessage($adminChatId, implode("\n", $lines), 'HTML');
        return $result['success'];
    }

    /**
     * Dispatch an instant Telegram alert for an Audit Trail event to the Super Admin.
     */
    public static function notifyAuditEvent(AuditLog $log): bool
    {
        if (!self::isAdminConfigured()) {
            return false;
        }

        $adminChatId = config('services.telegram.admin_chat_id');
        $event = strtolower((string) $log->event);

        // Friendly Event Labels with Icons
        $eventHeader = match ($event) {
            'create' => '🟢 [เพิ่มข้อมูลใหม่ / Create]',
            'update' => '🟡 [แก้ไขข้อมูล / Update]',
            'delete' => '🔴 [ลบข้อมูล / Delete]',
            'approve' => '✅ [อนุมัติเผยแพร่ / Approved]',
            'reject' => '❌ [ส่งกลับแก้ไข / Rejected]',
            'backup' => '💾 [สำรองข้อมูลระบบ / Backup]',
            'restore' => '⚠️ [กู้คืนระบบ / Restore]',
            'login' => '🔐 [เข้าสู่ระบบ / Auth Login]',
            'impersonate' => '👤 [สวมสิทธิ์ผู้ใช้งาน / Impersonation]',
            'system_scheduler.manual_run' => '⚡ [สั่งรันงานอัตโนมัติ / Scheduler Run]',
            default => 'ℹ️ [กิจกรรมระบบ: ' . strtoupper($event) . ']',
        };

        // Module Names in Thai
        $moduleName = match ($log->auditable_type) {
            'RdbProject' => 'โครงการวิจัย (Research Project)',
            'RdbPublished' => 'ผลงานตีพิมพ์ (Publication)',
            'RdbDip' => 'ทรัพย์สินทางปัญญา (Patent / IP)',
            'RdbProjectUtilize' => 'การนำไปใช้ประโยชน์ (Utilization)',
            'RdbResearcher' => 'ทำเนียบนักวิจัย (Researcher)',
            'User' => 'บัญชีผู้ใช้งาน (User Account)',
            'RdbReviewAssignment' => 'งานประเมินโครงการ (Review)',
            'ResearchNews' => 'ข่าวทุนวิจัย (Funding News)',
            'ResearchConference' => 'ข่าวงานประชุม (Conference)',
            'SystemBackup' => 'การสำรองและกู้คืน (Backup & Restore)',
            'SystemCommand' => 'คำสั่งบำรุงรักษา (System Commands)',
            default => $log->auditable_type ?: 'ระบบงานทั่วไป (Core System)',
        };

        // Check if file upload occurred
        $hasFileUpload = false;
        $uploadedFiles = [];
        if (is_array($log->new_values)) {
            $fileKeywords = ['file', 'path', 'cover', 'pdf', 'upload', 'image'];
            foreach ($log->new_values as $key => $val) {
                if (!empty($val) && is_string($val)) {
                    foreach ($fileKeywords as $kw) {
                        if (str_contains(strtolower($key), $kw) && (str_contains($val, '.') || str_contains($val, '/'))) {
                            $hasFileUpload = true;
                            $uploadedFiles[] = basename($val);
                            break;
                        }
                    }
                }
            }
        }

        $nowThai = self::formatThaiDateTime($log->created_at ?? now());

        $lines = [];
        $lines[] = "🏛️ <b>[PCRU RDB Audit Alert]</b>";
        $lines[] = "<b>" . $eventHeader . "</b>";
        $lines[] = "";
        $lines[] = "📌 <b>ระบบงาน:</b> " . htmlspecialchars($moduleName);
        if ($log->auditable_id) {
            $lines[] = "🆔 <b>รหัสรายการ:</b> #" . htmlspecialchars((string) $log->auditable_id);
        }
        $lines[] = "👤 <b>ผู้ดำเนินการ:</b> " . htmlspecialchars($log->user_name ?: 'ระบบอัตโนมัติ') . " (" . htmlspecialchars($log->user_role ?: 'Guest') . ")";

        if (!empty($log->ip_address)) {
            $lines[] = "🌐 <b>IP / เครือข่าย:</b> <code>" . htmlspecialchars($log->ip_address) . "</code>";
        }

        if ($hasFileUpload && !empty($uploadedFiles)) {
            $lines[] = "📎 <b>ไฟล์แนบ:</b> " . htmlspecialchars(implode(', ', array_slice($uploadedFiles, 0, 3)));
        }

        if (!empty($log->diff_summary)) {
            $lines[] = "📝 <b>การเปลี่ยนแปลง:</b> " . htmlspecialchars($log->diff_summary);
        } elseif (!empty($log->remarks)) {
            $lines[] = "📝 <b>หมายเหตุ:</b> " . htmlspecialchars($log->remarks);
        }

        $lines[] = "";
        $lines[] = "⏱️ <i>" . $nowThai . "</i>";

        // Check Admin preferences
        $telegramEnabled = \App\Models\SystemSetting::get('admin_notify_telegram', '1') === '1';
        $mailEnabled = \App\Models\SystemSetting::get('admin_notify_mail', '0') === '1';

        // Check if event is enabled in admin subscriptions
        $shouldNotify = match ($event) {
            'create' => \App\Models\SystemSetting::get('admin_notify_create', '1') === '1',
            'update' => $hasFileUpload ? (\App\Models\SystemSetting::get('admin_notify_upload', '1') === '1') : (\App\Models\SystemSetting::get('admin_notify_update', '1') === '1'),
            'delete' => \App\Models\SystemSetting::get('admin_notify_delete', '1') === '1',
            'approve', 'reject' => \App\Models\SystemSetting::get('admin_notify_update', '1') === '1',
            'backup', 'restore' => \App\Models\SystemSetting::get('admin_notify_backup', '1') === '1',
            'login', 'impersonate' => \App\Models\SystemSetting::get('admin_notify_auth', '0') === '1',
            'system_scheduler.manual_run' => \App\Models\SystemSetting::get('admin_notify_scheduler', '1') === '1',
            default => true,
        };

        if (!$shouldNotify) {
            return false;
        }

        $sentTelegram = false;
        if ($telegramEnabled && self::isAdminConfigured()) {
            $result = self::sendMessage(self::getAdminChatId(), implode("\n", $lines), 'HTML');
            $sentTelegram = $result['success'];
        }

        if ($mailEnabled) {
            try {
                $adminEmail = config('mail.admin_address') ?: \App\Models\User::where('id', 129)->value('email');
                if (!empty($adminEmail)) {
                    \Illuminate\Support\Facades\Mail::raw(strip_tags(str_replace(['<br>', '<b>', '</b>'], ["\n", '', ''], implode("\n", $lines))), function ($m) use ($adminEmail, $eventHeader, $moduleName) {
                        $m->to($adminEmail)->subject("[PCRU RDB Admin Alert] {$eventHeader} - {$moduleName}");
                    });
                }
            } catch (\Throwable $me) {
                Log::warning('Admin email audit alert skipped: ' . $me->getMessage());
            }
        }

        return $sentTelegram;
    }

    /**
     * Send connection test message.
     */
    public static function testConnection(?string $chatId = null): array
    {
        $targetChatId = $chatId ?: config('services.telegram.admin_chat_id');
        if (empty($targetChatId)) {
            return [
                'success' => false,
                'error' => 'ยังไม่ได้ระบุ Telegram Chat ID สำหรับทดสอบ (กรุณาระบุใน .env หรือฟอร์ม)',
            ];
        }

        $nowThai = self::formatThaiDateTime(now());
        $message = "🏛️ <b>[PCRU RDB] ทดสอบการเชื่อมต่อระบบแจ้งเตือน Telegram สำเร็จ!</b>\n\n"
                 . "✅ บอทระบบฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ สามารถส่งข้อความมายัง Chat ID นี้ได้ตามปกติ 100%\n"
                 . "📱 <b>Target Chat ID:</b> <code>{$targetChatId}</code>\n"
                 . "⏱️ <b>เวลาทดสอบ:</b> {$nowThai}\n\n"
                 . "<i>ระบบพร้อมส่งแจ้งเตือนการ เพิ่ม/ลบ/แก้ไข/อัปโหลดไฟล์ และงานสำคัญอัตโนมัติ</i>";

        return self::sendMessage($targetChatId, $message, 'HTML');
    }

    /**
     * Format DateTime into Thai Buddhist Era string.
     */
    public static function formatThaiDateTime($dateTime): string
    {
        $dt = is_object($dateTime) ? $dateTime : \Carbon\Carbon::parse($dateTime);
        $thaiMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
        ];

        $day = $dt->format('j');
        $month = $thaiMonths[(int) $dt->format('n')] ?? $dt->format('M');
        $buddhistYear = (int) $dt->format('Y') + 543;
        $time = $dt->format('H:i:s');

        return "{$day} {$month} {$buddhistYear} เวลา {$time} น.";
    }
}
