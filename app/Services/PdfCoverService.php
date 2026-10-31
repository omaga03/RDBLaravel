<?php

namespace App\Services;

use App\Models\RdbProject;
use App\Models\RdbPublished;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PdfCoverService
{
    protected DSpaceExportService $dspaceService;

    public function __construct(DSpaceExportService $dspaceService)
    {
        $this->dspaceService = $dspaceService;
    }

    /**
     * ดึงหรือค้นหาไบนารี pdftoppm บนระบบ (Poppler CLI)
     */
    public function getPdftoppmBinary(): ?string
    {
        $custom = env('PDFTOPPM_PATH');
        if (!empty($custom) && file_exists($custom)) {
            return $custom;
        }

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // 1. ตรวจสอบใน PATH
        $testCmd = $isWindows ? 'where pdftoppm 2>nul' : 'which pdftoppm 2>/dev/null';
        $pathOutput = @shell_exec($testCmd);
        if (!empty($pathOutput)) {
            $lines = array_filter(array_map('trim', explode("\n", $pathOutput)));
            if (!empty($lines)) {
                $first = reset($lines);
                if (file_exists($first)) {
                    return $first;
                }
                return 'pdftoppm';
            }
        }

        // 2. ตรวจสอบพาธมาตรฐานใน Windows (Laragon / Git / Poppler)
        if ($isWindows) {
            $candidates = [
                'C:\laragon\bin\poppler\bin\pdftoppm.exe',
                'C:\laragon\bin\git\mingw64\bin\pdftoppm.exe',
                'C:\Program Files\Git\mingw64\bin\pdftoppm.exe',
                'C:\Program Files\poppler\bin\pdftoppm.exe',
                'C:\poppler\bin\pdftoppm.exe',
                'D:\laragon\bin\poppler\bin\pdftoppm.exe',
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand)) {
                    return $cand;
                }
            }
        }

        // 3. ตรวจสอบพาธมาตรฐานใน Linux
        $linuxCandidates = [
            '/usr/bin/pdftoppm',
            '/usr/local/bin/pdftoppm',
            '/bin/pdftoppm',
        ];
        foreach ($linuxCandidates as $cand) {
            if (file_exists($cand)) {
                return $cand;
            }
        }

        return null;
    }

    /**
     * สร้างภาพหน้าปก .webp สำหรับเล่มรายงานวิจัยฉบับสมบูรณ์ (RdbProject)
     */
    public function generateProjectCover(RdbProject $project, bool $force = false): ?string
    {
        // ตรวจสอบว่าให้เผยแพร่ข้อมูลหรือไม่ (0 = เปิดเผยแพร่, 1 = ปิด, 2 = ร่าง)
        // หาก pro_file_show != 0 และไม่ force ให้ข้าม
        if ((int)$project->pro_file_show !== 0 && !$force) {
            return null;
        }

        // หากมี cover อยู่แล้วและไฟล์ยังอยู่ และไม่ force
        if (!$force && !empty($project->pro_cover_path) && Storage::disk('public')->exists($project->pro_cover_path)) {
            return $project->pro_cover_path;
        }

        // ค้นหาพาธไฟล์ PDF บนดิสก์
        $pdfPath = $this->dspaceService->resolveProjectFileOnDisk($project, 'report');
        if (!$pdfPath || !file_exists($pdfPath)) {
            return null;
        }

        $folder = 'project/covers';
        $filename = 'project_' . $project->pro_id . '_' . substr(md5($project->pro_id . '_' . filemtime($pdfPath)), 0, 8) . '.webp';
        $relativeStoragePath = $folder . '/' . $filename;
        $absoluteTargetPath = Storage::disk('public')->path($relativeStoragePath);

        // ตรวจสอบและสร้างโฟลเดอร์ปลายทาง
        $targetDir = dirname($absoluteTargetPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $metadata = [
            'type' => 'รายงานวิจัยฉบับสมบูรณ์',
            'title' => $project->pro_nameTH ?: $project->pro_nameEN ?: 'โครงการวิจัย',
            'code' => $project->pro_code ?: ('PCRU-PRO-' . $project->pro_id),
            'author' => optional($project->researcher)->full_name ?: 'นักวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์',
            'year' => optional($project->year)->year_name ? ('ปีงบประมาณ พ.ศ. ' . $project->year->year_name) : '',
            'department' => optional($project->department)->department_nameTH ?: 'มหาวิทยาลัยราชภัฏเพชรบูรณ์',
        ];

        $success = $this->extractFirstPageToWebp($pdfPath, $absoluteTargetPath, $metadata);

        if ($success && file_exists($absoluteTargetPath)) {
            $project->pro_cover_path = $relativeStoragePath;
            $project->saveQuietly();
            return $relativeStoragePath;
        }

        return null;
    }

    /**
     * สร้างภาพหน้าปก .webp สำหรับผลงานตีพิมพ์ (RdbPublished)
     */
    public function generatePublishedCover(RdbPublished $published, bool $force = false): ?string
    {
        // ตรวจสอบสถานะการเผยแพร่ (data_show == 1 หรือ pub_download == 1)
        if ($published->data_show != 1 && !$force) {
            return null;
        }

        // หากมี cover อยู่แล้วและไฟล์ยังอยู่ และไม่ force
        if (!$force && !empty($published->pub_cover_path) && Storage::disk('public')->exists($published->pub_cover_path)) {
            return $published->pub_cover_path;
        }

        // ค้นหาพาธไฟล์ PDF บนดิสก์
        $pdfPath = $this->dspaceService->resolvePublishedFileOnDisk($published);
        if (!$pdfPath || !file_exists($pdfPath)) {
            return null;
        }

        $folder = 'published/covers';
        $filename = 'published_' . $published->id . '_' . substr(md5($published->id . '_' . filemtime($pdfPath)), 0, 8) . '.webp';
        $relativeStoragePath = $folder . '/' . $filename;
        $absoluteTargetPath = Storage::disk('public')->path($relativeStoragePath);

        $targetDir = dirname($absoluteTargetPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $metadata = [
            'type' => 'บทความวิจัยตีพิมพ์',
            'title' => $published->pub_name ?: 'ผลงานตีพิมพ์ทางวิชาการ',
            'code' => optional($published->pubtype)->pubtype_name ?: 'งานตีพิมพ์เผยแพร่',
            'author' => optional($published->researcher)->full_name ?: 'นักวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์',
            'year' => optional($published->year)->year_name ? ('ปีงบประมาณ พ.ศ. ' . $published->year->year_name) : '',
            'department' => optional($published->department)->department_nameTH ?: 'มหาวิทยาลัยราชภัฏเพชรบูรณ์',
        ];

        $success = $this->extractFirstPageToWebp($pdfPath, $absoluteTargetPath, $metadata);

        if ($success && file_exists($absoluteTargetPath)) {
            $published->pub_cover_path = $relativeStoragePath;
            $published->saveQuietly();
            return $relativeStoragePath;
        }

        return null;
    }

    /**
     * ดึงหน้าแรกของ PDF ออกมาเป็น .webp ด้วยสถาปัตยกรรม 5-Tier Fallback
     */
    public function extractFirstPageToWebp(string $pdfPath, string $targetWebpPath, array $metadata = []): bool
    {
        // Tier 1: Poppler pdftoppm (ดีที่สุด คมชัด แม่นยำระดับ 100%)
        if ($this->extractViaPdftoppm($pdfPath, $targetWebpPath)) {
            return true;
        }

        // Tier 2: PHP Imagick Extension
        if ($this->extractViaImagick($pdfPath, $targetWebpPath)) {
            return true;
        }

        // Tier 3: Ghostscript CLI
        if ($this->extractViaGhostscript($pdfPath, $targetWebpPath)) {
            return true;
        }

        // Tier 4: Embedded Image Extraction via Smalot\PdfParser
        if ($this->extractViaPdfParserEmbeddedImage($pdfPath, $targetWebpPath)) {
            return true;
        }

        // Tier 5: Academic Editorial Cover Generator via PHP GD (ทำงานได้ 100% ทุกเซิร์ฟเวอร์)
        return $this->generateAcademicGdCover($targetWebpPath, $metadata);
    }

    /**
     * Tier 1: Poppler pdftoppm
     */
    protected function extractViaPdftoppm(string $pdfPath, string $targetWebpPath): bool
    {
        $pdftoppm = $this->getPdftoppmBinary();
        if (!$pdftoppm) {
            return false;
        }

        $tempPrefix = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pdf_cover_' . uniqid();
        $cmd = escapeshellarg($pdftoppm) . ' -png -r 150 -f 1 -l 1 ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tempPrefix);
        @exec($cmd, $out, $ret);

        $candidates = [
            $tempPrefix . '-1.png',
            $tempPrefix . '-01.png',
            $tempPrefix . '-001.png',
        ];

        $pngFile = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand)) {
                $pngFile = $cand;
                break;
            }
        }

        if ($pngFile && file_exists($pngFile)) {
            $success = $this->convertImageToWebp($pngFile, $targetWebpPath);
            @unlink($pngFile);
            return $success;
        }

        return false;
    }

    /**
     * Tier 2: PHP Imagick Extension
     */
    protected function extractViaImagick(string $pdfPath, string $targetWebpPath): bool
    {
        if (!extension_loaded('imagick') || !class_exists('\Imagick')) {
            return false;
        }

        try {
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($pdfPath . '[0]');
            $imagick->setImageFormat('webp');
            $imagick->setImageCompressionQuality(85);
            $imagick->writeImage($targetWebpPath);
            $imagick->clear();
            $imagick->destroy();
            return file_exists($targetWebpPath) && filesize($targetWebpPath) > 0;
        } catch (\Throwable $e) {
            Log::debug('Imagick extraction failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Tier 3: Ghostscript CLI
     */
    protected function extractViaGhostscript(string $pdfPath, string $targetWebpPath): bool
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $gsCmd = $isWindows ? 'gswin64c' : 'gs';
        $test = @shell_exec($isWindows ? "where $gsCmd 2>nul" : "which $gsCmd 2>/dev/null");
        if (empty($test)) {
            return false;
        }

        $tempPng = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gs_cover_' . uniqid() . '.png';
        $cmd = escapeshellarg(trim($gsCmd)) . " -sDEVICE=png16m -dFirstPage=1 -dLastPage=1 -r150 -o " . escapeshellarg($tempPng) . " " . escapeshellarg($pdfPath);
        @exec($cmd, $out, $ret);

        if (file_exists($tempPng) && filesize($tempPng) > 0) {
            $success = $this->convertImageToWebp($tempPng, $targetWebpPath);
            @unlink($tempPng);
            return $success;
        }

        return false;
    }

    /**
     * Tier 4: ดึงภาพที่ฝังอยู่ในหน้าแรกผ่าน Smalot\PdfParser
     */
    protected function extractViaPdfParserEmbeddedImage(string $pdfPath, string $targetWebpPath): bool
    {
        if (!class_exists('\Smalot\PdfParser\Parser')) {
            return false;
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $pages = $pdf->getPages();
            if (empty($pages)) {
                return false;
            }

            $firstPage = $pages[0];
            $images = $firstPage->getXObjects();

            foreach ($images as $xobj) {
                if (method_exists($xobj, 'getHeader') && method_exists($xobj, 'getContent')) {
                    $header = $xobj->getHeader();
                    if ($header && (string)$header->get('Subtype') === 'Image') {
                        $content = $xobj->getContent();
                        if (!empty($content)) {
                            $gdImg = @imagecreatefromstring($content);
                            if ($gdImg) {
                                if (function_exists('imagewebp')) {
                                    $saved = imagewebp($gdImg, $targetWebpPath, 85);
                                    imagedestroy($gdImg);
                                    if ($saved && file_exists($targetWebpPath)) {
                                        return true;
                                    }
                                }
                                imagedestroy($gdImg);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug('PdfParser embedded image extraction skipped: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Tier 5: สร้างปกวิชาการด้วย PHP GD สไตล์ Scholarly Minimalist (รันได้ 100% บนทุกเครื่อง)
     */
    protected function generateAcademicGdCover(string $targetWebpPath, array $metadata = []): bool
    {
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return false;
        }

        $width = 600;
        $height = 850;

        $im = imagecreatetruecolor($width, $height);

        // สีธีมสไตล์วิชาการ (Academic Navy & Citations)
        $bgTop = imagecolorallocate($im, 248, 250, 252);     // Slate 50
        $bgBottom = imagecolorallocate($im, 241, 245, 249);  // Slate 100
        $navy = imagecolorallocate($im, 30, 58, 95);         // Deep Navy #1E3A5F
        $amber = imagecolorallocate($im, 217, 119, 6);       // Amber #D97706
        $dark = imagecolorallocate($im, 15, 23, 42);         // Slate 900
        $gray = imagecolorallocate($im, 100, 116, 139);      // Slate 500
        $lightBorder = imagecolorallocate($im, 226, 232, 240); // Slate 200
        $white = imagecolorallocate($im, 255, 255, 255);

        // วาดพื้นหลัง Gradient นุ่มนวล
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int)(248 * (1 - $ratio) + 241 * $ratio);
            $g = (int)(250 * (1 - $ratio) + 245 * $ratio);
            $b = (int)(252 * (1 - $ratio) + 249 * $ratio);
            $c = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, $width, $y, $c);
        }

        // กรอบนอกระดับ Hairline 1px
        imagerectangle($im, 15, 15, $width - 16, $height - 16, $lightBorder);
        imagerectangle($im, 20, 20, $width - 21, $height - 21, $lightBorder);

        // แถบสี Deep Academic Navy ด้านบน
        imagefilledrectangle($im, 20, 20, $width - 21, 95, $navy);

        // ป้ายประเภทเอกสาร Amber
        imagefilledrectangle($im, 35, 35, 190, 65, $amber);

        // ข้อความบนแถบ Header
        $typeLabel = $metadata['type'] ?? 'เอกสารงานวิจัย';
        $codeLabel = $metadata['code'] ?? 'PCRU-RDB';
        imagestring($im, 4, 45, 42, $this->sanitizeAscii($typeLabel, 'RESEARCH'), $white);
        imagestring($im, 3, 210, 44, 'PCRU RESEARCH DATABASE', $white);
        imagestring($im, 2, 210, 62, $this->sanitizeAscii($codeLabel, 'DOCUMENT'), $gray);

        // วาดการ์ดเนื้อหาตรงกลาง
        imagefilledrectangle($im, 40, 130, $width - 41, $height - 120, $white);
        imagerectangle($im, 40, 130, $width - 41, $height - 120, $lightBorder);

        // แถบตกแต่งซ้าย
        imagefilledrectangle($im, 40, 130, 48, $height - 120, $navy);

        // ข้อความชื่อเรื่อง (Wrap 35 ตัวอักษร)
        $rawTitle = $metadata['title'] ?? 'Research Report / Published Article';
        $lines = explode("\n", wordwrap($this->sanitizeAscii($rawTitle, 'RESEARCH DOCUMENT'), 36, "\n", true));
        $startY = 170;
        imagestring($im, 3, 65, $startY - 25, 'DOCUMENT TITLE:', $amber);
        foreach (array_slice($lines, 0, 7) as $idx => $line) {
            imagestring($im, 5, 65, $startY + ($idx * 26), $line, $dark);
        }

        // ข้อมูลผู้เขียน/นักวิจัย
        $authorY = 400;
        imagestring($im, 3, 65, $authorY, 'AUTHOR / RESEARCHER:', $gray);
        $author = $metadata['author'] ?? 'Faculty Researcher';
        imagestring($im, 4, 65, $authorY + 22, $this->sanitizeAscii($author, 'Researcher PCRU'), $navy);

        // ข้อมูลหน่วยงานและปี
        $metaY = 480;
        imagestring($im, 3, 65, $metaY, 'INSTITUTION & FISCAL YEAR:', $gray);
        $dept = $metadata['department'] ?? 'Phetchabun Rajabhat University';
        $year = $metadata['year'] ?? date('Y');
        imagestring($im, 4, 65, $metaY + 22, $this->sanitizeAscii($dept, 'Phetchabun Rajabhat University'), $dark);
        imagestring($im, 3, 65, $metaY + 45, $this->sanitizeAscii($year, 'Fiscal Year ' . (date('Y') + 543)), $amber);

        // ป้ายสัญลักษณ์ด้านล่าง
        imagefilledrectangle($im, 20, $height - 80, $width - 21, $height - 21, $navy);
        imagestring($im, 3, 45, $height - 58, 'PHETCHABUN RAJABHAT UNIVERSITY - RDB SYSTEM', $white);
        imagestring($im, 2, 45, $height - 40, 'Official Research Publication & Report Document (1st Page Preview)', $gray);

        $saved = imagewebp($im, $targetWebpPath, 85);
        imagedestroy($im);

        return $saved && file_exists($targetWebpPath);
    }

    /**
     * แปลงไฟล์ภาพ (PNG/JPG) เป็น .webp คุณภาพสูงด้วย GD
     */
    protected function convertImageToWebp(string $sourceImagePath, string $targetWebpPath): bool
    {
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return false;
        }

        $info = @getimagesize($sourceImagePath);
        if (!$info) {
            return false;
        }

        $mime = $info['mime'] ?? '';
        $srcImg = match ($mime) {
            'image/png' => @imagecreatefrompng($sourceImagePath),
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourceImagePath),
            'image/webp' => @imagecreatefromwebp($sourceImagePath),
            default => null,
        };

        if (!$srcImg) {
            return false;
        }

        // ย่อขนาดให้เหมาะสม (ความกว้างสูงสุด 800px)
        $origWidth = imagesx($srcImg);
        $origHeight = imagesy($srcImg);
        $maxWidth = 800;

        if ($origWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int)($origHeight * ($maxWidth / $origWidth));
            $dstImg = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($srcImg);
            $srcImg = $dstImg;
        }

        $saved = imagewebp($srcImg, $targetWebpPath, 85);
        imagedestroy($srcImg);

        return $saved && file_exists($targetWebpPath);
    }

    /**
     * กรองข้อความสำหรับ imagestring (ASCII fallback)
     */
    protected function sanitizeAscii(?string $str, string $fallback = ''): string
    {
        if (empty($str)) {
            return $fallback;
        }
        // แปลง UTF-8 ภาษาไทย หรืออักขระพิเศษเป็นข้อความที่อ่านได้
        $clean = preg_replace('/[^\x20-\x7E]/', '', $str);
        if (strlen(trim($clean)) < 3) {
            return $fallback ?: 'RESEARCH DOCUMENT';
        }
        return trim($clean);
    }
}
