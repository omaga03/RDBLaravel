<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPublishedBranch extends Model
{
    protected $table = 'rdb_published_branch';
    protected $primaryKey = 'branch_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'branch_name',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
