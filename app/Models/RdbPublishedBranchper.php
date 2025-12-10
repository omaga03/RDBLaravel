<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPublishedBranchper extends Model
{
    protected $table = 'rdb_published_branchper';
    protected $primaryKey = 'branchper_id';
    public $timestamps = false;

    public function year()
    {
        return $this->belongsTo(RdbYear::class, 'year_id', 'year_id');
    }

    public function branch()
    {
        return $this->belongsTo(RdbPublishedBranch::class, 'branch_id', 'branch_id');
    }

    protected $fillable = [
        'year_id',
        'branch_id',
        'branchper_percent',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
