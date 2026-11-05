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
    private string $makeWebhookUrl = 'https://hook.eu1.make.com/9juofv3nru0zf6z9gtnyufvkd3ryvknn';

    /**
     * Dry-Run / Test fetch conference posts from Facebook without saving to DB or sending webhooks
     *
     * @param int $limit
     * @param string|null $specificFbid
     * @param string|null $customCookie
     * @return array
     */
    public function testSync(int $limit = 3, ?string $specificFbid = null, ?string $customCookie = null): array
    {
        $limit = min(15, max(1, $limit));
        $cookie = !empty($customCookie) ? $customCookie : (config('services.facebook.cookie') ?: env('FACEBOOK_SCRAPE_COOKIE') ?: SystemSetting::get('facebook_scrape_cookie', ''));

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language' => 'th-TH,th;q=0.9,en;q=0.8',
            'Cache-Control' => 'max-age=0',
            'Sec-Ch-Ua' => '"Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'same-origin',
            'Sec-Fetch-User' => '?1',
            'Upgrade-Insecure-Requests' => '1',
        ];

        if (!empty($cookie)) {
            $headers['Cookie'] = $cookie;
        }

        $discoveredCandidates = [];
        $diagnostics = [
            'has_cookie' => !empty($cookie),
            'cookie_length' => strlen($cookie ?? ''),
            'specific_fbid' => $specificFbid,
            'http_status' => null,
            'discovered_links_count' => 0,
        ];

        if (!empty($specificFbid)) {
            $discoveredCandidates[] = 'fbid=' . trim($specificFbid);
        } else {
            try {
                $response = Http::timeout(25)->withHeaders($headers)->get($this->targetPageUrl);
                $diagnostics['http_status'] = $response->status();

                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'diagnostics' => $diagnostics,
                        'error' => "เชื่อมต่อ Facebook ไม่สำเร็จ (HTTP {$response->status()})",
                        'items' => [],
                    ];
                }

                $body = $response->body();
                $extractedQueries = [];
                if (preg_match_all('/"url":"https:....www\.facebook\.com..photo\.php\.(.*?)"/', $body, $exdata2)) {
                    $extractedQueries = $exdata2[1];
                }
                if (empty($extractedQueries) && preg_match_all('/photo\.php\?([^\'"\s>]+)/i', $body, $mStd)) {
                    $extractedQueries = $mStd[1];
                }

                foreach ($extractedQueries as $linkQuery) {
                    $cleanQuery = str_replace(['\\u0026', '&amp;'], '&', $linkQuery);
                    $discoveredCandidates[] = $cleanQuery;
                }

                $diagnostics['discovered_links_count'] = count($discoveredCandidates);
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'diagnostics' => $diagnostics,
                    'error' => "เกิดข้อผิดพลาดในการเชื่อมต่อ Facebook: " . $e->getMessage(),
                    'items' => [],
                ];
            }
        }

        if (empty($discoveredCandidates)) {
            return [
                'success' => false,
                'diagnostics' => $diagnostics,
                'error' => "ไม่พบรูปภาพงานประชุมจาก Facebook (อาจติดบล็อกหรือ Cookie หมดอายุ)",
                'items' => [],
            ];
        }

        $itemsReport = [];
        $count = 0;

        foreach ($discoveredCandidates as $linkQuery) {
            if ($count >= $limit) {
                break;
            }
            $count++;

            $fbid = null;
            if (preg_match('/fbid=([0-9]+)/', $linkQuery, $sLink)) {
                $fbid = $sLink[1];
            }
            if (!$fbid) {
                continue;
            }

            $photoPageUrl = 'https://www.facebook.com/photo.php?' . $linkQuery;
            $existing = ResearchConference::where('con_id', $fbid)->first();

            try {
                $pResp = Http::timeout(20)->withHeaders($headers)->get($photoPageUrl);
                if (!$pResp->successful()) {
                    $itemsReport[] = [
                        'status' => 'error',
                        'con_id' => $fbid,
                        'url' => $photoPageUrl,
                        'message' => "HTTP {$pResp->status()} ไม่สามารถดึงหน้าภาพถ่ายได้",
                    ];
                    continue;
                }

                $data2 = $pResp->body();

                $rawText = null;
                if (preg_match('/mobileUrl":".*?","__isNode":".*?","id":".*?"},"entity_is_weak_reference":.*?,"length":.*?,"offset":.*?}],"color_ranges":.*?,"text":"(.*?)"},"message_preferred_body"/', $data2, $extextconf)) {
                    $rawText = str_replace(["\\n"], "||", $extextconf[1]);
                } elseif (preg_match('/"text":"(.*?)"(?:,"message_preferred_body"|,"entity_ranges")/s', $data2, $mTextAlt)) {
                    $rawText = str_replace(["\r\n", "\r", "\n", "\\n"], "||", $mTextAlt[1]);
                }

                if (!$rawText) {
                    $itemsReport[] = [
                        'status' => 'skipped',
                        'con_id' => $fbid,
                        'url' => $photoPageUrl,
                        'message' => "ไม่พบข้อความเนื้อหาในหน้า Facebook (preg_match ข้อความไม่พบ)",
                        'exists_in_db' => $existing !== null,
                        'existing_id' => $existing?->id,
                    ];
                    continue;
                }

                $jsonStr = '{"text":"' . $rawText . '"}';
                $objText = json_decode($jsonStr);
                $textDecoded = $objText->text ?? $rawText;

                // Extract image URI
                $imgUri = null;
                if (preg_match('/"image":{"uri":"(.*?)"/', $data2, $exuriconf)) {
                    $jsonUri = '{"uri":"' . $exuriconf[1] . '"}';
                    $objUri = json_decode($jsonUri);
                    $imgUri = $objUri->uri ?? stripslashes($exuriconf[1]);
                }

                // Format text into HTML
                $textdata2 = str_replace("||", "<br>", $textDecoded);
                $textdata2 = str_replace('<a href="', '<a target="_blank" href="', $textdata2);
                $textdata2 = str_replace('-\\', '-', $textdata2);
                $textdata2 = preg_replace('/<a class(.*?)href="\/hashtag\/(.*?)>#(.*?)<.a>/', '', $textdata2);
                $textdata2 = str_replace("[เบื้องต้น]<br>.<br>", "", $textdata2);
                $textdata2 = $this->sanitizeOutput($textdata2);

                // Keywords check
                $hasConfKeyword = (
                    str_contains($textdata2, 'ประชุม') ||
                    str_contains(strtolower($textdata2), 'onference') ||
                    str_contains(strtolower($textdata2), 'nternational') ||
                    str_contains(strtolower($textdata2), 'ymposium') ||
                    str_contains(strtolower($textdata2), 'ongress') ||
                    str_contains(strtolower($textdata2), 'esearch')
                );

                $hasDetailKeyword = (
                    str_contains(strtolower($textdata2), 'eadline') ||
                    str_contains(strtolower($textdata2), 'ubmission') ||
                    str_contains(strtolower($textdata2), 'ebsite') ||
                    str_contains(strtolower($textdata2), 'acebook') ||
                    str_contains(strtolower($textdata2), 'http')
                );

                // Parse fields
                $lines = explode('<br>', $textdata2);
                $conName = trim(strip_tags($lines[0] ?? "งานประชุมวิชาการ {$fbid}"));

                $conEvenDate = null;
                if (preg_match('/<br>\s*วัน(.+?)<br>/u', $textdata2, $mEvent)) {
                    $conEvenDate = 'วัน' . trim(strip_tags($mEvent[1]));
                } elseif (preg_match('/(?:วัน(?:ที่)?\s*)?(\d{1,2}\s+(?:ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.|มกราคม|กุมภาพันธ์|มีนาคม|เมษายน|พฤษภาคม|มิถุนายน|กรกฎาคม|สิงหาคม|กันยายน|ตุลาคม|พฤศจิกายน|ธันวาคม)\s+\d{2,4})/u', $textdata2, $mEventAlt)) {
                    $conEvenDate = trim($mEventAlt[0]);
                }

                $patterndeadline = '/(?:<br>|\s)Deadline sub.*n:\s*(\d{1,2}\s+(?:ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.|มกราคม|กุมภาพันธ์|มีนาคม|เมษายน|พฤษภาคม|มิถุนายน|กรกฎาคม|สิงหาคม|กันยายน|ตุลาคม|พฤศจิกายน|ธันวาคม)\s+\d{2,4})/u';
                $deadlineSubmission = null;
                if (preg_match($patterndeadline, $textdata2, $mDl)) {
                    $deadlineSubmission = trim($mDl[1]);
                }

                $venue = null;
                if (preg_match('/[<br>|<br> ]ณ.(.+?)<br>/u', $textdata2, $mVn)) {
                    $venue = trim(strip_tags($mVn[1]));
                }

                $cleanDetail = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $textdata2);

                $simulatedWebhook = [
                    'event' => 'research_conference_created',
                    'con_id' => $fbid,
                    'con_name' => Str::limit($conName, 250),
                    'con_even_date' => $conEvenDate,
                    'con_sub_deadline' => $deadlineSubmission,
                    'con_venue' => $venue,
                    'con_link' => $photoPageUrl,
                    'image_url' => $imgUri,
                    'simulated_storage_file' => "uploads/datacoferenceinthai/conth_{$fbid}.webp",
                ];

                $simulatedTelegram = "📢 [จำลองการแจ้งเตือน] งานประชุมวิชาการใหม่\n"
                    . "📌 หัวข้อ: " . Str::limit($conName, 120) . "\n"
                    . ($conEvenDate ? "📅 วันจัดงาน: {$conEvenDate}\n" : "")
                    . ($deadlineSubmission ? "⏰ หมดเขต: {$deadlineSubmission}\n" : "")
                    . ($venue ? "📍 สถานที่: {$venue}\n" : "")
                    . "🔗 ลิงก์โพสต์: {$photoPageUrl}";

                $itemsReport[] = [
                    'status' => 'success',
                    'con_id' => $fbid,
                    'url' => $photoPageUrl,
                    'con_name' => Str::limit($conName, 250),
                    'con_even_date' => $conEvenDate,
                    'con_sub_deadline' => $deadlineSubmission,
                    'con_venue' => $venue,
                    'image_uri' => $imgUri,
                    'con_detail' => $cleanDetail,
                    'keyword_checks' => [
                        'has_conf_keyword' => $hasConfKeyword,
                        'has_detail_keyword' => $hasDetailKeyword,
                        'passed' => $hasConfKeyword && $hasDetailKeyword,
                    ],
                    'exists_in_db' => $existing !== null,
                    'existing_id' => $existing?->id,
                    'simulated_webhook' => $simulatedWebhook,
                    'simulated_telegram' => $simulatedTelegram,
                ];
            } catch (\Throwable $e) {
                $itemsReport[] = [
                    'status' => 'error',
                    'con_id' => $fbid,
                    'url' => $photoPageUrl,
                    'message' => "เกิดข้อผิดพลาดในการดึงข้อมูล FBID {$fbid}: " . $e->getMessage(),
                ];
            }
        }

        return [
            'success' => true,
            'diagnostics' => $diagnostics,
            'limit' => $limit,
            'specific_fbid' => $specificFbid,
            'items' => $itemsReport,
        ];
    }

    /**
     * Sync conference posts from Facebook page
     *
     * @param int $limit Max items to inspect (default 8 like Yii2)
     * @param callable|null $progressCallback
     * @return array [stats => array, items => array, discovered_links => array, message => string]
     */
    public function sync(int $limit = 8, ?callable $progressCallback = null): array
    {
        $stats = ['synced' => 0, 'skipped' => 0, 'failed' => 0, 'message' => ''];
        $itemsReport = [];
        $discoveredLinks = [];

        $cookie = config('services.facebook.cookie') 
            ?: env('FACEBOOK_SCRAPE_COOKIE') 
            ?: SystemSetting::get('facebook_scrape_cookie', '');

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language' => 'th-TH,th;q=0.9,en;q=0.8',
            'Cache-Control' => 'max-age=0',
            'Sec-Ch-Ua' => '"Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'same-origin',
            'Sec-Fetch-User' => '?1',
            'Upgrade-Insecure-Requests' => '1',
        ];

        if (!empty($cookie)) {
            $headers['Cookie'] = $cookie;
        }

        try {
            $response = Http::timeout(25)->withHeaders($headers)->get($this->targetPageUrl);
            if (!$response->successful()) {
                $msg = "เชื่อมต่อ Facebook ไม่สำเร็จ (HTTP {$response->status()})";
                if ($progressCallback) $progressCallback($msg);
                $stats['message'] = $msg;
                return ['stats' => $stats, 'items' => [], 'discovered_links' => [], 'error' => $msg];
            }

            $body = $response->body();
        } catch (\Throwable $e) {
            $msg = "เกิดข้อผิดพลาดในการเชื่อมต่อ Facebook: " . $e->getMessage();
            if ($progressCallback) $progressCallback($msg);
            $stats['message'] = $msg;
            return ['stats' => $stats, 'items' => [], 'discovered_links' => [], 'error' => $msg];
        }

        // 1. ตรวจสอบ preg_match_all หน้าหลัก เหมือน Yii2:
        // /"url":"https:....www.facebook.com..photo.php.(.*?)"/
        $extractedQueries = [];
        if (preg_match_all('/"url":"https:....www\.facebook\.com..photo\.php\.(.*?)"/', $body, $exdata2)) {
            $extractedQueries = $exdata2[1];
        }

        // Fallback standard regex
        if (empty($extractedQueries)) {
            if (preg_match_all('/photo\.php\?([^\'"\s>]+)/i', $body, $mStd)) {
                $extractedQueries = $mStd[1];
            }
        }

        $allCandidates = [];
        foreach ($extractedQueries as $idx => $linkQuery) {
            // Unescape unicode slashes if any
            $cleanQuery = str_replace(['\\u0026', '&amp;'], '&', $linkQuery);
            $fullUrl = 'https://www.facebook.com/photo.php?' . $cleanQuery;
            $allCandidates[] = $cleanQuery;
            $discoveredLinks[] = [
                'index' => $idx + 1,
                'url' => $fullUrl,
                'label' => mb_substr($fullUrl, 0, 100, 'UTF-8'),
            ];
        }

        if (empty($allCandidates)) {
            $msg = "ไม่สามารถดึงข้อมูลรายการภาพถ่ายหน้าหลัก Facebook ได้ (อาจติดหน้า Login หรือต้องระบุ Facebook Scrape Cookie)";
            if ($progressCallback) $progressCallback($msg);
            $stats['message'] = $msg;
            return ['stats' => $stats, 'items' => [], 'discovered_links' => [], 'error' => $msg];
        }

        $i = 0;
        foreach ($allCandidates as $linkQuery) {
            if ($i >= $limit) {
                break;
            }
            $i++;

            // Extract fbid
            $fbid = null;
            if (preg_match('/fbid=([0-9]+)/', $linkQuery, $sLink)) {
                $fbid = $sLink[1];
            }

            if (!$fbid) {
                continue;
            }

            $photoPageUrl = 'https://www.facebook.com/photo.php?' . $linkQuery;

            // ตรวจสอบ ID ซ้ำในฐานข้อมูล
            $existing = ResearchConference::where('con_id', $fbid)->first();
            if ($existing) {
                $stats['skipped']++;
                $dupMsg = "{$fbid} -- not save (ID ซ้ำ) --";
                if ($progressCallback) $progressCallback($dupMsg);
                $itemsReport[] = [
                    'status' => 'skipped',
                    'con_id' => $fbid,
                    'url' => $photoPageUrl,
                    'title' => $existing->con_name,
                    'message' => $dupMsg,
                    'detail_url' => route('backend.research_conference.show', $existing->id),
                ];
                continue;
            }

            try {
                $pResp = Http::timeout(20)->withHeaders($headers)->get($photoPageUrl);
                if (!$pResp->successful()) {
                    $stats['failed']++;
                    $itemsReport[] = [
                        'status' => 'failed',
                        'con_id' => $fbid,
                        'url' => $photoPageUrl,
                        'message' => "{$fbid} -- HTTP Error {$pResp->status()} --",
                    ];
                    continue;
                }

                $data2 = $pResp->body();

                // ตรวจสอบ preg_match ข่าว เหมือน Yii2:
                $rawText = null;
                if (preg_match('/mobileUrl":".*?","__isNode":".*?","id":".*?"},"entity_is_weak_reference":.*?,"length":.*?,"offset":.*?}],"color_ranges":.*?,"text":"(.*?)"},"message_preferred_body"/', $data2, $extextconf)) {
                    $rawText = str_replace(["\\n"], "||", $extextconf[1]);
                } elseif (preg_match('/"text":"(.*?)"(?:,"message_preferred_body"|,"entity_ranges")/s', $data2, $mTextAlt)) {
                    $rawText = str_replace(["\r\n", "\r", "\n", "\\n"], "||", $mTextAlt[1]);
                }

                if (!$rawText) {
                    $stats['skipped']++;
                    $itemsReport[] = [
                        'status' => 'skipped',
                        'con_id' => $fbid,
                        'url' => $photoPageUrl,
                        'message' => "{$fbid} -- not save -- (preg_match ข่าวไม่ได้)",
                    ];
                    continue;
                }

                // Decode JSON escaped string
                $jsonStr = '{"text":"' . $rawText . '"}';
                $objText = json_decode($jsonStr);
                $textDecoded = $objText->text ?? $rawText;

                // Extract image URI
                $imgUri = null;
                if (preg_match('/"image":{"uri":"(.*?)"/', $data2, $exuriconf)) {
                    $jsonUri = '{"uri":"' . $exuriconf[1] . '"}';
                    $objUri = json_decode($jsonUri);
                    $imgUri = $objUri->uri ?? stripslashes($exuriconf[1]);
                }

                // Format text into HTML
                $textdata2 = str_replace("||", "<br>", $textDecoded);
                $textdata2 = str_replace('<a href="', '<a target="_blank" href="', $textdata2);
                $textdata2 = str_replace('-\\', '-', $textdata2);
                $textdata2 = preg_replace('/<a class(.*?)href="\/hashtag\/(.*?)>#(.*?)<.a>/', '', $textdata2);
                $textdata2 = str_replace("[เบื้องต้น]<br>.<br>", "", $textdata2);
                $textdata2 = $this->sanitizeOutput($textdata2);

                // ตรวจสอบเงื่อนไขคำสำคัญของงานประชุมวิชาการ
                $hasConfKeyword = (
                    str_contains($textdata2, 'ประชุม') ||
                    str_contains(strtolower($textdata2), 'onference') ||
                    str_contains(strtolower($textdata2), 'nternational') ||
                    str_contains(strtolower($textdata2), 'ymposium') ||
                    str_contains(strtolower($textdata2), 'ongress') ||
                    str_contains(strtolower($textdata2), 'esearch')
                );

                $hasDetailKeyword = (
                    str_contains(strtolower($textdata2), 'eadline') ||
                    str_contains(strtolower($textdata2), 'ubmission') ||
                    str_contains(strtolower($textdata2), 'ebsite') ||
                    str_contains(strtolower($textdata2), 'acebook') ||
                    str_contains(strtolower($textdata2), 'http')
                );

                if (!$hasConfKeyword || !$hasDetailKeyword) {
                    $stats['skipped']++;
                    $itemsReport[] = [
                        'status' => 'skipped',
                        'con_id' => $fbid,
                        'url' => $photoPageUrl,
                        'message' => "{$fbid} -- not save -- (ไม่ตรงเงื่อนไขคำสำคัญงานประชุม)",
                    ];
                    continue;
                }

                // Download image
                $newImageName = null;
                if (!empty($imgUri)) {
                    $newImageName = $this->downloadConferenceImage($imgUri, $fbid);
                }

                // Parse parsed fields
                $lines = explode('<br>', $textdata2);
                $conName = trim(strip_tags($lines[0] ?? "งานประชุมวิชาการ {$fbid}"));

                $conEvenDate = null;
                if (preg_match('/<br>\s*วัน(.+?)<br>/u', $textdata2, $mEvent)) {
                    $conEvenDate = 'วัน' . trim(strip_tags($mEvent[1]));
                } elseif (preg_match('/(?:วัน(?:ที่)?\s*)?(\d{1,2}\s+(?:ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.|มกราคม|กุมภาพันธ์|มีนาคม|เมษายน|พฤษภาคม|มิถุนายน|กรกฎาคม|สิงหาคม|กันยายน|ตุลาคม|พฤศจิกายน|ธันวาคม)\s+\d{2,4})/u', $textdata2, $mEventAlt)) {
                    $conEvenDate = trim($mEventAlt[0]);
                }

                // Deadline pattern
                $patterndeadline = '/(?:<br>|\s)Deadline sub.*n:\s*(\d{1,2}\s+(?:ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.|มกราคม|กุมภาพันธ์|มีนาคม|เมษายน|พฤษภาคม|มิถุนายน|กรกฎาคม|สิงหาคม|กันยายน|ตุลาคม|พฤศจิกายน|ธันวาคม)\s+\d{2,4})/u';
                $deadlineSubmission = null;
                if (preg_match($patterndeadline, $textdata2, $mDl)) {
                    $deadlineSubmission = trim($mDl[1]);
                }

                // Venue pattern
                $venue = null;
                if (preg_match('/[<br>|<br> ]ณ.(.+?)<br>/u', $textdata2, $mVn)) {
                    $venue = trim(strip_tags($mVn[1]));
                }

                // Clean emojis from details
                $cleanDetail = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $textdata2);

                $model = new ResearchConference();
                $model->con_id = (string) $fbid;
                $model->con_name = Str::limit($conName, 250);
                $model->con_even_date = $conEvenDate;
                $model->con_detail = $cleanDetail;
                $model->con_sub_deadline = $deadlineSubmission ?: '';
                $model->con_venue = $venue ?: '';
                $model->con_website = "https://facebook.com/photo.php?fbid=" . $fbid;
                if ($newImageName) {
                    $model->con_img = $newImageName;
                    $model->con_path = "uploads/datacoferenceinthai/{$newImageName}";
                    $model->con_safe_name = $newImageName;
                }
                $model->data_show = 1;
                $model->con_count = 0;
                $model->save();

                $stats['synced']++;

                // 1. Send to Make.com Webhook
                $makeStatus = $this->sendToMakeWebhook($model);

                // 2. Send Telegram Notify
                $this->sendTelegramNotify($model, $makeStatus);

                if ($progressCallback) {
                    $progressCallback("FBID: {$fbid} - บันทึกสำเร็จ: " . Str::limit($model->con_name, 50));
                }

                $itemsReport[] = [
                    'status' => 'synced',
                    'id' => $model->id,
                    'con_id' => $model->con_id,
                    'name' => $model->con_name,
                    'even_date' => $model->con_even_date,
                    'deadline' => $model->con_sub_deadline,
                    'venue' => $model->con_venue,
                    'website' => $model->con_website,
                    'con_img' => $model->con_img,
                    'image_url' => $model->image_url,
                    'detail_url' => route('backend.research_conference.show', $model->id),
                    'detail_preview' => Str::limit(strip_tags(str_replace('<br>', ' ', $model->con_detail)), 180),
                    'make_status' => $makeStatus,
                ];

            } catch (\Throwable $e) {
                $stats['failed']++;
                $itemsReport[] = [
                    'status' => 'failed',
                    'con_id' => $fbid,
                    'url' => $photoPageUrl,
                    'message' => "{$fbid} -- ข้อผิดพลาด: " . $e->getMessage(),
                ];
            }
        }

        $stats['message'] = "ดึงข้อมูลจาก Facebook เสร็จสิ้น (บันทึกใหม่: {$stats['synced']}, ข้าม: {$stats['skipped']})";
        return [
            'stats' => $stats,
            'synced' => $stats['synced'],
            'skipped' => $stats['skipped'],
            'failed' => $stats['failed'],
            'items' => $itemsReport,
            'discovered_links' => $discoveredLinks,
        ];
    }

    private function sanitizeOutput(string $buffer): string
    {
        $search = ['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s', '/<!--(.|\s)*?-->/'];
        $replace = ['>', '<', '\\1', ''];
        $buffer = preg_replace($search, $replace, $buffer);
        return str_replace("\n", '', $buffer);
    }

    private function downloadConferenceImage(string $imgUri, string $fbid): ?string
    {
        try {
            $resp = Http::timeout(15)->get(html_entity_decode($imgUri));
            if (!$resp->successful()) {
                return null;
            }

            $content = $resp->body();
            if (empty($content)) {
                return null;
            }

            $newFileName = "conth_{$fbid}.webp";
            $targetDir = storage_path('app/public/uploads/datacoferenceinthai');
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
                    if ($width > 2048 || $height > 2048) {
                        $percent = 2000 / max($width, $height);
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
            $nameExp = explode('?', $imgUri);
            $nameExpArr = explode('.', $nameExp[0]);
            $ext = strtolower(end($nameExpArr)) ?: 'jpg';
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $ext = 'jpg';
            }

            $fallbackFileName = "conth_{$fbid}.{$ext}";
            file_put_contents($targetDir . DIRECTORY_SEPARATOR . $fallbackFileName, $content);
            return $fallbackFileName;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function sendToMakeWebhook(ResearchConference $model): string
    {
        try {
            $makeNewsData = [
                'news_id' => (string) $model->id,
                'type' => 'coference',
                'title' => $model->con_name,
                'content' => $model->con_detail,
                'source_url' => route('backend.research_conference.show', $model->id),
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

    private function sendTelegramNotify(ResearchConference $model, string $makeStatus): void
    {
        try {
            $txt = strip_tags($model->con_detail, '<br>');
            $txt = mb_substr($txt, 0, 500, 'UTF-8');
            $dataArr = explode('<br>', $txt);

            $textLine = "📌 " . $model->con_name . "\n";
            $textLine .= "--------------------\n";

            foreach ($dataArr as $val) {
                $val = trim($val);
                if (empty($val)) continue;
                if (
                    str_contains($val, 'วันที่') ||
                    str_contains($val, 'วันจันทร์ที่') ||
                    str_contains($val, 'วันอังคารที่') ||
                    str_contains($val, 'วันพุธที่') ||
                    str_contains($val, 'วันพฤหัส') ||
                    str_contains($val, 'วันศุกร์ที่') ||
                    str_contains($val, 'วันเสาร์ที่') ||
                    str_contains($val, 'วันอาทิตย์ที่') ||
                    str_contains(strtolower($val), 'deadline') ||
                    str_contains(strtolower($val), 'website')
                ) {
                    $textLine .= ">> " . $val . "\n";
                }
            }

            $textLine .= "📑 รายละเอียด: " . route('backend.research_conference.show', $model->id) . "\n";
            $imageUrl = $model->image_url;

            if ($imageUrl) {
                TelegramNotificationService::sendMessageToNotify($textLine . "\n🔎 " . $imageUrl . "\n" . $makeStatus, $imageUrl);
            } else {
                TelegramNotificationService::sendMessageToNotify($textLine . "\n" . $makeStatus);
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }
    }
}
