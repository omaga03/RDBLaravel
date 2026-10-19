<?php

namespace App\Helpers;

class AbstractHelper
{
    /**
     * Split a bilingual abstract (Thai & English) based on 3 or more consecutive <br> tags or newlines,
     * and intelligently group chunks by language.
     *
     * @param string|null $rawAbstract
     * @return array Array with 1 element (single abstract) or 2 elements [Thai, English]
     */
    public static function split(?string $rawAbstract): array
    {
        if (empty($rawAbstract) || empty(trim(strip_tags($rawAbstract)))) {
            return [];
        }

        // 1. Split by 3 or more consecutive <br> tags (with optional spaces, newlines, or &nbsp;)
        $chunks = preg_split('/(?:<br\s*\/?>[\s\r\n]*(?:&nbsp;[\s\r\n]*)?){3,}/i', trim($rawAbstract));
        
        // 2. Fallback: If only 1 chunk, try 3 or more consecutive empty paragraphs or newlines
        if (count($chunks) < 2) {
            $chunks = preg_split('/(?:(?:\s*<p>(?:&nbsp;|\s)*<\/p>\s*)|(?:\r?\n\s*)){3,}/i', trim($rawAbstract));
        }

        // Filter out completely empty chunks
        $chunks = array_values(array_filter($chunks, function ($c) {
            return !empty(trim(strip_tags($c)));
        }));

        if (count($chunks) < 2) {
            return $chunks;
        }

        // 3. Intelligently group chunks into Thai and English
        $thaiChunks = [];
        $engChunks = [];

        foreach ($chunks as $chunk) {
            $clean = strip_tags($chunk);
            $thaiCount = preg_match_all('/[\x{0E00}-\x{0E7F}]/u', $clean);
            $engCount = preg_match_all('/[a-zA-Z]/', $clean);
            
            if ($engCount > $thaiCount && $engCount > 20) {
                $engChunks[] = $chunk;
            } else {
                if (empty($engChunks)) {
                    $thaiChunks[] = $chunk;
                } else {
                    if ($thaiCount > $engCount) {
                        $thaiChunks[] = $chunk;
                    } else {
                        $engChunks[] = $chunk;
                    }
                }
            }
        }

        if (!empty($thaiChunks) && !empty($engChunks)) {
            $thai = implode('<br><br>', $thaiChunks);
            $eng = implode('<br><br>', $engChunks);
            $thai = trim(preg_replace('/^(?:\s*<br\s*\/?>\s*)+|(?:\s*<br\s*\/?>\s*)+$/i', '', trim($thai)));
            $eng = trim(preg_replace('/^(?:\s*<br\s*\/?>\s*)+|(?:\s*<br\s*\/?>\s*)+$/i', '', trim($eng)));
            return [$thai, $eng];
        }

        // Fallback if only 1 language detected but 3+ line breaks exist:
        $part0 = trim(preg_replace('/^(?:\s*<br\s*\/?>\s*)+|(?:\s*<br\s*\/?>\s*)+$/i', '', trim($chunks[0])));
        $part1 = trim(preg_replace('/^(?:\s*<br\s*\/?>\s*)+|(?:\s*<br\s*\/?>\s*)+$/i', '', trim(implode('<br><br>', array_slice($chunks, 1)))));
        return [$part0, $part1];
    }
}
