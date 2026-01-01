<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbYear extends Model
{
    protected $table = 'rdb_year';
    protected $primaryKey = 'year_id';
    public $timestamps = false;

    public function projects()
    {
        return $this->hasMany(RdbProject::class, 'year_id', 'year_id');
    }

    public function canDelete()
    {
        return $this->projects()->count() === 0;
    }

    protected $fillable = [
        'year_name',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
