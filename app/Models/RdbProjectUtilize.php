<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectUtilize extends Model
{
    protected $table = 'rdb_project_utilize';
    protected $primaryKey = 'utz_id';
    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function changwat()
    {
        return $this->belongsTo(RdbChangwat::class, 'chw_id', 'id');
    }

    protected $fillable = [
        'pro_id',
        'utz_year_id',
        'utz_year_bud',
        'utz_year_edu',
        'utz_date',
        'utz_leading',
        'utz_leading_position',
        'utz_department_name',
        'chw_id',
        'utz_group',
        'utz_group_qa',
        'utz_detail',
        'utz_budget',
        'utz_count',
        'utz_countfile',
        'utz_files',
        'data_show',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
