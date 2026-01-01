<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectPosition extends Model
{
    protected $table = 'rdb_project_position';
    protected $primaryKey = 'position_id';
    public $timestamps = false;

    public function projectWorks()
    {
        return $this->hasMany(RdbProjectWork::class, 'position_id', 'position_id');
    }

    public function canDelete()
    {
        return $this->projectWorks()->count() === 0;
    }

    protected $fillable = [
        'position_nameTH',
        'position_desc',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    const DIRECTOR = 1;
    const HEAD = 2;
}
