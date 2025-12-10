<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbPublishedWork extends Model
{
    protected $table = 'rdb_published_work';
    protected $primaryKey = 'published_id';
    public $timestamps = false;

    public function published()
    {
        return $this->belongsTo(RdbPublished::class, 'published_id', 'published_id');
    }

    public function researcher()
    {
        return $this->belongsTo(RdbResearcher::class, 'researcher_id', 'researcher_id');
    }

    public function publishedTypeAuthor()
    {
        return $this->belongsTo(RdbPublishedTypeAuthor::class, 'pubta_id', 'pubta_id');
    }

    public function authorType()
    {
        return $this->belongsTo(RdbPublishedTypeAuthor::class, 'pubta_id', 'pubta_id');
    }

    protected $fillable = [
        'researcher_id',
        'pubta_id',
        'pubw_main',
        'pubw_bud',
        'user_created',
        'user_updated',
        'created_at',
        'updated_at',
    ];
}
