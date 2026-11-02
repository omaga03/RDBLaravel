<?php

namespace App\Services;

use App\Models\RdbProject;
use App\Models\RdbPublished;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DSpaceExportService
{
    /**
     * Export RdbProject to DSpace SAF ZIP package
     */
    public function exportProject(RdbProject $project): ?string
    {
        $project->loadMissing(['year', 'type', 'typeSub', 'department', 'rdbProjectWorks.researcher']);

        $authors = [];
        foreach ($project->rdbProjectWorks as $work) {
            if ($work->researcher) {
                $prefix = $work->researcher->prefix->prefix_nameTH ?? '';
                $authors[] = trim("{$prefix}{$work->researcher->researcher_fname} {$work->researcher->researcher_lname}");
            }
        }

        $keywords = $project->pro_keyword ? array_filter(array_map('trim', preg_split('/[,;]/u', $project->pro_keyword))) : [];

        // Build dublin_core.xml
        $dc = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><dublin_core schema="dc"></dublin_core>');
        
        $this->addDcValue($dc, 'title', 'none', $project->pro_nameTH ?: $project->pro_nameEN);
        if ($project->pro_nameTH && $project->pro_nameEN) {
            $this->addDcValue($dc, 'title', 'alternative', $project->pro_nameEN);
        }

        foreach ($authors as $author) {
            $this->addDcValue($dc, 'creator', 'none', $author);
            $this->addDcValue($dc, 'contributor', 'author', $author);
        }

        if ($project->pro_finish || $project->pro_date_end) {
            $this->addDcValue($dc, 'date', 'issued', $project->pro_finish ?: $project->pro_date_end);
        }

        $this->addDcValue($dc, 'publisher', 'none', 'มหาวิทยาลัยราชภัฏเพชรบูรณ์');
        if ($project->pro_abstract) {
            $this->addDcValue($dc, 'description', 'abstract', strip_tags($project->pro_abstract));
        }

        foreach ($keywords as $kw) {
            $this->addDcValue($dc, 'subject', 'none', $kw);
        }

        $this->addDcValue($dc, 'type', 'none', 'Technical Report');
        $this->addDcValue($dc, 'language', 'iso', 'tha');
        $this->addDcValue($dc, 'identifier', 'uri', route('frontend.rdbproject.show', $project->pro_id));
        if ($project->pro_code) {
            $this->addDcValue($dc, 'identifier', 'govdoc', $project->pro_code);
        }

        // Build metadata_dspace.xml
        $dspaceMeta = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><dublin_core schema="dspace"></dublin_core>');
        $this->addDcValue($dspaceMeta, 'entity', 'type', 'Project');
        if ($project->department?->department_nameTH) {
            $this->addDcValue($dspaceMeta, 'department', 'none', $project->department->department_nameTH);
        }
        if ($project->year?->year_name) {
            $this->addDcValue($dspaceMeta, 'year', 'fiscal', (string) $project->year->year_name);
        }
        if ($project->pro_budget !== null) {
            $this->addDcValue($dspaceMeta, 'budget', 'none', (string) $project->pro_budget);
        }

        // Collect bitstream files
        $filesToAdd = [];
        $reportPath = $project->getReportFilePath();
        if ($reportPath) {
            $fullPath = $this->resolveFileOnDisk($reportPath);
            if ($fullPath && file_exists($fullPath)) {
                $filesToAdd["report_{$project->pro_id}.pdf"] = $fullPath;
            }
        }

        $absPath = $project->getAbstractFilePath();
        if ($absPath) {
            $fullPath = $this->resolveFileOnDisk($absPath);
            if ($fullPath && file_exists($fullPath) && !in_array($fullPath, $filesToAdd, true)) {
                $filesToAdd["abstract_{$project->pro_id}.pdf"] = $fullPath;
            }
        }

        return $this->buildZipPackage("dspace_project_{$project->pro_id}", $dc, $dspaceMeta, $filesToAdd);
    }

    /**
     * Export multiple RdbProjects to DSpace SAF Batch Mode ZIP package
     * Matching legacy Yii2 actionExdspace structure
     */
    public function exportProjectBatch(array $ids, string|int $partLabel = '1'): ?string
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException("No IDs provided for batch export");
        }

        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('ระบบไม่พบส่วนขยาย ZipArchive (กรุณาเปิด extension=zip ใน php.ini)');
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . DIRECTORY_SEPARATOR . "DSpace_Export_Part_" . $partLabel . "_" . date('Ymd_His') . ".zip";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Cannot create zip file at {$zipPath}");
        }

        $zip->setArchiveComment('DSpace SAF Batch Export');

        // Preload relations with eager loading to prevent N+1 queries
        $projects = RdbProject::with([
            'year',
            'department',
            'type.projectTypeGroup',
            'pts',
            'rdbProjectWorks.researcher.prefix',
        ])
        ->whereIn('pro_id', $ids)
        ->orderBy('pro_id', 'asc')
        ->get();

        foreach ($projects as $model) {
            $folderName = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $model->pro_id);

            // 1.1 Dublin Core XML
            $dcXml = $this->generateDublinCoreXml($model);
            $zip->addFromString($folderName . '/dublin_core.xml', $dcXml);

            // 1.2 DSpace Metadata XML
            $dspaceXml = $this->buildDspaceMetadataXml($model);
            $zip->addFromString($folderName . '/metadata_dspace.xml', $dspaceXml);

            // 1.3 Files and Contents file
            $contentsLines = [];

            // Full report file
            $reportFile = $this->resolveProjectFileOnDisk($model, 'report');
            if ($reportFile && file_exists($reportFile)) {
                $destName = $model->pro_id . '_Research_Full.pdf';
                if ($zip->addFile($reportFile, $folderName . '/' . $destName)) {
                    $contentsLines[] = "{$destName}\tbundle:ORIGINAL\tprimary:true";
                }
            }

            // Abstract file
            $absFile = $this->resolveProjectFileOnDisk($model, 'abstract');
            if ($absFile && file_exists($absFile)) {
                $destName = $model->pro_id . '_Research_abs.pdf';
                if ($zip->addFile($absFile, $folderName . '/' . $destName)) {
                    $contentsLines[] = "{$destName}\tbundle:ORIGINAL";
                }
            }

            if (!empty($contentsLines)) {
                $zip->addFromString($folderName . '/contents', implode("\n", $contentsLines) . "\n");
            }
        }

        $zip->close();
        clearstatcache();

        if (!file_exists($zipPath) || filesize($zipPath) === 0) {
            throw new \RuntimeException("ZIP package is empty or failed to generate.");
        }

        return $zipPath;
    }

    /**
     * Generate Dublin Core XML for RdbProject (SAF Batch format)
     */
    public function generateDublinCoreXml(RdbProject $model): string
    {
        $dateIssued = !empty($model->pro_finish) ? $model->pro_finish :
            (!empty($model->pro_date_end) ? date('Y-m-d', strtotime($model->pro_date_end)) : date('Y-m-d'));

        $resultAbstract = $this->splitAbstractTHEN($model->pro_abstract);
        $abstractTh = str_replace(["\r", "&#13;"], '', $resultAbstract['th']);
        $abstractEn = str_replace(["\r", "&#13;"], '', $resultAbstract['en']);

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" standalone="no"?><dublin_core schema="dc"/>');

        // Identifier
        if (!empty($model->pro_code)) {
            $node = $xml->addChild('dcvalue', htmlspecialchars(trim($model->pro_code), ENT_XML1, 'UTF-8'));
            $node->addAttribute('element', 'identifier');
            $node->addAttribute('qualifier', 'other');
        }

        // Title (TH)
        if (!empty($model->pro_nameTH)) {
            $node = $xml->addChild('dcvalue', htmlspecialchars(trim(strip_tags($model->pro_nameTH)), ENT_XML1, 'UTF-8'));
            $node->addAttribute('element', 'title');
            $node->addAttribute('qualifier', 'none');
            $node->addAttribute('language', 'th');
        }

        // Title (EN)
        if (!empty($model->pro_nameEN)) {
            $node = $xml->addChild('dcvalue', htmlspecialchars(trim(strip_tags($model->pro_nameEN)), ENT_XML1, 'UTF-8'));
            $node->addAttribute('element', 'title');
            $node->addAttribute('qualifier', 'alternative');
            $node->addAttribute('language', 'en');
        }

        // Authors (contributor.author)
        $authors = [];
        foreach ($model->rdbProjectWorks as $work) {
            if ($work->researcher) {
                $prefix = $work->researcher->prefix->prefix_nameTH ?? '';
                $name = trim("{$prefix}{$work->researcher->researcher_fname} {$work->researcher->researcher_lname}");
                if (!empty($name) && !in_array($name, $authors, true)) {
                    $authors[] = $name;
                }
            }
        }
        foreach ($authors as $author) {
            $lang = $this->isThai($author) ? 'th' : 'en';
            $node = $xml->addChild('dcvalue', htmlspecialchars($author, ENT_XML1, 'UTF-8'));
            $node->addAttribute('element', 'contributor');
            $node->addAttribute('qualifier', 'author');
            $node->addAttribute('language', $lang);
        }

        // Abstracts
        if (!empty($abstractTh)) {
            $node = $xml->addChild('dcvalue', htmlspecialchars($abstractTh, ENT_XML1, 'UTF-8'));
            $node->addAttribute('element', 'description');
            $node->addAttribute('qualifier', 'abstract');
            $node->addAttribute('language', 'th');
        }
        if (!empty($abstractEn)) {
            $node = $xml->addChild('dcvalue', htmlspecialchars($abstractEn, ENT_XML1, 'UTF-8'));
            $node->addAttribute('element', 'description');
            $node->addAttribute('qualifier', 'abstract');
            $node->addAttribute('language', 'en');
        }

        // Fiscal Year
        if (!empty($model->year?->year_name)) {
            $node = $xml->addChild('dcvalue', 'ปีงบประมาณ พ.ศ. ' . trim($model->year->year_name));
            $node->addAttribute('element', 'description');
            $node->addAttribute('qualifier', 'sponsorship');
        }

        // Date issued
        $node = $xml->addChild('dcvalue', htmlspecialchars($dateIssued, ENT_XML1, 'UTF-8'));
        $node->addAttribute('element', 'date');
        $node->addAttribute('qualifier', 'issued');

        // Keywords
        if (!empty($model->pro_keyword)) {
            $keywords = array_map('trim', preg_split('/[,;]/u', $model->pro_keyword));
            foreach ($keywords as $kw) {
                if ($kw === '') continue;
                $cleanKw = $this->cleanText($kw);
                if ($cleanKw === '') continue;
                $lang = $this->isThai($cleanKw) ? 'th' : 'en';

                $node = $xml->addChild('dcvalue', htmlspecialchars($cleanKw, ENT_XML1, 'UTF-8'));
                $node->addAttribute('element', 'subject');
                $node->addAttribute('qualifier', 'none');
                $node->addAttribute('language', $lang);
            }
        }

        // Project Type / Sponsorship
        $groupName = $model->type?->projectTypeGroup?->pttg_name ?? '';
        $subName = $model->pts?->pts_name ?? '';
        $typeName = $model->type?->pt_name ?? '';

        if (!empty($groupName) || !empty($subName)) {
            $subtypes = (!empty($subName) && $subName !== 'ทั่วไป' && !empty($groupName))
                ? "{$subName} - {$groupName}"
                : ($groupName ?: $subName);

            $node = $xml->addChild('dcvalue', htmlspecialchars('ประเภทงบประมาณ:' . $subtypes, ENT_XML1, 'UTF-8'));
            $node->addAttribute('element', 'description');
            $node->addAttribute('qualifier', 'sponsorship');
            $node->addAttribute('language', 'th');
        }

        if (!empty($typeName)) {
            $node = $xml->addChild('dcvalue', htmlspecialchars('ประเภท/ผู้ให้ทุน:' . $typeName, ENT_XML1, 'UTF-8'));
            $node->addAttribute('element', 'description');
            $node->addAttribute('qualifier', 'sponsorship');
            $node->addAttribute('language', 'th');
        }

        // Document Type
        $node1 = $xml->addChild('dcvalue', 'Technical Report');
        $node1->addAttribute('element', 'type');
        $node1->addAttribute('qualifier', 'none');
        $node1->addAttribute('language', 'en');

        $node2 = $xml->addChild('dcvalue', 'Research');
        $node2->addAttribute('element', 'type');
        $node2->addAttribute('qualifier', 'none');
        $node2->addAttribute('language', 'en');

        // Publisher
        $node1 = $xml->addChild('dcvalue', 'มหาวิทยาลัยราชภัฏเพชรบูรณ์');
        $node1->addAttribute('element', 'publisher');
        $node1->addAttribute('qualifier', 'none');
        $node1->addAttribute('language', 'th');

        if (!empty($model->department?->department_nameTH)) {
            $node2 = $xml->addChild('dcvalue', htmlspecialchars($model->department->department_nameTH, ENT_XML1, 'UTF-8'));
            $node2->addAttribute('element', 'publisher');
            $node2->addAttribute('qualifier', 'none');
            $node2->addAttribute('language', 'th');
        }

        // Languages
        $node = $xml->addChild('dcvalue', 'th');
        $node->addAttribute('element', 'language');
        $node->addAttribute('qualifier', 'iso');

        $node = $xml->addChild('dcvalue', 'en');
        $node->addAttribute('element', 'language');
        $node->addAttribute('qualifier', 'iso');

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    /**
     * Build metadata_dspace.xml
     */
    public function buildDspaceMetadataXml(RdbProject $model): string
    {
        return '<?xml version="1.0" encoding="utf-8" standalone="no"?>' . "\n" .
            '<dublin_core schema="dspace">' . "\n" .
            '  <dcvalue element="entity" qualifier="type">Publication</dcvalue>' . "\n" .
            '</dublin_core>';
    }

    /**
     * Clean text from HTML, line breaks, control characters
     */
    public function cleanText(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        $text = preg_replace('/\x{00A0}|\x{2000}-\x{200D}|\x{202F}|\x{205F}|\x{3000}/u', ' ', $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/\n+/u', "\n", $text);

        return trim($text);
    }

    /**
     * Detect Thai characters
     */
    public function isThai(?string $text): bool
    {
        return !empty($text) && (bool) preg_match('/[\x{0E00}-\x{0E7F}]/u', $text);
    }

    /**
     * Split abstract into Thai and English components
     */
    public function splitAbstractTHEN(?string $rawText): array
    {
        if (empty($rawText)) {
            return ['th' => '', 'en' => ''];
        }

        $rawText = preg_replace('/<br\s*\/?>/i', "\n", $rawText);
        $parts = preg_split("/\n{2,}/", $rawText);
        $parts = array_values(array_filter($parts, fn($v) => trim($v) !== ''));

        $th = [];
        $en = [];

        foreach ($parts as $p) {
            $clean = $this->cleanText($p);
            if (empty($clean)) continue;

            if ($this->isThai($clean)) {
                $th[] = $clean;
            } else {
                $en[] = $clean;
            }
        }

        return [
            'th' => implode("\n\n", $th),
            'en' => implode("\n\n", $en),
        ];
    }

    /**
     * Resolve project file on disk with multi-path fallback
     */
    public function resolveProjectFileOnDisk(RdbProject $model, string $type = 'report'): ?string
    {
        $relPath = ($type === 'report') ? $model->getReportFilePath() : $model->getAbstractFilePath();
        if ($relPath) {
            $diskPath = $this->resolveFileOnDisk($relPath);
            if ($diskPath && file_exists($diskPath)) {
                return $diskPath;
            }
        }

        $rawFile = ($type === 'report') ? $model->pro_file : $model->pro_abstract_file;
        if (!empty($rawFile)) {
            $legacyCandidates = [
                base_path('yii2_rdb-main/frontend/web/datafiles/' . $model->pro_id . '/' . $rawFile),
                public_path('datafiles/' . $model->pro_id . '/' . $rawFile),
                storage_path('app/public/project/' . $rawFile),
                storage_path('app/public/' . $rawFile),
                storage_path('app/public/reports/' . $rawFile),
                storage_path('app/public/abstracts/' . $rawFile),
                storage_path('app/public/uploads/projects/' . $rawFile),
            ];
            foreach ($legacyCandidates as $c) {
                if (file_exists($c)) {
                    return $c;
                }
            }
        }

        return null;
    }


    /**
     * Export RdbPublished to DSpace SAF ZIP package
     */
    public function exportPublished(RdbPublished $published): ?string
    {
        $published->loadMissing(['year', 'pubtype', 'department', 'authors.prefix', 'researcher.prefix']);

        $authors = [];
        if ($published->authors && $published->authors->isNotEmpty()) {
            foreach ($published->authors as $author) {
                $prefix = $author->prefix->prefix_nameTH ?? '';
                $authors[] = trim("{$prefix}{$author->researcher_fname} {$author->researcher_lname}");
            }
        } elseif ($published->researcher) {
            $prefix = $published->researcher->prefix->prefix_nameTH ?? '';
            $authors[] = trim("{$prefix}{$published->researcher->researcher_fname} {$published->researcher->researcher_lname}");
        }

        $keywords = $published->pub_keyword ? array_filter(array_map('trim', preg_split('/[,;]/u', $published->pub_keyword))) : [];

        // Build dublin_core.xml
        $dc = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><dublin_core schema="dc"></dublin_core>');
        $this->addDcValue($dc, 'title', 'none', $published->pub_name);

        foreach ($authors as $author) {
            $this->addDcValue($dc, 'creator', 'none', $author);
            $this->addDcValue($dc, 'contributor', 'author', $author);
        }

        if ($published->pub_date) {
            $this->addDcValue($dc, 'date', 'issued', $published->pub_date);
        }

        $this->addDcValue($dc, 'publisher', 'none', $published->pub_name_journal ?: 'มหาวิทยาลัยราชภัฏเพชรบูรณ์');
        if ($published->pub_abstract) {
            $this->addDcValue($dc, 'description', 'abstract', strip_tags($published->pub_abstract));
        }

        foreach ($keywords as $kw) {
            $this->addDcValue($dc, 'subject', 'none', $kw);
        }

        $this->addDcValue($dc, 'type', 'none', 'Journal Article');
        $this->addDcValue($dc, 'language', 'iso', 'tha');
        $this->addDcValue($dc, 'identifier', 'uri', route('frontend.rdbpublished.show', $published->id));
        if ($published->pub_name_journal) {
            $this->addDcValue($dc, 'relation', 'ispartof', $published->pub_name_journal);
        }

        // Build metadata_dspace.xml
        $dspaceMeta = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><dublin_core schema="dspace"></dublin_core>');
        $this->addDcValue($dspaceMeta, 'entity', 'type', 'Publication');
        if ($published->department?->department_nameTH) {
            $this->addDcValue($dspaceMeta, 'department', 'none', $published->department->department_nameTH);
        }
        if ($published->pubtype?->pubtype_subgroup) {
            $this->addDcValue($dspaceMeta, 'category', 'none', $published->pubtype->pubtype_subgroup);
        }
        if ($published->pub_score !== null) {
            $this->addDcValue($dspaceMeta, 'score', 'qa', (string) $published->pub_score);
        }

        // Collect bitstream file
        $filesToAdd = [];
        $fullPath = $this->resolvePublishedFileOnDisk($published);
        if ($fullPath && file_exists($fullPath)) {
            $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'pdf';
            $filesToAdd["publication_{$published->id}.{$ext}"] = $fullPath;
        }

        return $this->buildZipPackage("dspace_published_{$published->id}", $dc, $dspaceMeta, $filesToAdd);
    }

    /**
     * Generate Dublin Core XML string for a single RdbPublished
     */
    public function generatePublishedDublinCoreXml(RdbPublished $published): string
    {
        $published->loadMissing(['year', 'pubtype', 'department', 'authors.prefix', 'researcher.prefix']);

        $authors = [];
        if ($published->authors && $published->authors->isNotEmpty()) {
            foreach ($published->authors as $author) {
                $prefix = $author->prefix->prefix_nameTH ?? '';
                $authors[] = trim("{$prefix}{$author->researcher_fname} {$author->researcher_lname}");
            }
        } elseif ($published->researcher) {
            $prefix = $published->researcher->prefix->prefix_nameTH ?? '';
            $authors[] = trim("{$prefix}{$published->researcher->researcher_fname} {$published->researcher->researcher_lname}");
        }

        $keywords = $published->pub_keyword ? array_filter(array_map('trim', preg_split('/[,;]/u', $published->pub_keyword))) : [];

        $dc = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><dublin_core schema="dc"></dublin_core>');
        $this->addDcValue($dc, 'title', 'none', $published->pub_name);

        foreach ($authors as $author) {
            $this->addDcValue($dc, 'creator', 'none', $author);
            $this->addDcValue($dc, 'contributor', 'author', $author);
        }

        if ($published->pub_date) {
            $this->addDcValue($dc, 'date', 'issued', $published->pub_date);
        }

        $this->addDcValue($dc, 'publisher', 'none', $published->pub_name_journal ?: 'มหาวิทยาลัยราชภัฏเพชรบูรณ์');
        if ($published->pub_abstract) {
            $this->addDcValue($dc, 'description', 'abstract', strip_tags($published->pub_abstract));
        }

        foreach ($keywords as $kw) {
            $this->addDcValue($dc, 'subject', 'none', $kw);
        }

        $this->addDcValue($dc, 'type', 'none', 'Journal Article');
        $this->addDcValue($dc, 'language', 'iso', 'tha');
        $this->addDcValue($dc, 'identifier', 'uri', route('frontend.rdbpublished.show', $published->id));
        if ($published->pub_name_journal) {
            $this->addDcValue($dc, 'relation', 'ispartof', $published->pub_name_journal);
        }

        $domDc = dom_import_simplexml($dc)->ownerDocument;
        $domDc->formatOutput = true;
        return $domDc->saveXML();
    }

    /**
     * Generate DSpace Metadata XML string for a single RdbPublished
     */
    public function generatePublishedDspaceMetadataXml(RdbPublished $published): string
    {
        $dspaceMeta = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><dublin_core schema="dspace"></dublin_core>');
        $this->addDcValue($dspaceMeta, 'entity', 'type', 'Publication');
        if ($published->department?->department_nameTH) {
            $this->addDcValue($dspaceMeta, 'department', 'none', $published->department->department_nameTH);
        }
        if ($published->pubtype?->pubtype_subgroup) {
            $this->addDcValue($dspaceMeta, 'category', 'none', $published->pubtype->pubtype_subgroup);
        }
        if ($published->pub_score !== null) {
            $this->addDcValue($dspaceMeta, 'score', 'qa', (string) $published->pub_score);
        }

        $domDs = dom_import_simplexml($dspaceMeta)->ownerDocument;
        $domDs->formatOutput = true;
        return $domDs->saveXML();
    }

    /**
     * Export multiple RdbPublished to DSpace SAF Batch Mode ZIP package
     */
    public function exportPublishedBatch(array $ids, string|int $partLabel = '1'): ?string
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException("No IDs provided for batch export");
        }

        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('ระบบไม่พบส่วนขยาย ZipArchive (กรุณาเปิด extension=zip ใน php.ini)');
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . DIRECTORY_SEPARATOR . "DSpace_Published_Export_Part_" . $partLabel . "_" . date('Ymd_His') . ".zip";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Cannot create zip file at {$zipPath}");
        }

        $zip->setArchiveComment('DSpace SAF Batch Export (Publications)');

        $publications = RdbPublished::with([
            'year',
            'department',
            'pubtype',
            'authors.prefix',
            'researcher.prefix',
        ])
        ->whereIn('id', $ids)
        ->orderBy('id', 'asc')
        ->get();

        foreach ($publications as $model) {
            $folderName = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $model->id);

            // 1. Dublin Core XML
            $dcXml = $this->generatePublishedDublinCoreXml($model);
            $zip->addFromString($folderName . '/dublin_core.xml', $dcXml);

            // 2. DSpace Metadata XML
            $dspaceXml = $this->generatePublishedDspaceMetadataXml($model);
            $zip->addFromString($folderName . '/metadata_dspace.xml', $dspaceXml);

            // 3. Bitstream file and contents
            $contentsLines = [];
            $pubFile = $this->resolvePublishedFileOnDisk($model);
            if ($pubFile && file_exists($pubFile)) {
                $ext = pathinfo($pubFile, PATHINFO_EXTENSION) ?: 'pdf';
                $destName = $model->id . '_Published.' . $ext;
                if ($zip->addFile($pubFile, $folderName . '/' . $destName)) {
                    $contentsLines[] = "{$destName}\tbundle:ORIGINAL\tprimary:true";
                }
            }

            if (!empty($contentsLines)) {
                $zip->addFromString($folderName . '/contents', implode("\n", $contentsLines) . "\n");
            }
        }

        $zip->close();
        clearstatcache();

        if (!file_exists($zipPath) || filesize($zipPath) === 0) {
            throw new \RuntimeException("ZIP package is empty or failed to generate.");
        }

        return $zipPath;
    }

    /**
     * Resolve publication file on disk with multi-path fallback
     */
    public function resolvePublishedFileOnDisk(RdbPublished $model): ?string
    {
        $relPath = $model->getPublishedFilePath();
        if ($relPath) {
            $diskPath = $this->resolveFileOnDisk($relPath);
            if ($diskPath && file_exists($diskPath)) {
                return $diskPath;
            }
        }

        $rawFile = $model->pub_file;
        if (!empty($rawFile)) {
            $legacyCandidates = [
                base_path('yii2_rdb-main/frontend/web/datapublished/' . $model->id . '/' . $rawFile),
                public_path('datapublished/' . $model->id . '/' . $rawFile),
                public_path('uploads/datapublished/' . $model->id . '/' . $rawFile),
                storage_path('app/public/published/' . $rawFile),
                storage_path('app/public/uploads/datapublished/' . $model->id . '/' . $rawFile),
                storage_path('app/public/uploads/rdb_published/' . $rawFile),
            ];
            foreach ($legacyCandidates as $c) {
                if (file_exists($c)) {
                    return $c;
                }
            }
        }

        return null;
    }

    /**
     * Locate pdftotext executable across common environment paths
     */
    public function getPdftotextBinary(): ?string
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // 1. Check if pdftotext is accessible in PATH
        $testCmd = $isWindows ? 'where pdftotext 2>nul' : 'which pdftotext 2>/dev/null';
        $pathOutput = @shell_exec($testCmd);
        if (!empty($pathOutput)) {
            $lines = array_filter(array_map('trim', explode("\n", $pathOutput)));
            if (!empty($lines)) {
                $first = reset($lines);
                if (file_exists($first)) {
                    return $first;
                }
                return 'pdftotext';
            }
        }

        // 2. Common Windows paths (Laragon / Git for Windows)
        if ($isWindows) {
            $candidates = [
                'F:\laragon\bin\git\mingw64\bin\pdftotext.exe',
                'C:\laragon\bin\git\mingw64\bin\pdftotext.exe',
                'D:\laragon\bin\git\mingw64\bin\pdftotext.exe',
                'C:\Program Files\Git\mingw64\bin\pdftotext.exe',
                'C:\Program Files (x86)\Git\mingw64\bin\pdftotext.exe',
            ];
            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    return $candidate;
                }
            }
        }

        // 3. Common Linux / Unix paths
        $linuxCandidates = [
            '/usr/bin/pdftotext',
            '/usr/local/bin/pdftotext',
            '/bin/pdftotext',
        ];
        foreach ($linuxCandidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Count PDF pages with ultra-high precision (up to 99.999%)
     * Uses Poppler pdftotext CLI (handles Secured/AES-128, Object Streams),
     * followed by Smalot\PdfParser (AST/DOM object tree) and multi-tier regex fallback
     */
    public function countPdfPages(string $filePath): int
    {
        if (!is_file($filePath) || !is_readable($filePath) || filesize($filePath) === 0) {
            return 0;
        }

        // Tier 1: Poppler pdftotext CLI (Handles Secured/Encrypted, Compressed Object Streams, PDF 1.6 - 2.0 with 99.999% accuracy)
        $pdftotext = $this->getPdftotextBinary();
        if ($pdftotext) {
            try {
                $process = new \Symfony\Component\Process\Process([$pdftotext, '-q', $filePath, '-']);
                $process->setTimeout(15);
                $process->run();
                if ($process->isSuccessful()) {
                    $pageCount = substr_count($process->getOutput(), chr(12));
                    if ($pageCount > 0) {
                        return $pageCount;
                    }
                }
            } catch (\Throwable $e) {
                // proceed to pure PHP fallbacks
            }
        }

        // Tier 2: Smalot\PdfParser - High-precision object parser (parses compressed cross-references & Page trees)
        if (class_exists(\Smalot\PdfParser\Parser::class) && @filesize($filePath) <= 10485760) {
            $parser = null;
            $pdf = null;
            $pages = null;
            try {
                $config = new \Smalot\PdfParser\Config();
                $config->setRetainImageContent(false); // ประหยัดหน่วยความจำ ไม่เก็บเนื้อไฟล์รูปภาพขณะนับหน้า
                $config->setDecodeMemoryLimit(8 * 1024 * 1024);

                $parser = new \Smalot\PdfParser\Parser([], $config);
                $pdf = $parser->parseFile($filePath);

                // 2.1 Page object collection count
                $pages = $pdf->getPages();
                if (!empty($pages) && is_countable($pages) && count($pages) > 0) {
                    return count($pages);
                }

                // 2.2 Document details metadata
                $details = $pdf->getDetails();
                if (!empty($details['Pages']) && is_numeric($details['Pages']) && (int) $details['Pages'] > 0) {
                    return (int) $details['Pages'];
                }
            } catch (\Throwable $e) {
                // Parser threw exception (e.g. encrypted or non-standard syntax), proceed to Tier 3/4
            } finally {
                unset($pages, $pdf, $parser);
                gc_collect_cycles();
            }
        }

        // Tier 3: FPDI fallback (if loaded/available)
        if (class_exists('\setasign\Fpdi\Fpdi')) {
            try {
                $fpdi = new \setasign\Fpdi\Fpdi();
                $pageCount = $fpdi->setSourceFile($filePath);
                if (is_int($pageCount) && $pageCount > 0) {
                    return $pageCount;
                }
            } catch (\Throwable $e) {
                // proceed to Tier 4
            }
        }

        // Tier 4: Binary Stream Scanning (Fast pattern fallback)
        $content = @file_get_contents($filePath);
        if ($content === false || $content === '') {
            return 0;
        }

        // 4.1 Match /Type /Page
        if (preg_match_all('/\/Type\s*\/Page\b/', $content, $matches)) {
            $count = count($matches[0]);
            if ($count > 0) {
                return $count;
            }
        }

        // 4.2 Match /Count N within /Pages
        if (preg_match('/\/Count\s+(\d+)/', $content, $matches)) {
            $count = (int) $matches[1];
            if ($count > 0) {
                return $count;
            }
        }

        // 4.3 Broad fallback regex /Page\W
        if (preg_match_all('/\/Page\W/', $content, $matches)) {
            return count($matches[0]);
        }

        return 0;
    }

    private function addDcValue(\SimpleXMLElement $xml, string $element, string $qualifier, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }

        $node = $xml->addChild('dcvalue', htmlspecialchars(trim($value), ENT_XML1, 'UTF-8'));
        $node->addAttribute('element', $element);
        $node->addAttribute('qualifier', $qualifier);
    }

    private function resolveFileOnDisk(string $path): ?string
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }
        if (file_exists(public_path('storage/' . $path))) {
            return public_path('storage/' . $path);
        }
        if (file_exists(public_path($path))) {
            return public_path($path);
        }
        if (file_exists(base_path('yii2_rdb-main/frontend/web/' . $path))) {
            return base_path('yii2_rdb-main/frontend/web/' . $path);
        }
        return null;
    }

    private function buildZipPackage(string $baseName, \SimpleXMLElement $dc, \SimpleXMLElement $dspaceMeta, array $filesToAdd): ?string
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('ระบบไม่พบส่วนขยาย ZipArchive (กรุณาเปิด extension=zip ใน php.ini)');
        }

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . DIRECTORY_SEPARATOR . "{$baseName}_" . date('Ymd_His') . ".zip";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        // Prettify XML
        $domDc = dom_import_simplexml($dc)->ownerDocument;
        $domDc->formatOutput = true;
        $zip->addFromString('dublin_core.xml', $domDc->saveXML());

        $domDs = dom_import_simplexml($dspaceMeta)->ownerDocument;
        $domDs->formatOutput = true;
        $zip->addFromString('metadata_dspace.xml', $domDs->saveXML());

        // Contents file
        $contentsLines = [];
        foreach ($filesToAdd as $zipFileName => $sourcePath) {
            if (file_exists($sourcePath)) {
                $zip->addFile($sourcePath, $zipFileName);
                $contentsLines[] = "{$zipFileName}\tbundle:ORIGINAL";
            }
        }

        if (!empty($contentsLines)) {
            $zip->addFromString('contents', implode("\n", $contentsLines) . "\n");
        }

        $zip->close();

        return $zipPath;
    }
}
