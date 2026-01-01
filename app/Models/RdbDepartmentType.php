<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDepartmentType extends Model
{
    protected $table = 'rdb_department_type';
    protected $primaryKey = 'tdepartment_id';
    public $timestamps = false;

    public function departments()
    {
        return $this->hasMany(RdbDepartment::class, 'tdepartment_id', 'tdepartment_id');
    }

    public function canDelete()
    {
        return $this->departments()->count() === 0;
    }

    protected $fillable = [
        'tdepartment_nameTH',
        'tdepartment_nameEN',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
