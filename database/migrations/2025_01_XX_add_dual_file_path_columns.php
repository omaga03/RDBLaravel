<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * การเพิ่มฟิวด์สำหรับ Dual File Path Support
     * เพื่อรองรับทั้งชื่อไฟล์เก่า (fallback) และ UUID path ใหม่
     */
    public function up(): void
    {
        // ตาราโครงการตีพิมพ์ (rdb_published)
        if (Schema::hasTable('rdb_published') && Schema::hasColumn('rdb_published', 'pub_file')) {
            Schema::table('rdb_published', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_published', 'pf_path')) {
                    $table->string('pf_path')->nullable()->after('pub_file')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('rdb_published', 'pf_size')) {
                    $table->unsignedBigInteger('pf_size')->nullable()->after('pf_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('rdb_published', 'pf_mime_type')) {
                    $table->string('pf_mime_type')->nullable()->after('pf_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตารานักวิจัย (rdb_researcher)
        if (Schema::hasTable('rdb_researcher') && Schema::hasColumn('rdb_researcher', 'researcher_picture')) {
            Schema::table('rdb_researcher', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_researcher', 'rf_path')) {
                    $table->string('rf_path')->nullable()->after('researcher_picture')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('rdb_researcher', 'rf_size')) {
                    $table->unsignedBigInteger('rf_size')->nullable()->after('rf_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('rdb_researcher', 'rf_mime_type')) {
                    $table->string('rf_mime_type')->nullable()->after('rf_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตาราการประชุมทั้งประเทศ (research_coferenceinthai)
        if (Schema::hasTable('research_coferenceinthai') && Schema::hasColumn('research_coferenceinthai', 'con_img')) {
            Schema::table('research_coferenceinthai', function (Blueprint $table) {
                if (!Schema::hasColumn('research_coferenceinthai', 'file_path')) {
                    $table->string('file_path')->nullable()->after('con_img')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('research_coferenceinthai', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable()->after('file_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('research_coferenceinthai', 'file_mime_type')) {
                    $table->string('file_mime_type')->nullable()->after('file_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตาราข่าววิจัย (research_news)
        if (Schema::hasTable('research_news') && Schema::hasColumn('research_news', 'news_img')) {
            Schema::table('research_news', function (Blueprint $table) {
                if (!Schema::hasColumn('research_news', 'news_path')) {
                    $table->string('news_path')->nullable()->after('news_img')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('research_news', 'news_size')) {
                    $table->unsignedBigInteger('news_size')->nullable()->after('news_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('research_news', 'news_mime_type')) {
                    $table->string('news_mime_type')->nullable()->after('news_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตาราประเภทโครงการย่อย (rdb_project_types_sub)
        if (Schema::hasTable('rdb_project_types_sub') && Schema::hasColumn('rdb_project_types_sub', 'pts_file')) {
            Schema::table('rdb_project_types_sub', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_project_types_sub', 'pts_path')) {
                    $table->string('pts_path')->nullable()->after('pts_file')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('rdb_project_types_sub', 'pts_size')) {
                    $table->unsignedBigInteger('pts_size')->nullable()->after('pts_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('rdb_project_types_sub', 'pts_mime_type')) {
                    $table->string('pts_mime_type')->nullable()->after('pts_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตาราดิปโลม (rdb_dip)
        if (Schema::hasTable('rdb_dip') && Schema::hasColumn('rdb_dip', 'dip_files')) {
            Schema::table('rdb_dip', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_dip', 'dip_path')) {
                    $table->string('dip_path')->nullable()->after('dip_files')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('rdb_dip', 'dip_size')) {
                    $table->unsignedBigInteger('dip_size')->nullable()->after('dip_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('rdb_dip', 'dip_mime_type')) {
                    $table->string('dip_mime_type')->nullable()->after('dip_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตาระสถานะวารสาร (journal_status)
        if (Schema::hasTable('journal_status') && Schema::hasColumn('journal_status', 'jou_files')) {
            Schema::table('journal_status', function (Blueprint $table) {
                if (!Schema::hasColumn('journal_status', 'js_path')) {
                    $table->string('js_path')->nullable()->after('jou_files')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('journal_status', 'js_size')) {
                    $table->unsignedBigInteger('js_size')->nullable()->after('js_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('journal_status', 'js_mime_type')) {
                    $table->string('js_mime_type')->nullable()->after('js_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตาราหน้าอื่นๆของวารสาร (journalotherpage)
        if (Schema::hasTable('journalotherpage') && Schema::hasColumn('journalotherpage', 'file_name')) {
            Schema::table('journalotherpage', function (Blueprint $table) {
                if (!Schema::hasColumn('journalotherpage', 'jop_path')) {
                    $table->string('jop_path')->nullable()->after('file_name')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('journalotherpage', 'jop_size')) {
                    $table->unsignedBigInteger('jop_size')->nullable()->after('jop_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('journalotherpage', 'jop_mime_type')) {
                    $table->string('jop_mime_type')->nullable()->after('jop_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตารา NACC (rdb_nacc)
        if (Schema::hasTable('rdb_nacc') && Schema::hasColumn('rdb_nacc', 'nacc_files')) {
            Schema::table('rdb_nacc', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_nacc', 'nacc_path')) {
                    $table->string('nacc_path')->nullable()->after('nacc_files')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('rdb_nacc', 'nacc_size')) {
                    $table->unsignedBigInteger('nacc_size')->nullable()->after('nacc_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('rdb_nacc', 'nacc_mime_type')) {
                    $table->string('nacc_mime_type')->nullable()->after('nacc_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตาราโครงการ (rdb_project)
        if (Schema::hasTable('rdb_project')) {
            Schema::table('rdb_project', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_project', 'pro_abstract_file_path')) {
                    $table->string('pro_abstract_file_path')->nullable()->after('pro_abstract_file')->comment('ที่เก็บไฟล์บทคัดย่อใหม่');
                }
                if (!Schema::hasColumn('rdb_project', 'pro_abstract_size')) {
                    $table->unsignedBigInteger('pro_abstract_size')->nullable()->after('pro_abstract_file_path')->comment('ขนาดไฟล์บทคัดย่อ');
                }
                if (!Schema::hasColumn('rdb_project', 'pro_abstract_mime')) {
                    $table->string('pro_abstract_mime')->nullable()->after('pro_abstract_size')->comment('MIME type บทคัดย่อ');
                }
                if (!Schema::hasColumn('rdb_project', 'pro_file_path')) {
                    $table->string('pro_file_path')->nullable()->after('pro_file')->comment('ที่เก็บไฟล์รายงานใหม่');
                }
                if (!Schema::hasColumn('rdb_project', 'pro_file_size')) {
                    $table->unsignedBigInteger('pro_file_size')->nullable()->after('pro_file_path')->comment('ขนาดไฟล์รายงาน');
                }
                if (!Schema::hasColumn('rdb_project', 'pro_file_mime')) {
                    $table->string('pro_file_mime')->nullable()->after('pro_file_size')->comment('MIME type รายงาน');
                }
            });
        }

        // ตาราไฟล์โครงการ (rdb_project_files)
        if (Schema::hasTable('rdb_project_files')) {
            Schema::table('rdb_project_files', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_project_files', 'prf_safe_name')) {
                    $table->string('prf_safe_name')->nullable()->after('rf_files')->comment('ชื่อไฟล์ที่ปลอดภัย (UUID)');
                }
                if (!Schema::hasColumn('rdb_project_files', 'prf_path')) {
                    $table->string('prf_path')->nullable()->after('prf_safe_name')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('rdb_project_files', 'prf_size')) {
                    $table->unsignedBigInteger('prf_size')->nullable()->after('prf_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('rdb_project_files', 'prf_mime_type')) {
                    $table->string('prf_mime_type')->nullable()->after('prf_size')->comment('MIME type ของไฟล์');
                }
            });
        }

        // ตาราการใช้ประโยชน์โครงการ (rdb_project_utilize)
        if (Schema::hasTable('rdb_project_utilize') && Schema::hasColumn('rdb_project_utilize', 'utz_files')) {
            Schema::table('rdb_project_utilize', function (Blueprint $table) {
                if (!Schema::hasColumn('rdb_project_utilize', 'uf_path')) {
                    $table->string('uf_path')->nullable()->after('utz_files')->comment('ที่เก็บไฟล์ใหม่ (UUID path)');
                }
                if (!Schema::hasColumn('rdb_project_utilize', 'uf_size')) {
                    $table->unsignedBigInteger('uf_size')->nullable()->after('uf_path')->comment('ขนาดไฟล์ (bytes)');
                }
                if (!Schema::hasColumn('rdb_project_utilize', 'uf_mime_type')) {
                    $table->string('uf_mime_type')->nullable()->after('uf_size')->comment('MIME type ของไฟล์');
                }
            });
        }
    }

    public function down(): void
    {
        $tableColumns = [
            'rdb_published' => ['pf_path', 'pf_size', 'pf_mime_type'],
            'rdb_researcher' => ['rf_path', 'rf_size', 'rf_mime_type'],
            'research_coferenceinthai' => ['file_path', 'file_size', 'file_mime_type'],
            'research_news' => ['news_path', 'news_size', 'news_mime_type'],
            'rdb_project_types_sub' => ['pts_path', 'pts_size', 'pts_mime_type'],
            'rdb_dip' => ['dip_path', 'dip_size', 'dip_mime_type'],
            'journal_status' => ['js_path', 'js_size', 'js_mime_type'],
            'journalotherpage' => ['jop_path', 'jop_size', 'jop_mime_type'],
            'rdb_nacc' => ['nacc_path', 'nacc_size', 'nacc_mime_type'],
            'rdb_project' => ['pro_abstract_file_path', 'pro_abstract_size', 'pro_abstract_mime', 'pro_file_path', 'pro_file_size', 'pro_file_mime'],
            'rdb_project_files' => ['prf_safe_name', 'prf_path', 'prf_size', 'prf_mime_type'],
            'rdb_project_utilize' => ['uf_path', 'uf_size', 'uf_mime_type'],
        ];

        foreach ($tableColumns as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($columns) {
                    foreach ($columns as $column) {
                        if (Schema::hasColumn($t->getTable(), $column)) {
                            $t->dropColumn($column);
                        }
                    }
                });
            }
        }
    }
};