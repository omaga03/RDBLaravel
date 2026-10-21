<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class HtmlContentHelper
{
    /**
     * Format legacy/scraped ResearchNews detail HTML safely and cleanly for both Light and Dark mode.
     */
    public static function formatNews(?string $rawDetail, ?string $existingImageUrl = null): string
    {
        if (empty($rawDetail)) {
            return '';
        }

        $html = html_entity_decode((string) $rawDetail, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 1. Remove duplicate NRIIS title (<span id="ctl00_ContentDetail_tbNNAME">...</span>)
        $html = preg_replace('/<h[1-4][^>]*>\s*<span[^>]*id=["\']?ctl00_ContentDetail_tbNNAME["\']?[^>]*>.*?<\/span>\s*<\/h[1-4]>/isu', '', $html);
        $html = preg_replace('/<span[^>]*id=["\']?ctl00_ContentDetail_tbNNAME["\']?[^>]*>.*?<\/span>/isu', '', $html);

        // 2. Extract <iframe ... src="..."> (e.g., NRIIS embedded PDFs with malformed quotes src='...pdf'')
        $embeds = [];
        $html = preg_replace_callback(
            '/<iframe\b[^>]*?\bsrc\s*=\s*[\'"]+(https?:\/\/[^\'"\s>]+)[\'"]*[^>]*>.*?<\/iframe>/isu',
            function ($m) use (&$embeds) {
                $url = trim($m[1]);
                $key = '[[EMBED_PDF_' . count($embeds) . ']]';
                $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                $embeds[$key] = '<div class="my-3 p-3 rounded-3 bg-body-tertiary border">'
                    . '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">'
                    . '<div class="d-flex align-items-center text-body fw-semibold">'
                    . '<i class="bi bi-file-earmark-pdf-fill text-danger fs-4 me-2"></i>'
                    . '<span>เอกสารประกาศแนบ (PDF)</span>'
                    . '</div>'
                    . '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">'
                    . '<i class="bi bi-box-arrow-up-right me-1"></i>เปิดดูเอกสารในแท็บใหม่'
                    . '</a>'
                    . '</div>'
                    . '<div class="ratio ratio-16x9 rounded overflow-hidden border bg-body" style="min-height: 600px;">'
                    . '<iframe src="' . $safeUrl . '" loading="lazy" title="เอกสารประกาศ PDF"></iframe>'
                    . '</div>'
                    . '</div>';
                return $key;
            },
            $html
        );

        // 3. Extract <img ... src="..."> and normalize
        $html = preg_replace_callback(
            '/<img\b[^>]*?\bsrc\s*=\s*[\'"]+(https?:\/\/[^\'">\s]+)[\'"]*[^>]*\/?>/isu',
            function ($m) use (&$embeds, $existingImageUrl) {
                $url = trim($m[1]);
                if ($existingImageUrl && $url === $existingImageUrl) {
                    return '';
                }
                $key = '[[EMBED_IMG_' . count($embeds) . ']]';
                $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                $embeds[$key] = '<div class="text-center my-3">'
                    . '<img src="' . $safeUrl . '" class="img-fluid rounded shadow-sm border" style="max-height: 520px;" loading="lazy" alt="ภาพประกอบข่าว">'
                    . '</div>';
                return $key;
            },
            $html
        );

        // 4. Normalize <a ... href="..."> (fixes broken quotes like href='https://..." in NRIIS CKEditor links)
        $html = preg_replace_callback(
            '/<a\b[^>]*?\bhref\s*=\s*[\'"]+(https?:\/\/[^\'">\s]+|mailto:[^\'">\s]+)[\'"][^>]*>(.*?)<\/a>/isu',
            function ($m) use (&$embeds) {
                $url = trim($m[1]);
                $label = trim(strip_tags($m[2]));
                if ($label === '') {
                    $label = $url;
                }
                $key = '[[EMBED_LINK_' . count($embeds) . ']]';
                $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                $isFile = preg_match('/\.(pdf|docx?|xlsx?|zip|rar)$/i', parse_url($url, PHP_URL_PATH) ?? '');
                if ($isFile) {
                    $embeds[$key] = '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer" class="d-inline-flex align-items-center text-decoration-none my-1">'
                        . '<i class="bi bi-file-earmark-arrow-down me-1 text-primary"></i>' . $safeLabel . '</a>';
                } else {
                    $embeds[$key] = '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">' . $safeLabel . '</a>';
                }
                return $key;
            },
            $html
        );

        // 5. Convert table row endings and block endings to <br> before stripping layout tables
        $html = preg_replace('/<\/tr\s*>/iu', '<br>', $html);
        $html = preg_replace('/<\/(h[1-6]|div)\s*>/iu', '<br>', $html);
        $html = preg_replace('/<hr\s*\/?>/iu', ' [[EMBED_HR]] ', $html);

        // 6. Strip all remaining HTML tags except safe basic text formatting
        $html = strip_tags($html, '<p><br><b><strong><i><em><u><ul><ol><li><sub><sup>');

        // 7. Remove all inline attributes (style, class, id, align) from remaining tags so Dark Mode is never broken
        $html = preg_replace('/<([a-z0-9]+)\b[^>]*>/iu', '<$1>', $html);

        // 8. Auto-link bare http/https URLs that were not in <a> tags
        $html = preg_replace_callback(
            '/(?<![\'"=])(https?:\/\/[^\s<>"\'`\[\]]+)/iu',
            function ($m) use (&$embeds) {
                $url = rtrim($m[1], '.,;:)');
                $key = '[[EMBED_LINK_' . count($embeds) . ']]';
                $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                $embeds[$key] = '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">' . $safeUrl . '</a>';
                return $key;
            },
            $html
        );

        // 9. Clean up excessive whitespace / empty paragraphs / multiple <br>
        $html = str_replace("\xc2\xa0", ' ', $html);
        $html = str_replace('&nbsp;', ' ', $html);
        $html = preg_replace('/<p>\s*<\/p>/iu', '', $html);
        $html = preg_replace('/<(b|strong|i|em|u)>\s*<\/\1>/iu', '', $html);
        $html = preg_replace('/(\s*<br>\s*){3,}/iu', '<br><br>', $html);
        $html = preg_replace('/^(\s*<br>\s*)+/iu', '', $html);
        $html = preg_replace('/(\s*<br>\s*)+$/iu', '', $html);

        // 10. Balance tags and restore HR + safe embeds (2 passes for nested <a><img></a>)
        $html = self::balanceTags($html);
        $html = preg_replace('/(?:\s*<br>\s*)*\[\[EMBED_HR\]\](?:\s*<br>\s*)*/iu', '<hr class="my-3 opacity-25">', $html);
        if (!empty($embeds)) {
            $html = strtr($html, $embeds);
            $html = strtr($html, $embeds);
        }

        return trim($html);
    }

    /**
     * Extract plain text summary for ResearchNews list views.
     */
    public static function plainSummaryNews(?string $rawDetail, int $limit = 180): string
    {
        if (empty($rawDetail)) {
            return '';
        }

        $html = html_entity_decode((string) $rawDetail, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = preg_replace('/<span[^>]*id=["\']?ctl00_ContentDetail_tbNNAME["\']?[^>]*>.*?<\/span>/isu', '', $html);
        $html = preg_replace('/<iframe\b[^>]*>.*?<\/iframe>/isu', '', $html);
        $text = strip_tags($html);
        $text = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $text);
        $text = preg_replace('/\s+/u', ' ', trim($text));

        return Str::limit($text, $limit);
    }

    /**
     * Format legacy/scraped ResearchConference detail HTML safely and cleanly for both Light and Dark mode.
     */
    public static function formatConference(?string $rawDetail): string
    {
        if (empty($rawDetail)) {
            return '';
        }

        $content = html_entity_decode((string) $rawDetail, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 1. Replace legacy @@@ line-break delimiters with <br>
        $content = preg_replace('/\s*@{3,}\s*/u', '<br>', $content);

        // 2. Replace <br>.<br> (scraper spacer pattern) with <br><br> without stripping bullet points (- or •)
        $content = preg_replace('/<br\s*\/?>\s*\.\s*(?=<br\s*\/?>)/iu', '<br>', $content);
        $content = preg_replace('/^\s*\.\s*<br\s*\/?>/iu', '', $content);

        // 3. Extract existing <a ... href="..."> tags safely before processing plain-text URLs
        $links = [];
        $content = preg_replace_callback(
            '/<a\b[^>]*?\bhref\s*=\s*[\'"]+(https?:\/\/[^\'">\s]+|mailto:[^\'">\s]+)[\'"][^>]*>(.*?)<\/a>/isu',
            function ($m) use (&$links) {
                $url = trim($m[1]);
                $label = trim(strip_tags($m[2]));
                if ($label === '') {
                    $label = $url;
                }
                // Ignore broken hashtag links like https://#CMSD2022
                if (preg_match('/^https?:\/\/#/i', $url)) {
                    return htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                }
                $key = '[[CONF_LINK_' . count($links) . ']]';
                $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                $links[$key] = '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">' . $safeLabel . '</a>';
                return $key;
            },
            $content
        );

        // 4. Convert section dividers (========= or ---------) into clean HR placeholders (surrounded by spaces so URLs don't glue to them)
        $content = preg_replace('/(?:<br\s*\/?>|\r?\n|\s)*[=]{4,}(?:<br\s*\/?>|\r?\n|\s)*/iu', ' [[CONF_HR_MAIN]] ', $content);
        $content = preg_replace('/(?:<br\s*\/?>|\r?\n|\s)*[-_]{5,}(?:<br\s*\/?>|\r?\n|\s)*/iu', ' [[CONF_HR_SUB]] ', $content);

        // 5. Strip all remaining HTML tags except safe basic formatting
        $content = strip_tags($content, '<p><br><b><strong><i><em><u><ul><ol><li><sub><sup>');
        $content = preg_replace('/<([a-z0-9]+)\b[^>]*>/iu', '<$1>', $content);

        // 6. Auto-link bare http/https URLs that were not in <a> tags
        $content = preg_replace_callback(
            '/(?<![\'"=])(https?:\/\/[^\s<>"\'`\[\]]+)/iu',
            function ($m) use (&$links) {
                $url = rtrim($m[1], '.,;:)');
                $key = '[[CONF_LINK_' . count($links) . ']]';
                $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                $links[$key] = '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">' . $safeUrl . '</a>';
                return $key;
            },
            $content
        );

        // 7. Normalize <br> and whitespace
        $content = str_replace("\xc2\xa0", ' ', $content);
        $content = str_replace('&nbsp;', ' ', $content);
        $content = preg_replace('/<p>\s*<\/p>/iu', '', $content);
        $content = preg_replace('/(\s*<br>\s*){3,}/iu', '<br><br>', $content);
        $content = preg_replace('/^(\s*<br>\s*)+/iu', '', $content);
        $content = preg_replace('/(\s*<br>\s*)+$/iu', '', $content);

        // 8. Balance tags and restore HR + links
        $content = self::balanceTags($content);
        $content = preg_replace('/(?:\s*<br>\s*)*\[\[CONF_HR_MAIN\]\](?:\s*<br>\s*)*/iu', '<hr class="my-3 opacity-25">', $content);
        $content = preg_replace('/(?:\s*<br>\s*)*\[\[CONF_HR_SUB\]\](?:\s*<br>\s*)*/iu', '<hr class="my-2 opacity-25" style="border-top-style: dashed;">', $content);
        if (!empty($links)) {
            $content = strtr($content, $links);
        }

        return trim($content);
    }

    /**
     * Extract plain text summary for ResearchConference list views.
     */
    public static function plainSummaryConference(?string $rawDetail, int $limit = 180): string
    {
        if (empty($rawDetail)) {
            return '';
        }

        $text = html_entity_decode((string) $rawDetail, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/@{3,}/u', ' ', $text);
        $text = preg_replace('/<br\s*\/?>\s*\.\s*<br\s*\/?>/iu', ' ', $text);
        $text = preg_replace('/[=\-_]{4,}/u', ' ', $text);
        $text = strip_tags($text);
        $text = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $text);
        $text = preg_replace('/\s+/u', ' ', trim($text));

        return Str::limit($text, $limit);
    }

    /**
     * Ensure all allowed inline/block tags are balanced so unclosed tags never break page layout.
     */
    public static function balanceTags(string $html, array $tags = ['p', 'b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'sub', 'sup']): string
    {
        foreach ($tags as $tag) {
            $openCount = preg_match_all('/<' . $tag . '\b[^>]*>/i', $html);
            $closeCount = preg_match_all('/<\/' . $tag . '\s*>/i', $html);
            if ($openCount > $closeCount) {
                $html .= str_repeat('</' . $tag . '>', $openCount - $closeCount);
            } elseif ($closeCount > $openCount) {
                $diff = $closeCount - $openCount;
                $html = preg_replace('/<\/' . $tag . '\s*>/i', '', $html, $diff);
            }
        }
        return $html;
    }
}
