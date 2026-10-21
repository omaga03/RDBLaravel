<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectTypeMaster extends Model
{
    protected $table = 'rdb_project_types_master';
    protected $primaryKey = 'master_pt_id';
    public $timestamps = true;

    protected $fillable = [
        'master_pt_name',
        'pttg_id',
        'order_index',
        'color_code',
    ];

    public function projectTypeGroup()
    {
        return $this->belongsTo(RdbProjectTypesGroup::class, 'pttg_id', 'pttg_id');
    }

    public function projectTypes()
    {
        return $this->hasMany(RdbProjectType::class, 'master_pt_id', 'master_pt_id');
    }

    public function projects()
    {
        return $this->hasManyThrough(
            RdbProject::class,
            RdbProjectType::class,
            'master_pt_id', // Foreign key on rdb_project_types table...
            'pt_id',        // Foreign key on rdb_project table...
            'master_pt_id', // Local key on rdb_project_types_master table...
            'pt_id'         // Local key on rdb_project_types table...
        );
    }
}
