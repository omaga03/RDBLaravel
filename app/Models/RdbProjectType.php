<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectType extends Model
{
    protected $table = 'rdb_project_types';
    protected $primaryKey = 'pt_id';
    public $timestamps = false;

    public function rdbProjects()
    {
        return $this->hasMany(RdbProject::class, 'pt_id', 'pt_id');
    }

    protected $fillable = [
        'year_id',
        'pt_name',
        'pt_for',
        'pt_created',
        'pt_type',
        'pt_utz',
        'pttg_id',
        'pt_note',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];

    public function year()
    {
        return $this->belongsTo(RdbYear::class, 'year_id', 'year_id');
    }

    public function projectTypeGroup()
    {
        return $this->belongsTo(RdbProjectTypesGroup::class, 'pttg_id', 'pttg_id');
    }

    public function projectTypeSubs()
    {
        return $this->hasMany(RdbProjectTypeSub::class, 'pt_id', 'pt_id');
    }

    public function canDelete()
    {
        return $this->rdbProjects()->count() === 0 && $this->projectTypeSubs()->count() === 0;
    }

    public static function getPtforlist()
    {
        return  [
            '1' => 'อาจารย์/พนักงานฯ สายวิชาการ',
            '2' => 'เจ้าหน้าที่/พนักงานฯ สายสนับสนุน',
            '3' => 'งบประมาณส่วนตัว'
        ];
    }

    public static function getCreatelist()
    {
        return  [
            '1' => 'สถาบันวิจัยและพัฒนา',
            '2' => 'คณะ/ศูนย์/สำนัก',
            '3' => 'งบประมาณส่วนตัว',
            '4' => 'แหล่งทุนอื่น'
        ];
    }

    public static function getProTyTeaChart($year_id, $type)
    {
        $query = self::query();

        if ($year_id) {
            $query->where('year_id', $year_id);
        }

        if ($type) {
            $query->where('pt_for', $type);
        }
        
        $ids = $query->pluck('pt_id')->toArray();

        // Ensure we don't return an empty array if possible, 
        // to prevent SQL syntax error in IN() clause downstream.
        // If empty, return [0] which matches nothing but is valid SQL IN (0).
        if (empty($ids)) {
            return [0];
        }

        return $ids;
    }
}
