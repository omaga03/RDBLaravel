<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileUploadService
{
    protected ?DSpaceExportService $dspaceService = null;

    public function __construct(?DSpaceExportService $dspaceService = null)
    {
        $this->dspaceService = $dspaceService;
    }

    protected function getDspaceService(): DSpaceExportService
    {
        if ($this->dspaceService === null) {
            $this->dspaceService = app(DSpaceExportService::class);
        }
        return $this->dspaceService;
    }
    // ชนิดไฟล์ที่อนุญาต
    private const ALLOWED_MIME_TYPES = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'application/x-rar-compressed' => 'rar',
        'application/vnd.rar' => 'rar',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    private const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50 MB

    /**
     * ตรวจสอบและสร้างชื่อไฟล์ที่ปลอดภัย
     * 
     * @param UploadedFile $file
     * @return string ชื่อไฟล์ที่ปลอดภัย (UUID + extension)
     * @throws \Exception
     */
    public function generateSafeFileName(UploadedFile $file): string
    {
        // Validate file
        if (!$file || !$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        // ตรวจสอบขนาดไฟล์
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('ไฟล์ขนาดใหญ่เกินไป (สูงสุด 50 MB)');
        }

        $extensionMap = [
            'pdf' => 'pdf',
            'doc' => 'doc',
            'docx' => 'docx',
            'xls' => 'xls',
            'xlsx' => 'xlsx',
            'ppt' => 'ppt',
            'pptx' => 'pptx',
            'zip' => 'zip',
            'rar' => 'rar',
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'png' => 'png',
        ];

        // ตรวจสอบ MIME type
        $mimeType = $file->getMimeType();
        $originalExtension = strtolower($file->getClientOriginalExtension());

        if (!empty($mimeType) && isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            $extension = self::ALLOWED_MIME_TYPES[$mimeType];
        } elseif (isset($extensionMap[$originalExtension])) {
            $extension = $extensionMap[$originalExtension];
        } else {
            throw new \Exception('ประเภทไฟล์ไม่อนุญาต (รองรับเฉพาะ PDF, Word, Excel, PowerPoint, ZIP, JPG, PNG)');
        }

        // สร้างชื่อไฟล์ด้วย UUID (ป้องกัน Directory Traversal)
        $uuid = Str::uuid();
        if (empty($uuid)) {
            throw new \Exception('Failed to generate UUID for file name');
        }

        $uniqueName = $uuid . '.' . $extension;

        if (empty($uniqueName)) {
            throw new \Exception('Failed to generate unique file name');
        }

        return $uniqueName;
    }

    /**
     * บันทึกไฟล์ลง Storage ที่ปลอดภัย
     * 
     * @param UploadedFile $file
     * @param string $directory เช่น 'projects', 'researchers'
     * @return array ['path' => '...', 'safe_name' => '...', 'size' => ..., 'mime_type' => '...']
     * @throws \Exception
     */
    public function storeFile(UploadedFile $file, string $directory): array
    {
        // Validate inputs
        if (empty($directory)) {
            throw new \Exception('Directory path must not be empty');
        }

        if (!$file || !$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        $safeFileName = $this->generateSafeFileName($file);

        // Validate generated filename
        if (empty($safeFileName)) {
            throw new \Exception('Failed to generate safe file name');
        }

        // Ensure directory doesn't have leading/trailing slashes
        $directory = trim($directory, '/');
        
        // Validate directory is not empty after trim
        if (empty($directory)) {
            throw new \Exception('Directory path is empty after sanitization');
        }

        // Ensure directory exists in storage
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory, 0755, true);
        }

        // Build full path
        $fullPath = $directory . '/' . $safeFileName;
        
        // Validate path components
        if (empty($fullPath) || empty($safeFileName)) {
            throw new \Exception('Invalid file path: directory="' . $directory . '", filename="' . $safeFileName . '"');
        }

        // เก็บลง storage/{directory}/ โดยใช้ Storage facade โดยตรง
        try {
            // Get file content using get() method (safer than getRealPath())
            $fileContent = $file->get();
            
            if ($fileContent === false || empty($fileContent)) {
                throw new \Exception('Failed to read file content or file is empty');
            }
            
            // Store file using put() method
            $stored = Storage::disk('public')->put($fullPath, $fileContent);
            
            if (!$stored) {
                throw new \Exception('Storage::put() returned false');
            }
            
            $storagePath = $fullPath;
        } catch (\Exception $e) {
            throw new \Exception('Failed to store file: ' . $e->getMessage() . ' (directory: ' . $directory . ', filename: ' . $safeFileName . ')');
        }

        if (empty($storagePath)) {
            throw new \Exception('Failed to store file. Storage path is empty after upload.');
        }

        // Auto-count PDF pages if file is PDF
        $pageCount = null;
        $mimeType = $file->getMimeType();
        $isPdf = ($mimeType === 'application/pdf') || str_ends_with(strtolower($safeFileName), '.pdf');
        if ($isPdf) {
            try {
                $fullDiskPath = Storage::disk('public')->path($storagePath);
                $counted = $this->getDspaceService()->countPdfPages($fullDiskPath);
                if ($counted > 0) {
                    $pageCount = $counted;
                }
            } catch (\Throwable $e) {
                Log::warning("Could not count pages for uploaded PDF {$storagePath}: " . $e->getMessage());
            }
        }

        return [
            'path' => $storagePath,
            'safe_name' => $safeFileName,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $mimeType,
            'page_count' => $pageCount,
        ];
    }

    /**
     * นับจำนวนหน้าของไฟล์ PDF ด้วยเอนจินความแม่นยำสูง (Poppler pdftotext / Smalot / FPDI fallback)
     *
     * @param string $filePath พาธไฟล์ (แบบเต็ม หรือสัมพัทธ์ใน Storage)
     * @return int จำนวนหน้า (หรือ 0 หากนับไม่ได้)
     */
    public function countPdfPages(string $filePath): int
    {
        return $this->getDspaceService()->countPdfPages($filePath);
    }

    /**
     * ใช้ฟิวด์เดิม (Legacy Mode)
     * รองรับการใช้คอลัมน์เดิมแบบ old_filename โดยไม่เพิ่มฟิวด์ใหม่
     * 
     * @param UploadedFile $file
     * @param string $directory
     * @param string $prefix เช่น 'pro', 'rf' สำหรับสร้างชื่อไฟล์แบบ Yii2
     * @return array ['filename' => '...', 'size' => ..., 'mime_type' => '...']
     * @throws \Exception
     */
    public function storeFileLegacy(UploadedFile $file, string $directory, string $prefix = ''): array
    {
        // ตรวจสอบขนาดไฟล์
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('ไฟล์ขนาดใหญ่เกินไป (สูงสุด 50 MB)');
        }

        // ตรวจสอบ MIME type
        $mimeType = $file->getMimeType();
        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new \Exception('ประเภทไฟล์ไม่อนุญาต: ' . $mimeType);
        }

        // สร้างชื่อไฟล์แบบ Yii2 convention
        // Format: {prefix}{YmdHis}{random40}~.{ext}
        $extension = self::ALLOWED_MIME_TYPES[$mimeType];
        $filename = $prefix . date('YmdHis') . Str::random(40) . '~.' . $extension;

        // เก็บลง storage/{directory}/
        $storagePath = $file->storeAs($directory, $filename, 'public');

        return [
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $storagePath,
            'size' => $file->getSize(),
            'mime_type' => $mimeType,
        ];
    }

    /**
     * ตรวจสอบว่าไฟล์มีความปลอดภัยหรือไม่
     * 
     * @param string $filePath
     * @return bool
     */
    public static function isSafePath(string $filePath): bool
    {
        // ตรวจสอบ Directory Traversal patterns
        $dangerous = ['../', '..\\', '~', '\x00'];
        
        foreach ($dangerous as $pattern) {
            if (strpos($filePath, $pattern) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Migrate file dari nama lama ke namaใหม่ (UUID)
     * Tetapi tetap simpan nama asli untuk backward compatibility
     * 
     * @param string $oldFileName ชื่อไฟล์เก่า
     * @param string $directory ที่เก็บใหม่
     * @return array ['old_filename' => '...', 'new_filename' => '...', 'path' => '...']
     */
    public function migrateFilePath(string $oldFileName, string $directory): array
    {
        // สร้าง UUID ใหม่ เก็บ extension เดิม
        $ext = pathinfo($oldFileName, PATHINFO_EXTENSION);
        $newFileName = Str::uuid() . '.' . $ext;
        $newPath = $directory . '/' . $newFileName;

        return [
            'old_filename' => $oldFileName,
            'new_filename' => $newFileName,
            'path' => $newPath,
        ];
    }

    /**
     * ตรวจสอบว่าไฟล์จริงๆ มีอยู่ในระบบจริง
     * สำหรับ testing สามารถใช้ Mock mode ได้
     * 
     * @param string $filePath
     * @param bool $mockMode ใช้ mock data แทนตรวจสอบจริง
     * @return bool
     */
    public static function fileExists(string $filePath, bool $mockMode = false): bool
    {
        if (empty($filePath)) {
            return false;
        }

        if ($mockMode) {
            // Mock mode: ถ้ามี path ที่ดูสมเหตุสมผล ให้ถือว่ามี
            return !empty($filePath) && strlen($filePath) > 5;
        }

        return Storage::exists($filePath);
    }

    /**
     * ตรวจสอบไฟล์เก่าว่ายังมีอยู่จริงหรือไม่
     * สำหรับ testing สามารถใช้ Mock mode ได้
     * 
     * @param string $oldFileName
     * @param string $oldDirectory เช่น 'uploads/projects'
     * @param bool $mockMode ใช้ mock data แทนตรวจสอบจริง
     * @return bool
     */
    public static function legacyFileExists(string $oldFileName, string $oldDirectory, bool $mockMode = false): bool
    {
        if (empty($oldFileName)) {
            return false;
        }

        $oldPath = rtrim($oldDirectory, '/') . '/' . $oldFileName;

        if ($mockMode) {
            // Mock mode: ถ้าชื่อไฟล์เก่ามีลักษณะสมเหตุสมผล ให้ถือว่ามี
            return !empty($oldFileName) && strlen($oldFileName) > 3 && strpos($oldFileName, '.') !== false;
        }

        return Storage::exists($oldPath);
    }

    /**
     * ตรวจสอบข้อมูลความสมบูรณ์ของไฟล์ (Integrity Check)
     * สำหรับ debug/testing สามารถใช้ Mock mode ได้
     * 
     * @param string $newPath ชื่อ path ใหม่
     * @param string $oldFileName ชื่อไฟล์เก่า
     * @param string $oldDirectory directory เก่า
     * @param bool $mockMode ใช้ mock data แทนตรวจสอบจริง
     * @return array ['status' => 'ok'/'warning'/'error', 'message' => '...']
     */
    public static function checkFileIntegrity(
        string $newPath, 
        string $oldFileName, 
        string $oldDirectory,
        bool $mockMode = false
    ): array {
        $newExists = self::fileExists($newPath, $mockMode);
        $oldExists = self::legacyFileExists($oldFileName, $oldDirectory, $mockMode);

        // ทั้งอันใหม่และเก่ายังมี
        if ($newExists && $oldExists) {
            return [
                'status' => 'ok',
                'message' => '✅ ไฟล์ใหม่และเก่าอยู่ครบ',
                'newPath' => $newPath,
                'oldFileName' => $oldFileName,
            ];
        }

        // มีเฉพาะไฟล์ใหม่
        if ($newExists && !$oldExists) {
            return [
                'status' => 'ok',
                'message' => '✅ ใช้ไฟล์ใหม่ (UUID)',
                'newPath' => $newPath,
            ];
        }

        // มีเฉพาะไฟล์เก่า
        if (!$newExists && $oldExists) {
            return [
                'status' => 'warning',
                'message' => '⚠️  ใช้ fallback ไฟล์เก่า',
                'oldFileName' => $oldFileName,
            ];
        }

        // ไม่มีไฟล์ทั้งคู่
        return [
            'status' => 'error',
            'message' => '❌ ไฟล์หายไป (DB มีแต่ folder ไม่มี)',
            'newPath' => $newPath,
            'oldFileName' => $oldFileName,
        ];
    }

    /**
     * Mock data generator: สำหรับ testing โดยอ้างอิงจากชื่อไฟล์เก่า
     * 
     * @param string $oldFileName ชื่อไฟล์เก่า เช่น 'report_2025.pdf'
     * @return array Mock file data
     */
    public static function generateMockFileData(string $oldFileName): array
    {
        $ext = pathinfo($oldFileName, PATHINFO_EXTENSION);
        $uuidName = Str::uuid() . '.' . $ext;

        // สร้าง mock MIME type จาก extension
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
        ];

        // Mock size based on extension (ตัวอย่าง)
        $mockSizes = [
            'pdf' => 2097152,    // 2 MB
            'docx' => 1048576,   // 1 MB
            'xlsx' => 512000,    // 500 KB
            'jpg' => 3145728,    // 3 MB
            'png' => 2621440,    // 2.5 MB
        ];

        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        $size = $mockSizes[$ext] ?? 1048576;

        return [
            'old_filename' => $oldFileName,
            'new_filename' => $uuidName,
            'mime_type' => $mimeType,
            'size' => $size,
            'extension' => $ext,
        ];
    }

    /**
     * Mock integrity check: อ้างอิงจากชื่อไฟล์เก่า
     * ใช้สำหรับทดสอบโดยไม่ต้องไฟล์จริง
     * 
     * @param string $oldFileName ชื่อไฟล์เก่า
     * @return array Mock integrity result
     */
    public static function mockIntegrityCheck(string $oldFileName): array
    {
        // Generate mock data from old filename
        $mockData = self::generateMockFileData($oldFileName);
        
        // Simulate different scenarios based on filename
        if (strpos($oldFileName, 'missing') !== false || empty($oldFileName)) {
            return [
                'status' => 'error',
                'message' => '❌ ไฟล์หายไป (DB มีแต่ folder ไม่มี)',
                'oldFileName' => $oldFileName,
                'mockData' => $mockData,
            ];
        }

        if (strpos($oldFileName, 'legacy') !== false) {
            return [
                'status' => 'warning',
                'message' => '⚠️  ใช้ fallback ไฟล์เก่า',
                'oldFileName' => $oldFileName,
                'mockData' => $mockData,
            ];
        }

        // Default: assume both old and new files exist
        return [
            'status' => 'ok',
            'message' => '✅ ไฟล์ใหม่และเก่าอยู่ครบ',
            'oldFileName' => $oldFileName,
            'newPath' => 'projects/' . $mockData['new_filename'],
            'mockData' => $mockData,
        ];
    }
}
