<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDipType extends Model
{
    protected $table = 'rdb_dip_type';
    protected $primaryKey = 'dipt_id';
    public $timestamps = true; // Changed to true

    protected $fillable = [
        'dipt_name',
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
}
