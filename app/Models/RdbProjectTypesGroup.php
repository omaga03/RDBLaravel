<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectTypesGroup extends Model
{
    protected $table = 'rdb_project_types_group';
    protected $primaryKey = 'pttg_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'pttg_name',
        'pttg_group',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
