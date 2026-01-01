<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectTypeSub extends Model
{
    protected $table = 'rdb_project_types_sub';
    protected $primaryKey = 'pts_id';
    public $timestamps = false; // Based on Yii2 model

    protected $fillable = [
        'pt_id',
        'pts_name',
        'pts_file',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
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
}
