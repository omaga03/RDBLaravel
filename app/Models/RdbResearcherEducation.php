<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbResearcherEducation extends Model
{
    protected $table = 'rdb_researcher_education';
    protected $primaryKey = 'reedu_id';
    public $timestamps = false;

    public function researcher()
    {
        return $this->belongsTo(RdbResearcher::class, 'researcher_id', 'researcher_id');
    }

    protected $fillable = [
        'researcher_id',
        'reedu_status',
        'reedu_year',
        'reedu_type',
        'reedu_name',
        'reedu_department',
        'reedu_major',
        'reedu_cational',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
