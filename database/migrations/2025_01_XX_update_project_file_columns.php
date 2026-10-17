<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // เพิ่มฟิวด์ใหม่ ตารางโครงการ
        if (Schema::hasTable('rdb_project')) {
            Schema::table('rdb_project', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_project', 'pro_abstract_file_path')) {
                    $table->string('pro_abstract_file_path')->nullable()->after('pro_abstract_file');
                }
                if (!Schema::hasColumn('rdb_project', 'pro_abstract_size')) {
                    $table->unsignedBigInteger('pro_abstract_size')->nullable()->after('pro_abstract_file_path');
                }
                if (!Schema::hasColumn('rdb_project', 'pro_abstract_mime')) {
                    $table->string('pro_abstract_mime')->nullable()->after('pro_abstract_size');
                }

                if (!Schema::hasColumn('rdb_project', 'pro_file_path')) {
                    $table->string('pro_file_path')->nullable()->after('pro_file');
                }
                if (!Schema::hasColumn('rdb_project', 'pro_file_size')) {
                    $table->unsignedBigInteger('pro_file_size')->nullable()->after('pro_file_path');
                }
                if (!Schema::hasColumn('rdb_project', 'pro_file_mime')) {
                    $table->string('pro_file_mime')->nullable()->after('pro_file_size');
                }
            });
        }

        // เพิ่มฟิวด์ใหม่ ตารางไฟล์โครงการ
        if (Schema::hasTable('rdb_project_files')) {
            Schema::table('rdb_project_files', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_project_files', 'prf_safe_name')) {
                    $table->string('prf_safe_name')->nullable()->after('rf_files');
                }
                if (!Schema::hasColumn('rdb_project_files', 'prf_path')) {
                    $table->string('prf_path')->nullable()->after('prf_safe_name');
                }
                if (!Schema::hasColumn('rdb_project_files', 'prf_size')) {
                    $table->unsignedBigInteger('prf_size')->nullable()->after('prf_path');
                }
                if (!Schema::hasColumn('rdb_project_files', 'prf_mime_type')) {
                    $table->string('prf_mime_type')->nullable()->after('prf_size');
                }
            });
        }

        // Migrate ข้อมูลเก่าไปใหม่
        $this->migrateProjectFiles();
        $this->migrateProjectFileRecords();
    }

    /**
     * Migrate ข้อมูลไฟล์จาก pro_abstract_file และ pro_file ไปยังฟิวด์ใหม่
     */
    private function migrateProjectFiles(): void
    {
        if (!Schema::hasTable('rdb_project')) {
            return;
        }

        $projects = \Illuminate\Support\Facades\DB::table('rdb_project')
            ->where(function($q) {
                $q->whereNotNull('pro_abstract_file')
                  ->orWhereNotNull('pro_file');
            })
            ->get();

        foreach ($projects as $project) {
            // Migrate abstract file
            if ($project->pro_abstract_file) {
                $this->migrateFileField(
                    'rdb_project',
                    $project->pro_id,
                    'pro_id',
                    'pro_abstract_file',
                    'pro_abstract_file_path',
                    'pro_abstract_size',
                    'pro_abstract_mime',
                    'abstracts'
                );
            }

            // Migrate full report file
            if ($project->pro_file) {
                $this->migrateFileField(
                    'rdb_project',
                    $project->pro_id,
                    'pro_id',
                    'pro_file',
                    'pro_file_path',
                    'pro_file_size',
                    'pro_file_mime',
                    'reports'
                );
            }
        }
    }

    /**
     * Migrate ข้อมูลไฟล์จาก rdb_project_files
     */
    private function migrateProjectFileRecords(): void
    {
        if (!Schema::hasTable('rdb_project_files')) {
            return;
        }

        $files = \Illuminate\Support\Facades\DB::table('rdb_project_files')
            ->whereNotNull('rf_files')
            ->get();

        foreach ($files as $file) {
            $oldFileName = $file->rf_files;
            $oldFilePath = 'uploads/project_files/' . $oldFileName;

            try {
                // สร้าง UUID ใหม่ เก็บ extension เดิม
                $ext = pathinfo($oldFileName, PATHINFO_EXTENSION);
                $newFileName = Str::uuid() . '.' . $ext;
                $newPath = 'project_files/' . $newFileName;

                // Copy ไฟล์เก่าไปชื่อใหม่ (ตรวจสอบการมีอยู่ก่อน)
                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('local')->copy($oldFilePath, $newPath);

                    // Update ฟิวด์ใหม่
                    \Illuminate\Support\Facades\DB::table('rdb_project_files')
                        ->where('id', $file->id)
                        ->update([
                            'prf_safe_name' => $newFileName,
                            'prf_path' => $newPath,
                            'prf_size' => Storage::disk('local')->size($newPath),
                            'prf_mime_type' => mime_content_type(Storage::disk('local')->path($newPath)) ?: 'application/octet-stream',
                        ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to migrate file ID {$file->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Helper: Migrate single file field
     */
    private function migrateFileField(
        string $table,
        mixed $id,
        string $pkField,
        string $oldField,
        string $newPathField,
        string $newSizeField,
        string $newMimeField,
        string $newDirectory
    ): void {
        $record = \Illuminate\Support\Facades\DB::table($table)->where($pkField, $id)->first();
        
        if (!$record || !$record->$oldField) {
            return;
        }

        $oldFileName = $record->$oldField;
        $oldFilePath = 'uploads/projects/' . $oldFileName;

        try {
            if (Storage::disk('public')->exists($oldFilePath)) {
                // สร้าง UUID ใหม่ เก็บ extension เดิม
                $ext = pathinfo($oldFileName, PATHINFO_EXTENSION);
                $newFileName = Str::uuid() . '.' . $ext;
                $newPath = $newDirectory . '/' . $newFileName;

                // Copy ไฟล์จากเดิม
                Storage::disk('local')->copy($oldFilePath, $newPath);

                // อัพเดตฟิวด์ใหม่
                \Illuminate\Support\Facades\DB::table($table)
                    ->where($pkField, $id)
                    ->update([
                        $newPathField => $newPath,
                        $newSizeField => Storage::disk('local')->size($newPath),
                        $newMimeField => mime_content_type(Storage::disk('local')->path($newPath)) ?: 'application/octet-stream',
                    ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to migrate file for $table ID $id: " . $e->getMessage());
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rdb_project')) {
            Schema::table('rdb_project', function (Blueprint $table) {
                $columns = ['pro_abstract_file_path', 'pro_abstract_size', 'pro_abstract_mime', 
                           'pro_file_path', 'pro_file_size', 'pro_file_mime'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('rdb_project', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('rdb_project_files')) {
            Schema::table('rdb_project_files', function (Blueprint $table) {
                $columns = ['prf_safe_name', 'prf_path', 'prf_size', 'prf_mime_type'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('rdb_project_files', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
