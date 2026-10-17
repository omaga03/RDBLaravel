<?php

namespace App\Services;

use App\Models\AuthItem;
use App\Models\AuthItemChild;

class RbacService
{
    /**
     * Get all configurable permissions grouped by module.
     */
    public static function getPermissionsGrouped(): array
    {
        return [
            'project' => [
                'name' => 'โครงการวิจัย (Research Project)',
                'icon' => 'bi-journal-text',
                'color' => 'primary',
                'description' => 'การจัดการข้อมูลโครงการวิจัย ไฟล์แนบ คณะผู้วิจัย และบทคัดย่อ',
                'permissions' => [
                    'RdbProject.View' => [
                        'label' => 'ดูรายการโครงการ (View Projects)',
                        'desc' => 'เข้าถึงหน้ารายการ รายละเอียดโครงการ บทคัดย่อ และรายงาน',
                    ],
                    'RdbProject.Create' => [
                        'label' => 'เพิ่มโครงการใหม่ (Create Project)',
                        'desc' => 'สร้างและกรอกข้อมูลโครงการวิจัยใหม่เข้าสู่ระบบ',
                    ],
                    'RdbProject.Update' => [
                        'label' => 'แก้ไขข้อมูลโครงการ (Update Project)',
                        'desc' => 'แก้ไขข้อมูลโครงการ จัดการคณะผู้วิจัย และอัปโหลดไฟล์',
                    ],
                    'RdbProject.Delete' => [
                        'label' => 'ลบโครงการวิจัย (Delete Project)',
                        'desc' => 'ลบข้อมูลโครงการวิจัยออกจากระบบ',
                    ],
                ]
            ],
            'researcher' => [
                'name' => 'นักวิจัย (Researcher)',
                'icon' => 'bi-person-badge',
                'color' => 'info',
                'description' => 'การจัดการฐานข้อมูลประวัตินักวิจัย สังกัด และความเชี่ยวชาญ',
                'permissions' => [
                    'RdbResearcher.View' => [
                        'label' => 'ดูรายชื่อนักวิจัย (View Researchers)',
                        'desc' => 'ดูรายชื่อและข้อมูลประวัติของนักวิจัยในระบบ',
                    ],
                    'RdbResearcher.Create' => [
                        'label' => 'เพิ่มข้อมูลนักวิจัย (Create Researcher)',
                        'desc' => 'เพิ่มประวัตินักวิจัยใหม่เข้าสู่ระบบ',
                    ],
                    'RdbResearcher.Update' => [
                        'label' => 'แก้ไขข้อมูลนักวิจัย (Update Researcher)',
                        'desc' => 'แก้ไขข้อมูลประวัติ รูปภาพ และซิงค์ Scopus',
                    ],
                    'RdbResearcher.Delete' => [
                        'label' => 'ลบข้อมูลนักวิจัย (Delete Researcher)',
                        'desc' => 'ลบประวัตินักวิจัยออกจากระบบ',
                    ],
                ]
            ],
            'published' => [
                'name' => 'การตีพิมพ์เผยแพร่ (Publications)',
                'icon' => 'bi-book',
                'color' => 'success',
                'description' => 'การจัดการบทความวิจัยที่ได้รับการตีพิมพ์เผยแพร่ในวารสารต่างๆ',
                'permissions' => [
                    'RdbPublished.View' => [
                        'label' => 'ดูรายการตีพิมพ์ (View Publications)',
                        'desc' => 'ดูรายการและรายละเอียดของบทความตีพิมพ์',
                    ],
                    'RdbPublished.Create' => [
                        'label' => 'เพิ่มบทความตีพิมพ์ (Create Publication)',
                        'desc' => 'บันทึกข้อมูลบทความตีพิมพ์ใหม่และผู้แต่ง',
                    ],
                    'RdbPublished.Update' => [
                        'label' => 'แก้ไขบทความตีพิมพ์ (Update Publication)',
                        'desc' => 'แก้ไขข้อมูลบทความ ผู้แต่ง และอัปโหลดไฟล์ Full Paper',
                    ],
                    'RdbPublished.Delete' => [
                        'label' => 'ลบบทความตีพิมพ์ (Delete Publication)',
                        'desc' => 'ลบรายการบทความตีพิมพ์ออกจากระบบ',
                    ],
                ]
            ],
            'dip' => [
                'name' => 'ทรัพย์สินทางปัญญา (IP / DIP)',
                'icon' => 'bi-award',
                'color' => 'warning',
                'description' => 'การจัดการสิทธิบัตร อนุสิทธิบัตร และทรัพย์สินทางปัญญา',
                'permissions' => [
                    'RdbDip.View' => [
                        'label' => 'ดูรายการทรัพย์สินฯ (View IP)',
                        'desc' => 'ดูรายการข้อมูลทรัพย์สินทางปัญญาและการยื่นขอ',
                    ],
                    'RdbDip.Create' => [
                        'label' => 'เพิ่มทรัพย์สินฯ (Create IP)',
                        'desc' => 'บันทึกข้อมูลการยื่นขอทรัพย์สินทางปัญญาใหม่',
                    ],
                    'RdbDip.Update' => [
                        'label' => 'แก้ไขทรัพย์สินฯ (Update IP)',
                        'desc' => 'แก้ไขรายละเอียด สถานะ และไฟล์เอกสาร',
                    ],
                    'RdbDip.Delete' => [
                        'label' => 'ลบทรัพย์สินฯ (Delete IP)',
                        'desc' => 'ลบข้อมูลทรัพย์สินทางปัญญาออกจากระบบ',
                    ],
                ]
            ],
            'utilize' => [
                'name' => 'การนำไปใช้ประโยชน์ (Utilization)',
                'icon' => 'bi-graph-up-arrow',
                'color' => 'danger',
                'description' => 'การบันทึกการนำผลงานวิจัยไปใช้ประโยชน์เชิงพาณิชย์ ชุมชน หรือนโยบาย',
                'permissions' => [
                    'RdbProjectUtilize.View' => [
                        'label' => 'ดูรายการการใช้ประโยชน์ (View Utilization)',
                        'desc' => 'ดูรายการผลงานวิจัยที่มีการนำไปใช้ประโยชน์',
                    ],
                    'RdbProjectUtilize.Create' => [
                        'label' => 'เพิ่มการใช้ประโยชน์ (Create Utilization)',
                        'desc' => 'บันทึกข้อมูลการนำผลงานวิจัยไปใช้ประโยชน์ใหม่',
                    ],
                    'RdbProjectUtilize.Update' => [
                        'label' => 'แก้ไขการใช้ประโยชน์ (Update Utilization)',
                        'desc' => 'แก้ไขข้อมูลและแนบหลักฐานการใช้ประโยชน์',
                    ],
                    'RdbProjectUtilize.Delete' => [
                        'label' => 'ลบการใช้ประโยชน์ (Delete Utilization)',
                        'desc' => 'ลบรายการการนำไปใช้ประโยชน์ออกจากระบบ',
                    ],
                ]
            ],
            'master_data' => [
                'name' => 'ข้อมูลพื้นฐานและการตั้งค่า (Master Data)',
                'icon' => 'bi-database-gear',
                'color' => 'secondary',
                'description' => 'การจัดการข้อมูลตั้งค่าระบบ เช่น คณะ สาขาวิจัย ประเภททุน ปีงบประมาณ',
                'permissions' => [
                    'MasterData.Manage' => [
                        'label' => 'จัดการข้อมูลพื้นฐานทั่วไป (Manage Master Data)',
                        'desc' => 'จัดการกลุ่มโครงการ ประเภททุน สาขาวิจัย คำนำหน้า ฯลฯ',
                    ],
                    'Department' => [
                        'label' => 'จัดการคณะ/หน่วยงาน (Departments)',
                        'desc' => 'จัดการโครงสร้างหน่วยงาน คณะ และสาขาวิชา',
                    ],
                    'Year' => [
                        'label' => 'จัดการปีงบประมาณ (Fiscal Years)',
                        'desc' => 'จัดการปีงบประมาณ พ.ศ. และสถานะการเปิดรับ',
                    ],
                    'Budget' => [
                        'label' => 'จัดการข้อมูลงบประมาณ (Budgets)',
                        'desc' => 'จัดการหมวดงบประมาณและการเบิกจ่าย',
                    ],
                ]
            ],
            'admin' => [
                'name' => 'ผู้ดูแลระบบและความปลอดภัย (Administration)',
                'icon' => 'bi-shield-lock',
                'color' => 'dark',
                'description' => 'การจัดการบัญชีผู้ใช้งาน และการกำหนดสิทธิ์การเข้าถึงระบบ',
                'permissions' => [
                    'User Manager' => [
                        'label' => 'จัดการผู้ใช้งาน (User Management)',
                        'desc' => 'เพิ่ม แก้ไข กำหนดบทบาท และระงับบัญชีผู้ใช้งาน',
                    ],
                    'Rbac.Management' => [
                        'label' => 'จัดการบทบาทและสิทธิ์ (RBAC Management)',
                        'desc' => 'สร้างบทบาท และกำหนด Permission Matrix ให้กับแต่ละบทบาท',
                    ],
                ]
            ],
            'communication' => [
                'name' => 'ข่าวสารและเอกสารเผยแพร่ (Communication)',
                'icon' => 'bi-newspaper',
                'color' => 'primary',
                'description' => 'การประชาสัมพันธ์ข่าว ปฏิทินกิจกรรม และเอกสารดาวน์โหลด',
                'permissions' => [
                    'news' => [
                        'label' => 'จัดการข่าวประชาสัมพันธ์ (News)',
                        'desc' => 'สร้างและจัดการข่าวสารงานวิจัย',
                    ],
                    'Event' => [
                        'label' => 'จัดการปฏิทินกิจกรรม (Events)',
                        'desc' => 'สร้างและจัดการตารางกิจกรรมการวิจัย',
                    ],
                    'Download' => [
                        'label' => 'จัดการเอกสารดาวน์โหลด (Downloads)',
                        'desc' => 'อัปโหลดและจัดการแบบฟอร์มเอกสารดาวน์โหลด',
                    ],
                    'Nacc' => [
                        'label' => 'การเปิดเผยราคากลาง (NACC)',
                        'desc' => 'จัดการข้อมูลการเปิดเผยราคากลางตาม ป.ป.ช.',
                    ],
                ]
            ],
        ];
    }

