<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    // If this model stores docs, use helper; otherwise available for future use
    public function getTypeSubFilePath(string $newField = 'pts_path', string $oldField = 'pts_file', string $oldDir = 'uploads/project_types'): ?string
    {
        return $this->resolveDualFilePath($newField, $oldField, $oldDir);
    }
}
