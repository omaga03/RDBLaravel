<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectUtilization extends Model
{
    protected $table = 'rdb_project_utilization';
    protected $primaryKey = 'uti_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'uti_nameTH',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
