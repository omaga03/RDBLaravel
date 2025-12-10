<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPublishedCheckyear extends Model
{
    protected $table = 'rdb_published_checkyear';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function year()
    {
        return $this->belongsTo(RdbYear::class, 'year_id', 'year_id');
    }

    protected $fillable = [
        'year_id',
        'rdbyearedu_start',
        'rdbyearedu_end',
        'rdbyearbud_start',
        'rdbyearbud_end',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
