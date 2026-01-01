<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectStatus extends Model
{
    protected $table = 'rdb_project_status';
    protected $primaryKey = 'ps_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'ps_icon',
        'ps_name',
        'ps_color',
        'ps_rank',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    // Project Status Constants
    const IN_PROGRESS = 1;        // อยู่ระหว่างดำเนินการ
    const COMPLETED = 2;          // ดำเนินการเสร็จสิ้น
    const EXTENDED = 3;           // ขยายเวลาดำเนินการ
    const EXTENDED_2 = 4;         // ขยายเวลาดำเนินการ ครั้งที่ 2
    const OVERDUE = 5;            // ค้างส่งโครงการวิจัย

    const REFUNDED = 6;           // คืนงบประมาณ
    const STATUS_CANCELLED = 6;   // Alias for REFUNDED (used in RdbProject sql)
    const DRAFT_SUBMITTED = 7;    // ส่งร่างรายงานวิจัย
    const TERMINATED = 8;         // ยุติโครงการ
}
