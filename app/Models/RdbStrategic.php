<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbStrategic extends Model
{
    protected $table = 'rdb_strategic';
    protected $primaryKey = 'strategic_id';
    public $timestamps = true; // Changed to true

    public function year()
    {
        return $this->belongsTo(RdbYear::class, 'year_id', 'year_id');
    }

    public function projects()
    {
        return $this->hasMany(RdbProject::class, 'strategic_id', 'strategic_id');
    }

    public function canDelete()
    {
        return $this->projects()->count() === 0;
    }

    protected $fillable = [
        'year_id',
        'strategic_nameTH',
        'strategic_nameEN',
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
