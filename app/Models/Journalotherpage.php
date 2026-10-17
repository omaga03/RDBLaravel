<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasDualFilePath;

class Journalotherpage extends Model
{
    use HasDualFilePath;

    protected $table = 'journalotherpage';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'article_id',
        'submission_file_id',
        'file_name',
        'setting_value',
        'authorsall',
        'issues_setting_value',
        'credated_at',
    ];

    public function getOtherPageFilePath(string $newField = 'jop_path', string $oldField = 'jop_file', string $oldDir = 'uploads/journal_pages'): ?string
    {
        return $this->resolveDualFilePath($newField, $oldField, $oldDir);
    }
}
