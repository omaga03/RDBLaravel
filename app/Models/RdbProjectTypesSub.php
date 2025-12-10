<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectTypesSub extends Model
{
    protected $table = 'rdb_project_types_sub';
    protected $primaryKey = 'pts_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'pt_id',
        'pts_name',
        'pts_file',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
