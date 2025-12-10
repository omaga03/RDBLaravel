<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectChartBudget extends Model
{
    protected $table = 'rdb_project_chart_budget';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'year_id',
        'year_name',
        'department_id',
        'department_nameTH',
        'pt_id',
        'pt_name',
        'department_color',
        'sumallbudget',
        'pp_num',
        'pp_standard',
        'updated_at',
        'updated_at1',
    ];
}
