<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDateevent extends Model
{
    protected $table = 'rdb_dateevent';
    protected $primaryKey = 'ev_id';
    public $timestamps = false;

    public function eventType()
    {
        return $this->belongsTo(RdbDateeventType::class, 'evt_id', 'evt_id');
    }

    protected $fillable = [
        'evt_id',
        'ev_title',
        'ev_detail',
        'ev_datestart',
        'ev_timestart',
        'ev_dateend',
        'ev_timeend',
        'ev_url',
        'ev_status',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
