<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDepartmentType extends Model
{
    protected $table = 'rdb_department_type';
    protected $primaryKey = 'tdepartment_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'tdepartment_nameTH',
        'tdepartment_nameEN',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
