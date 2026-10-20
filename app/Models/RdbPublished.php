<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\HasDualFilePath;
use App\Services\FileUploadService;

class RdbPublished extends Model
{
    use \App\Traits\HasDataShowScope, HasDualFilePath, \App\Traits\Auditable;

    protected $table = 'rdb_published';
    protected $primaryKey = 'id';
    public $timestamps = true; // Assuming no standard created_at/updated_at, change if needed

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
        'pf_safe_name',
        'pf_path',
        'pf_size',
        'pf_mime_type',
        'pub_score',
        'pub_budget',
        'pub_note',
        'pub_download',
        'data_show',
        'workflow_status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_remarks',
        'unlock_reason',
        'unlock_requested_at',
        'user_created',
        'user_updated',
        'todspace',
        'dspace_uuid',
        'deleted_at',
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

    /**
     * Get published file path (UUID first, then legacy fallback)
     */
    public function getPublishedFilePath(): ?string
    {
        if (!empty($this->pf_path)) {
            return $this->pf_path;
        }

        if (!empty($this->pub_file)) {
            if (Storage::disk('public')->exists('uploads/rdb_published/' . $this->pub_file) || file_exists(public_path('uploads/rdb_published/' . $this->pub_file)) || file_exists(public_path('storage/uploads/rdb_published/' . $this->pub_file))) {
                return 'uploads/rdb_published/' . $this->pub_file;
            }
            if (Storage::disk('public')->exists('uploads/published/' . $this->pub_file) || file_exists(public_path('uploads/published/' . $this->pub_file)) || file_exists(public_path('storage/uploads/published/' . $this->pub_file))) {
                return 'uploads/published/' . $this->pub_file;
            }
            return 'uploads/rdb_published/' . $this->pub_file;
        }

        return null;
    }

    /**
     * Check file integrity
     */
    public function checkFileIntegrity(): array
    {
        return FileUploadService::checkFileIntegrity(
            $this->pf_path ?? '',
            $this->pub_file ?? '',
            'uploads/published'
        );
    }

    public function usingNewPath(): bool
    {
        return !empty($this->pf_path);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'user_updated');
    }
}
