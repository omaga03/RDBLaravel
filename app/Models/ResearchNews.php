<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchNews extends Model
{
    protected $table = 'research_news';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'news_id',
        'news_type',
        'news_name',
        'news_img',
        'news_date',
        'news_event_start',
        'news_event_end',
        'news_event_guarantee',
        'news_detail',
        'news_reference',
        'news_link',
        'news_count',
        'created_at',
        'updated_at',
    ];
}
