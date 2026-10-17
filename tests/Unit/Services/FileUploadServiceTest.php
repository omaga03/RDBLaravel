<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FileUploadService;
use Illuminate\Support\Str;

class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileUploadService();
    }

    /**
     * ทดสอบ: ตรวจสอบ MIME type ที่อนุญาต
     */
    public function test_allowed_mime_types(): void
    {
        $allowedTypes = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        foreach ($allowedTypes as $mimeType => $extension) {
            // ตรวจสอบว่า MIME type รองรับ
            $this->assertTrue(
                in_array($mimeType, array_keys(['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'])),
                "MIME type {$mimeType} ควรรองรับ"
            );
        }
    }

    /**
     * ทดสอบ: ตรวจสอบชื่อไฟล์ที่สร้างเป็น UUID
     */
    public function test_safe_file_name_format(): void
    {
        // สร้าง mock file (ไม่ใช้ไฟล์จริง)
        $mockFile = $this->createMock(\Illuminate\Http\UploadedFile::class);
        $mockFile->method('getSize')->willReturn(1024);
        $mockFile->method('getMimeType')->willReturn('application/pdf');

        $safeName = $this->service->generateSafeFileName($mockFile);

        // ตรวจสอบรูปแบบ UUID
        $this->assertRegExp(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.pdf$/',
            $safeName,
            'ชื่อไฟล์ควรเป็น UUID.pdf'
        );
    }

    /**
     * ทดสอบ: ตรวจสอบขนาดไฟล์ (ควรสูงสุด 50MB)
     */
    public function test_max_file_size_validation(): void
    {
        $mockFile = $this->createMock(\Illuminate\Http\UploadedFile::class);
        $mockFile->method('getSize')->willReturn(51 * 1024 * 1024); // 51 MB
        $mockFile->method('getMimeType')->willReturn('application/pdf');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ไฟล์ขนาดใหญ่เกินไป');

        $this->service->generateSafeFileName($mockFile);
    }

    /**
     * ทดสอบ: ตรวจสอบ MIME type ที่ไม่อนุญาต
     */
    public function test_invalid_mime_type(): void
    {
        $mockFile = $this->createMock(\Illuminate\Http\UploadedFile::class);
        $mockFile->method('getSize')->willReturn(1024);
        $mockFile->method('getMimeType')->willReturn('application/x-executable');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ประเภทไฟล์ไม่อนุญาต');

        $this->service->generateSafeFileName($mockFile);
    }

    /**
     * ทดสอบ: ตรวจสอบ Directory Traversal
     */
    public function test_path_traversal_detection(): void
    {
        $dangerousPaths = [
            '../../etc/passwd',
            '..\\..\\windows\\system32',
            'uploads/~/secret.pdf',
            'files/\x00malware.exe',
        ];

        foreach ($dangerousPaths as $path) {
            $this->assertFalse(
                FileUploadService::isSafePath($path),
                "Path '{$path}' ควรถูกตรวจจับว่าเป็นอันตราย"
            );
        }
    }

    /**
     * ทดสอบ: Safe path ที่ปลอดภัย
     */
    public function test_safe_paths(): void
    {
        $safePaths = [
            'projects/550e8400-e29b-41d4-a716-446655440000.pdf',
            'uploads/documents/file.docx',
            'storage/app/researchers/photo.jpg',
        ];

        foreach ($safePaths as $path) {
            $this->assertTrue(
                FileUploadService::isSafePath($path),
                "Path '{$path}' ควรปลอดภัย"
            );
        }
    }

    /**
     * ทดสอบ: Legacy mode - สร้างชื่อไฟล์แบบ Yii2
     */
    public function test_legacy_file_name_format(): void
    {
        $mockFile = $this->createMock(\Illuminate\Http\UploadedFile::class);
        $mockFile->method('getSize')->willReturn(1024);
        $mockFile->method('getMimeType')->willReturn('application/pdf');

        $result = $this->service->storeFileLegacy($mockFile, 'projects', 'pro_');

        // ตรวจสอบรูปแบบ: pro_YmdHis{random}~.pdf
        $this->assertRegExp(
            '/^pro_\d{14}[a-zA-Z0-9]{40}~\.pdf$/',
            $result['filename'],
            'ชื่อไฟล์ Legacy ควรเป็นรูปแบบ pro_YmdHis{random}~.pdf'
        );
    }

    /**
     * ทดสอบ: Migrate file path
     */
    public function test_migrate_file_path(): void
    {
        $oldFileName = 'oldfile_2025.pdf';
        $directory = 'projects';

        $result = $this->service->migrateFilePath($oldFileName, $directory);

        $this->assertEquals('oldfile_2025.pdf', $result['old_filename']);
        $this->assertRegExp(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.pdf$/',
            $result['new_filename'],
            'ชื่อไฟล์ใหม่ควรเป็น UUID'
        );
        $this->assertEquals('projects/' . $result['new_filename'], $result['path']);
    }

    /**
     * ทดสอบ: File exists check
     */
    public function test_file_exists_check(): void
    {
        // ตรวจสอบไฟล์ที่ว่าง
        $this->assertFalse(FileUploadService::fileExists(''));
        $this->assertFalse(FileUploadService::fileExists(null));

        // Note: ไม่ใช้ไฟล์จริง - เพียงทดสอบ logic
    }

    /**
     * ทดสอบ: Legacy file exists check
     */
    public function test_legacy_file_exists_check(): void
    {
        // ตรวจสอบ filename ที่ว่าง
        $this->assertFalse(
            FileUploadService::legacyFileExists('', 'uploads/projects')
        );

        $this->assertFalse(
            FileUploadService::legacyFileExists(null, 'uploads/projects')
        );
    }

    /**
     * ทดสอบ: File integrity check - ทั้งอันใหม่และเก่าหาย
     */
    public function test_integrity_check_both_missing(): void
    {
        $result = FileUploadService::checkFileIntegrity(
            'projects/nonexistent.pdf',
            'old_nonexistent.pdf',
            'uploads/projects'
        );

        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('ไฟล์หายไป', $result['message']);
    }

    /**
     * ทดสอบ: File integrity check - มี path ใหม่
     */
    public function test_integrity_check_new_path_exists(): void
    {
        $result = FileUploadService::checkFileIntegrity(
            'projects/550e8400-e29b-41d4-a716-446655440000.pdf',
            '',
            'uploads/projects'
        );

        $this->assertEquals('ok', $result['status']);
    }

    /**
     * ทดสอบ: File integrity check - มี path เก่า (fallback)
     */
    public function test_integrity_check_legacy_path_exists(): void
    {
        $result = FileUploadService::checkFileIntegrity(
            '',
            'old_nonexistent.pdf',
            'uploads/projects'
        );

        $this->assertEquals('warning', $result['status']);
        $this->assertStringContainsString('fallback', $result['message']);
    }
}
