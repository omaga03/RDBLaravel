<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDipType extends Model
{
    protected $table = 'rdb_dip_type';
    protected $primaryKey = 'dipt_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'dipt_name',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
