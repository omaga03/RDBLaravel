<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDepartmentCategory extends Model
{
    protected $table = 'rdb_department_category';
    protected $primaryKey = 'depcat_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'depcat_name',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    /**
     * Smart Accessors to prevent silent null on legacy Yii2 / Laravel naming conventions
     */
    public function getDepcatNameTHAttribute(): ?string
    {
        return $this->depcat_name;
    }

    public function getNameAttribute(): ?string
    {
        return $this->depcat_name;
    }
}
