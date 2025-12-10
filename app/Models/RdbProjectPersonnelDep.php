<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectPersonnelDep extends Model
{
    protected $table = 'rdb_project_personnel_dep';
    protected $primaryKey = 'ppd_id';
    public $timestamps = false;

    public function year()
    {
        return $this->belongsTo(RdbYear::class, 'year_id', 'year_id');
    }

    public function department()
    {
        return $this->belongsTo(RdbDepartment::class, 'department_id', 'department_id');
    }

    public function departmentCourse()
    {
        return $this->belongsTo(RdbDepartmentCourse::class, 'depcou_id', 'depcou_id');
    }

    public function depMajor()
    {
        return $this->belongsTo(RdbDepMajor::class, 'major_id', 'maj_code');
    }

    public function departmentCategory()
    {
        return $this->belongsTo(RdbDepartmentCategory::class, 'depcat_id', 'depcat_id');
    }

    protected $fillable = [
        'year_id',
        'department_id',
        'depcou_id',
        'major_id',
        'depcat_id',
        'pp_num',
        'pp_standard',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
