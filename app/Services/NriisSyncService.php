<?php

namespace App\Services;

use App\Models\ResearchNews;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NriisSyncService
{
    private string $baseApiUrl = 'https://nriis.go.th/home/api/v1';
    private string $baseUrl = 'https://nriis.go.th/home';

    /**
     * Sync news from NRIIS
     *
     * @param int $limit Number of items to fetch
     * @param bool $force Re-sync already existing news
     * @param callable|null $progressCallback Optional callback for CLI output
     * @return array [synced => int, skipped => int, failed => int]
     */
    public function sync(int $limit = 50, bool $force = false, ?callable $progressCallback = null): array
    {
        $stats = ['synced' => 0, 'skipped' => 0, 'failed' => 0];

        try {
            $response = Http::timeout(20)->get("{$this->baseApiUrl}/general/news/?ngroup=&d=&page=1&limit={$limit}");
            if (!$response->successful()) {
                if ($progressCallback) {
                    $progressCallback("Error connecting to NRIIS API (HTTP {$response->status()})");
                }
                return $stats;
            }

            $listData = $response->json();
            $items = $listData['data'] ?? [];
        } catch (\Throwable $e) {
            if ($progressCallback) {
                $progressCallback("Exception connecting to NRIIS API: " . $e->getMessage());
            }
            return $stats;
        }

        foreach ($items as $item) {
            $nid = (string) ($item['NID'] ?? '');
            if (empty($nid)) {
                continue;
            }

            $existing = ResearchNews::where('news_id', $nid)->first();
            if ($existing && !$force) {
                $stats['skipped']++;
                if ($progressCallback) {
                    $progressCallback("ID: {$nid} - ซ้ำ (ข้ามไป)");
                }
                continue;
            }

            try {
                $detailResponse = Http::timeout(15)->get("{$this->baseApiUrl}/general/news/{$nid}");
                if (!$detailResponse->successful()) {
                    $stats['failed']++;
                    continue;
                }

                $news = $detailResponse->json();
                if (!$news || empty($news['NNAME'])) {
                    $stats['skipped']++;
                    continue;
                }

                $model = $existing ?: new ResearchNews();
                $model->news_id = $nid;
                $model->news_type = $news['NTNAME'] ?? 'ข่าวประชาสัมพันธ์';
                $model->news_name = trim($news['NNAME']);
                
                // Format news_date
                $rawDate = $news['createdateTH'] ?? null;
                $model->news_date = $rawDate ?: now()->format('Y-m-d');

                $model->news_reference = 'NRIIS : ระบบข้อมูลสารสนเทศวิจัยและนวัตกรรมแห่งชาติ';
                $model->news_link = "{$this->baseUrl}/news/{$nid}";

                // Clean iframe in details
                $rawDetail = $news['NDETAIL'] ?? '';
                $htmlDetail = $this->cleanIframeDetail($rawDetail);

                // Main PDF attachment
                if (!empty($news['NFILE_PDF'])) {
                    $mainFileUrl = 'https://nriis.go.th/FileUpload/AttatchFile/News/' . trim($news['NFILE_PDF']);
                    $htmlDetail .= '<div class="alert alert-info mt-3 p-3 rounded border">';
                    $htmlDetail .= '<b><i class="bi bi-file-earmark-pdf-fill me-1"></i> เอกสารประกาศหลัก:</b> ';
                    $htmlDetail .= '<a href="' . htmlspecialchars($mainFileUrl) . '" target="_blank" class="btn btn-primary btn-sm ms-2"><i class="bi bi-download me-1"></i> ดาวน์โหลดประกาศ</a>';
                    $htmlDetail .= '</div>';
                }

                // Additional attachments
                if (!empty($news['files']) && is_array($news['files'])) {
                    $fileHtml = '<div class="card bg-light mt-3 p-3 border rounded">';
                    $fileHtml .= '<b class="mb-2 d-block"><i class="bi bi-paperclip me-1"></i> ไฟล์แนบเพิ่มเติม:</b><ul class="list-unstyled mb-0">';
                    foreach ($news['files'] as $file) {
                        $fUrl = $file['fileUrl'] ?? '';
                        $fName = $file['FileNameOriginal'] ?? 'ดาวน์โหลดไฟล์';
                        if ($fUrl) {
                            $fileHtml .= '<li class="mb-1"><a href="' . htmlspecialchars($fUrl) . '" target="_blank" class="text-decoration-none"><i class="bi bi-file-earmark-arrow-down me-1"></i>' . htmlspecialchars($fName) . '</a></li>';
                        }
                    }
                    $fileHtml .= '</ul></div>';
                    $htmlDetail .= $fileHtml;
                }

                $model->news_detail = $htmlDetail;
                $model->data_show = 1;

                // Download and store image locally if available
                if (!empty($news['imgNews'])) {
                    $savedPath = $this->downloadImage($news['imgNews'], $nid);
                    if ($savedPath) {
                        $model->news_path = $savedPath;
                        $model->news_img = basename($savedPath);
                        $model->news_safe_name = basename($savedPath);
                    }
                }

                $model->save();
                $stats['synced']++;
                if ($progressCallback) {
                    $progressCallback("ID: {$nid} - บันทึกสำเร็จ: " . Str::limit($model->news_name, 50));
                }
            } catch (\Throwable $e) {
                $stats['failed']++;
                if ($progressCallback) {
                    $progressCallback("ID: {$nid} - เกิดข้อผิดพลาด: " . $e->getMessage());
                }
            }
        }

        return $stats;
    }

    private function cleanIframeDetail(string $content): string
    {
        $base = 'https://nriis.go.th';
        $pattern = '~<iframe[^>]*src=["\']([^"\']+)["\'][^>]*>.*?</iframe>~is';

        return preg_replace_callback($pattern, function ($m) use ($base) {
            $src = trim($m[1], '\'"');
            if (!preg_match('~^https?://~i', $src)) {
                $src = rtrim($base, '/') . '/' . ltrim($src, '/');
            }
            return '<p><a target="_blank" rel="noopener" href="' . htmlspecialchars($src) . '" class="btn btn-outline-secondary btn-sm my-2"><i class="bi bi-file-earmark-pdf me-1"></i> เปิดดูเอกสารแนบ</a></p>';
        }, $content);
    }

    private function downloadImage(string $url, string $nid): ?string
    {
        try {
            $resp = Http::timeout(10)->get(trim($url));
            if (!$resp->successful()) {
                return null;
            }

            $content = $resp->body();
            if (empty($content)) {
                return null;
            }

            $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
            $ext = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? strtolower($ext) : 'jpg';

            $filename = "nriis_{$nid}.{$ext}";
            $relativePath = "uploads/datanewsimg/{$filename}";

            Storage::disk('public')->put($relativePath, $content);
            return $relativePath;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
