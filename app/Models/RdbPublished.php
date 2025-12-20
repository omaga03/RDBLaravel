<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPublished extends Model
{
    use \App\Traits\HasDataShowScope;

    protected $table = 'rdb_published';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'year_id',
        'year_edu',
        'year_bud',
        'branch_id',
        'pubtype_id',
        'pub_name',
        'pro_id',
        'researcher_id',
        'department_id',
        'depcou_id',
        'major_id',
        'pub_abstract',
        'pub_keyword',
        'pub_name_journal',
        'pub_date',
        'pub_date_end',
        'pub_file',
        'pub_score',
        'pub_budget',
        'pub_note',
        'pub_download',
        'data_show',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
    public function year()
    {
        return $this->belongsTo(RdbYear::class, 'year_id', 'year_id');
    }

    public function researcher()
    {
        return $this->belongsTo(RdbResearcher::class, 'researcher_id', 'researcher_id');
    }

    public function department()
    {
        return $this->belongsTo(RdbDepartment::class, 'department_id', 'department_id');
    }

    public function pubtype()
    {
        return $this->belongsTo(RdbPublishedType::class, 'pubtype_id', 'pubtype_id');
    }

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function branch()
    {
        return $this->belongsTo(RdbPublishedBranch::class, 'branch_id', 'branch_id');
    }

    public function authors()
    {
        return $this->belongsToMany(RdbResearcher::class, 'rdb_published_work', 'published_id', 'researcher_id')
                    ->withPivot('pubta_id', 'pubw_main', 'pubw_bud')
                    ->orderByPivot('pubw_bud', 'desc')
                    ->orderByPivot('pubw_main', 'desc')
                    ->orderByPivot('pubta_id', 'asc');
    }
}
