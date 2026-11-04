<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Models\User;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $hidden = array_merge($model->getHidden() ?? [], [
                'password',
                'password_hash',
                'auth_key',
                'remember_token',
                'password_reset_token',
                'account_activation_token',
            ]);

            $newValues = array_diff_key($model->getAttributes(), array_flip($hidden));
            $newValues = static::sanitizeAuditAttributes($newValues);

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
            $hidden = array_merge($model->getHidden() ?? [], [
                'password',
                'password_hash',
                'auth_key',
                'remember_token',
                'password_reset_token',
                'account_activation_token',
                'updated_at',
                'user_updated',
            ]);

            $filteredDirty = array_diff_key($dirty, array_flip($hidden));
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
            $hidden = array_merge($model->getHidden() ?? [], [
                'password',
                'password_hash',
                'auth_key',
                'remember_token',
                'password_reset_token',
                'account_activation_token',
            ]);

            $oldValues = array_diff_key($model->getAttributes(), array_flip($hidden));
            $oldValues = static::sanitizeAuditAttributes($oldValues);

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
