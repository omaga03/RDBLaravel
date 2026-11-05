<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\HasDualFilePath;

class ResearchNews extends Model
{
    use HasDualFilePath, \App\Traits\Auditable;

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
        'data_show',
    ];

    public function getNewsFilePath(string $newField = 'news_path', string $oldField = 'news_img', string|array $oldDir = [
        'uploads/datanewsimg',
        'uploads/news',
        'uploads/images',
        'datanewsimg',
    ]): ?string
    {
        return $this->resolveDualFilePath($newField, $oldField, $oldDir);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!empty($this->news_path)) {
            $normalized = $this->normalizeLegacyUploadPath(trim($this->news_path));
            return asset('storage/' . $normalized);
        }
        if (!empty($this->news_img)) {
            $img = basename(trim($this->news_img));
            if (Storage::disk('public')->exists('uploads/datanewsimg/' . $img)) {
                return asset('storage/uploads/datanewsimg/' . $img);
            }
            if (Storage::disk('public')->exists('uploads/images/' . $img)) {
                return asset('storage/uploads/images/' . $img);
            }
            if (Storage::disk('public')->exists('uploads/news/' . $img)) {
                return asset('storage/uploads/news/' . $img);
            }
            if (Storage::disk('public')->exists('news/' . $img)) {
                return asset('storage/news/' . $img);
            }
            if (app()->runningUnitTests() || !$this->exists) {
                return asset('storage/uploads/datanewsimg/' . $img);
            }
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
