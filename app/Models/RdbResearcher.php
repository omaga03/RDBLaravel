<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbResearcher extends Model
{
    protected $table = 'rdb_researcher';
    protected $primaryKey = 'researcher_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'researcher_codeid',
        'tea_code',
        'department_id',
        'depcat_id',
        'depcou_id',
        'maj_id',
        'researcher_gender',
        'prefix_id',
        'researcher_fname',
        'researcher_lname',
        'researcher_fnameEN',
        'researcher_lnameEN',
        'restatus_id',
        'researcher_birthdate',
        'researcher_address',
        'researcher_workaddress',
        'researcher_email',
        'researcher_tel',
        'researcher_mobile',
        'researcher_fax',
        'researcher_picture',
        'scopus_authorId',
        'orcid',
        'researcher_note',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
    public function prefix()
    {
        return $this->belongsTo(RdbPrefix::class, 'prefix_id', 'prefix_id');
    }

    public function department()
    {
        return $this->belongsTo(RdbDepartment::class, 'department_id', 'department_id');
    }

    public function major()
    {
        return $this->belongsTo(RdbDepMajor::class, 'maj_id', 'maj_id');
    }

    public function departmentCategory()
    {
        return $this->belongsTo(RdbDepartmentCategory::class, 'depcat_id', 'depcat_id');
    }

    public function departmentCourse()
    {
        return $this->belongsTo(RdbDepartmentCourse::class, 'depcou_id', 'depcou_id');
    }

    public function status()
    {
        return $this->belongsTo(RdbResearcherStatus::class, 'restatus_id', 'restatus_id');
    }
    public function rdbResearcherEducations()
    {
        return $this->hasMany(RdbResearcherEducation::class, 'researcher_id', 'researcher_id');
    }

    public function rdbProjects()
    {
        return $this->belongsToMany(RdbProject::class, 'rdb_project_work', 'researcher_id', 'pro_id')
                    ->withPivot('position_id', 'ratio');
    }

    public function rdbPublisheds()
    {
        return $this->belongsToMany(
            RdbPublished::class,
            'rdb_published_work',
            'researcher_id',
            'published_id',
            'researcher_id',
            'id'
        )->withPivot('pubta_id', 'pubw_main', 'pubw_bud');
    }

    public function rdbDips()
    {
        return $this->hasMany(RdbDip::class, 'researcher_id', 'researcher_id');
    }
}
