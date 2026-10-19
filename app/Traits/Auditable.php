<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $hidden = array_merge($model->getHidden() ?? [], ['password', 'password_hash', 'auth_key', 'remember_token']);
            $newValues = array_diff_key($model->getAttributes(), array_flip($hidden));

            AuditLog::record(
                event: 'create',
                auditable: $model,
                oldValues: null,
                newValues: $newValues,
                remarks: 'สร้างข้อมูลใหม่',
                diffSummary: 'เพิ่มรายการใหม่'
            );
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            $hidden = array_merge($model->getHidden() ?? [], ['password', 'password_hash', 'auth_key', 'remember_token', 'updated_at', 'user_updated']);
            
            $filteredDirty = array_diff_key($dirty, array_flip($hidden));
            if (empty($filteredDirty)) {
                return;
            }

            $original = $model->getOriginal();
            $oldValues = array_intersect_key($original, $filteredDirty);
            $newValues = $filteredDirty;

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
            $hidden = array_merge($model->getHidden() ?? [], ['password', 'password_hash', 'auth_key', 'remember_token']);
            $oldValues = array_diff_key($model->getAttributes(), array_flip($hidden));

            AuditLog::record(
                event: 'delete',
                auditable: $model,
                oldValues: $oldValues,
                newValues: null,
                remarks: 'ลบรายการข้อมูล',
                diffSummary: 'ลบข้อมูลออกจากระบบ'
            );
        });
    }

    /**
     * Get audit logs for this model instance
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'auditable_id')
            ->where('auditable_type', class_basename($this))
            ->orderBy('id', 'desc');
    }
}
