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
        $pubPath = $published->getPublishedFilePath();
        if ($pubPath) {
            $fullPath = $this->resolveFileOnDisk($pubPath);
            if ($fullPath && file_exists($fullPath)) {
                $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'pdf';
                $filesToAdd["publication_{$published->id}.{$ext}"] = $fullPath;
            }
        }

        return $this->buildZipPackage("dspace_published_{$published->id}", $dc, $dspaceMeta, $filesToAdd);
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
