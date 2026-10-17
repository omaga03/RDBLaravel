<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasDualFilePath;
use App\Services\FileUploadService;

class RdbResearcher extends Model
{
    use HasDualFilePath;

    protected $table = 'rdb_researcher';
    protected $primaryKey = 'researcher_id';
    public $timestamps = true;

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
        'rf_safe_name',
        'rf_path',
        'rf_size',
        'rf_mime_type',
        'scopus_authorId',
        'researcher_hindex',
        'scopus_synced_at',
        'orcid',
        'researcher_note',
        'user_created',
        'user_updated',
    ];

    /**
     * Get researcher picture/profile file path
     */
    public function getProfileFilePath(): ?string
    {
        if (!empty($this->rf_path)) {
            return $this->rf_path;
        }

        if (!empty($this->researcher_picture)) {
            return 'uploads/researchers/' . $this->researcher_picture;
        }

        return null;
    }

    /**
     * Check profile file integrity
     */
    public function checkProfileFileIntegrity(): array
    {
        return FileUploadService::checkFileIntegrity(
            $this->rf_path ?? '',
            $this->researcher_picture ?? '',
            'uploads/researchers'
        );
    }

    public function usingNewProfilePath(): bool
    {
        return !empty($this->rf_path);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function updater() {
        return $this->belongsTo(User::class, 'user_updated');
    }
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

    public function projects()
    {
        return $this->rdbProjects();
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

    public function canDelete()
    {
        return $this->rdbProjects()->doesntExist() && 
               $this->rdbPublisheds()->doesntExist() && 
               $this->rdbDips()->doesntExist() &&
               $this->rdbResearcherEducations()->doesntExist();
    }

    // Example helper for researcher file (e.g. CV/photo)
    public function getResearcherFilePath(string $newField = 'rf_path', string $oldField = 'rf_file', string $oldDir = 'uploads/researchers'): ?string
    {
        return $this->resolveDualFilePath($newField, $oldField, $oldDir);
    }
}
