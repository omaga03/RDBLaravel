<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDepartment extends Model
{
    protected $table = 'rdb_department';
    protected $primaryKey = 'department_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    public function rdbResearchers()
    {
        return $this->hasMany(RdbResearcher::class, 'department_id', 'department_id');
    }

    public function depMajors()
    {
        return $this->hasMany(RdbDepMajor::class, 'department_id', 'department_id');
    }

    public function departmentType()
    {
        return $this->belongsTo(RdbDepartmentType::class, 'tdepartment_id', 'tdepartment_id');
    }

    // Consolidating relationships and logic below

    public static function getProDepTea($type)
    {
        $query = RdbDepartment::query();

        switch ($type) {
            case '1':
                $query->where('tdepartment_id', 1);
                break;
            case '2':
                // All
                break;
        }

        $ids = $query->pluck('department_id')->toArray();

        // Patch: Include Office of President (1) and University (12) in Type 1 to match total count
        if ($type == '1') {
            $ids = array_merge($ids, [1, 12]);
            $ids = array_unique($ids); // Prevent duplicates if they were already included
        }

        return $ids;
    }

    public function researchers()
    {
        return $this->hasMany(RdbResearcher::class, 'department_id');
    }

    public function projects()
    {
        return $this->hasMany(RdbProject::class, 'department_id', 'department_id');
    }

    public function canDelete()
    {
        return $this->researchers()->doesntExist() && 
               $this->projects()->doesntExist() && 
               $this->depMajors()->doesntExist();
    }

    protected $fillable = [
        'tdepartment_id',
        'department_code',
        'department_nameTH',
        'department_nameEN',
        'department_color',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function updater() {
        return $this->belongsTo(User::class, 'user_updated');
    }
}
