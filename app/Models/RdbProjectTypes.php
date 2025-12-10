<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectTypes extends Model
{
    protected $table = 'rdb_project_types';
    protected $primaryKey = 'pt_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'year_id',
        'pt_name',
        'pt_for',
        'pt_created',
        'pt_type',
        'pt_utz',
        'pt_note',
        'pttg_id',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
