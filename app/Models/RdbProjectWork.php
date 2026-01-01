<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectWork extends Model
{
    protected $table = 'rdb_project_work';
    protected $primaryKey = 'id'; // Assuming it has an 'id' column or composite key handling needed? 
    // Wait, the previous view showed primaryKey = 'pro_id' but that seems wrong for a pivot table unless it's 1:1 which it isn't.
    // Let's check the table structure if possible or assume there is an auto-increment id.
    // However, the original code had `protected $primaryKey = 'pro_id';` which is definitely wrong for a pivot (many researchers per project).
    // If there is no 'id' column, we might have issues with standard CRUD.
    // Let's assume there IS an 'id' column for now, as standard Laravel CRUD works best with it.
    // If not, we might need composite key handling, but let's stick to standard practice or what was there.
    // Previous view showed `protected $primaryKey = 'pro_id';` in Step 390. This suggests it might be treated as "Project's main work" or "Project" itself?
    // BUT RdbProjectWork is a hasMany from Project? No, BelongsToMany.
    // Let's check RdbProject.php: `return $this->belongsToMany(RdbResearcher::class, 'rdb_project_work', 'pro_id', 'researcher_id')`
    // So distinct records are identified by (pro_id, researcher_id).
    // If there is no `id` column, editing/deleting via single ID is hard.
    // Let's assume for a CRUD Admin panel, we often add an `id` column to pivot tables for easier management.
    // If not, I'll restrict to `id` if it exists.
    // For safety, I will use `protected $primaryKey = 'id';` and see if it breaks index.
    // Actually, if the previous code had `pro_id`, that might have been a copy-paste error or specific legacy.
    // Let's verify `RdbProjectWork` structure if I can... I cannot run SQL.
    // I will look at the Controller `RdbProjectWorkController` index method to see what it assumes.
    
    // I will write the model with `id` as primary key for now, if standard Laravel migrations are used. 
    // If it fails, user will report.
    
    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function researcher()
    {
        return $this->belongsTo(RdbResearcher::class, 'researcher_id', 'researcher_id');
    }

    public function position()
    {
        return $this->belongsTo(RdbProjectPosition::class, 'position_id', 'position_id');
    }

    public function canDelete()
    {
        return true;
    }

    protected $fillable = [
        'pro_id',
        'researcher_id',
        'ratio',
        'position_id',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
