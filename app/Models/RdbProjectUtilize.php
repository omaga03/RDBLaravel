<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasDualFilePath;

class RdbProjectUtilize extends Model
{
    use \App\Traits\HasDataShowScope, HasDualFilePath;

    protected $table = 'rdb_project_utilize';
    protected $primaryKey = 'utz_id';
    public $timestamps = true;

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function changwat()
    {
        return $this->belongsTo(RdbChangwat::class, 'chw_id', 'id');
    }

    public function utilizeType()
    {
        return $this->belongsTo(RdbProjectUtilizeType::class, 'utz_group', 'utz_type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_created', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'user_updated', 'id');
    }

    protected $fillable = [
        'pro_id',
        'utz_year_id',
        'utz_year_bud',
        'utz_year_edu',
        'utz_date',
        'utz_leading',
        'utz_leading_position',
        'utz_department_name',
        'utz_department_address',
        'chw_id',
        'utz_group',
        'utz_group_qa',
        'utz_detail',
        'utz_budget',
        'utz_count',
        'utz_countfile',
        'utz_files',
        'uf_safe_name',
        'uf_path',
        'uf_size',
        'uf_mime_type',
        'data_show',
        'user_created',
        'user_updated',
    ];

    use HasDualFilePath;

    public function getUtilizeFilePath(string $newField = 'uf_path', string $oldField = 'utz_files', string $oldDir = 'uploads/utilize'): ?string
    {
        return $this->resolveDualFilePath($newField, $oldField, $oldDir);
    }
}
