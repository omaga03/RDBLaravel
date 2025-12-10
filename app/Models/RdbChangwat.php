<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbChangwat extends Model
{
    protected $table = 'rdb_changwat';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'ta_id',
        'tambon_t',
        'tambon_e',
        'am_id',
        'amphoe_t',
        'amphoe_e',
        'ch_id',
        'changwat_t',
        'changwat_e',
        'lat',
        'long',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
