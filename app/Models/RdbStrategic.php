<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbStrategic extends Model
{
    protected $table = 'rdb_strategic';
    protected $primaryKey = 'strategic_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'year_id',
        'strategic_nameTH',
        'strategic_nameEN',
        'created_at',
        'updated_at',
    ];
}
