<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Models\User;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $ignoredKeys = static::getIgnoredAuditKeys($model);
            $attributes = array_filter(
                $model->getAttributes(),
                fn($val, $key) => !static::shouldIgnoreAuditKey($key, $ignoredKeys),
                ARRAY_FILTER_USE_BOTH
            );
            $newValues = static::sanitizeAuditAttributes($attributes);

            AuditLog::record(
                event: 'create',
                auditable: $model,
                oldValues: null,
                newValues: $newValues,
                remarks: 'สร้างรายการใหม่',
                diffSummary: 'สร้างรายการใหม่ในระบบ'
            );
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            $ignoredKeys = static::getIgnoredAuditKeys($model);

            $filteredDirty = array_filter(
                $dirty,
                fn($val, $key) => !static::shouldIgnoreAuditKey($key, $ignoredKeys),
                ARRAY_FILTER_USE_BOTH
            );

            if (empty($filteredDirty)) {
                return;
            }

            $original = $model->getOriginal();
            $oldValues = array_intersect_key($original, $filteredDirty);
            $newValues = $filteredDirty;

            $oldValues = static::sanitizeAuditAttributes($oldValues);
            $newValues = static::sanitizeAuditAttributes($newValues);

            $changedFields = array_keys($filteredDirty);
            $diffSummary = 'แก้ไข ' . count($changedFields) . ' ฟิลด์ (' . implode(', ', $changedFields) . ')';

            AuditLog::record(
                event: 'update',
                auditable: $model,
                oldValues: $oldValues,
                newValues: $newValues,
                remarks: 'อัปเดตข้อมูล',
                diffSummary: $diffSummary
            );
        });

        static::deleted(function ($model) {
            $ignoredKeys = static::getIgnoredAuditKeys($model);
            $attributes = array_filter(
                $model->getAttributes(),
                fn($val, $key) => !static::shouldIgnoreAuditKey($key, $ignoredKeys),
                ARRAY_FILTER_USE_BOTH
            );
            $oldValues = static::sanitizeAuditAttributes($attributes);

            AuditLog::record(
                event: 'delete',
                auditable: $model,
                oldValues: $oldValues,
                newValues: null,
                remarks: 'ลบรายการข้อมูล',
                diffSummary: 'ลบออกจากระบบ'
            );
        });
    }

    /**
     * Get keys that should never be recorded in audit logs
     */
    protected static function getIgnoredAuditKeys($model): array
    {
        $base = [
            // Passwords & Auth Tokens
            'password',
            'password_hash',
            'auth_key',
            'remember_token',
            'password_reset_token',
            'account_activation_token',
            'verification_token',
            'access_token',

            // Internal Timestamps & Audit Actors
            'created_at',
            'updated_at',
            'user_created',
            'user_updated',

            // Hit Counters & File Download Metrics across modules
            'pro_count_page',
            'pro_count_abs',
            'pro_count_full',
            'pub_download',
            'rf_download',
            'utz_count',
            'utz_countfile',
            'news_count',
            'con_count',
            'tr_count',
        ];

        $hidden = $model->getHidden() ?? [];
        $custom = property_exists($model, 'ignoreAudit') ? (array) $model->ignoreAudit : [];

        return array_unique(array_merge($base, $hidden, $custom));
    }

    /**
     * Determine if a specific attribute key should be ignored from audit trail
     */
    protected static function shouldIgnoreAuditKey(string $key, array $ignoredKeys): bool
    {
        if (in_array($key, $ignoredKeys, true)) {
            return true;
        }

        // Pattern matching for hit counters, page views, and download counts
        if (str_ends_with($key, '_count') || 
            str_ends_with($key, '_download') || 
            str_starts_with($key, 'pro_count_') ||
            str_starts_with($key, 'count_')) {
            return true;
        }

        return false;
    }

    /**
     * Sanitize and mask sensitive values before saving to audit log
     */
    protected static function sanitizeAuditAttributes(array $attributes): array
    {
        $sensitiveKeys = ['researcher_codeid', 'citizen_id', 'id_card', 'pin'];
        foreach ($sensitiveKeys as $key) {
            if (isset($attributes[$key]) && is_string($attributes[$key]) && strlen($attributes[$key]) > 4) {
                $val = $attributes[$key];
                $attributes[$key] = substr($val, 0, 3) . 'XXXX' . substr($val, -3);
            }
        }
        return $attributes;
    }

    /**
     * Get audit logs for this model instance
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'auditable_id', $this->getKeyName())
            ->where('auditable_type', class_basename($this))
            ->orderBy('id', 'desc');
    }

    /**
     * Check if a given user can view the audit trail for this model instance
     */
    public function canViewAuditTrail(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        // 1. Super Admins & Creators always can view
        if ($user->hasRole(['The Creator', 'Admin', 'Admin Research', 'Admin Project']) || (int)$user->id === 129) {
            return true;
        }

        // 2. User/Researcher model: can view own profile audit logs
        if ($this instanceof User) {
            return (int)$user->id === (int)$this->id || $user->hasPermission('User Manager');
        }

        if (class_basename($this) === 'RdbResearcher') {
            return (int)$user->researcher_id === (int)$this->getKey() || $user->hasRole(['Admin', 'Admin Research', 'Faculty Coordinator']);
        }

        // 3. Research, Published, Utilization, DIP models: check policy or ownership
        try {
            return $user->can('view', $this);
        } catch (\Throwable $e) {
            // Fallback to checking creator or researcher
            if (isset($this->user_created) && (int)$this->user_created === (int)$user->id) {
                return true;
            }
            return false;
        }
    }
}
