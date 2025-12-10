<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPublishedPersonnel extends Model
{
    protected $table = 'rdb_published_personnel';
    protected $primaryKey = 'personnel_id';
    public $timestamps = false;

    public function year()
    {
        return $this->belongsTo(RdbYear::class, 'year_id', 'year_id');
    }

    public function department()
    {
        return $this->belongsTo(RdbDepartment::class, 'department_id', 'department_id');
    }

    public function departmentCategory()
    {
        return $this->belongsTo(RdbDepartmentCategory::class, 'depcat_id', 'depcat_id');
    }

    protected $fillable = [
        'year_id',
        'department_id',
        'depcat_id',
        'personnel_num',
        'personnel_numedu',
        'personnel_numbud',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
