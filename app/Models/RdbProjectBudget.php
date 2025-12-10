<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectBudget extends Model
{
    protected $table = 'rdb_project_budget';
    protected $primaryKey = 'ckb_id';
    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    protected $fillable = [
        'pro_id',
        'ckb_annuity',
        'ckb_note',
        'ckb_status',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
