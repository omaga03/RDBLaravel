<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPublishedBranch extends Model
{
    protected $table = 'rdb_published_branch';
    protected $primaryKey = 'branch_id';
    public $timestamps = false;

    public function publications()
    {
        return $this->hasMany(RdbPublished::class, 'branch_id', 'branch_id');
    }

    public function canDelete()
    {
        return $this->publications()->count() === 0;
    }

    protected $fillable = [
        'branch_name',
        'user_created',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function updater() {
        return $this->belongsTo(User::class, 'user_updated');
    }
}
