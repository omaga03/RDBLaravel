<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPublishedType extends Model
{
    protected $table = 'rdb_published_type';
    protected $primaryKey = 'pubtype_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'pubtype_group',
        'pubtype_grouptype',
        'pubtype_subgroup',
        'pubtype_score',
        'created_at',
        'updated_at',
    ];
}
