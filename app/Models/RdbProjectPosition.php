<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectPosition extends Model
{
    protected $table = 'rdb_project_position';
    protected $primaryKey = 'position_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'position_nameTH',
        'position_desc',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    // Position Constants
    const DIRECTOR = 1;  // หัวหน้าโครงการ
    const HEAD = 2;      // ผู้ร่วมวิจัย (หัวหน้า)
}
