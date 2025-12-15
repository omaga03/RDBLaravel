<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProject extends Model
{
    use \App\Traits\HasDataShowScope;

    protected $table = 'rdb_project';
    protected $primaryKey = 'pro_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'pro_code',
        'pgroup_id',
        'pt_id',
        'pts_id',
        'pro_nameTH',
        'pro_nameEN',
        'department_id',
        'depcou_id',
        'major_id',
        'year_id',
        'pro_abstract',
        'pro_reference',
        'pro_date_start',
        'pro_date_end',
        'strategic_id',
        'pro_budget',
        'pro_keyword',
        'pro_abstract_file',
        'pro_file',
        'pro_file_show',
        'ps_id',
        'pro_page_num',
        'pro_finish',
        'pro_group',
        'pro_count_page',
        'pro_count_abs',
        'pro_count_full',
        'data_show',
        'pro_note',
        'library_in',
        'user_created',
        'user_updated',
        'created_at',
        'created_at',
        'updated_at',
    ];

    public function status()
    {
        return $this->belongsTo(RdbProjectStatus::class, 'ps_id', 'ps_id');
    }

    public function group()
    {
        return $this->belongsTo(RdbGroupproject::class, 'pgroup_id', 'pgroup_id');
    }

    public function type()
    {
        return $this->belongsTo(RdbProjectType::class, 'pt_id', 'pt_id');
    }

    public function typeSub()
    {
        return $this->belongsTo(RdbProjectTypeSub::class, 'pts_id', 'pts_id');
    }

    public function department()
    {
        return $this->belongsTo(RdbDepartment::class, 'department_id', 'department_id');
    }

    public function year()
    {
        return $this->belongsTo(RdbYear::class, 'year_id', 'year_id');
    }

    public function strategic()
    {
        return $this->belongsTo(RdbStrategic::class, 'strategic_id', 'strategic_id');
    }

    public function researchers()
    {
        return $this->belongsToMany(RdbResearcher::class, 'rdb_project_work', 'pro_id', 'researcher_id')
                    ->withPivot('position_id', 'ratio');
    }

    public function rdbProjectWorks()
    {
        return $this->hasMany(RdbProjectWork::class, 'pro_id', 'pro_id')
                    ->join('rdb_researcher', 'rdb_project_work.researcher_id', '=', 'rdb_researcher.researcher_id')
                    ->select('rdb_project_work.*') // Ensure we return ProjectWork models
                    ->orderBy('rdb_project_work.position_id', 'asc')
                    ->orderBy('rdb_project_work.ratio', 'desc')
                    ->orderBy('rdb_researcher.researcher_fname', 'asc');
    }

    public function files()
    {
        return $this->hasMany(RdbProjectFiles::class, 'pro_id', 'pro_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_created', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'user_updated', 'id');
    }

    public function pts()
    {
        return $this->belongsTo(RdbProjectTypeSub::class, 'pts_id', 'pts_id');
    }

    public function utilizations()
    {
        return $this->hasMany(RdbProjectUtilize::class, 'pro_id', 'pro_id');
    }

    public function publisheds()
    {
        return $this->hasMany(RdbPublished::class, 'pro_id', 'pro_id');
    }

    public function dips()
    {
        return $this->hasMany(RdbDip::class, 'pro_id', 'pro_id');
    }

    // Legacy Chart Logic Ported
    public static function getReportProjectChartSplit($dep, $ptid)
    {
        $impdep = implode(',', $dep);
        $impptid = implode(',', $ptid);

        // Replaced view_year with rdb_year
        $sql = "SELECT
            rdb_year.year_id,
            rdb_year.year_name,
            rdb_department.department_id,
            rdb_department.department_nameTH,
            (
            SELECT
            COUNT(cpro.pro_id)
            FROM
            rdb_project cpro
            WHERE
            cpro.year_id = rdb_year.year_id
            AND cpro.data_show = 1
            AND cpro.ps_id <> 6  
            AND cpro.department_id = rdb_department.department_id
            AND cpro.pt_id IN ($impptid)
            ) AS cpro,
            (
            ifnull(
            (
            SELECT
            sum(d.pro_budget)
            FROM
            rdb_project d
            WHERE
            d.year_id = rdb_year.year_id
            AND d.data_show = 1
            AND d.ps_id <> 6 
            AND d.department_id = rdb_department.department_id
            AND d.pt_id IN ($impptid)
            ),
            0
            )
            ) AS sum_budget
            FROM
            rdb_year,
            rdb_department
            WHERE
            rdb_department.department_id IN ($impdep)
            ORDER BY
            rdb_department.department_nameTH ASC,
            rdb_year.year_name ASC";

        return $sql;
    }

    public static function getNewProjectData()
    {
        $count = RdbProject::where('data_show', 1)
            ->whereRaw('DATEDIFF(NOW(), created_at) <= 7')
            ->count();

        if ($count > 0) {
            return '<i class="bi bi-bell-fill text-danger newblink"></i>';
        } else {
            return false;
        }
    }
}
