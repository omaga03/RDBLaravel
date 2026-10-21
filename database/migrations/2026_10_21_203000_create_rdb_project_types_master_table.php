<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create rdb_project_types_master table
        if (!Schema::hasTable('rdb_project_types_master')) {
            Schema::create('rdb_project_types_master', function (Blueprint $table) {
                $table->id('master_pt_id');
                $table->string('master_pt_name');
                $table->unsignedBigInteger('pttg_id')->nullable()->comment('FK to rdb_project_types_group');
                $table->integer('order_index')->default(0);
                $table->string('color_code', 20)->nullable();
                $table->timestamps();
            });
        }

        // 2. Add master_pt_id column to rdb_project_types
        if (!Schema::hasColumn('rdb_project_types', 'master_pt_id')) {
            Schema::table('rdb_project_types', function (Blueprint $table) {
                $table->unsignedBigInteger('master_pt_id')->nullable()->after('pttg_id')->index();
            });
        }

        // 3. Seed Master Categories (6 Standard Categories)
        $masters = [
            1 => [
                'master_pt_name' => 'ววน. / สกสว.',
                'pttg_id' => 1,
                'order_index' => 1,
                'color_code' => '#3B82F6',
            ],
            2 => [
                'master_pt_name' => 'แหล่งทุนภายนอก',
                'pttg_id' => 2,
                'order_index' => 2,
                'color_code' => '#F59E0B',
            ],
            3 => [
                'master_pt_name' => 'ทุนภายในคณะ',
                'pttg_id' => 3,
                'order_index' => 3,
                'color_code' => '#10B981',
            ],
            4 => [
                'master_pt_name' => 'รายได้มหาวิทยาลัย',
                'pttg_id' => 3,
                'order_index' => 4,
                'color_code' => '#8B5CF6',
            ],
            5 => [
                'master_pt_name' => 'ทุนวิจัยสถาบัน',
                'pttg_id' => 3,
                'order_index' => 5,
                'color_code' => '#06B6D4',
            ],
            6 => [
                'master_pt_name' => 'งบประมาณส่วนตัว',
                'pttg_id' => 4,
                'order_index' => 6,
                'color_code' => '#64748B',
            ],
        ];

        foreach ($masters as $id => $data) {
            DB::table('rdb_project_types_master')->updateOrInsert(
                ['master_pt_id' => $id],
                array_merge($data, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 4. Backfill existing 109 rows in rdb_project_types
        $existingTypes = DB::table('rdb_project_types')->get();
        foreach ($existingTypes as $type) {
            $name = trim(preg_replace('/\s+/', ' ', $type->pt_name));

            if (str_contains($name, 'ภายนอก')) {
                $masterId = 2; // แหล่งทุนภายนอก
            } elseif (str_contains($name, 'ส่วนตัว')) {
                $masterId = 6; // งบประมาณส่วนตัว
            } elseif (str_contains($name, 'วิจัยสถาบัน')) {
                $masterId = 5; // ทุนวิจัยสถาบัน
            } elseif ($name === 'ทุนภายในคณะ') {
                $masterId = 3; // ทุนภายในคณะ
            } elseif (
                str_contains($name, 'ชุมชน') ||
                str_contains($name, 'ท้องถิ่น') ||
                str_contains($name, 'เชิงพื้นที่') ||
                str_contains($name, 'ศาสตร์พระราชา') ||
                str_contains($name, 'การเรียนการสอน') ||
                str_contains($name, 'การจัดการความรู้') ||
                str_contains($name, 'นักวิจัยรุ่นใหม่') ||
                str_contains($name, 'นักวิจัยหน้าใหม่') ||
                str_contains($name, 'เงินรายได้') ||
                str_contains($name, 'งานวิจัยและพัฒนานวัตกรรม') ||
                $name === 'ทั่วไป' ||
                $name === 'ทุนอื่น ๆ'
            ) {
                $masterId = 4; // รายได้มหาวิทยาลัย
            } else {
                $masterId = 1; // ววน. / สกสว.
            }

            // Also clean up trailing tab/whitespace from pt_name
            DB::table('rdb_project_types')
                ->where('pt_id', $type->pt_id)
                ->update([
                    'master_pt_id' => $masterId,
                    'pt_name' => $name,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('rdb_project_types', 'master_pt_id')) {
            Schema::table('rdb_project_types', function (Blueprint $table) {
                $table->dropColumn('master_pt_id');
            });
        }

        Schema::dropIfExists('rdb_project_types_master');
    }
};
