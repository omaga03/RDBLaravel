<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbGroupproject extends Model
{
    protected $table = 'rdb_groupproject';
    protected $primaryKey = 'pgroup_id';
    public $timestamps = true;

    public function projects()
    {
        return $this->hasMany(RdbProject::class, 'pgroup_id', 'pgroup_id');
    }

    public function canDelete()
    {
        return $this->projects()->count() === 0;
    }

    protected $fillable = [
        'pgroup_nameTH',
        'pgroup_nameEN',
        'user_created',
        'user_updated',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'user_updated');
    }

    const GROUP_PROJECT = 1;
}
