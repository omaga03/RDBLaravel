<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasDualFilePath;

class ResearchConferenceInThai extends Model
{
    use HasDualFilePath;

    protected $table = 'research_coferenceinthai';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'con_id',
        'con_name',
        'con_detail',
        'con_even_date',
        'con_sub_deadline',
        'con_venue',
        'con_website',
        'con_img',
        'con_site_img',
        'con_count',
        'file_path',
        'file_size',
        'file_mime_type',
    ];

    public function getConferenceFilePath(string $newField = 'file_path', string $oldField = 'file_name', string $oldDir = 'uploads/conferences'): ?string
    {
        return $this->resolveDualFilePath($newField, $oldField, $oldDir);
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->con_path ?: $this->file_path;
        if (!empty($path)) {
            return asset('storage/' . $path);
        }
        if (!empty($this->con_img)) {
            return asset('storage/uploads/conference/' . $this->con_img);
        }
        return null;
    }

    public function getFormattedDetailAttribute(): string
    {
        return \App\Helpers\HtmlContentHelper::formatConference($this->con_detail);
    }

    public function getPlainSummaryAttribute(): string
    {
        return \App\Helpers\HtmlContentHelper::plainSummaryConference($this->con_detail, 180);
    }
}
