<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoleScope extends Model
{
    protected $table = 'user_role_scopes';

    protected $fillable = [
        'user_id',
        'role_name',
        'department_id',
        'domain_key',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(RdbDepartment::class, 'department_id', 'department_id');
    }

    /**
     * Get human-friendly label for this role and scope
     */
    public function getLabelAttribute(): string
    {
        $deptName = $this->department ? $this->department->department_name : null;

        return match ($this->role_name) {
            'The Creator' => '👑 ผู้ดูแลระบบสูงสุด (Super Admin)',
            'Admin Project' => '📁 ผู้ดูแลงานโครงการวิจัย (ส่วนกลาง)',
            'Admin Publication' => '📚 ผู้ดูแลงานผลงานตีพิมพ์ (ส่วนกลาง)',
            'Admin Utilize' => '🌱 ผู้ดูแลการนำไปใช้ประโยชน์ (ส่วนกลาง)',
            'Admin Researcher' => '👥 ผู้ดูแลข้อมูลนักวิจัย (ส่วนกลาง)',
            'Admin Research' => '🔬 ผู้ดูแลงานวิจัยส่วนกลาง',
            'Faculty Coordinator' => '🏛️ ผู้ประสานงานระดับคณะ' . ($deptName ? " ({$deptName})" : ''),
            'Researcher' => '🎓 นักวิจัย' . ($deptName ? " ({$deptName})" : ''),
            'General User' => '👤 สมาชิกทั่วไป',
            default => $this->role_name . ($deptName ? " ({$deptName})" : ''),
        };
    }
}