    /**
     * Ensure all permissions exist in auth_item table (type = 2).
     */
    public static function ensurePermissionsExist(): void
    {
        $groups = self::getPermissionsGrouped();
        $now = time();

        foreach ($groups as $group) {
            foreach ($group['permissions'] as $name => $item) {
                AuthItem::firstOrCreate(
                    ['name' => $name],
                    [
                        'type' => 2, // Permission
                        'description' => $item['label'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    /**
     * Get all permission names assigned to a role.
     */
    public static function getAssignedPermissions(string $roleName): array
    {
        return AuthItemChild::where('parent', $roleName)->pluck('child')->toArray();
    }

    /**
     * Sync assigned permissions for a given role.
     */
    public static function syncRolePermissions(string $roleName, array $newPermissions): void
    {
        self::ensurePermissionsExist();

        // Aliases for backward compatibility
        $aliases = ['Dip', 'Utilize', 'Project', 'Published'];

        // Get all recognized permission names
        $allPermissions = $aliases;
        foreach (self::getPermissionsGrouped() as $group) {
            foreach (array_keys($group['permissions']) as $name) {
                $allPermissions[] = $name;
            }
        }

        // Delete currently assigned permissions belonging to recognized permissions
        AuthItemChild::where('parent', $roleName)
            ->whereIn('child', $allPermissions)
            ->delete();

        // Also handle backward compatibility aliases
        if (in_array('RdbDip.View', $newPermissions) && !in_array('Dip', $newPermissions)) {
            $newPermissions[] = 'Dip';
        }
        if (in_array('RdbProjectUtilize.View', $newPermissions) && !in_array('Utilize', $newPermissions)) {
            $newPermissions[] = 'Utilize';
        }
        if (in_array('RdbProject.View', $newPermissions) && !in_array('Project', $newPermissions)) {
            $newPermissions[] = 'Project';
        }
        if (in_array('RdbPublished.View', $newPermissions) && !in_array('Published', $newPermissions)) {
            $newPermissions[] = 'Published';
        }

        // Insert selected permissions
        $insertData = [];
        $uniqueSelected = array_unique($newPermissions);

        foreach ($uniqueSelected as $perm) {
            // Check if permission exists in auth_item
            if (AuthItem::where('name', $perm)->exists()) {
                $insertData[] = [
                    'parent' => $roleName,
                    'child' => $perm,
                ];
            }
        }

        if (!empty($insertData)) {
            AuthItemChild::insertOrIgnore($insertData);
        }
    }

    /**
     * Check if a user has a specific permission (for Gate::before).
     */
    public function checkPermission(int|string|\App\Models\User $user, string $ability): bool
    {
        if (!$user instanceof \App\Models\User) {
            $user = \App\Models\User::find($user);
        }

        if (!$user) {
            return false;
        }

        if ($user->hasRole('The Creator')) {
            return true;
        }

        return $user->hasPermission($ability);
    }
}
