<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'scope_info',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'diff_summary',
        'url',
        'ip_address',
        'user_agent',
        'remarks',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Record an audit log entry safely
     */
    public static function record(
        string $event,
        $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $remarks = null,
        ?string $diffSummary = null
    ): ?self {
        try {
            $user = auth()->user();
            $scope = ($user && method_exists($user, 'getActiveRoleScope')) ? $user->getActiveRoleScope() : null;

            return self::create([
                'user_id' => $user?->id,
                'user_name' => $user ? ($user->researcher ? $user->researcher->full_name : $user->username) : 'Guest / System',
                'user_role' => $scope ? $scope->role_name : ($user ? ($user->roles->first()?->item_name ?? 'User') : 'Guest'),
                'scope_info' => $scope ? ($scope->department ? "Dept: {$scope->department->department_name}" : ($scope->domain_key ? "Domain: {$scope->domain_key}" : 'Central')) : null,
                'event' => $event,
                'auditable_type' => is_object($auditable) ? class_basename($auditable) : (string) $auditable,
                'auditable_id' => is_object($auditable) ? ($auditable->getKey() ?? 0) : 0,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'diff_summary' => $diffSummary,
                'url' => request()?->fullUrl(),
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'remarks' => $remarks,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('AuditLog record error: ' . $e->getMessage());
            return null;
        }
    }
}
