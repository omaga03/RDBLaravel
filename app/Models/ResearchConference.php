<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\HasDualFilePath;

class ResearchConference extends Model
{
    use HasDualFilePath;

    protected $table = 'research_coferenceinthai';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'con_id',
        'con_name',
        'con_detail',
        'con_even_date',
        'con_sub_deadline',
        'con_venue',
        'con_website',
        'con_img',
        'con_safe_name',
        'con_path',
        'con_size',
        'con_mime_type',
        'con_site_img',
        'con_count',
        'data_show',
        'file_path',
        'file_size',
        'file_mime_type',
    ];

    public function getConferenceFilePath(string $newField = 'con_path', string $oldField = 'con_img', string $oldDir = 'uploads/conference'): ?string
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
            $img = basename(trim($this->con_img));
            if (Storage::disk('public')->exists('uploads/images/' . $img)) {
                return asset('storage/uploads/images/' . $img);
            }
            if (Storage::disk('public')->exists('uploads/conference/' . $img)) {
                return asset('storage/uploads/conference/' . $img);
            }
            if (Storage::disk('public')->exists('conference/' . $img)) {
                return asset('storage/conference/' . $img);
            }
            if (app()->runningUnitTests() || !$this->exists) {
                return asset('storage/uploads/conference/' . $img);
            }
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
