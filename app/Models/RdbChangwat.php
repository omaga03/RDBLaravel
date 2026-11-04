<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbChangwat extends Model
{
    protected $table = 'rdb_changwat';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'ta_id',
        'tambon_t',
        'tambon_e',
        'am_id',
        'amphoe_t',
        'amphoe_e',
        'ch_id',
        'changwat_t',
        'changwat_e',
        'lat',
        'long',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    /**
     * Smart Accessors to prevent silent null on legacy Yii2 / Laravel naming conventions
     */
    public function getChangwatNameAttribute(): ?string
    {
        return $this->changwat_t;
    }

    public function getProvinceNameAttribute(): ?string
    {
        return $this->changwat_t;
    }

    public function getNameAttribute(): ?string
    {
        return $this->changwat_t;
    }

    public function getAmphoeNameAttribute(): ?string
    {
        return $this->amphoe_t;
    }

    public function getTambonNameAttribute(): ?string
    {
        return $this->tambon_t;
    }
}
