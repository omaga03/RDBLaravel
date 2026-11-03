<?php

namespace App\Services;

use App\Models\RdbProject;
use App\Models\RdbPublished;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfCoverService
{
    protected DSpaceExportService $dspaceService;
    protected bool $pdftoppmChecked = false;
    protected ?string $cachedPdftoppm = null;
    protected bool $pythonChecked = false;
    protected ?string $cachedPython = null;
    protected bool $ghostscriptChecked = false;
    protected ?string $cachedGhostscript = null;

    public function __construct(DSpaceExportService $dspaceService)
    {
        $this->dspaceService = $dspaceService;
    }

    /**
     * ตรวจสอบชื่อ Engine หลักที่ระบบใช้งานสำหรับการเรนเดอร์หน้าแรกของ PDF ทั้งหน้า
     */
    public function getActiveEngineDescription(): string
    {
        if ($pdftoppm = $this->getPdftoppmBinary()) {
            return "Poppler pdftoppm Full-Page Rasterizer [{$pdftoppm}]";
        }
        if ($python = $this->getPythonPyMuPdfBinary()) {
            return "MuPDF (PyMuPDF) Full-Page Rasterizer [{$python}]";
        }
        if (extension_loaded('imagick') && class_exists('\Imagick')) {
            return "PHP Imagick Full-Page Rasterizer";
        }
        if ($gs = $this->getGhostscriptBinary()) {
            return "Ghostscript CLI Full-Page Rasterizer [{$gs}]";
        }
        return "Academic GD Fallback Cover Generator (No PDF Rasterizer found)";
    }

    /**
     * ดึงหรือค้นหาไบนารี pdftoppm บนระบบ (Poppler CLI) พร้อมแคชผลลัพธ์ในหน่วยความจำ
     */
    public function getPdftoppmBinary(): ?string
    {
        if ($this->pdftoppmChecked) {
            return $this->cachedPdftoppm;
        }
        $this->pdftoppmChecked = true;

        $custom = env('PDFTOPPM_PATH');
        if (!empty($custom) && file_exists($custom)) {
            return $this->cachedPdftoppm = $custom;
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
                    return $this->cachedPdftoppm = $first;
                }
                return $this->cachedPdftoppm = 'pdftoppm';
            }
        }

        // 2. ตรวจสอบพาธมาตรฐานใน Windows (Laragon / Git / Poppler)
        if ($isWindows) {
            $candidates = [
                'F:\laragon\bin\poppler\bin\pdftoppm.exe',
                'F:\laragon\bin\git\mingw64\bin\pdftoppm.exe',
                'C:\laragon\bin\poppler\bin\pdftoppm.exe',
                'C:\laragon\bin\git\mingw64\bin\pdftoppm.exe',
                'C:\Program Files\Git\mingw64\bin\pdftoppm.exe',
                'C:\Program Files\poppler\bin\pdftoppm.exe',
                'C:\poppler\bin\pdftoppm.exe',
                'D:\laragon\bin\poppler\bin\pdftoppm.exe',
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand)) {
                    return $this->cachedPdftoppm = $cand;
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
                return $this->cachedPdftoppm = $cand;
            }
        }

        return $this->cachedPdftoppm = null;
    }

    /**
     * ดึงหรือค้นหาไบนารี Python ที่ติดตั้ง PyMuPDF (fitz) และ Pillow สำหรับเรนเดอร์หน้าแรกของ PDF ทั้งหน้า
     */
    public function getPythonPyMuPdfBinary(): ?string
    {
        if ($this->pythonChecked) {
            return $this->cachedPython;
        }
        $this->pythonChecked = true;

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $candidates = [];

        $custom = env('PYTHON_PATH');
        if (!empty($custom) && file_exists($custom)) {
            $candidates[] = $custom;
        }

        if ($isWindows) {
            $winPaths = [
                'F:\laragon\bin\python\python-3.10\python.exe',
                'C:\laragon\bin\python\python-3.10\python.exe',
                'D:\laragon\bin\python\python-3.10\python.exe',
            ];
            foreach (['F:\laragon\bin\python', 'C:\laragon\bin\python', 'D:\laragon\bin\python'] as $baseDir) {
                if (is_dir($baseDir)) {
                    foreach (glob($baseDir . '\python*\python.exe') ?: [] as $foundExe) {
                        $winPaths[] = $foundExe;
                    }
                }
            }
            foreach ($winPaths as $p) {
                if (file_exists($p) && !in_array($p, $candidates, true)) {
                    $candidates[] = $p;
                }
            }

            $whereOut = @shell_exec('where python 2>nul');
            if (!empty($whereOut)) {
                foreach (array_filter(array_map('trim', explode("\n", $whereOut))) as $line) {
                    if (file_exists($line) && !in_array($line, $candidates, true)) {
                        $candidates[] = $line;
                    }
                }
            }
        } else {
            foreach (['/usr/bin/python3', '/usr/local/bin/python3', '/bin/python3'] as $p) {
                if (file_exists($p)) {
                    $candidates[] = $p;
                }
            }
            $whichOut = @shell_exec('which python3 2>/dev/null');
            if (!empty($whichOut)) {
                $first = trim(explode("\n", trim($whichOut))[0]);
                if ($first && file_exists($first) && !in_array($first, $candidates, true)) {
                    $candidates[] = $first;
                }
            }
        }

        $nullDev = $isWindows ? 'nul' : '/dev/null';
        foreach ($candidates as $pythonBin) {
            $checkCmd = escapeshellarg($pythonBin) . ' -c "import pymupdf, PIL; print(199)" 2>' . $nullDev;
            $out = trim((string) @shell_exec($checkCmd));
            if ($out === '199') {
                return $this->cachedPython = $pythonBin;
            }
        }

        return $this->cachedPython = null;
    }

    /**
     * ดึงหรือค้นหาไบนารี Ghostscript CLI พร้อมแคชผลลัพธ์
     */
    public function getGhostscriptBinary(): ?string
    {
        if (!$this->ghostscriptChecked) {
            $this->ghostscriptChecked = true;
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $gsCmd = $isWindows ? 'gswin64c' : 'gs';
            $test = @shell_exec($isWindows ? "where $gsCmd 2>nul" : "which $gsCmd 2>/dev/null");
            $this->cachedGhostscript = !empty($test) ? trim(explode("\n", trim($test))[0]) : null;
        }

        return $this->cachedGhostscript;
    }

    /**
     * สร้างภาพหน้าปก .webp สำหรับเล่มรายงานวิจัยฉบับสมบูรณ์ (RdbProject)
     */
    public function generateProjectCover(RdbProject $project, bool $force = false): ?string
    {
        // ตรวจสอบว่าให้เผยแพร่ข้อมูลหรือไม่ (0 = เปิดเผยแพร่, 1 = ปิด, 2 = ร่าง)
        if ((int)$project->pro_file_show !== 0 && !$force) {
            return null;
        }

        // หากมี cover อยู่แล้วและไฟล์ยังอยู่ และไม่ force
        if (!$force && !empty($project->pro_cover_path) && Storage::disk('public')->exists($project->pro_cover_path)) {
            return $project->pro_cover_path;
        }

        // ค้นหาพาธไฟล์ PDF บนดิสก์ (เล่มรายงาน -> หากไม่มีให้ลองใช้ไฟล์บทคัดย่อ)
        $pdfPath = $this->dspaceService->resolveProjectFileOnDisk($project, 'report')
            ?: $this->dspaceService->resolveProjectFileOnDisk($project, 'abstract');

        $folder = 'project/covers';
        $hashSeed = ($pdfPath && file_exists($pdfPath)) ? filemtime($pdfPath) : ($project->pro_file ?: $project->pro_id);
        $filename = 'project_' . $project->pro_id . '_' . substr(md5($project->pro_id . '_' . $hashSeed), 0, 8) . '.webp';
        $relativeStoragePath = $folder . '/' . $filename;
        $absoluteTargetPath = Storage::disk('public')->path($relativeStoragePath);

        $targetDir = dirname($absoluteTargetPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $metadata = $this->buildProjectMetadata($project);

        if ($pdfPath && file_exists($pdfPath)) {
            $success = $this->extractFirstPageToWebp($pdfPath, $absoluteTargetPath, $metadata);
        } else {
            $success = $this->generateAcademicGdCover($absoluteTargetPath, $metadata);
        }

        if ($success && file_exists($absoluteTargetPath) && filesize($absoluteTargetPath) > 0) {
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
        if ($published->data_show != 1 && !$force) {
            return null;
        }

        if (!$force && !empty($published->pub_cover_path) && Storage::disk('public')->exists($published->pub_cover_path)) {
            return $published->pub_cover_path;
        }

        $pdfPath = $this->dspaceService->resolvePublishedFileOnDisk($published);

        $folder = 'published/covers';
        $hashSeed = ($pdfPath && file_exists($pdfPath)) ? filemtime($pdfPath) : ($published->pub_file ?: $published->id);
        $filename = 'published_' . $published->id . '_' . substr(md5($published->id . '_' . $hashSeed), 0, 8) . '.webp';
        $relativeStoragePath = $folder . '/' . $filename;
        $absoluteTargetPath = Storage::disk('public')->path($relativeStoragePath);

        $targetDir = dirname($absoluteTargetPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $metadata = $this->buildPublishedMetadata($published);

        if ($pdfPath && file_exists($pdfPath)) {
            $success = $this->extractFirstPageToWebp($pdfPath, $absoluteTargetPath, $metadata);
        } else {
            $success = $this->generateAcademicGdCover($absoluteTargetPath, $metadata);
        }

        if ($success && file_exists($absoluteTargetPath) && filesize($absoluteTargetPath) > 0) {
            $published->pub_cover_path = $relativeStoragePath;
            $published->saveQuietly();
            return $relativeStoragePath;
        }

        return null;
    }

    /**
     * เตรียมข้อมูล Metadata ของโครงการวิจัยสำหรับปกสำรอง
     */
    public function buildProjectMetadata(RdbProject $project): array
    {
        $authorName = 'นักวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์';
        if ($project->relationLoaded('rdbProjectWorks') || $project->rdbProjectWorks) {
            $works = $project->rdbProjectWorks ? $project->rdbProjectWorks->filter(fn($w) => !empty($w->researcher)) : collect();
            $headWork = $works->firstWhere('position_id', 2) ?? $works->firstWhere('position_id', 1) ?? $works->first();
            if ($headWork && $headWork->researcher) {
                $r = $headWork->researcher;
                $prefix = optional($r->prefix)->prefix_nameTH ?? '';
                $full = trim($prefix . $r->researcher_fname . ' ' . $r->researcher_lname);
                if ($full !== '') {
                    $authorName = $full;
                }
            }
        }

        return [
            'type' => 'รายงานวิจัยฉบับสมบูรณ์',
            'title' => strip_tags((string) ($project->pro_nameTH ?: $project->pro_nameEN ?: 'โครงการวิจัย')),
            'code' => $project->pro_code ? ('รหัสโครงการ: ' . $project->pro_code) : ('PCRU-PRO-' . $project->pro_id),
            'author' => $authorName,
            'year' => optional($project->year)->year_name ? ('ปีงบประมาณ พ.ศ. ' . $project->year->year_name) : '',
            'department' => optional($project->department)->department_nameTH ?: 'มหาวิทยาลัยราชภัฏเพชรบูรณ์',
        ];
    }

    /**
     * เตรียมข้อมูล Metadata ของผลงานตีพิมพ์สำหรับปกสำรอง
     */
    public function buildPublishedMetadata(RdbPublished $published): array
    {
        return [
            'type' => 'บทความวิจัยตีพิมพ์',
            'title' => $published->pub_name ?: 'ผลงานตีพิมพ์ทางวิชาการ',
            'code' => optional($published->pubtype)->pubtype_name ?: 'งานตีพิมพ์เผยแพร่',
            'author' => 'นักวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์',
            'year' => optional($published->year)->year_name ? ('ปีงบประมาณ พ.ศ. ' . $published->year->year_name) : '',
            'department' => optional($published->department)->department_nameTH ?: 'มหาวิทยาลัยราชภัฏเพชรบูรณ์',
        ];
    }

    /**
     * เรนเดอร์ภาพหน้าแรกของ PDF ทั้งหน้าแบบ Batch (ความเร็วสูงสำหรับ CLI หลายพันไฟล์)
     *
     * @param array<int, array{key: int|string, pdf: string, webp: string, meta: array}> $batchItems
     * @return array<int|string, bool>
     */
    public function renderBatchFirstPageToWebp(array $batchItems): array
    {
        if (empty($batchItems)) {
            return [];
        }

        $results = [];
        $python = $this->getPythonPyMuPdfBinary();

        if ($python) {
            $payload = [];
            foreach ($batchItems as $item) {
                $targetDir = dirname($item['webp']);
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }
                $payload[] = [
                    'key' => (string) $item['key'],
                    'pdf' => $item['pdf'],
                    'webp' => $item['webp'],
                ];
            }

            $tmpJson = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rdb_pdf_batch_in_' . uniqid() . '.json';
            $tmpOutJson = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rdb_pdf_batch_out_' . uniqid() . '.json';
            $tmpPy = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rdb_pdf_batch_' . uniqid() . '.py';

            @file_put_contents($tmpJson, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $pyScript = <<<'PYTHON'
import sys, json, os
import pymupdf
from PIL import Image

pymupdf.TOOLS.mupdf_display_errors(False)
pymupdf.TOOLS.mupdf_display_warnings(False)

def main():
    json_path = sys.argv[1]
    out_path = sys.argv[2]
    with open(json_path, 'r', encoding='utf-8') as f:
        items = json.load(f)
    out = {}
    for item in items:
        key = item['key']
        pdf_path = item['pdf']
        webp_path = item['webp']
        try:
            if os.path.isfile(webp_path):
                os.remove(webp_path)
            doc = pymupdf.open(pdf_path)
            if len(doc) < 1:
                doc.close()
                out[key] = False
                continue
            page = doc[0]
            rect = page.rect
            w = rect.width if rect.width > 0 else 595.0
            zoom = max(1.2, min(3.0, 800.0 / w))
            pix = page.get_pixmap(matrix=pymupdf.Matrix(zoom, zoom), alpha=False)
            img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
            if img.width > 800:
                new_h = max(1, int(img.height * (800.0 / img.width)))
                img = img.resize((800, new_h), Image.Resampling.LANCZOS)
            img.save(webp_path, "WEBP", quality=85, method=4)
            img.close()
            pix = None
            doc.close()
            out[key] = os.path.isfile(webp_path) and os.path.getsize(webp_path) > 0
        except Exception:
            out[key] = False
    with open(out_path, 'w', encoding='utf-8') as f:
        json.dump(out, f)

if __name__ == '__main__':
    main()
PYTHON;

            @file_put_contents($tmpPy, $pyScript);
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $nullDev = $isWindows ? 'nul' : '/dev/null';
            $cmd = escapeshellarg($python) . ' ' . escapeshellarg($tmpPy) . ' ' . escapeshellarg($tmpJson) . ' ' . escapeshellarg($tmpOutJson) . ' >' . $nullDev . ' 2>&1';
            @shell_exec($cmd);

            $rawOut = is_file($tmpOutJson) ? @file_get_contents($tmpOutJson) : null;

            @unlink($tmpJson);
            @unlink($tmpOutJson);
            @unlink($tmpPy);

            $decoded = is_string($rawOut) ? @json_decode(trim($rawOut), true) : null;
            if (is_array($decoded)) {
                foreach ($batchItems as $item) {
                    $k = (string) $item['key'];
                    if (!empty($decoded[$k]) && file_exists($item['webp']) && filesize($item['webp']) > 0) {
                        $results[$item['key']] = true;
                    } else {
                        // หาก PDF เสียหายหรืออ่านไม่ได้ ให้ใช้ Fallback
                        $results[$item['key']] = $this->generateAcademicGdCover($item['webp'], $item['meta'] ?? []);
                    }
                }
                return $results;
            }
        }

        // Fallback เรียกทีละรายการ
        foreach ($batchItems as $item) {
            $results[$item['key']] = $this->extractFirstPageToWebp($item['pdf'], $item['webp'], $item['meta'] ?? []);
        }

        return $results;
    }

    /**
     * เรนเดอร์หน้าแรกของ PDF ทั้งหน้า (Full-Page Rasterization) ออกมาเป็น .webp
     * ห้ามดึงเฉพาะภาพย่อย (Embedded Image/Logo) ที่ฝังในหน้าแรกเด็ดขาด เพื่อให้ได้ภาพหน้าปกเต็มหน้า 100%
     */
    public function extractFirstPageToWebp(string $pdfPath, string $targetWebpPath, array $metadata = []): bool
    {
        // Tier 1: Poppler pdftoppm (เรนเดอร์หน้าแรกทั้งหน้า)
        if ($this->extractViaPdftoppm($pdfPath, $targetWebpPath)) {
            return true;
        }

        // Tier 2: MuPDF / PyMuPDF (เรนเดอร์หน้าแรกทั้งหน้า คมชัด รองรับฟอนต์ไทย/เวกเตอร์/ตารางครบถ้วน)
        if ($this->extractViaPyMuPdf($pdfPath, $targetWebpPath)) {
            return true;
        }

        // Tier 3: PHP Imagick Extension (เรนเดอร์หน้าแรก [0] ทั้งหน้า)
        if ($this->extractViaImagick($pdfPath, $targetWebpPath)) {
            return true;
        }

        // Tier 4: Ghostscript CLI (เรนเดอร์หน้าแรก -dFirstPage=1 -dLastPage=1 ทั้งหน้า)
        if ($this->extractViaGhostscript($pdfPath, $targetWebpPath)) {
            return true;
        }

        // Tier 5: Academic Editorial Cover Generator via PHP GD (กรณีไฟล์ PDF เสียหายหรือเซิร์ฟเวอร์ไม่มี PDF Engine)
        return $this->generateAcademicGdCover($targetWebpPath, $metadata);
    }

    /**
     * Tier 1: Poppler pdftoppm (เรนเดอร์หน้าแรกทั้งหน้า)
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
     * Tier 2: MuPDF / PyMuPDF เรนเดอร์หน้าแรกของ PDF ทั้งหน้าเป็น .webp โดยตรง
     */
    protected function extractViaPyMuPdf(string $pdfPath, string $targetWebpPath): bool
    {
        $python = $this->getPythonPyMuPdfBinary();
        if (!$python || !is_file($pdfPath)) {
            return false;
        }

        $payloadB64 = base64_encode(json_encode([
            'pdf' => $pdfPath,
            'webp' => $targetWebpPath,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $pyInline = 'import sys, json, base64, pymupdf; from PIL import Image; '
            . 'pymupdf.TOOLS.mupdf_display_errors(False); pymupdf.TOOLS.mupdf_display_warnings(False); '
            . 'd = json.loads(base64.b64decode(sys.argv[1]).decode("utf-8")); '
            . 'doc = pymupdf.open(d["pdf"]); '
            . 'page = doc[0]; '
            . 'w = page.rect.width if page.rect.width > 0 else 595.0; '
            . 'z = max(1.2, min(3.0, 800.0 / w)); '
            . 'pix = page.get_pixmap(matrix=pymupdf.Matrix(z, z), alpha=False); '
            . 'img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples); '
            . 'img = img.resize((800, max(1, int(img.height * (800.0 / img.width)))), Image.Resampling.LANCZOS) if img.width > 800 else img; '
            . 'img.save(d["webp"], "WEBP", quality=85, method=4); '
            . 'doc.close(); print("OK")';

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $nullDev = $isWindows ? 'nul' : '/dev/null';
        $cmd = escapeshellarg($python) . ' -c ' . escapeshellarg($pyInline) . ' ' . escapeshellarg($payloadB64) . ' 2>' . $nullDev;
        $out = trim((string) @shell_exec($cmd));

        return $out === 'OK' && file_exists($targetWebpPath) && filesize($targetWebpPath) > 0;
    }

    /**
     * Tier 3: PHP Imagick Extension (เรนเดอร์หน้าแรกทั้งหน้า)
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
     * Tier 4: Ghostscript CLI (เรนเดอร์หน้าแรกทั้งหน้า)
     */
    protected function extractViaGhostscript(string $pdfPath, string $targetWebpPath): bool
    {
        $gs = $this->getGhostscriptBinary();
        if (!$gs) {
            return false;
        }

        $tempPng = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gs_cover_' . uniqid() . '.png';
        $cmd = escapeshellarg($gs) . " -sDEVICE=png16m -dFirstPage=1 -dLastPage=1 -r150 -o " . escapeshellarg($tempPng) . " " . escapeshellarg($pdfPath);
        @exec($cmd, $out, $ret);

        if (file_exists($tempPng) && filesize($tempPng) > 0) {
            $success = $this->convertImageToWebp($tempPng, $targetWebpPath);
            @unlink($tempPng);
            return $success;
        }

        return false;
    }

    /**
     * Tier 5: สร้างปกวิชาการด้วย PHP GD สไตล์ Scholarly Minimalist พร้อมรองรับฟอนต์ภาษาไทย TTF
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
        $navy = imagecolorallocate($im, 30, 58, 95);          // Deep Navy #1E3A5F
        $amber = imagecolorallocate($im, 217, 119, 6);        // Amber #D97706
        $dark = imagecolorallocate($im, 15, 23, 42);          // Slate 900
        $gray = imagecolorallocate($im, 100, 116, 139);       // Slate 500
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
        imagefilledrectangle($im, 35, 35, 215, 68, $amber);

        // วาดการ์ดเนื้อหาตรงกลาง
        imagefilledrectangle($im, 40, 130, $width - 41, $height - 120, $white);
        imagerectangle($im, 40, 130, $width - 41, $height - 120, $lightBorder);
        imagefilledrectangle($im, 40, 130, 48, $height - 120, $navy);

        $ttfFont = $this->resolveThaiTtfFont();
        $typeLabel = $metadata['type'] ?? 'เอกสารงานวิจัย';
        $codeLabel = $metadata['code'] ?? 'PCRU-RDB';
        $rawTitle = $metadata['title'] ?? 'Research Report / Published Article';
        $author = $metadata['author'] ?? 'นักวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์';
        $dept = $metadata['department'] ?? 'มหาวิทยาลัยราชภัฏเพชรบูรณ์';
        $year = $metadata['year'] ?? ('ปีงบประมาณ พ.ศ. ' . (date('Y') + 543));

        if ($ttfFont && function_exists('imagettftext')) {
            @imagettftext($im, 11, 0, 45, 57, $white, $ttfFont, $typeLabel);
            @imagettftext($im, 12, 0, 230, 50, $white, $ttfFont, 'PCRU RESEARCH DATABASE');
            @imagettftext($im, 10, 0, 230, 72, $lightBorder, $ttfFont, $codeLabel);

            @imagettftext($im, 11, 0, 65, 165, $amber, $ttfFont, 'ชื่อผลงานวิจัย / บทความวิชาการ:');
            $wrappedLines = $this->wrapUtf8Text($rawTitle, 38);
            foreach (array_slice($wrappedLines, 0, 7) as $idx => $line) {
                @imagettftext($im, 14, 0, 65, 200 + ($idx * 30), $dark, $ttfFont, $line);
            }

            @imagettftext($im, 10, 0, 65, 440, $gray, $ttfFont, 'ผู้วิจัย / เจ้าของผลงาน:');
            @imagettftext($im, 13, 0, 65, 466, $navy, $ttfFont, $author);

            @imagettftext($im, 10, 0, 65, 520, $gray, $ttfFont, 'หน่วยงานและปีงบประมาณ:');
            @imagettftext($im, 12, 0, 65, 546, $dark, $ttfFont, $dept);
            @imagettftext($im, 12, 0, 65, 574, $amber, $ttfFont, $year);
        } else {
            imagestring($im, 4, 45, 42, $this->sanitizeAscii($typeLabel, 'RESEARCH'), $white);
            imagestring($im, 3, 225, 44, 'PCRU RESEARCH DATABASE', $white);
            imagestring($im, 2, 225, 62, $this->sanitizeAscii($codeLabel, 'DOCUMENT'), $gray);

            $lines = explode("\n", wordwrap($this->sanitizeAscii($rawTitle, 'RESEARCH DOCUMENT'), 36, "\n", true));
            $startY = 170;
            imagestring($im, 3, 65, $startY - 25, 'DOCUMENT TITLE:', $amber);
            foreach (array_slice($lines, 0, 7) as $idx => $line) {
                imagestring($im, 5, 65, $startY + ($idx * 26), $line, $dark);
            }

            imagestring($im, 3, 65, 400, 'AUTHOR / RESEARCHER:', $gray);
            imagestring($im, 4, 65, 422, $this->sanitizeAscii($author, 'Researcher PCRU'), $navy);

            imagestring($im, 3, 65, 480, 'INSTITUTION & FISCAL YEAR:', $gray);
            imagestring($im, 4, 65, 502, $this->sanitizeAscii($dept, 'Phetchabun Rajabhat University'), $dark);
            imagestring($im, 3, 65, 525, $this->sanitizeAscii($year, 'Fiscal Year ' . (date('Y') + 543)), $amber);
        }

        // ป้ายสัญลักษณ์ด้านล่าง
        imagefilledrectangle($im, 20, $height - 80, $width - 21, $height - 21, $navy);
        imagestring($im, 3, 45, $height - 58, 'PHETCHABUN RAJABHAT UNIVERSITY - RDB SYSTEM', $white);
        imagestring($im, 2, 45, $height - 40, 'Official Research Publication & Report Document (1st Page Preview)', $gray);

        $saved = imagewebp($im, $targetWebpPath, 85);
        imagedestroy($im);

        return $saved && file_exists($targetWebpPath);
    }

    /**
     * ค้นหาไฟล์ฟอนต์ภาษาไทย (.ttf) บนระบบสำหรับ GD Fallback
     */
    protected function resolveThaiTtfFont(): ?string
    {
        $candidates = [
            'C:\Windows\Fonts\tahoma.ttf',
            'C:\Windows\Fonts\leelawui.ttf',
            'C:\Windows\Fonts\angsana.ttc',
            '/usr/share/fonts/truetype/tlwg/Garuda.ttf',
            '/usr/share/fonts/truetype/tlwg/Loma.ttf',
            '/usr/share/fonts/truetype/noto/NotoSansThai-Regular.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];
        foreach ($candidates as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }
        return null;
    }

    /**
     * ตัดบรรทัดข้อความภาษาไทย/อังกฤษแบบ UTF-8
     */
    protected function wrapUtf8Text(string $text, int $maxCharsPerLine = 38): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return ['เอกสารงานวิจัย'];
        }
        $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $lines = [];
        $current = '';

        foreach ($words as $part) {
            if (mb_strlen($current . $part, 'UTF-8') <= $maxCharsPerLine) {
                $current .= $part;
            } else {
                if (trim($current) !== '') {
                    $lines[] = trim($current);
                    $current = '';
                }
                while (mb_strlen($part, 'UTF-8') > $maxCharsPerLine) {
                    $lines[] = mb_substr($part, 0, $maxCharsPerLine, 'UTF-8');
                    $part = mb_substr($part, $maxCharsPerLine, null, 'UTF-8');
                }
                $current = ltrim($part);
            }
        }
        if (trim($current) !== '') {
            $lines[] = trim($current);
        }

        return $lines ?: [$text];
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
        $clean = preg_replace('/[^\x20-\x7E]/', '', $str);
        if (strlen(trim($clean)) < 3) {
            return $fallback ?: 'RESEARCH DOCUMENT';
        }
        return trim($clean);
    }
}
