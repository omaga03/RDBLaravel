<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectStatus extends Model
{
    protected $table = 'rdb_project_status';
    protected $primaryKey = 'ps_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'ps_icon',
        'ps_name',
        'ps_color',
        'ps_rank',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    const STATUS_CANCELLED = 6;
}
