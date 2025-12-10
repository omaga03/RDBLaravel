<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journalotherpage extends Model
{
    protected $table = 'journalotherpage';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'article_id',
        'submission_file_id',
        'file_name',
        'setting_value',
        'authorsall',
        'issues_setting_value',
        'credated_at',
    ];
}
