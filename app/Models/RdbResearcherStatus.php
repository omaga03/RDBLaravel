<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbResearcherStatus extends Model
{
    protected $table = 'rdb_researcher_status';
    protected $primaryKey = 'restatus_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'restatus_name',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
