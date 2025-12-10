<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPublishedTypeAuthor extends Model
{
    protected $table = 'rdb_published_type_author';
    protected $primaryKey = 'pubta_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'pubta_nameTH',
        'pubta_nameEN',
        'pubta_score',
        'created_at',
        'updated_at',
    ];
}
