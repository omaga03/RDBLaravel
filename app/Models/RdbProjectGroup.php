<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectGroup extends Model
{
    protected $table = 'rdb_project_group';
    protected $primaryKey = 'group_id';
    public $timestamps = false;

    protected $fillable = [
        'pro_id',
        'pgroup_id',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function groupProject()
    {
        return $this->belongsTo(RdbGroupProject::class, 'pgroup_id', 'pgroup_id');
    }
}
