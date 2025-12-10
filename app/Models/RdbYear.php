<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbYear extends Model
{
    protected $table = 'rdb_year';
    protected $primaryKey = 'year_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'year_name',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
