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

    /**
     * Smart Accessors to prevent silent null on legacy Yii2 / Laravel naming conventions
     */
    public function getDiptNameTHAttribute(): ?string
    {
        return $this->dipt_name;
    }

    public function getNameAttribute(): ?string
    {
        return $this->dipt_name;
    }

    public function getTypeNameAttribute(): ?string
    {
        return $this->dipt_name;
    }
}
