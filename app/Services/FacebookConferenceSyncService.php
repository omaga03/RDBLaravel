<?php

namespace App\Services;

use App\Models\ResearchConference;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FacebookConferenceSyncService
{
    private string $targetPageUrl = 'https://www.facebook.com/prachume/photos';

    /**
     * Sync conference posts from Facebook page
     *
     * @param int $limit Max items to inspect
     * @param callable|null $progressCallback
     * @return array [synced => int, skipped => int, failed => int, message => string]
     */
    public function sync(int $limit = 8, ?callable $progressCallback = null): array
    {
        $stats = ['synced' => 0, 'skipped' => 0, 'failed' => 0, 'message' => ''];

        $cookie = config('services.facebook.cookie') 
            ?: env('FACEBOOK_SCRAPE_COOKIE') 
            ?: SystemSetting::get('facebook_scrape_cookie', '');

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'th-TH,th;q=0.9,en;q=0.8',
        ];

        if (!empty($cookie)) {
            $headers['Cookie'] = $cookie;
        }

        try {
            $response = Http::timeout(20)->withHeaders($headers)->get($this->targetPageUrl);
            if (!$response->successful()) {
                $msg = "เชื่อมต่อ Facebook ไม่สำเร็จ (HTTP {$response->status()})";
                if ($progressCallback) $progressCallback($msg);
                $stats['message'] = $msg;
                return $stats;
            }

            $body = $response->body();
        } catch (\Throwable $e) {
            $msg = "เกิดข้อผิดพลาดในการเชื่อมต่อ Facebook: " . $e->getMessage();
            if ($progressCallback) $progressCallback($msg);
            $stats['message'] = $msg;
            return $stats;
        }

        // Match photo links: /photo.php?fbid=... or JSON encoded url
        $photoLinks = [];
        if (preg_match_all('/(?:photo\.php\?(?:fbid=([0-9]+)|.*?[?&]fbid=([0-9]+))|"url":"https:[^"]*?photo\.php\?(?:fbid=([0-9]+)|[^"]*?fbid=([0-9]+)))/i', $body, $matches)) {
            $allFbids = array_filter(array_merge($matches[1], $matches[2], $matches[3], $matches[4]));
            $photoLinks = array_values(array_unique($allFbids));
        }

        if (empty($photoLinks)) {
            // Also try matching pure numeric fbid patterns in node text
            if (preg_match_all('/"fbid":"([0-9]{12,20})"/i', $body, $matchesFbid)) {
                $photoLinks = array_values(array_unique($matchesFbid[1]));
            }
        }

        if (empty($photoLinks)) {
            $msg = "ไม่พบรายการภาพข่าวงานประชุมจาก Facebook (อาจต้องเข้าสู่ระบบหรือ Facebook มีการจำกัดการเข้าถึง)";
            if ($progressCallback) $progressCallback($msg);
            $stats['message'] = $msg;
            return $stats;
        }

        $count = 0;
        foreach ($photoLinks as $fbid) {
            if ($count >= $limit) {
                break;
            }
            $count++;

            $existing = ResearchConference::where('con_id', $fbid)->first();
            if ($existing) {
                $stats['skipped']++;
                if ($progressCallback) $progressCallback("FBID: {$fbid} - ซ้ำ (ข้ามไป)");
                continue;
            }

            try {
                $photoUrl = "https://www.facebook.com/photo.php?fbid={$fbid}";
                $photoResp = Http::timeout(15)->withHeaders($headers)->get($photoUrl);
                if (!$photoResp->successful()) {
                    $stats['failed']++;
                    continue;
                }

                $pBody = $photoResp->body();

                // Extract post caption/text
                $rawText = '';
                if (preg_match('/"text":"(.*?)"(?:,"message_preferred_body"|,"entity_ranges")/s', $pBody, $mText)) {
                    $rawText = json_decode('"' . $mText[1] . '"') ?: $mText[1];
                }

                // Extract image URI
                $imgUri = null;
                if (preg_match('/"image":{"uri":"(https:[^"]+)"/i', $pBody, $mImg)) {
                    $imgUri = stripslashes($mImg[1]);
                }

                if (empty($rawText) && empty($imgUri)) {
                    $stats['skipped']++;
                    continue;
                }

                // Parse conference fields from text
                $textFormatted = str_replace(["\r\n", "\r", "\n"], '<br>', trim($rawText));
                $lines = array_values(array_filter(explode('<br>', $textFormatted)));
                $title = !empty($lines) ? trim(strip_tags($lines[0])) : "งานประชุมวิชาการ FB {$fbid}";

                // Event date pattern
                $eventDate = null;
                if (preg_match('/(?:วัน(?:ที่)?\s*)?(\d{1,2}\s+(?:ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.|มกราคม|กุมภาพันธ์|มีนาคม|เมษายน|พฤษภาคม|มิถุนายน|กรกฎาคม|สิงหาคม|กันยายน|ตุลาคม|พฤศจิกายน|ธันวาคม)\s+\d{2,4})/u', $textFormatted, $mDate)) {
                    $eventDate = trim($mDate[0]);
                }

                // Deadline pattern
                $deadline = null;
                if (preg_match('/(?:Deadline|หมดเขต|ส่งบทความ(?:ภายใน)?|ปิดรับ)\s*[:\-]?\s*(\d{1,2}\s+(?:ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.|มกราคม|กุมภาพันธ์|มีนาคม|เมษายน|พฤษภาคม|มิถุนายน|กรกฎาคม|สิงหาคม|กันยายน|ตุลาคม|พฤศจิกายน|ธันวาคม)\s+\d{2,4})/ui', $textFormatted, $mDl)) {
                    $deadline = trim($mDl[1]);
                }

                // Venue pattern
                $venue = null;
                if (preg_match('/(?:ณ|สถานที่|จัดที่)\s*[:\-]?\s*(.+?)(?:<br>|$)/u', $textFormatted, $mVn)) {
                    $venue = trim(strip_tags($mVn[1]));
                }

                // Download image
                $savedPath = null;
                $savedFileName = null;
                if ($imgUri) {
                    $savedPath = $this->downloadImage($imgUri, $fbid);
                    if ($savedPath) {
                        $savedFileName = basename($savedPath);
                    }
                }

                $model = new ResearchConference();
                $model->con_id = (string) $fbid;
                $model->con_name = Str::limit($title, 250);
                $model->con_detail = $textFormatted;
                $model->con_even_date = $eventDate;
                $model->con_sub_deadline = $deadline;
                $model->con_venue = $venue;
                $model->con_website = $photoUrl;
                if ($savedPath) {
                    $model->con_path = $savedPath;
                    $model->con_safe_name = $savedFileName;
                    $model->con_img = $savedFileName;
                }
                $model->data_show = 1;
                $model->con_count = 0;
                $model->save();

                $stats['synced']++;
                if ($progressCallback) $progressCallback("FBID: {$fbid} - บันทึกสำเร็จ: " . Str::limit($model->con_name, 50));
            } catch (\Throwable $e) {
                $stats['failed']++;
                if ($progressCallback) $progressCallback("FBID: {$fbid} - ข้อผิดพลาด: " . $e->getMessage());
            }
        }

        $stats['message'] = "ดึงข้อมูลจาก Facebook เสร็จสิ้น (บันทึกใหม่: {$stats['synced']}, ข้าม: {$stats['skipped']})";
        return $stats;
    }

    private function downloadImage(string $url, string $fbid): ?string
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

            $filename = "conth_{$fbid}.jpg";
            $relativePath = "uploads/datacoferenceinthai/{$filename}";

            Storage::disk('public')->put($relativePath, $content);
            return $relativePath;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
