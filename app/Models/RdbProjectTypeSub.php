<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\HasDualFilePath;

class RdbProjectTypeSub extends Model
{
    use HasDualFilePath;

    protected $table = 'rdb_project_types_sub';
    protected $primaryKey = 'pts_id';
    public $timestamps = true; // Based on Yii2 model

    protected $fillable = [
        'pt_id',
        'pts_name',
        'pts_file',
        'pts_safe_name',
        'pts_path',
        'pts_size',
        'pts_mime_type',
        'user_created',
        'user_updated',
    ];

    public function rdbProjects()
    {
        return $this->hasMany(RdbProject::class, 'pts_id', 'pts_id');
    }

    public function projectType()
    {
        return $this->belongsTo(RdbProjectType::class, 'pt_id', 'pt_id');
    }

    public function canDelete()
    {
        return $this->rdbProjects()->count() === 0;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_created', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'user_updated', 'id');
    }

    /**
     * Get path ที่ถูกต้องสำหรับไฟล์ประเภทย่อยโครงการ
     */
    public function getTypeSubFilePath(): ?string
    {
        $file = $this->pts_safe_name ?: $this->pts_file;
        return $this->resolveDualFilePath('pts_path', !empty($this->pts_safe_name) ? 'pts_safe_name' : 'pts_file', [
            'uploads/datapts',
            'uploads/project_types_sub',
            'project_types_sub',
            'datapts',
        ]) ?? (!empty($file) ? 'uploads/project_types_sub/' . basename(trim($file)) : null);
    }
}
