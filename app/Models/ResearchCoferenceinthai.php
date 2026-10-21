<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchCoferenceinthai extends Model
{
    protected $table = 'research_coferenceinthai';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

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
        'created_at',
        'updated_at',
    ];

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
