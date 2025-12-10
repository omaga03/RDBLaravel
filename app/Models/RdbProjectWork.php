<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectWork extends Model
{
    protected $table = 'rdb_project_work';
    protected $primaryKey = 'pro_id';
    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function researcher()
    {
        return $this->belongsTo(RdbResearcher::class, 'researcher_id', 'researcher_id');
    }

    public function position()
    {
        return $this->belongsTo(RdbProjectPosition::class, 'position_id', 'position_id');
    }

    protected $fillable = [
        'researcher_id',
        'ratio',
        'position_id',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
