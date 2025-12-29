<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbDip extends Model
{
    use \App\Traits\HasDataShowScope;

    protected $table = 'rdb_dip';
    protected $primaryKey = 'dip_id';
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dip_request_date' => 'date',
        'dip_data2_dateend' => 'date',
    ];

    public function dipType()
    {
        return $this->belongsTo(RdbDipType::class, 'dipt_id', 'dipt_id');
    }

    public function project()
    {
        return $this->belongsTo(RdbProject::class, 'pro_id', 'pro_id');
    }

    public function researcher()
    {
        return $this->belongsTo(RdbResearcher::class, 'researcher_id', 'researcher_id');
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
        'dip_type',
        'dipt_id',
        'dip_files',
        'dip_startdate',
        'dip_enddate',
        'pro_id',
        'dip_request_number',
        'dip_request_date',
        'dip_request_dateget',
        'dip_number',
        'dip_publication_date',
        'dip_publication_no',
        'dip_patent_number',
        'dip_data1_datestart',
        'dip_data1_files',
        'dip_data2_patent',
        'researcher_id',
        'dip_data2_name',
        'dip_data2_agent',
        'dip_data2_status',
        'dip_data2_dateend',
        'dip_data2_conclusion',
        'dip_data2_files_con',
        'dip_data2_assertion',
        'dip_data2_tag',
        'dip_data3_allassertion',
        'dip_data3_filesass1',
        'dip_data3_filesass2',
        'dip_data3_filesass3',
        'dip_data3_drawing_picture',
        'dip_data_forms_request',
        'dip_url',
        'dip_note',
        'data_show',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
