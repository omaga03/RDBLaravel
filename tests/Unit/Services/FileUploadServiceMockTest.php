<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FileUploadService;

class FileUploadServiceMockTest extends TestCase
{
    /**
     * ทดสอบ: Mock file data generation จากชื่อไฟล์เก่า
     */
    public function test_generate_mock_file_data(): void
    {
        $mockData = FileUploadService::generateMockFileData('report_2025.pdf');

        $this->assertEquals('report_2025.pdf', $mockData['old_filename']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}.*\.pdf$/', $mockData['new_filename']);
        $this->assertEquals('application/pdf', $mockData['mime_type']);
        $this->assertIsInt($mockData['size']);
    }

    /**
     * ทดสอบ: Mock integrity check - ไฟล์เก่า
     */
    public function test_mock_integrity_check_legacy(): void
    {
        $result = FileUploadService::mockIntegrityCheck('legacy_document.docx');

        $this->assertEquals('warning', $result['status']);
        $this->assertStringContainsString('fallback', $result['message']);
        $this->assertEquals('legacy_document.docx', $result['oldFileName']);
    }

    /**
     * ทดสอบ: Mock integrity check - ไฟล์หาย
     */
    public function test_mock_integrity_check_missing(): void
    {
        $result = FileUploadService::mockIntegrityCheck('missing_file.pdf');

        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('หายไป', $result['message']);
    }

    /**
     * ทดสอบ: Mock integrity check - ไฟล์ปกติ
     */
    public function test_mock_integrity_check_normal(): void
    {
        $result = FileUploadService::mockIntegrityCheck('report_2025.pdf');

        $this->assertEquals('ok', $result['status']);
        $this->assertStringContainsString('อยู่ครบ', $result['message']);
        $this->assertArrayHasKey('mockData', $result);
    }

    /**
     * ทดสอบ: File exists check with mock mode
     */
    public function test_file_exists_mock_mode(): void
    {
        // Mock mode: true (ใช้ mock data)
        $this->assertTrue(
            FileUploadService::fileExists('projects/report.pdf', true)
        );

        // Mock mode: false (ตรวจสอบจริง - ไม่มี)
        $this->assertFalse(
            FileUploadService::fileExists('projects/nonexistent.pdf', false)
        );
    }

    /**
     * ทดสอบ: Legacy file exists check with mock mode
     */
    public function test_legacy_file_exists_mock_mode(): void
    {
        // Mock mode: true (ใช้ mock data)
        $this->assertTrue(
            FileUploadService::legacyFileExists('old_report.pdf', 'uploads/projects', true)
        );

        // ชื่อไฟล์ว่าง
        $this->assertFalse(
            FileUploadService::legacyFileExists('', 'uploads/projects', true)
        );
    }

    /**
     * ทดสอบ: Integrity check with mock mode
     */
    public function test_integrity_check_mock_mode(): void
    {
        $result = FileUploadService::checkFileIntegrity(
            'projects/550e8400-e29b-41d4-a716-446655440000.pdf',
            'old_report.pdf',
            'uploads/projects',
            true  // mockMode
        );

        $this->assertEquals('ok', $result['status']);
        $this->assertStringContainsString('อยู่ครบ', $result['message']);
    }

    /**
     * ทดสอบ: Migrate multiple old filenames
     */
    public function test_migrate_batch_old_filenames(): void
    {
        $oldFileNames = [
            'abstract_2025.pdf',
            'report_final.docx',
            'summary_2024.xlsx',
            'photo_researcher.jpg',
        ];

        foreach ($oldFileNames as $oldFileName) {
            $mockData = FileUploadService::generateMockFileData($oldFileName);
            
            $this->assertEquals($oldFileName, $mockData['old_filename']);
            $this->assertNotEmpty($mockData['new_filename']);
            $this->assertNotEmpty($mockData['mime_type']);
            $this->assertIsInt($mockData['size']);
        }
    }
}
