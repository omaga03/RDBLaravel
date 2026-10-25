<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Storage;

trait HasDualFilePath
{
	/**
	 * Resolve file path: prefer newField (stored path), fallback to oldField under oldDir (or multiple candidates).
	 *
	 * @param string $newField  e.g. 'pro_file_path' (stored path like 'reports/uuid.pdf')
	 * @param string $oldField  e.g. 'pro_file' (legacy filename stored, e.g. 'oldname.pdf')
	 * @param string|array $oldDir e.g. 'uploads/projects' or ['uploads/project_files/{pro_id}', 'uploads/projects']
	 * @return string|null      relative storage path or null
	 */
	public function resolveDualFilePath(string $newField, string $oldField, string|array $oldDir = []): ?string
	{
        // 1. Prefer new path if present
        if (!empty($this->{$newField})) {
            return $this->{$newField};
        }

        // 2. Fallback to legacy filename under candidate directories
        if (!empty($this->{$oldField})) {
            $filename = basename(trim($this->{$oldField}));
            if (empty($filename)) {
                return null;
            }

            $dirs = is_array($oldDir) ? $oldDir : [$oldDir];
            foreach ($dirs as $dir) {
                if (empty($dir)) {
                    continue;
                }
                // Support {id}, {pro_id}, {utz_id}, {dip_id} placeholders
                $resolvedDir = str_replace(
                    ['{id}', '{pro_id}', '{utz_id}', '{dip_id}'],
                    [
                        $this->getKey() ?? '',
                        $this->pro_id ?? $this->getKey() ?? '',
                        $this->utz_id ?? $this->getKey() ?? '',
                        $this->dip_id ?? $this->getKey() ?? '',
                    ],
                    $dir
                );

                $oldPath = rtrim($resolvedDir, '/') . '/' . $filename;
                if (Storage::disk('public')->exists($oldPath) || file_exists(public_path('storage/' . $oldPath)) || file_exists(public_path($oldPath))) {
                    return $oldPath;
                }
            }
        }

		return null;
	}

	/**
	 * Convenience: check whether the new-field is present.
	 */
	public function usingNewPath(string $newField): bool
	{
		return !empty($this->{$newField});
	}
}