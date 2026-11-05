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
    private string $makeWebhookUrl = 'https://hook.eu1.make.com/9juofv3nru0zf6z9gtnyufvkd3ryvknn';

    /**
     * Dry-Run / Test fetch from NRIIS API without saving to DB or sending webhooks
     *
     * @param int $limit
     * @param string|null $specificNid
     * @return array
     */
    public function testSync(int $limit = 5, ?string $specificNid = null): array
    {
        $limit = min(50, max(1, $limit));
        $itemsReport = [];

        $targetNids = [];
        if (!empty($specificNid)) {
            $targetNids[] = trim($specificNid);
        } else {
            try {
                $response = Http::timeout(25)->get("{$this->baseApiUrl}/general/news/?ngroup=&d=&page=1&limit={$limit}");
                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'error' => "เชื่อมต่อ API รายการหน้าหลัก NRIIS ไม่ได้ (HTTP {$response->status()})",
                        'items' => [],
                    ];
                }

                $listData = $response->json();
                $items = $listData['data'] ?? [];
                foreach ($items as $item) {
                    $nid = (string) ($item['NID'] ?? '');
                    if (!empty($nid)) {
                        $targetNids[] = $nid;
                    }
                }
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'error' => "เกิดข้อผิดพลาดในการเชื่อมต่อ NRIIS API: " . $e->getMessage(),
                    'items' => [],
                ];
            }
        }

        if (empty($targetNids)) {
            return [
                'success' => false,
                'error' => "ไม่พบรายการข่าวจาก NRIIS API",
                'items' => [],
            ];
        }

        foreach ($targetNids as $nid) {
            try {
                $detailResponse = Http::timeout(20)->get("{$this->baseApiUrl}/general/news/{$nid}");
                if (!$detailResponse->successful()) {
                    $itemsReport[] = [
                        'status' => 'error',
                        'news_id' => $nid,
                        'message' => "HTTP {$detailResponse->status()} ไม่สามารถดึงรายละเอียดข่าวได้",
                    ];
                    continue;
                }

                $news = $detailResponse->json();
                if (!$news || empty($news['NNAME'])) {
                    $itemsReport[] = [
                        'status' => 'empty',
                        'news_id' => $nid,
                        'message' => "ไม่พบข้อมูลเนื้อหาข่าวจาก API (NNAME ว่างเปล่า)",
                    ];
                    continue;
                }

                // Check DB status (informative only, DO NOT SKIP!)
                $existing = ResearchNews::where('news_id', $nid)->first();

                // Format detail & attachments
                $rawDetail = $news['NDETAIL'] ?? '';
                $htmlDetail = $this->cleanIframeDetail($rawDetail);

                $pdfAttachment = null;
                if (!empty($news['NFILE_PDF'])) {
                    $pdfAttachment = 'https://nriis.go.th/FileUpload/AttatchFile/News/' . trim($news['NFILE_PDF']);
                    $htmlDetail .= '<div class="alert alert-info mt-3 p-3 rounded border">';
                    $htmlDetail .= '<b><i class="bi bi-file-earmark-pdf-fill me-1"></i> เอกสารประกาศหลัก:</b> ';
                    $htmlDetail .= '<a href="' . htmlspecialchars($pdfAttachment) . '" target="_blank" class="btn btn-primary btn-sm ms-2"><i class="bi bi-download me-1"></i> ดาวน์โหลดประกาศ</a>';
                    $htmlDetail .= '</div>';
                }

                $filesList = [];
                if (!empty($news['files']) && is_array($news['files'])) {
                    $fileHtml = '<div class="card bg-body-tertiary mt-3 p-3 border rounded">';
                    $fileHtml .= '<b class="mb-2 d-block"><i class="bi bi-paperclip me-1"></i> ไฟล์แนบเพิ่มเติม:</b><ul class="mb-0 ps-3">';
                    foreach ($news['files'] as $file) {
                        $fUrl = $file['fileUrl'] ?? '';
                        $fName = $file['FileNameOriginal'] ?? 'ดาวน์โหลดไฟล์';
                        if ($fUrl) {
                            $filesList[] = ['name' => $fName, 'url' => $fUrl];
                            $fileHtml .= '<li class="mb-1"><a href="' . htmlspecialchars($fUrl) . '" target="_blank" class="text-decoration-none"><i class="bi bi-file-earmark-arrow-down me-1"></i>' . htmlspecialchars($fName) . '</a></li>';
                        }
                    }
                    $fileHtml .= '</ul></div>';
                    $htmlDetail .= $fileHtml;
                }

                $imgNewsUrl = $news['imgNews'] ?? null;
                $simulatedFileName = "{$nid}.webp";

                $simulatedWebhook = [
                    'event' => 'research_news_created',
                    'news_id' => $nid,
                    'news_type' => $news['NTNAME'] ?? 'ข่าวประชาสัมพันธ์',
                    'news_name' => trim($news['NNAME']),
                    'news_date' => $news['createdateTH'] ?? now()->format('Y-m-d'),
                    'news_link' => "{$this->baseUrl}/news/{$nid}",
                    'image_url' => $imgNewsUrl,
                    'simulated_storage_file' => "uploads/datanewsimg/{$simulatedFileName}",
                ];

                $simulatedTelegram = "📢 [จำลองการแจ้งเตือน] ข่าวทุนวิจัยใหม่จาก NRIIS\n"
                    . "📌 หัวข้อ: " . trim($news['NNAME']) . "\n"
                    . "🏷️ ประเภท: " . ($news['NTNAME'] ?? 'ข่าวประชาสัมพันธ์') . "\n"
                    . "📅 วันที่: " . ($news['createdateTH'] ?? now()->format('Y-m-d')) . "\n"
                    . "🔗 ลิงก์: {$this->baseUrl}/news/{$nid}";

                $itemsReport[] = [
                    'status' => 'success',
                    'news_id' => $nid,
                    'news_name' => trim($news['NNAME']),
                    'news_type' => $news['NTNAME'] ?? 'ข่าวประชาสัมพันธ์',
                    'news_date' => $news['createdateTH'] ?? now()->format('Y-m-d'),
                    'news_link' => "{$this->baseUrl}/news/{$nid}",
                    'news_img_url' => $imgNewsUrl,
                    'simulated_file_name' => $simulatedFileName,
                    'pdf_attachment' => $pdfAttachment,
                    'files_list' => $filesList,
                    'html_detail' => $htmlDetail,
                    'exists_in_db' => $existing !== null,
                    'existing_id' => $existing?->id,
                    'simulated_webhook' => $simulatedWebhook,
                    'simulated_telegram' => $simulatedTelegram,
                    'raw_api_data' => $news,
                ];
            } catch (\Throwable $e) {
                $itemsReport[] = [
                    'status' => 'error',
                    'news_id' => $nid,
                    'message' => "เกิดข้อผิดพลาดในการประมวลผล NID {$nid}: " . $e->getMessage(),
                ];
            }
        }

        return [
            'success' => true,
            'limit' => $limit,
            'specific_nid' => $specificNid,
            'items' => $itemsReport,
        ];
    }

    /**
     * Sync news from NRIIS
     *
     * @param int $limit Number of items to fetch
     * @param bool $force Re-sync already existing news
     * @param callable|null $progressCallback Optional callback for CLI output
     * @return array [stats => [synced => int, skipped => int, failed => int], items => array]
     */
    public function sync(int $limit = 50, bool $force = false, ?callable $progressCallback = null): array
    {
        $stats = ['synced' => 0, 'skipped' => 0, 'failed' => 0];
        $itemsReport = [];

        try {
            $response = Http::timeout(25)->get("{$this->baseApiUrl}/general/news/?ngroup=&d=&page=1&limit={$limit}");
            if (!$response->successful()) {
                $err = "เชื่อมต่อ API รายการหน้าหลัก NRIIS ไม่ได้ (HTTP {$response->status()})";
                if ($progressCallback) {
                    $progressCallback($err);
                }
                return ['stats' => $stats, 'items' => [], 'error' => $err];
            }

            $listData = $response->json();
            $items = $listData['data'] ?? [];
        } catch (\Throwable $e) {
            $err = "เกิดข้อผิดพลาดในการเชื่อมต่อ NRIIS API: " . $e->getMessage();
            if ($progressCallback) {
                $progressCallback($err);
            }
            return ['stats' => $stats, 'items' => [], 'error' => $err];
        }

        foreach ($items as $item) {
            $nid = (string) ($item['NID'] ?? '');
            if (empty($nid)) {
                continue;
            }

            $existing = ResearchNews::where('news_id', $nid)->first();
            if ($existing && !$force) {
                $stats['skipped']++;
                $skipMsg = "ID: {$nid} -- ซ้ำ (ข้ามไป) --";
                if ($progressCallback) {
                    $progressCallback($skipMsg);
                }
                $itemsReport[] = [
                    'status' => 'skipped',
                    'news_id' => $nid,
                    'title' => $existing->news_name,
                    'message' => $skipMsg,
                    'detail_url' => route('backend.research_news.show', $existing->id),
                ];
                continue;
            }

            try {
                $detailResponse = Http::timeout(20)->get("{$this->baseApiUrl}/general/news/{$nid}");
                if (!$detailResponse->successful()) {
                    $stats['failed']++;
                    $itemsReport[] = [
                        'status' => 'failed',
                        'news_id' => $nid,
                        'message' => "ID: {$nid} -- เกิดข้อผิดพลาด: HTTP {$detailResponse->status()}",
                    ];
                    continue;
                }

                $news = $detailResponse->json();
                if (!$news || empty($news['NNAME']) || mb_strlen(trim($news['NNAME'])) <= 10) {
                    $stats['skipped']++;
                    $itemsReport[] = [
                        'status' => 'skipped',
                        'news_id' => $nid,
                        'title' => $news['NNAME'] ?? '',
                        'message' => "ID: {$nid} -- หัวข้อข่าวสั้นเกินไปหรือไม่พบเนื้อหา --",
                    ];
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

                // 1. Clean iframe in details
                $rawDetail = $news['NDETAIL'] ?? '';
                $htmlDetail = $this->cleanIframeDetail($rawDetail);

                // 2. Main PDF attachment (NFILE_PDF)
                if (!empty($news['NFILE_PDF'])) {
                    $mainFileUrl = 'https://nriis.go.th/FileUpload/AttatchFile/News/' . trim($news['NFILE_PDF']);
                    $htmlDetail .= '<div class="alert alert-info mt-3 p-3 rounded border">';
                    $htmlDetail .= '<b><i class="bi bi-file-earmark-pdf-fill me-1"></i> เอกสารประกาศหลัก:</b> ';
                    $htmlDetail .= '<a href="' . htmlspecialchars($mainFileUrl) . '" target="_blank" class="btn btn-primary btn-sm ms-2"><i class="bi bi-download me-1"></i> ดาวน์โหลดประกาศ</a>';
                    $htmlDetail .= '</div>';
                }

                // 3. Additional attachments (files)
                if (!empty($news['files']) && is_array($news['files'])) {
                    $fileHtml = '<div class="card bg-body-tertiary mt-3 p-3 border rounded">';
                    $fileHtml .= '<b class="mb-2 d-block"><i class="bi bi-paperclip me-1"></i> ไฟล์แนบเพิ่มเติม:</b><ul class="mb-0 ps-3">';
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

                // 4. Download and resize image locally: {nid}.{ext}
                if (!empty($news['imgNews'])) {
                    $savedFileName = $this->downloadAndResize($news['imgNews'], $nid);
                    if ($savedFileName) {
                        $model->news_path = "uploads/datanewsimg/{$savedFileName}";
                        $model->news_img = $savedFileName;
                        $model->news_safe_name = $savedFileName;
                    }
                }

                $model->save();
                $stats['synced']++;

                // 5. Send to Make.com Webhook (Facebook automation)
                $makeStatus = $this->sendToMakeWebhook($model);

                // 6. Send Line/Telegram Notify
                $this->sendTelegramNotify($model, $makeStatus);

                if ($progressCallback) {
                    $progressCallback("ID: {$nid} - บันทึกสำเร็จ: " . Str::limit($model->news_name, 50));
                }

                $itemsReport[] = [
                    'status' => 'synced',
                    'news_id' => $model->news_id,
                    'id' => $model->id,
                    'title' => $model->news_name,
                    'title_len' => mb_strlen(trim($model->news_name)),
                    'news_link' => $model->news_link,
                    'news_img' => $model->news_img,
                    'image_url' => $model->image_url,
                    'news_date' => $model->news_date,
                    'news_type' => $model->news_type,
                    'news_detail' => $model->news_detail,
                    'detail_url' => route('backend.research_news.show', $model->id),
                    'make_status' => $makeStatus,
                ];

            } catch (\Throwable $e) {
                $stats['failed']++;
                $errMsg = "ID: {$nid} - เกิดข้อผิดพลาด: " . $e->getMessage();
                if ($progressCallback) {
                    $progressCallback($errMsg);
                }
                $itemsReport[] = [
                    'status' => 'failed',
                    'news_id' => $nid,
                    'message' => $errMsg,
                ];
            }
        }

        return [
            'stats' => $stats,
            'synced' => $stats['synced'],
            'skipped' => $stats['skipped'],
            'failed' => $stats['failed'],
            'items' => $itemsReport,
        ];
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

    /**
     * Download and resize image using GD (same logic as Yii2 downloadAndResize)
     * Saves file as {nid}.{ext} under storage/app/public/uploads/datanewsimg/
     */
    private function downloadAndResize(string $urlImg, string $nid): ?string
    {
        try {
            $resp = Http::timeout(15)->get(trim($urlImg));
            if (!$resp->successful()) {
                return null;
            }

            $content = $resp->body();
            if (empty($content)) {
                return null;
            }

            $newFileName = "{$nid}.webp";
            $targetDir = storage_path('app/public/uploads/datanewsimg');
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $newFileName;

            if (extension_loaded('gd') && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
                $im = @imagecreatefromstring($content);
                if ($im) {
                    imagepalettetotruecolor($im);
                    imagealphablending($im, true);
                    imagesavealpha($im, true);

                    $width = imagesx($im);
                    $height = imagesy($im);
                    if ($width > 1024 || $height > 1024) {
                        $percent = 1024 / max($width, $height);
                        $newW = (int) round($width * $percent);
                        $newH = (int) round($height * $percent);
                        $thumb = imagecreatetruecolor($newW, $newH);
                        imagealphablending($thumb, false);
                        imagesavealpha($thumb, true);
                        imagecopyresampled($thumb, $im, 0, 0, 0, 0, $newW, $newH, $width, $height);
                        imagewebp($thumb, $targetPath, 85);
                        imagedestroy($thumb);
                    } else {
                        imagewebp($im, $targetPath, 85);
                    }
                    imagedestroy($im);
                    return $newFileName;
                }
            }

            // Fallback direct write if GD/WebP is unavailable
            $fallbackExt = pathinfo(parse_url($urlImg, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
            $ext = in_array(strtolower($fallbackExt), ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? strtolower($fallbackExt) : 'jpg';
            $fallbackName = "{$nid}.{$ext}";
            $targetPathFallback = $targetDir . DIRECTORY_SEPARATOR . $fallbackName;
            file_put_contents($targetPathFallback, $content);
            return $fallbackName;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Send payload to Make.com Webhook
     */
    private function sendToMakeWebhook(ResearchNews $model): string
    {
        try {
            $makeNewsData = [
                'news_id' => (string) $model->news_id,
                'type' => $model->news_type,
                'title' => $model->news_name,
                'content' => $model->news_detail,
                'source_url' => route('backend.research_news.show', $model->id),
                'image_url' => $model->image_url ?: '',
            ];

            $response = Http::timeout(8)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->makeWebhookUrl, $makeNewsData);

            if ($response->successful()) {
                return $model->id . " - ส่งข้อมูลไป Make สำเร็จ!";
            } else {
                return $model->id . " - ส่งไป Make เกิดข้อผิดพลาด (HTTP {$response->status()})";
            }
        } catch (\Throwable $e) {
            return $model->id . " - ส่งไป Make ไม่สำเร็จ: " . $e->getMessage();
        }
    }

    /**
     * Send Telegram notification matching Yii2 format
     */
    private function sendTelegramNotify(ResearchNews $model, string $makeStatus): void
    {
        try {
            $text = "📌 " . $model->news_name . "\n";
            $text .= "--------------------\n";
            $text .= "🔗 รายละเอียด: " . route('backend.research_news.show', $model->id) . "\n";
            $text .= "📅 วันที่ประกาศ: " . $model->news_date;

            $imageUrl = $model->image_url;
            if ($imageUrl) {
                TelegramNotificationService::sendMessageToNotify($text . "\n\n🔎 " . $imageUrl . "\n\n" . $makeStatus, $imageUrl);
            } else {
                TelegramNotificationService::sendMessageToNotify($text . "\n\n" . $makeStatus);
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }
    }
}
