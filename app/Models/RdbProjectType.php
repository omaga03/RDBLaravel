<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectType extends Model
{
    protected $table = 'rdb_project_types';
    protected $primaryKey = 'pt_id';

    public function rdbProjects()
    {
        return $this->hasMany(RdbProject::class, 'pt_id', 'pt_id');
    }

    public static function getProTyTeaChart($y, $type)
    {
        // Simplified logic: Get all Project Types, optionally filtered by type
        // Legacy code logic was a bit convoluted with ViewYear, but essentially it wanted valid PT IDs.
        
        $query = RdbProjectType::query();

        switch ($type) {
            case '1':
                $query->where('pt_for', '1'); // Changed from pt_type to pt_for based on fillable
                break;
            case '2':
                // All
                break;
            case '3':
                // All (Legacy comment: same as 2 but with commented out filter)
                break;
        }

        $ids = $query->pluck('pt_id')->toArray();

        // Patch for missing legacy IDs (99, 100, 101) found in Year 2568
        // User confirmed counts match these specific types
        if ($type == '1') {
            $ids = array_merge($ids, [99, 100, 101]);
        }

        return $ids;
    }

    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'pt_name',
        'pt_for',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
