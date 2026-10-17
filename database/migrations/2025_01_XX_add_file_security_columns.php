<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ตาราไฟล์โครงการ
        if (Schema::hasTable('rdb_project_files')) {
            Schema::table('rdb_project_files', function (Blueprint $table) {
                // ลบ prf_filename ถ้าต้องการเก็บแค่ UUID
                // หรือเปลี่ยนชื่อเป็น prf_safe_name
                if (!Schema::hasColumn('rdb_project_files', 'prf_safe_name')) {
                    $table->string('prf_safe_name')->nullable()->comment('ชื่อไฟล์ (UUID)');
                }
                if (!Schema::hasColumn('rdb_project_files', 'prf_mime_type')) {
                    $table->string('prf_mime_type')->nullable()->comment('MIME type ของไฟล์');
                }
                if (!Schema::hasColumn('rdb_project_files', 'prf_size')) {
                    $table->unsignedBigInteger('prf_size')->nullable()->comment('ขนาดไฟล์ (bytes)');
                }
            });
        }

        // ตารากำหนดการ (ถ้ามี)
        if (Schema::hasTable('rdb_research_files')) {
            Schema::table('rdb_research_files', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_research_files', 'rf_safe_name')) {
                    $table->string('rf_safe_name')->nullable()->comment('ชื่อไฟล์ที่ปลอดภัย (UUID)');
                }
                if (!Schema::hasColumn('rdb_research_files', 'rf_mime_type')) {
                    $table->string('rf_mime_type')->nullable()->comment('MIME type ของไฟล์');
                }
                if (!Schema::hasColumn('rdb_research_files', 'rf_size')) {
                    $table->unsignedBigInteger('rf_size')->nullable()->comment('ขนาดไฟล์ (bytes)');
                }
            });
        }

        // ตารานักวิจัย
        if (Schema::hasTable('rdb_researcher_files')) {
            Schema::table('rdb_researcher_files', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_researcher_files', 'rf_safe_name')) {
                    $table->string('rf_safe_name')->nullable()->comment('ชื่อไฟล์ที่ปลอดภัย (UUID)');
                }
                if (!Schema::hasColumn('rdb_researcher_files', 'rf_mime_type')) {
                    $table->string('rf_mime_type')->nullable()->comment('MIME type ของไฟล์');
                }
                if (!Schema::hasColumn('rdb_researcher_files', 'rf_size')) {
                    $table->unsignedBigInteger('rf_size')->nullable()->comment('ขนาดไฟล์ (bytes)');
                }
            });
        }

        // ตารางการตีพิมพ์
        if (Schema::hasTable('rdb_published_files')) {
            Schema::table('rdb_published_files', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_published_files', 'pf_safe_name')) {
                    $table->string('pf_safe_name')->nullable()->comment('ชื่อไฟล์ที่ปลอดภัย (UUID)');
                }
                if (!Schema::hasColumn('rdb_published_files', 'pf_mime_type')) {
                    $table->string('pf_mime_type')->nullable()->comment('MIME type ของไฟล์');
                }
                if (!Schema::hasColumn('rdb_published_files', 'pf_size')) {
                    $table->unsignedBigInteger('pf_size')->nullable()->comment('ขนาดไฟล์ (bytes)');
                }
            });
        }

        // ตารางงบประมาณ
        if (Schema::hasTable('rdb_budget_files')) {
            Schema::table('rdb_budget_files', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_budget_files', 'bf_safe_name')) {
                    $table->string('bf_safe_name')->nullable()->comment('ชื่อไฟล์ที่ปลอดภัย (UUID)');
                }
                if (!Schema::hasColumn('rdb_budget_files', 'bf_mime_type')) {
                    $table->string('bf_mime_type')->nullable()->comment('MIME type ของไฟล์');
                }
                if (!Schema::hasColumn('rdb_budget_files', 'bf_size')) {
                    $table->unsignedBigInteger('bf_size')->nullable()->comment('ขนาดไฟล์ (bytes)');
                }
            });
        }

        // ตารางการใช้ประโยชน์
        if (Schema::hasTable('rdb_utilization_files')) {
            Schema::table('rdb_utilization_files', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_utilization_files', 'uf_safe_name')) {
                    $table->string('uf_safe_name')->nullable()->comment('ชื่อไฟล์ที่ปลอดภัย (UUID)');
                }
                if (!Schema::hasColumn('rdb_utilization_files', 'uf_mime_type')) {
                    $table->string('uf_mime_type')->nullable()->comment('MIME type ของไฟล์');
                }
                if (!Schema::hasColumn('rdb_utilization_files', 'uf_size')) {
                    $table->unsignedBigInteger('uf_size')->nullable()->comment('ขนาดไฟล์ (bytes)');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'rdb_project_files' => ['prf_safe_name', 'prf_mime_type', 'prf_size'],
            'rdb_research_files' => ['rf_safe_name', 'rf_mime_type', 'rf_size'],
            'rdb_researcher_files' => ['rf_safe_name', 'rf_mime_type', 'rf_size'],
            'rdb_published_files' => ['pf_safe_name', 'pf_mime_type', 'pf_size'],
            'rdb_budget_files' => ['bf_safe_name', 'bf_mime_type', 'bf_size'],
            'rdb_utilization_files' => ['uf_safe_name', 'uf_mime_type', 'uf_size'],
        ];

        foreach ($tables as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        if (Schema::hasColumn($table->getTable(), $column)) {
                            $table->dropColumn($column);
                        }
                    }
                });
            }
        }
    }
};