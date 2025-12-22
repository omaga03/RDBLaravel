<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbGroupproject extends Model
{
    protected $table = 'rdb_groupproject';
    protected $primaryKey = 'pgroup_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'pgroup_nameTH',
        'pgroup_nameEN',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    // Group Type Constants
    const GROUP_PROJECT = 1;  // โครงการชุด
}
