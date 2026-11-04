<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbYear extends Model
{
    protected $table = 'rdb_year';
    protected $primaryKey = 'year_id';
    public $timestamps = true;

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
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'user_updated');
    }

    /**
     * Smart Accessors to prevent silent null on legacy Yii2 / Laravel naming conventions
     */
    public function getYearNameTHAttribute(): ?string
    {
        return $this->year_name;
    }

    public function getNameAttribute(): ?string
    {
        return $this->year_name;
    }
}
