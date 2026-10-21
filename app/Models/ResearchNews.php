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
        if (!empty($this->news_detail)) {
            $decoded = html_entity_decode((string) $this->news_detail, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/<img\b[^>]*?\bsrc\s*=\s*[\'"]+(https?:\/\/[^\'">\s]+)[\'"]/isu', $decoded, $m)) {
                return trim($m[1]);
            }
        }
        return null;
    }

    public function getFormattedDetailAttribute(): string
    {
        return \App\Helpers\HtmlContentHelper::formatNews($this->news_detail, $this->image_url);
    }

    public function getPlainSummaryAttribute(): string
    {
        return \App\Helpers\HtmlContentHelper::plainSummaryNews($this->news_detail, 180);
    }
}
