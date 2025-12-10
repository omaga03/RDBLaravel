<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectUtilizeType extends Model
{
    protected $table = 'rdb_project_utilize_type';
    protected $primaryKey = 'utz_type_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'utz_typr_name',
        'utz_type_index',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
