<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDateeventType extends Model
{
    protected $table = 'rdb_dateevent_type';
    protected $primaryKey = 'evt_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'evt_name',
        'evt_color',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
