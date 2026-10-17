<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPrefix extends Model
{
    protected $table = 'rdb_prefix';
    protected $primaryKey = 'prefix_id';
    public $timestamps = true; // Changed to true

    protected $fillable = [
        'prefix_nameTH',
        'prefix_abbreviationTH',
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

    public function researchers()
    {
        return $this->hasMany(RdbResearcher::class, 'prefix_id', 'prefix_id');
    }

    public function canDelete()
    {
        return $this->researchers()->doesntExist();
    }
}
