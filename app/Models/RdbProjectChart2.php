<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectChart2 extends Model
{
    protected $table = 'rdb_project_chart_2';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'year_id',
        'year_name',
        'pt_id',
        'pt_name',
        'department_id',
        'department_nameTH',
        'department_color',
        'count_group',
        'count_project',
        'sum_budget_group',
        'sum_budget_project',
        'count_ps',
        'show_chart',
        'updated_at',
    ];
}
