<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectTypesGroup extends Model
{
    protected $table = 'rdb_project_types_group';
    protected $primaryKey = 'pttg_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'pttg_name',
        'pttg_group',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    public function rdbProjectTypes()
    {
        return $this->hasMany(RdbProjectType::class, 'pttg_id', 'pttg_id');
    }

    public function canDelete()
    {
        return $this->rdbProjectTypes()->count() === 0;
    }

    public static function getGroupList()
    {
        return [
            '1' => 'งบประมาณภายใน/ภาครัฐ',
            '2' => 'งบประมาณภายนอก',
            '3' => 'งบประมาณส่วนตัว'
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_created', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'user_updated', 'id');
    }
}
