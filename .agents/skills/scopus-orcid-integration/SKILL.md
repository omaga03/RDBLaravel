---
name: scopus-orcid-integration
description: Integration procedures for Elsevier Scopus Author Retrieval API, h-index synchronization, ORCID validation, and Thai Citizen ID checks.
---

# Scopus & Academic Profile Integration (laravel_rdb)

Use this skill when implementing or modifying researcher profile sync, Elsevier Scopus API, ORCID, or researcher identification codes.

## 1. Elsevier Scopus API Client
Fetch h-index and author metadata using Elsevier Author Retrieval API:
```php
$apiKey = config('services.scopus.api_key') ?: env('SCOPUS_API_KEY');
$instToken = config('services.scopus.inst_token') ?: env('SCOPUS_INST_TOKEN');
$authorId = trim($researcher->scopus_authorId);

$url = "https://api.elsevier.com/content/author/author_id/{$authorId}?view=METRICS";
$ch = curl_init($url);
$headers = [
    "X-ELS-APIKey: {$apiKey}",
    "Accept: application/json"
];
if ($instToken) {
    $headers[] = "X-ELS-Insttoken: {$instToken}";
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
// Handle SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
```

### Error Handling Protocol:
- **HTTP 401**: Prompt user to check university VPN or add `SCOPUS_INST_TOKEN`.
- **HTTP 404**: Scopus Author ID does not exist in Elsevier database.
- **HTTP 429**: Rate limit exceeded; wait before re-syncing.
- On HTTP 200, parse:
  `$hIndex = $data['author-retrieval-response'][0]['h-index'] ?? 0;`
  Update `researcher_hindex` and timestamp `scopus_synced_at = now()`.

## 2. Thai Citizen ID Validation (Modulo 11)
Validate 13-digit Thai National ID card before generating researcher code IDs:
```php
private function isValidThaiCitizenId(string $id): bool
{
    $clean = str_replace('-', '', $id);
    if (strlen($clean) !== 13 || !ctype_digit($clean)) {
        return false;
    }
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += (int)$clean[$i] * (13 - $i);
    }
    $check = (11 - ($sum % 11)) % 10;
    return $check === (int)$clean[12];
}
```

## 3. ORCID Formatting
Standard ORCID iD is 16 digits formatted in 4 blocks of 4 separated by hyphens (e.g. `0000-0002-1825-0097`). The final character may be an `X` (checksum).
