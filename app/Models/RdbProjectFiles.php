<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\HasDualFilePath;
use App\Services\FileUploadService;

class RdbProjectFiles extends Model
{
    use HasDualFilePath;

    protected $table = 'rdb_project_files';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function canDelete()
    {
        // Files is a leaf, can be deleted unconditionally or if needed
        return true;
    }

    /**
     * ✅ กำหนด fillable เฉพาะฟิลด์ที่ยอมให้แก้ไข
     * ไม่รวม: id, pro_id, user_created, user_updated
     */
    protected $fillable = [
        'pro_id',
        'rf_filesname',
        'rf_files',
        'prf_safe_name',
        'prf_path',
        'prf_size',
        'prf_mime_type',
        'rf_note',
        'rf_download',
        'rf_files_show',
        'user_created',
        'user_updated',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function updater() {
        return $this->belongsTo(User::class, 'user_updated');
    }

    /**
     * Resolve the actual file path for this project file.
     */
    public function getFilePath(): ?string
    {
        return $this->resolveDualFilePath('prf_path', 'rf_files', [
            'uploads/project_files/{pro_id}',
            'uploads/project_files',
            'project_files/{pro_id}',
            'project_files',
            'uploads/projects',
            'projects',
        ]);
    }

    /**
     * Check if the file is stored using the new UUID path.
     */
    public function usingNewPath(): bool
    {
        return $this->usingNewPath('prf_path');
    }}
