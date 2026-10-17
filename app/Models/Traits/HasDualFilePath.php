<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Storage;

trait HasDualFilePath
{
	/**
	 * Resolve file path: prefer newField (stored path), fallback to oldField under oldDir.
	 *
	 * @param string $newField  e.g. 'pro_file_path' (stored path like 'reports/uuid.pdf')
	 * @param string $oldField  e.g. 'pro_file' (legacy filename stored, e.g. 'oldname.pdf')
	 * @param string $oldDir    e.g. 'uploads/projects'
	 * @return string|null      relative storage path or null
	 */
	public function resolveDualFilePath(string $newField, string $oldField, string $oldDir): ?string
	{
        // Prefer new path if present and exists
        if (!empty($this->{$newField})) {
            // Resolve to absolute path for direct file existence check
            $fullNewPath = Storage::disk('public')->path($this->{$newField});
            if (file_exists($fullNewPath)) {
                return $this->{$newField};
            }
            if (file_exists(public_path($this->{$newField}))) {
                return $this->{$newField};
            }
        }

        // Fallback to legacy filename under oldDir
        if (!empty($this->{$oldField})) {
            $oldPath = rtrim($oldDir, '/') . '/' . $this->{$oldField};
            // Resolve to absolute path for direct file existence check
            $fullOldPath = Storage::disk('public')->path($oldPath);
            if (file_exists($fullOldPath)) {
                return $oldPath;
            }
            if (file_exists(public_path($oldPath))) {
                return $oldPath;
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