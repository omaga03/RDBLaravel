<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InitialMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = time();
        $nowTimestamp = date('Y-m-d H:i:s');

        // 1. ปีงบประมาณ (rdb_year) พ.ศ. 2560 - 2570
        for ($y = 2560; $y <= 2570; $y++) {
            DB::table('rdb_year')->updateOrInsert(
                ['year_name' => $y],
                ['created_at' => $nowTimestamp, 'updated_at' => $nowTimestamp]
            );
        }

        // 2. คำนำหน้าชื่อ (rdb_prefix)
        $prefixes = [
            ['prefix_id' => 1, 'prefix_nameTH' => 'นาย'],
            ['prefix_id' => 2, 'prefix_nameTH' => 'นาง'],
            ['prefix_id' => 3, 'prefix_nameTH' => 'นางสาว'],
            ['prefix_id' => 4, 'prefix_nameTH' => 'ดร.'],
            ['prefix_id' => 5, 'prefix_nameTH' => 'ผศ.'],
            ['prefix_id' => 6, 'prefix_nameTH' => 'ผศ.ดร.'],
            ['prefix_id' => 7, 'prefix_nameTH' => 'รศ.'],
            ['prefix_id' => 8, 'prefix_nameTH' => 'รศ.ดร.'],
            ['prefix_id' => 9, 'prefix_nameTH' => 'ศ.'],
            ['prefix_id' => 10, 'prefix_nameTH' => 'ศ.ดร.'],
        ];
        foreach ($prefixes as $p) {
            DB::table('rdb_prefix')->updateOrInsert(
                ['prefix_id' => $p['prefix_id']],
                ['prefix_nameTH' => $p['prefix_nameTH']]
            );
        }

        // 3. ประเภทหน่วยงาน (rdb_department_type) และ หมวดหมู่ (rdb_department_category)
        DB::table('rdb_department_type')->updateOrInsert(
            ['tdepartment_id' => 1],
            ['tdepartment_nameTH' => 'คณะ/หน่วยงานจัดการเรียนการสอน']
        );
        DB::table('rdb_department_type')->updateOrInsert(
            ['tdepartment_id' => 2],
            ['tdepartment_nameTH' => 'สถาบัน/สำนัก/หน่วยงานสนับสนุน']
        );

        DB::table('rdb_department_category')->updateOrInsert(
            ['depcat_id' => 1],
            ['depcat_name' => 'หน่วยงานสายวิชาการ']
        );
        DB::table('rdb_department_category')->updateOrInsert(
            ['depcat_id' => 2],
            ['depcat_name' => 'หน่วยงานสายสนับสนุน']
        );

        // คณะและหน่วยงาน (rdb_department)
        $departments = [
            ['department_id' => 1, 'department_nameTH' => 'คณะวิทยาศาสตร์และเทคโนโลยี', 'tdepartment_id' => 1],
            ['department_id' => 2, 'department_nameTH' => 'คณะมนุษยศาสตร์และสังคมศาสตร์', 'tdepartment_id' => 1],
            ['department_id' => 3, 'department_nameTH' => 'คณะวิทยาการจัดการ', 'tdepartment_id' => 1],
            ['department_id' => 4, 'department_nameTH' => 'คณะครุศาสตร์', 'tdepartment_id' => 1],
            ['department_id' => 5, 'department_nameTH' => 'คณะเทคโนโลยีการเกษตรและเทคโนโลยีอุตสาหกรรม', 'tdepartment_id' => 1],
            ['department_id' => 12, 'department_nameTH' => 'สถาบันวิจัยและพัฒนา', 'tdepartment_id' => 2],
        ];
        foreach ($departments as $d) {
            DB::table('rdb_department')->updateOrInsert(
                ['department_id' => $d['department_id']],
                $d
            );
        }

        // 4. ตำแหน่งในโครงการวิจัย (rdb_project_position)
        $positions = [
            ['position_id' => 1, 'position_nameTH' => 'หัวหน้าโครงการ'],
            ['position_id' => 2, 'position_nameTH' => 'ผู้ร่วมวิจัย'],
            ['position_id' => 3, 'position_nameTH' => 'ที่ปรึกษาโครงการ'],
            ['position_id' => 4, 'position_nameTH' => 'ผู้ช่วยวิจัย'],
        ];
        foreach ($positions as $pos) {
            DB::table('rdb_project_position')->updateOrInsert(
                ['position_id' => $pos['position_id']],
                ['position_nameTH' => $pos['position_nameTH']]
            );
        }

        // 5. สถานะโครงการ (rdb_project_status)
        $statuses = [
            ['ps_id' => 1, 'ps_name' => 'เสนอโครงการ'],
            ['ps_id' => 2, 'ps_name' => 'อนุมัติ/ทำสัญญา'],
            ['ps_id' => 3, 'ps_name' => 'กำลังดำเนินการ'],
            ['ps_id' => 4, 'ps_name' => 'ส่งรายงานฉบับสมบูรณ์'],
            ['ps_id' => 5, 'ps_name' => 'เสร็จสิ้นสมบูรณ์'],
            ['ps_id' => 6, 'ps_name' => 'คืนเงิน/ยกเลิก'],
        ];
        foreach ($statuses as $s) {
            DB::table('rdb_project_status')->updateOrInsert(
                ['ps_id' => $s['ps_id']],
                ['ps_name' => $s['ps_name']]
            );
        }

        // 6. ประเภทโครงการ (rdb_project_types_group และ rdb_project_types)
        DB::table('rdb_project_types_group')->updateOrInsert(
            ['pttg_id' => 1],
            ['pttg_name' => 'ทุนอุดหนุนการวิจัยทั่วไป']
        );

        $projectTypes = [
            ['pt_id' => 1, 'pt_name' => 'ทุนอุดหนุนการวิจัยงบประมาณแผ่นดิน', 'pttg_id' => 1],
            ['pt_id' => 2, 'pt_name' => 'ทุนอุดหนุนการวิจัยงบประมาณเงินรายได้', 'pttg_id' => 1],
            ['pt_id' => 3, 'pt_name' => 'ทุนอุดหนุนการวิจัยจากแหล่งทุนภายนอก', 'pttg_id' => 1],
        ];
        foreach ($projectTypes as $pt) {
            DB::table('rdb_project_types')->updateOrInsert(
                ['pt_id' => $pt['pt_id']],
                ['pt_name' => $pt['pt_name'], 'pttg_id' => $pt['pttg_id']]
            );
        }

        // 7. ประเภททรัพย์สินทางปัญญา (rdb_dip_type)
        $dipTypes = [
            ['dipt_id' => 1, 'dipt_name' => 'สิทธิบัตรการประดิษฐ์'],
            ['dipt_id' => 2, 'dipt_name' => 'อนุสิทธิบัตร'],
            ['dipt_id' => 3, 'dipt_name' => 'ลิขสิทธิ์'],
            ['dipt_id' => 4, 'dipt_name' => 'เครื่องหมายการค้า'],
        ];
        foreach ($dipTypes as $dt) {
            DB::table('rdb_dip_type')->updateOrInsert(
                ['dipt_id' => $dt['dipt_id']],
                ['dipt_name' => $dt['dipt_name']]
            );
        }

        // 8. ประเภทผลงานตีพิมพ์ (rdb_published_type)
        $pubTypes = [
            ['pubtype_id' => 1, 'pubtype_group' => 'วารสารวิชาการระดับนานาชาติ (Scopus/WoS)'],
            ['pubtype_id' => 2, 'pubtype_group' => 'วารสารวิชาการระดับชาติ (TCI กลุ่ม 1)'],
            ['pubtype_id' => 3, 'pubtype_group' => 'วารสารวิชาการระดับชาติ (TCI กลุ่ม 2)'],
            ['pubtype_id' => 4, 'pubtype_group' => 'รายงานสืบเนื่องจากการประชุมวิชาการ (Proceedings)'],
        ];
        foreach ($pubTypes as $pt) {
            DB::table('rdb_published_type')->updateOrInsert(
                ['pubtype_id' => $pt['pubtype_id']],
                ['pubtype_group' => $pt['pubtype_group']]
            );
        }

        // 9. ประเภทการนำไปใช้ประโยชน์ (rdb_project_utilize_type)
        $utilizeTypes = [
            ['utz_type_id' => 1, 'utz_typr_name' => 'การใช้ประโยชน์เชิงพาณิชย์'],
            ['utz_type_id' => 2, 'utz_typr_name' => 'การใช้ประโยชน์เชิงนโยบาย'],
            ['utz_type_id' => 3, 'utz_typr_name' => 'การใช้ประโยชน์เชิงสาธารณะ'],
            ['utz_type_id' => 4, 'utz_typr_name' => 'การใช้ประโยชน์เชิงวิชาการ'],
        ];
        foreach ($utilizeTypes as $ut) {
            DB::table('rdb_project_utilize_type')->updateOrInsert(
                ['utz_type_id' => $ut['utz_type_id']],
                ['utz_typr_name' => $ut['utz_typr_name']]
            );
        }

        // 10. RBAC Roles & Permissions
        $roles = [
            ['name' => 'The Creator', 'type' => 1, 'description' => 'Super Administrator (Full System Control)'],
            ['name' => 'Admin', 'type' => 1, 'description' => 'Administrator'],
            ['name' => 'Staff', 'type' => 1, 'description' => 'Staff Operator'],
            ['name' => 'Researcher', 'type' => 1, 'description' => 'Academic Researcher'],
        ];
        foreach ($roles as $r) {
            DB::table('auth_item')->updateOrInsert(
                ['name' => $r['name']],
                ['type' => $r['type'], 'description' => $r['description'], 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // 11. สร้างนักวิจัยตัวอย่าง (rdb_researcher)
        DB::table('rdb_researcher')->updateOrInsert(
            ['researcher_id' => 1],
            [
                'prefix_id' => 6, // ผศ.ดร.
                'researcher_fname' => 'ผู้ดูแลระบบ',
                'researcher_lname' => 'งานวิจัย',
                'researcher_fnameEN' => 'System',
                'researcher_lnameEN' => 'Administrator',
                'researcher_email' => 'admin@pcru.ac.th',
                'department_id' => 12, // สถาบันวิจัย
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ]
        );

        DB::table('rdb_researcher')->updateOrInsert(
            ['researcher_id' => 2],
            [
                'prefix_id' => 4, // ดร.
                'researcher_fname' => 'ชลธิชา',
                'researcher_lname' => 'ระลึก',
                'researcher_fnameEN' => 'Chonticha',
                'researcher_lnameEN' => 'Raluk',
                'researcher_email' => 'researcher@pcru.ac.th',
                'department_id' => 1, // วิทย์
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ]
        );

        // 12. สร้างผู้ใช้งานระบบ (user)
        // บัญชี 1: Super Admin (ID 129 ตาม BackendAuditTest)
        $passwordHash = Hash::make('admin1234');
        DB::table('user')->updateOrInsert(
            ['id' => 129],
            [
                'username' => 'admin',
                'email' => 'admin@pcru.ac.th',
                'password_hash' => $passwordHash,
                'auth_key' => bin2hex(random_bytes(16)),
                'status' => '10',
                'researcher_id' => 1,
                'onboarding_status' => 'approved',
                'flags' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // ผูกสิทธิ์ Super Admin
        DB::table('auth_assignment')->updateOrInsert(
            ['item_name' => 'The Creator', 'user_id' => '129'],
            ['created_at' => $now]
        );
        DB::table('auth_assignment')->updateOrInsert(
            ['item_name' => 'Admin', 'user_id' => '129'],
            ['created_at' => $now]
        );

        // บัญชี 2: Researcher (username 655169 ตาม BackendAuditTest)
        DB::table('user')->updateOrInsert(
            ['username' => '655169'],
            [
                'email' => 'researcher@pcru.ac.th',
                'password_hash' => Hash::make('password123'),
                'auth_key' => bin2hex(random_bytes(16)),
                'status' => '10',
                'researcher_id' => 2,
                'onboarding_status' => 'approved',
                'flags' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        $resUserId = DB::table('user')->where('username', '655169')->value('id');
        DB::table('auth_assignment')->updateOrInsert(
            ['item_name' => 'Researcher', 'user_id' => (string)$resUserId],
            ['created_at' => $now]
        );

        // 13. ข้อมูลโครงการวิจัยตัวอย่าง (rdb_project)
        $year2568 = DB::table('rdb_year')->where('year_name', 2568)->value('year_id');
        $year2569 = DB::table('rdb_year')->where('year_name', 2569)->value('year_id');

        DB::table('rdb_project')->updateOrInsert(
            ['pro_id' => 1],
            [
                'pro_nameTH' => 'การพัฒนาสารสกัดจากพืชสมุนไพรท้องถิ่นเพื่อการส่งเสริมสุขภาพในชุมชนจังหวัดเพชรบูรณ์',
                'pro_nameEN' => 'Development of Local Herbal Extracts for Health Promotion in Phetchabun Community',
                'year_id' => $year2569,
                'department_id' => 1,
                'pt_id' => 1,
                'ps_id' => 3, // กำลังดำเนินการ
                'pro_budget' => 250000.00,
                'data_show' => 1,
                'workflow_status' => 'published',
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ]
        );

        DB::table('rdb_project')->updateOrInsert(
            ['pro_id' => 2],
            [
                'pro_nameTH' => 'ระบบปัญญาประดิษฐ์เพื่อการตรวจจับศัตรูพืชในสวนมะขามหวานอย่างแม่นยำ',
                'pro_nameEN' => 'Artificial Intelligence System for Precise Pest Detection in Sweet Tamarind Orchards',
                'year_id' => $year2569,
                'department_id' => 5,
                'pt_id' => 2,
                'ps_id' => 5, // เสร็จสิ้นสมบูรณ์
                'pro_budget' => 180000.00,
                'data_show' => 1,
                'workflow_status' => 'published',
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ]
        );

        DB::table('rdb_project')->updateOrInsert(
            ['pro_id' => 3],
            [
                'pro_nameTH' => 'การยกระดับการท่องเที่ยวเชิงวัฒนธรรมและภูมิปัญญาท้องถิ่นมรดกโลกศรีเทพ',
                'pro_nameEN' => 'Enhancing Cultural and Local Wisdom Tourism in Si Thep World Heritage',
                'year_id' => $year2568,
                'department_id' => 2,
                'pt_id' => 1,
                'ps_id' => 5, // เสร็จสิ้นสมบูรณ์
                'pro_budget' => 350000.00,
                'data_show' => 1,
                'workflow_status' => 'published',
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ]
        );

        // ผูกนักวิจัยกับโครงการ (rdb_project_work)
        DB::table('rdb_project_work')->updateOrInsert(
            ['pro_id' => 1, 'researcher_id' => 2],
            ['position_id' => 1, 'ratio' => 100, 'created_at' => $nowTimestamp, 'updated_at' => $nowTimestamp]
        );
        DB::table('rdb_project_work')->updateOrInsert(
            ['pro_id' => 2, 'researcher_id' => 2],
            ['position_id' => 1, 'ratio' => 100, 'created_at' => $nowTimestamp, 'updated_at' => $nowTimestamp]
        );
        DB::table('rdb_project_work')->updateOrInsert(
            ['pro_id' => 3, 'researcher_id' => 1],
            ['position_id' => 1, 'ratio' => 100, 'created_at' => $nowTimestamp, 'updated_at' => $nowTimestamp]
        );

        // 14. ข่าวสารวิจัยตัวอย่าง (research_news)
        DB::table('research_news')->updateOrInsert(
            ['id' => 1],
            [
                'news_name' => 'ขอเชิญส่งบทความวิจัยเข้าร่วมการประชุมวิชาการระดับชาติและนานาชาติ ประจำปี 2569',
                'news_detail' => 'สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์ ขอเชิญคณาจารย์ นักวิจัย และบุคคลทั่วไป ส่งบทความวิชาการและบทความวิจัย',
                'data_show' => 1,
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ]
        );

        // 15. การประชุมวิชาการ (research_coferenceinthai)
        DB::table('research_coferenceinthai')->updateOrInsert(
            ['id' => 1],
            [
                'con_name' => 'การประชุมวิชาการระดับชาติด้านวิทยาศาสตร์ เทคโนโลยี และนวัตกรรมท้องถิ่น ครั้งที่ 12',
                'con_detail' => 'เปิดรับผลงานวิจัยเพื่อการตีพิมพ์และนำเสนอแบบ Oral & Poster Presentation',
                'data_show' => 1,
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ]
        );
    }
}
