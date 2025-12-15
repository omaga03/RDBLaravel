<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbNacc extends Model
{
    use \App\Traits\HasDataShowScope;

    protected $table = 'rdb_nacc';
    protected $primaryKey = 'nacc_id';
    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    protected $fillable = [
        'pro_id',
        'nacc_files',
        'nacc_note',
        'nacc_download',
        'data_show',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
