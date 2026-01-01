<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDepMajor extends Model
{
    protected $table = 'rdb_dep_major';
    protected $primaryKey = 'maj_code';
    public $timestamps = false;

    public function departmentCourse()
    {
        return $this->belongsTo(RdbDepartmentCourse::class, 'depcou_id', 'depcou_id');
    }

    public function department()
    {
        return $this->belongsTo(RdbDepartment::class, 'department_id', 'department_id');
    }

    public function researchers()
    {
        return $this->hasMany(RdbResearcher::class, 'maj_id', 'maj_id');
    }

    public function canDelete()
    {
        return $this->researchers()->count() === 0;
    }

    protected $fillable = [
        'maj_id',
        'depcou_id',
        'department_id',
        'maj_nameTH',
        'maj_nameEN',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
