<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Default fallback values
     */
    protected static array $defaults = [
        // Google & Auth controls
        'google_login_enabled' => '1',
        'google_client_id' => '',
        'google_client_secret' => '',
        'google_allowed_domains' => 'pcru.ac.th',
        'google_auto_register' => '1',
        'standard_login_enabled' => '1',

        // Document Download Policies (Multi-layer security)
        'download_policy_full_report' => 'members_only', // 'public', 'members_only', 'admin_only'
        'download_policy_abstract' => 'public',          // 'public', 'members_only'
        'download_policy_attachments' => 'members_only',  // 'public', 'members_only', 'admin_only'
        'download_require_login_message' => 'กรุณาเข้าสู่ระบบก่อนเพื่อดาวน์โหลดเอกสารเล่มรายงานวิจัยฉบับสมบูรณ์ ตามนโยบายความมั่นคงปลอดภัยสารสนเทศของมหาวิทยาลัย',
        'download_logging_enabled' => '1',
    ];

    /**
     * Get setting value by key with optional default
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "sys_setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $record = static::where('key', $key)->first();
            if ($record !== null) {
                return $record->value;
            }

            return $default ?? (static::$defaults[$key] ?? null);
        });
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value, string $group = 'general', ?string $description = null): self
    {
        Cache::forget("sys_setting_{$key}");

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string)$value,
                'group' => $group,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all defaults array
     */
    public static function getDefaults(): array
    {
        return static::$defaults;
    }
}
