<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Storage;

trait HasDualFilePath
{
	/**
	 * Normalize legacy upload folder prefixes to the canonical 9 legacy folder names.
	 */
	public function normalizeLegacyUploadPath(string $path): string
	{
		$map = [
			'uploads/project_files/' => 'uploads/datafiles/',
			'uploads/dips/'          => 'uploads/datadip/',
			'uploads/conference/'    => 'uploads/datacoferenceinthai/',
			'uploads/conferences/'   => 'uploads/datacoferenceinthai/',
			'uploads/news/'          => 'uploads/datanewsimg/',
			'uploads/journals/'      => 'uploads/datajournalstatus/',
		];

		foreach ($map as $oldPrefix => $newPrefix) {
			if (str_starts_with($path, $oldPrefix)) {
				$remapped = $newPrefix . substr($path, strlen($oldPrefix));
				if (Storage::disk('public')->exists($remapped) || file_exists(public_path('storage/' . $remapped)) || file_exists(public_path($remapped)) || !Storage::disk('public')->exists($path)) {
					return $remapped;
				}
			}
		}

		return $path;
	}

	/**
	 * Resolve file path: prefer newField (stored path), fallback to oldField under oldDir (or multiple candidates).
	 *
	 * @param string $newField  e.g. 'pro_file_path' (stored path like 'reports/uuid.pdf')
	 * @param string $oldField  e.g. 'pro_file' (legacy filename stored, e.g. 'oldname.pdf')
	 * @param string|array $oldDir e.g. 'uploads/datafiles/{pro_id}' or ['uploads/datafiles/{pro_id}', 'uploads/projects']
	 * @return string|null      relative storage path or null
	 */
	public function resolveDualFilePath(string $newField, string $oldField, string|array $oldDir = []): ?string
	{
        // 1. Prefer new path if present and valid (with automatic legacy folder prefix normalization)
        if (!empty($this->{$newField})) {
            $normalizedNew = $this->normalizeLegacyUploadPath(trim($this->{$newField}));
            if (Storage::disk('public')->exists($normalizedNew) || file_exists(public_path('storage/' . $normalizedNew)) || file_exists(public_path($normalizedNew))) {
                return $normalizedNew;
            }
            // If oldField is empty, return normalizedNew directly (supports unit tests and new UUID paths)
            if (empty($this->{$oldField})) {
                return $normalizedNew;
            }
        }

        // 2. Fallback to legacy filename under candidate directories
        if (!empty($this->{$oldField})) {
            $filename = basename(trim($this->{$oldField}));
            if (empty($filename)) {
                return !empty($this->{$newField}) ? $this->normalizeLegacyUploadPath(trim($this->{$newField})) : null;
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

        if (!empty($this->{$newField})) {
            return $this->normalizeLegacyUploadPath(trim($this->{$newField}));
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