<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasDualFilePath;

class ResearchNews extends Model
{
    use HasDualFilePath;

    protected $table = 'research_news';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'news_id',
        'news_type',
        'news_name',
        'news_img',
        'news_safe_name',
        'news_path',
        'news_size',
        'news_mime_type',
        'news_date',
        'news_event_start',
        'news_event_end',
        'news_event_guarantee',
        'news_detail',
        'news_reference',
        'news_link',
        'news_count',
    ];

    public function getNewsFilePath(string $newField = 'news_path', string $oldField = 'news_img', string $oldDir = 'uploads/news'): ?string
    {
        return $this->resolveDualFilePath($newField, $oldField, $oldDir);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!empty($this->news_path)) {
            return asset('storage/' . $this->news_path);
        }
        if (!empty($this->news_img)) {
            return asset('storage/uploads/news/' . $this->news_img);
        }
        return null;
    }
}
