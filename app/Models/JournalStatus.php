<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasDualFilePath;

class JournalStatus extends Model
{
    use \App\Traits\HasDataShowScope, HasDualFilePath;

    protected $table = 'journal_status';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'jou_name',
        'jou_respon',
        'jou_email',
        'jou_files',
        'jou_filesname',
        'jou_status',
        'jou_note',
        'data_show',
        'user_updated',
        'user_created',
        'created_at',
        'updated_at',
    ];

    public function getJournalStatusFilePath(string $newField = 'js_path', string $oldField = 'jou_files', string|array $oldDir = [
        'uploads/datajournalstatus/{id}',
        'uploads/datajournalstatus',
        'uploads/journals',
        'datajournalstatus/{id}',
    ]): ?string
    {
        $effectiveOldField = !empty($this->{$oldField}) ? $oldField : 'js_file';
        return $this->resolveDualFilePath($newField, $effectiveOldField, $oldDir);
    }
}
