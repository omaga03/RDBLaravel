<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectDownload extends Model
{
    protected $table = 'rdb_project_download';
    protected $primaryKey = 'pjdo_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'pro_id',
        'researcher_id',
        'uti_id',
        'pjdo_date',
        'pjdo_ip',
    ];
}
