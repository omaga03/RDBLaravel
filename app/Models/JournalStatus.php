<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalStatus extends Model
{
    use \App\Traits\HasDataShowScope;

    protected $table = 'journal_status';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'jou_name',
        'jou_respon',
        'jou_email',
        'jou_files',
        'jou_filesname',
        'jou_status',
        'jou_note',
        'data_show',
        'user_updated',
        'user_created',
        'created_at',
        'updated_at',
    ];
}
