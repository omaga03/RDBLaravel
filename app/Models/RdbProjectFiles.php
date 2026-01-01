<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectFiles extends Model
{
    protected $table = 'rdb_project_files';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function canDelete()
    {
        // Files is a leaf, can be deleted unconditionally or if needed
        return true;
    }

    protected $fillable = [
        'pro_id',
        'rf_files',
        'rf_filesname',
        'rf_download',
        'rf_note',
        'rf_files_show',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
