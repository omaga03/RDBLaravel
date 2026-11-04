<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDepartmentCourse extends Model
{
    protected $table = 'rdb_department_course';
    protected $primaryKey = 'depcou_id';
    public $timestamps = true;

    public function department()
    {
        return $this->belongsTo(RdbDepartment::class, 'department_id', 'department_id');
    }

    public function departmentCategory()
    {
        return $this->belongsTo(RdbDepartmentCategory::class, 'depcat_id', 'depcat_id');
    }

    protected $fillable = [
        'department_id',
        'depcat_id',
        'cou_name',
        'cou_name_sh',
        'user_created',
        'user_updated',
    ];

    /**
     * Smart Accessors to prevent silent null on legacy Yii2 / Laravel naming conventions
     */
    public function getCouNameTHAttribute(): ?string
    {
        return $this->cou_name;
    }

    public function getNameAttribute(): ?string
    {
        return $this->cou_name;
    }
}
