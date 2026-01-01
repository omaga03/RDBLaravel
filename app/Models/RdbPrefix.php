<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPrefix extends Model
{
    protected $table = 'rdb_prefix';
    protected $primaryKey = 'prefix_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'prefix_nameTH',
        'prefix_abbreviationTH',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    public function researchers()
    {
        return $this->hasMany(RdbResearcher::class, 'prefix_id', 'prefix_id');
    }

    public function canDelete()
    {
        return $this->researchers()->doesntExist();
    }
}
