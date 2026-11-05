<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\RdbResearcher;
use App\Models\ResearchNews;
use App\Models\ResearchConference;

class ConvertImagesToWebp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdb:convert-images-webp
                            {--module=all : Module to process (all, researcher, news, conference)}
                            {--force : Force reconvert even if already .webp}
                            {--limit= : Limit number of records to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert all images (Researchers, News, Conferences) to high-performance .webp format';

    public function handle(): int
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $module = strtolower($this->option('module') ?: 'all');
        $force = (bool) $this->option('force');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info("==================================================================");
        $this->info(" PCRU RDB - Universal System Images (.webp) Converter");
        $this->info(" Module: {$module} | Force: " . ($force ? 'YES' : 'NO') . " | Limit: " . ($limit ?: 'NONE'));
        $this->info("==================================================================");
        $this->newLine();

        if (in_array($module, ['all', 'researcher', 'researchers'])) {
            $this->processResearchers($force, $limit);
        }

        if (in_array($module, ['all', 'news'])) {
            $this->processNews($force, $limit);
        }

        if (in_array($module, ['all', 'conference', 'conferences'])) {
            $this->processConferences($force, $limit);
        }

        $this->newLine();
        $this->info("All processing completed successfully!");
        return Command::SUCCESS;
    }

    protected function processResearchers(bool $force, ?int $limit): void
    {
        $this->info("Processing Module: RdbResearcher (Profile Pictures)...");

        $query = RdbResearcher::withoutGlobalScopes()
            ->where(function ($q) {
                $q->whereNotNull('researcher_picture')->where('researcher_picture', '!=', '')
                  ->orWhereNotNull('rf_path')->where('rf_path', '!=', '');
            });

        if (!$force) {
            $query->where(function ($q) {
                $q->where('researcher_picture', 'not like', '%.webp')
                  ->orWhereNull('researcher_picture');
            });
        }

        if ($limit) {
            $query->limit($limit);
        }

        $items = $query->get();
        $total = $items->count();

        if ($total === 0) {
            $this->info("No researcher records found to convert.");
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $converted = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($items as $item) {
            $sourcePath = $this->resolveResearcherDiskPath($item);

            if (!$sourcePath || !file_exists($sourcePath)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $currentExt = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
            if ($currentExt === 'webp' && !$force) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $dir = dirname($sourcePath);
            $baseName = pathinfo($sourcePath, PATHINFO_FILENAME);
            $newFileName = $baseName . '.webp';
            $targetPath = $dir . DIRECTORY_SEPARATOR . $newFileName;

            $success = $this->convertImageToWebp($sourcePath, $targetPath);

            if ($success && file_exists($targetPath)) {
                $relativeDir = $this->getRelativeStorageDir($dir);
                $newRelativePath = $relativeDir ? ($relativeDir . '/' . $newFileName) : $newFileName;

                $item->researcher_picture = $newFileName;
                $item->rf_safe_name = $newFileName;
                $item->rf_path = $newRelativePath;
                $item->rf_mime_type = 'image/webp';
                $item->rf_size = filesize($targetPath);
                $item->saveQuietly();

                $converted++;
            } else {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("RdbResearcher summary: Converted: {$converted} | Skipped: {$skipped} | Failed: {$failed}");
        $this->newLine();
    }

    protected function processNews(bool $force, ?int $limit): void
    {
        $this->info("Processing Module: ResearchNews...");

        $query = ResearchNews::withoutGlobalScopes()
            ->where(function ($q) {
                $q->whereNotNull('news_img')->where('news_img', '!=', '')
                  ->orWhereNotNull('news_path')->where('news_path', '!=', '');
            });

        if (!$force) {
            $query->where(function ($q) {
                $q->where('news_img', 'not like', '%.webp')
                  ->orWhereNull('news_img');
            });
        }

        if ($limit) {
            $query->limit($limit);
        }

        $items = $query->get();
        $total = $items->count();

        if ($total === 0) {
            $this->info("No news records found to convert.");
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $converted = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($items as $item) {
            $sourcePath = $this->resolveNewsDiskPath($item);

            if (!$sourcePath || !file_exists($sourcePath)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $currentExt = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
            if ($currentExt === 'webp' && !$force) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $dir = dirname($sourcePath);
            $baseName = pathinfo($sourcePath, PATHINFO_FILENAME);
            $newFileName = $baseName . '.webp';
            $targetPath = $dir . DIRECTORY_SEPARATOR . $newFileName;

            $success = $this->convertImageToWebp($sourcePath, $targetPath);

            if ($success && file_exists($targetPath)) {
                $relativeDir = $this->getRelativeStorageDir($dir);
                $newRelativePath = $relativeDir ? ($relativeDir . '/' . $newFileName) : $newFileName;

                $item->news_img = $newFileName;
                $item->news_safe_name = $newFileName;
                $item->news_path = $newRelativePath;
                $item->news_mime_type = 'image/webp';
                $item->news_size = filesize($targetPath);
                $item->saveQuietly();

                $converted++;
            } else {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("ResearchNews summary: Converted: {$converted} | Skipped: {$skipped} | Failed: {$failed}");
        $this->newLine();
    }

    protected function processConferences(bool $force, ?int $limit): void
    {
        $this->info("Processing Module: ResearchConference...");

        $query = ResearchConference::withoutGlobalScopes()
            ->where(function ($q) {
                $q->whereNotNull('con_img')->where('con_img', '!=', '')
                  ->orWhereNotNull('con_path')->where('con_path', '!=', '');
            });

        if (!$force) {
            $query->where(function ($q) {
                $q->where('con_img', 'not like', '%.webp')
                  ->orWhereNull('con_img');
            });
        }

        if ($limit) {
            $query->limit($limit);
        }

        $items = $query->get();
        $total = $items->count();

        if ($total === 0) {
            $this->info("No conference records found to convert.");
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $converted = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($items as $item) {
            $sourcePath = $this->resolveConferenceDiskPath($item);

            if (!$sourcePath || !file_exists($sourcePath)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $currentExt = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
            if ($currentExt === 'webp' && !$force) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $dir = dirname($sourcePath);
            $baseName = pathinfo($sourcePath, PATHINFO_FILENAME);
            $newFileName = $baseName . '.webp';
            $targetPath = $dir . DIRECTORY_SEPARATOR . $newFileName;

            $success = $this->convertImageToWebp($sourcePath, $targetPath);

            if ($success && file_exists($targetPath)) {
                $relativeDir = $this->getRelativeStorageDir($dir);
                $newRelativePath = $relativeDir ? ($relativeDir . '/' . $newFileName) : $newFileName;

                $item->con_img = $newFileName;
                $item->con_safe_name = $newFileName;
                $item->con_path = $newRelativePath;
                $item->con_mime_type = 'image/webp';
                $item->con_size = filesize($targetPath);
                $item->saveQuietly();

                $converted++;
            } else {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("ResearchConference summary: Converted: {$converted} | Skipped: {$skipped} | Failed: {$failed}");
        $this->newLine();
    }

    public function convertImageToWebp(string $sourcePath, string $targetPath, int $maxDimension = 2048, int $quality = 85): bool
    {
        try {
            if (!extension_loaded('gd') || !function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
                return false;
            }

            $raw = @file_get_contents($sourcePath);
            if (!$raw) {
                return false;
            }

            $im = @imagecreatefromstring($raw);
            if (!$im) {
                return false;
            }

            imagepalettetotruecolor($im);
            imagealphablending($im, true);
            imagesavealpha($im, true);

            $width = imagesx($im);
            $height = imagesy($im);

            if ($width > $maxDimension || $height > $maxDimension) {
                $ratio = $maxDimension / max($width, $height);
                $newW = (int) round($width * $ratio);
                $newH = (int) round($height * $ratio);

                $target = imagecreatetruecolor($newW, $newH);
                imagealphablending($target, false);
                imagesavealpha($target, true);

                $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
                imagefilledrectangle($target, 0, 0, $newW, $newH, $transparent);

                imagecopyresampled($target, $im, 0, 0, 0, 0, $newW, $newH, $width, $height);
                imagedestroy($im);
                $im = $target;
            }

            $saved = @imagewebp($im, $targetPath, $quality);
            imagedestroy($im);

            return (bool) $saved;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function resolveResearcherDiskPath(RdbResearcher $item): ?string
    {
        $candidates = [];

        if (!empty($item->rf_path)) {
            $candidates[] = storage_path('app/public/' . ltrim($item->rf_path, '/\\'));
        }

        if (!empty($item->researcher_picture)) {
            $img = basename(trim($item->researcher_picture));
            $candidates[] = storage_path('app/public/uploads/dataresearcher/' . $img);
            $candidates[] = storage_path('app/public/researchers/' . $img);
            $candidates[] = storage_path('app/public/uploads/researchers/' . $img);
        }

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function resolveNewsDiskPath(ResearchNews $item): ?string
    {
        $candidates = [];

        if (!empty($item->news_path)) {
            $candidates[] = storage_path('app/public/' . ltrim($item->news_path, '/\\'));
        }

        if (!empty($item->news_img)) {
            $img = basename(trim($item->news_img));
            $candidates[] = storage_path('app/public/uploads/datanewsimg/' . $img);
            $candidates[] = storage_path('app/public/uploads/news/' . $img);
            $candidates[] = storage_path('app/public/news/' . $img);
            $candidates[] = storage_path('app/public/uploads/images/' . $img);
        }

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function resolveConferenceDiskPath(ResearchConference $item): ?string
    {
        $candidates = [];

        $path = $item->con_path ?: $item->file_path;
        if (!empty($path)) {
            $candidates[] = storage_path('app/public/' . ltrim($path, '/\\'));
        }

        if (!empty($item->con_img)) {
            $img = basename(trim($item->con_img));
            $candidates[] = storage_path('app/public/uploads/datacoferenceinthai/' . $img);
            $candidates[] = storage_path('app/public/uploads/conference/' . $img);
            $candidates[] = storage_path('app/public/conference/' . $img);
            $candidates[] = storage_path('app/public/uploads/images/' . $img);
        }

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function getRelativeStorageDir(string $absoluteDir): string
    {
        $baseStorage = realpath(storage_path('app/public'));
        $realDir = realpath($absoluteDir);

        if ($baseStorage && $realDir && str_starts_with($realDir, $baseStorage)) {
            $rel = substr($realDir, strlen($baseStorage));
            return trim(str_replace('\\', '/', $rel), '/');
        }

        return '';
    }
}
