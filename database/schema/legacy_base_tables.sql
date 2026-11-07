-- Legacy Yii2 & Base Schema for rdbpcru
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_role` varchar(100) DEFAULT NULL,
  `scope_info` varchar(255) DEFAULT NULL,
  `event` varchar(50) NOT NULL,
  `auditable_type` varchar(191) NOT NULL,
  `auditable_id` bigint(20) unsigned NOT NULL,
  `old_values` longtext DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext DEFAULT NULL CHECK (json_valid(`new_values`)),
  `diff_summary` text DEFAULT NULL,
  `url` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_event_index` (`event`),
  KEY `audit_logs_auditable_type_index` (`auditable_type`),
  KEY `audit_logs_auditable_id_index` (`auditable_id`),
  KEY `audit_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`) USING BTREE,
  CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `auth_item` (
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` mediumtext DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE,
  KEY `rule_name` (`rule_name`) USING BTREE,
  KEY `idx-auth_item-type` (`type`) USING BTREE,
  CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`) USING BTREE,
  KEY `child` (`child`) USING BTREE,
  CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` mediumtext DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `journalotherpage` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `article_id` int(11) DEFAULT NULL COMMENT 'รหัสบทความ',
  `submission_file_id` int(11) DEFAULT NULL COMMENT 'รหัสเอกสาร',
  `file_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อเอกสาร',
  `setting_value` varchar(255) DEFAULT NULL COMMENT 'ชื่อบทความ',
  `authorsall` varchar(255) DEFAULT NULL COMMENT 'ผู้แต่ง',
  `issues_setting_value` varchar(255) DEFAULT NULL COMMENT 'วารสาร',
  `credated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่สร้าง',
  `jop_path` varchar(255) DEFAULT NULL,
  `jop_size` bigint(20) unsigned DEFAULT NULL,
  `jop_mime_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `journal_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `jou_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อบทความ',
  `jou_respon` varchar(255) DEFAULT NULL COMMENT 'ชื่อผู้ส่ง',
  `jou_email` varchar(255) DEFAULT NULL COMMENT 'อีเมลล์',
  `jou_files` varchar(255) DEFAULT NULL COMMENT 'เอกสารแนบ',
  `jou_filesname` varchar(255) DEFAULT NULL COMMENT 'ชื่อเอกสารแนบ',
  `jou_status` char(1) DEFAULT NULL COMMENT 'สถานะ',
  `jou_note` varchar(255) DEFAULT NULL COMMENT 'รายละเอียด',
  `data_show` int(11) DEFAULT NULL,
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `js_path` varchar(255) DEFAULT NULL,
  `js_size` bigint(20) unsigned DEFAULT NULL,
  `js_mime_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `data` blob DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent` (`parent`) USING BTREE,
  CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `profile` (
  `user_id` int(11) NOT NULL COMMENT 'รหัสผู้ใช้งาน',
  `prefix_id` int(11) DEFAULT NULL COMMENT 'รหัสคำนำหน้า',
  `name` varchar(255) DEFAULT NULL COMMENT 'ชื่อ - สกุล',
  `gender` char(1) DEFAULT NULL COMMENT 'เพศ',
  `department_id` int(11) DEFAULT NULL COMMENT 'รหัสคณะ',
  `branch_id` int(11) DEFAULT NULL COMMENT 'รหัสสาขาวิชา',
  `birthdate` date DEFAULT NULL COMMENT 'วันเกิด',
  `address` varchar(255) DEFAULT NULL COMMENT 'ที่อยู่ (ทะเบียนบ้าน)',
  `workaddress` varchar(255) DEFAULT NULL COMMENT 'ที่อยู่ (ทำงาน/ติดต่อได้สะดวก)',
  `tel` char(11) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์บ้าน/ทำงาน',
  `mobile` char(12) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์มือถือ',
  `fax` char(11) DEFAULT NULL COMMENT 'เบอร์แฟกซ์',
  `picture` char(32) DEFAULT NULL COMMENT 'รูปประจำตัว',
  `public_email` varchar(255) DEFAULT NULL,
  `gravatar_email` varchar(255) DEFAULT NULL,
  `gravatar_id` varchar(32) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `bio` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`user_id`) USING BTREE,
  KEY `fk_prefix_id` (`prefix_id`) USING BTREE,
  KEY `fk_department_id` (`department_id`) USING BTREE,
  CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `profile_ibfk_2` FOREIGN KEY (`prefix_id`) REFERENCES `rdb_prefix` (`prefix_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `profile_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_changwat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ta_id` int(11) DEFAULT NULL COMMENT 'รหัสตำบล',
  `tambon_t` varchar(255) DEFAULT NULL COMMENT 'ตำบลภาษาไทย',
  `tambon_e` varchar(255) DEFAULT NULL COMMENT 'ตำบลอังกฤษ',
  `am_id` int(11) DEFAULT NULL COMMENT 'รหัสอำเภอ',
  `amphoe_t` varchar(255) DEFAULT NULL COMMENT 'อำเภอภาษาไทย',
  `amphoe_e` varchar(255) DEFAULT NULL COMMENT 'อำเภอภาษาอังกฤษ',
  `ch_id` int(11) DEFAULT NULL COMMENT 'รหัสจังหวัด',
  `changwat_t` varchar(255) DEFAULT NULL COMMENT 'จังหวัดภาษาไทย',
  `changwat_e` varchar(255) DEFAULT NULL COMMENT 'จังหวัดภาษาอังกฤษ',
  `lat` float DEFAULT NULL COMMENT 'ค่าละติจูด',
  `long` float DEFAULT NULL COMMENT 'ค่าลองติจูด',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_dateevent` (
  `ev_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทกิจกรรม',
  `evt_id` int(11) DEFAULT NULL COMMENT 'รหัสกิจกรรม',
  `ev_title` varchar(255) DEFAULT NULL COMMENT 'กิจกรรม',
  `ev_detail` mediumtext DEFAULT NULL COMMENT 'รายละเอียด',
  `ev_datestart` date DEFAULT NULL COMMENT 'วันที่เริ่ม',
  `ev_timestart` time DEFAULT NULL COMMENT 'เวลาเริ่ม',
  `ev_dateend` date DEFAULT NULL COMMENT 'วันที่สิ้นสุด',
  `ev_timeend` time DEFAULT NULL COMMENT 'เวลาสิ้นสุด',
  `ev_url` varchar(255) DEFAULT NULL COMMENT 'เว็บไซต์อ้างอิง',
  `ev_status` char(1) DEFAULT NULL COMMENT 'สถานะ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`ev_id`) USING BTREE,
  KEY `evt_id` (`evt_id`) USING BTREE,
  CONSTRAINT `evt_id` FOREIGN KEY (`evt_id`) REFERENCES `rdb_dateevent_type` (`evt_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_dateevent_type` (
  `evt_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภท',
  `evt_name` varchar(255) DEFAULT NULL COMMENT 'ประเภทกิจกรรม',
  `evt_color` varchar(255) DEFAULT NULL COMMENT 'สีประจำกิจกรรม',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`evt_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_department` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสคณะ',
  `tdepartment_id` int(11) DEFAULT NULL COMMENT 'ประเภท',
  `department_code` varchar(5) DEFAULT NULL,
  `department_nameTH` varchar(255) NOT NULL COMMENT 'ชื่อคณะ(ไทย)',
  `department_nameEN` varchar(255) DEFAULT NULL COMMENT 'ชื่อคณะ(อังกฤษ)',
  `department_color` char(7) DEFAULT NULL COMMENT 'สีประจำคณะ',
  `dspace_org_unit` varchar(255) DEFAULT NULL,
  `dspace_org_col_research` varchar(255) DEFAULT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`department_id`) USING BTREE,
  KEY `fk_dep_tdepartment_id` (`tdepartment_id`) USING BTREE,
  KEY `department_id_index` (`department_id`) USING BTREE,
  CONSTRAINT `rdb_department_ibfk_1` FOREIGN KEY (`tdepartment_id`) REFERENCES `rdb_department_type` (`tdepartment_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_department_category` (
  `depcat_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสกลุ่มสาขา',
  `depcat_name` varchar(255) DEFAULT NULL COMMENT 'กลุ่มสาขา',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`depcat_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_department_course` (
  `depcou_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสหลักสูตร',
  `department_id` int(11) DEFAULT NULL COMMENT 'คณะ',
  `depcat_id` int(11) DEFAULT NULL COMMENT 'ประเภท',
  `cou_name` varchar(255) DEFAULT NULL COMMENT 'หลักสูตร',
  `cou_name_sh` varchar(255) DEFAULT NULL COMMENT 'ตัวอย่อหลักสูตร',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`depcou_id`) USING BTREE,
  KEY `fb_dep_id` (`department_id`) USING BTREE,
  KEY `fb_depcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `fb_dep_id` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fb_depcat_id` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_department_type` (
  `tdepartment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทคณะ',
  `tdepartment_nameTH` varchar(255) NOT NULL COMMENT 'ชื่อประเภท(ไทย)',
  `tdepartment_nameEN` varchar(255) DEFAULT NULL COMMENT 'ชื่อประเภท(อังกฤษ)',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`tdepartment_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_dep_major` (
  `maj_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `depcou_id` int(11) DEFAULT NULL COMMENT 'หลักสูตร',
  `maj_code` varchar(10) NOT NULL COMMENT 'รหัสสาขาวิชา',
  `department_id` int(11) DEFAULT NULL COMMENT 'รหัสคณะ',
  `maj_nameTH` varchar(255) NOT NULL COMMENT 'ชื่อสาขาวิชา(ไทย)',
  `maj_nameEN` varchar(255) DEFAULT NULL COMMENT 'ชื่อสาขาวิชา(อังกฤษ)',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`maj_id`,`maj_code`) USING BTREE,
  UNIQUE KEY `maj_id` (`maj_id`) USING BTREE,
  KEY `fk_maj_department_id` (`department_id`) USING BTREE,
  KEY `depcou_id` (`depcou_id`) USING BTREE,
  CONSTRAINT `depcou_id` FOREIGN KEY (`depcou_id`) REFERENCES `rdb_department_course` (`depcou_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_dep_major_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_dip` (
  `dip_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `dip_type` char(1) DEFAULT NULL COMMENT 'สถานะ',
  `dipt_id` int(11) DEFAULT NULL COMMENT 'ประเภท',
  `dip_files` varchar(255) DEFAULT NULL COMMENT 'ไฟล์เอกสาร',
  `dip_startdate` date DEFAULT NULL COMMENT 'วันที่ออกสิทธิบัตร',
  `dip_enddate` date DEFAULT NULL COMMENT 'วันหมดอายุ',
  `pro_id` int(11) DEFAULT NULL COMMENT 'จากผลงานวิจัย',
  `dip_request_number` int(11) DEFAULT NULL COMMENT 'เลขที่คำขอ',
  `dip_request_date` date DEFAULT NULL COMMENT 'วันที่ขอ',
  `dip_request_dateget` date DEFAULT NULL COMMENT 'วันที่รับคำขอ',
  `dip_number` int(11) DEFAULT NULL COMMENT 'เลขที่',
  `dip_publication_date` date DEFAULT NULL COMMENT 'วันที่ประกาศ',
  `dip_publication_no` int(11) DEFAULT NULL,
  `dip_patent_number` int(11) DEFAULT NULL COMMENT 'เลขที่สิทธิบัตร',
  `dip_data1_datestart` date DEFAULT NULL COMMENT 'วันที่จดทะเบียน',
  `dip_data1_files` varchar(255) DEFAULT NULL COMMENT 'เอกสารประกาศโฆษณา',
  `dip_data2_patent` varchar(255) DEFAULT NULL COMMENT 'ผู้ขอจดทะเบียน',
  `researcher_id` int(11) DEFAULT NULL COMMENT 'ผู้ประดิษฐ์/ออกแบบ',
  `dip_data2_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อผลิตภัณฑ์/สิ่งประดิษฐ์',
  `dip_data2_agent` varchar(255) DEFAULT NULL COMMENT 'ตัวแทน',
  `dip_data2_status` varchar(255) DEFAULT NULL COMMENT 'สถานะสุดท้าย',
  `dip_data2_dateend` date DEFAULT NULL COMMENT 'วันที่ตามสถานะ',
  `dip_data2_conclusion` mediumtext DEFAULT NULL COMMENT 'บทสรุปการประดิษฐ์',
  `dip_data2_files_con` varchar(255) DEFAULT NULL COMMENT 'ไฟล์เอกสาร',
  `dip_data2_assertion` mediumtext DEFAULT NULL COMMENT 'ข้อถือสิทธิ์ (ข้อที่หนึ่ง)',
  `dip_data2_tag` varchar(255) DEFAULT NULL COMMENT 'แท็ก',
  `dip_data3_allassertion` mediumtext DEFAULT NULL COMMENT 'ข้อถือสิทธฺ์ (ทั้งหมด)',
  `dip_data3_filesass1` varchar(255) DEFAULT NULL COMMENT 'เอกสารข้อถือสิทธิ์',
  `dip_data3_filesass2` varchar(255) DEFAULT NULL COMMENT 'เอกสารแบบพิมพ์คำขอ',
  `dip_data3_filesass3` varchar(255) DEFAULT NULL COMMENT 'อกสารรายละเอียดการประดิษฐ์',
  `dip_data3_drawing_picture` varchar(255) DEFAULT NULL COMMENT 'ภาพเขียน',
  `dip_data_forms_request` varchar(255) DEFAULT NULL COMMENT 'แบบพิมพ์คำขอ',
  `dip_url` varchar(255) DEFAULT NULL COMMENT 'อ้างอิง',
  `dip_note` varchar(255) DEFAULT NULL COMMENT 'หมายเหตุ',
  `data_show` int(11) DEFAULT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `dip_safe_name` varchar(255) DEFAULT NULL,
  `dip_path` varchar(255) DEFAULT NULL,
  `dip_size` bigint(20) unsigned DEFAULT NULL,
  `dip_mime_type` varchar(100) DEFAULT NULL,
  `workflow_status` varchar(32) NOT NULL DEFAULT 'published',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_remarks` mediumtext DEFAULT NULL,
  `unlock_reason` mediumtext DEFAULT NULL,
  `unlock_requested_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`dip_id`) USING BTREE,
  KEY `fkdippro_id` (`pro_id`) USING BTREE,
  KEY `fkdipdipt_id` (`dipt_id`) USING BTREE,
  KEY `fkdipresearcher_id` (`researcher_id`) USING BTREE,
  KEY `idx_dip_data_show` (`data_show`),
  CONSTRAINT `fkdipdipt_id` FOREIGN KEY (`dipt_id`) REFERENCES `rdb_dip_type` (`dipt_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fkdippro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkdipresearcher_id` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_dip_type` (
  `dipt_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `dipt_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อประเภท',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`dipt_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_groupproject` (
  `pgroup_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทโครงการ',
  `pgroup_nameTH` varchar(255) NOT NULL COMMENT 'ประเภทโครงการ',
  `pgroup_nameEN` varchar(255) DEFAULT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pgroup_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_nacc` (
  `nacc_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสการเปิดเผย',
  `pro_id` int(11) NOT NULL COMMENT 'รหัสโครงการวิจัย',
  `nacc_files` varchar(255) NOT NULL COMMENT 'เอกสารแนบ',
  `nacc_note` varchar(255) DEFAULT NULL COMMENT 'หมายเหตุ',
  `nacc_download` int(11) DEFAULT NULL COMMENT 'จำนวนการดาวน์โหลด',
  `data_show` int(11) DEFAULT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `nacc_path` varchar(255) DEFAULT NULL,
  `nacc_size` bigint(20) unsigned DEFAULT NULL,
  `nacc_mime_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`nacc_id`) USING BTREE,
  KEY `fk_nacc_proid` (`pro_id`) USING BTREE,
  CONSTRAINT `fk_nacc_proid` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_prefix` (
  `prefix_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสคำนำหน้า',
  `prefix_nameTH` varchar(100) NOT NULL COMMENT 'คำนำหน้า(ตัวเต็ม)',
  `prefix_abbreviationTH` varchar(50) DEFAULT NULL COMMENT 'คำนำหน้า(ตัวย่อ)',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`prefix_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project` (
  `pro_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสโครงการ',
  `pro_code` varchar(50) DEFAULT NULL COMMENT 'รหัสโครงการวิจัย',
  `pgroup_id` int(11) DEFAULT NULL COMMENT 'ประเภทโครงการ',
  `pt_id` int(11) DEFAULT NULL COMMENT 'ประเภททุนอุดหนุน',
  `pts_id` int(11) DEFAULT NULL COMMENT 'ประเภทโครงการ',
  `pro_nameTH` mediumtext NOT NULL COMMENT 'ชื่อโครงการ(ไทย)',
  `pro_nameEN` mediumtext DEFAULT NULL COMMENT 'ชื่อโครงการ(อังกฤษ)',
  `department_id` int(11) DEFAULT NULL COMMENT 'สังกัดคณะ',
  `depcat_id` int(11) DEFAULT NULL,
  `depcou_id` int(11) DEFAULT NULL COMMENT 'หลักสูตร',
  `major_id` int(11) DEFAULT NULL,
  `year_id` int(11) DEFAULT NULL COMMENT 'ปีงบประมาณ',
  `pro_abstract` mediumtext DEFAULT NULL COMMENT 'บทคัดย่อ',
  `pro_reference` mediumtext DEFAULT NULL COMMENT 'แหล่งอา้งอิง',
  `pro_date_start` date DEFAULT NULL COMMENT 'วันที่เริ่มโครงการ',
  `pro_date_end` date DEFAULT NULL COMMENT 'วันที่สิ้นสุดโครงการ',
  `strategic_id` int(11) DEFAULT NULL COMMENT 'แผนยุทธศาสตร์',
  `pro_budget` float(10,2) NOT NULL COMMENT 'งบประมาณ',
  `pro_keyword` mediumtext DEFAULT NULL COMMENT 'คำสำคัญ',
  `pro_abstract_file` varchar(100) DEFAULT NULL COMMENT 'ไฟล์บทคัดย่อ',
  `pro_file` varchar(100) DEFAULT NULL COMMENT 'รหัสเอกสาร',
  `pro_file_show` char(1) DEFAULT '0' COMMENT 'ปกปิดข้อมูลเล่มรายงานวิจัย',
  `ps_id` int(11) DEFAULT NULL COMMENT 'สถานะโครงการ',
  `pro_page_num` char(3) DEFAULT NULL COMMENT 'จำนวนหน้าเล่มรายงาน',
  `pro_finish` date DEFAULT NULL COMMENT 'วันที่ปิดโครงการ',
  `pro_group` int(11) DEFAULT NULL COMMENT 'รหัสแผนโครงการ',
  `pro_count_page` int(11) DEFAULT 0 COMMENT 'จำนวนการเปิดดู',
  `pro_count_abs` int(11) DEFAULT 0 COMMENT 'จำนวนการเปิดบทคัดย่อ',
  `pro_count_full` int(11) DEFAULT 0 COMMENT 'จำนวนการเปิดเล่มรายงาน',
  `data_show` int(11) DEFAULT NULL COMMENT 'การแสดงข้อมูล',
  `pro_note` varchar(255) DEFAULT NULL COMMENT 'หมายเหตุ',
  `library_in` int(11) DEFAULT 0 COMMENT 'การนำเข้าห้องสมุด',
  `todspace` int(11) DEFAULT 0 COMMENT 'ToDspace',
  `dspace_uuid` char(36) DEFAULT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `pro_file_path` varchar(255) DEFAULT NULL,
  `pro_file_size` bigint(20) unsigned DEFAULT NULL,
  `pro_file_mime` varchar(100) DEFAULT NULL,
  `pro_cover_path` varchar(255) DEFAULT NULL COMMENT 'พาธภาพประกอบหน้าแรกของเล่มรายงานวิจัยฉบับสมบูรณ์ (.webp)',
  `pro_abstract_file_path` varchar(255) DEFAULT NULL,
  `pro_abstract_size` bigint(20) unsigned DEFAULT NULL,
  `pro_abstract_mime` varchar(100) DEFAULT NULL,
  `workflow_status` varchar(32) NOT NULL DEFAULT 'published',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_remarks` mediumtext DEFAULT NULL,
  `unlock_reason` mediumtext DEFAULT NULL,
  `unlock_requested_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`pro_id`) USING BTREE,
  KEY `fk_project_pgroup_id` (`pgroup_id`) USING BTREE,
  KEY `fk_project_ptid` (`pt_id`) USING BTREE,
  KEY `fk_project_department_id` (`department_id`) USING BTREE,
  KEY `fk_project_year_id` (`year_id`) USING BTREE,
  KEY `fk_project_strategic_id` (`strategic_id`) USING BTREE,
  KEY `major_id` (`major_id`) USING BTREE,
  KEY `ps_id` (`ps_id`) USING BTREE,
  KEY `pro_id` (`pro_id`) USING BTREE,
  KEY `fk_depcou_id` (`depcou_id`) USING BTREE,
  KEY `idx_data_show_pro_id` (`data_show`,`pro_id`) USING BTREE,
  KEY `idx_pro_data_show_date` (`data_show`,`pro_date_start`),
  KEY `idx_pro_data_show_year` (`data_show`,`year_id`),
  KEY `idx_pro_data_show_pt` (`data_show`,`pt_id`),
  CONSTRAINT `fk_depcou_id` FOREIGN KEY (`depcou_id`) REFERENCES `rdb_department_course` (`depcou_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_2` FOREIGN KEY (`pgroup_id`) REFERENCES `rdb_groupproject` (`pgroup_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_3` FOREIGN KEY (`pt_id`) REFERENCES `rdb_project_types` (`pt_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_4` FOREIGN KEY (`strategic_id`) REFERENCES `rdb_strategic---` (`strategic_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_5` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_6` FOREIGN KEY (`major_id`) REFERENCES `rdb_dep_major` (`maj_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_7` FOREIGN KEY (`ps_id`) REFERENCES `rdb_project_status` (`ps_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_budget` (
  `ckb_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสตรวจสอบ',
  `pro_id` int(11) DEFAULT NULL COMMENT 'รหัสโครงการ',
  `ckb_annuity` int(11) DEFAULT NULL COMMENT 'งวดที่ 1',
  `ckb_note` varchar(255) DEFAULT NULL COMMENT 'หมายเหตุ',
  `ckb_status` char(1) DEFAULT NULL COMMENT 'สถานะ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`ckb_id`) USING BTREE,
  KEY `pro_id` (`pro_id`) USING BTREE,
  KEY `ckb_id` (`ckb_id`) USING BTREE,
  CONSTRAINT `pro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_chart` (
  `year_id` int(11) DEFAULT NULL,
  `year_name` varchar(255) DEFAULT NULL,
  `pt_id` int(11) DEFAULT NULL,
  `pt_name` varchar(255) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `department_nameTH` varchar(255) DEFAULT NULL,
  `department_color` varchar(7) DEFAULT NULL,
  `count_group` int(11) DEFAULT NULL,
  `count_project` int(11) DEFAULT NULL,
  `sum_budget_group` float(20,2) DEFAULT NULL,
  `sum_budget_project` float(20,2) DEFAULT NULL,
  `count_ps` varchar(255) DEFAULT NULL,
  `show_chart` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_chart_2` (
  `year_id` int(11) DEFAULT NULL,
  `year_name` varchar(255) DEFAULT NULL,
  `pt_id` int(11) DEFAULT NULL,
  `pt_name` varchar(255) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `department_nameTH` varchar(255) DEFAULT NULL,
  `department_color` varchar(7) DEFAULT NULL,
  `count_group` int(11) DEFAULT NULL,
  `count_project` int(11) DEFAULT NULL,
  `sum_budget_group` float(20,2) DEFAULT NULL,
  `sum_budget_project` float(20,2) DEFAULT NULL,
  `count_ps` varchar(255) DEFAULT NULL,
  `show_chart` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_chart_budget` (
  `year_id` int(11) DEFAULT NULL,
  `year_name` varchar(255) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `department_nameTH` varchar(255) DEFAULT NULL,
  `pt_id` int(11) DEFAULT NULL,
  `pt_name` varchar(255) DEFAULT NULL,
  `department_color` char(7) DEFAULT NULL,
  `sumallbudget` float(20,2) DEFAULT NULL,
  `pp_num` float(20,2) DEFAULT NULL,
  `pp_standard` float(20,2) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_at1` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_chart_budget_2` (
  `year_id` int(11) DEFAULT NULL,
  `year_name` varchar(255) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `department_nameTH` varchar(255) DEFAULT NULL,
  `pt_id` int(11) DEFAULT NULL,
  `pt_name` varchar(255) DEFAULT NULL,
  `department_color` char(7) DEFAULT NULL,
  `sumallbudget` float(20,2) DEFAULT NULL,
  `pp_num` float(20,2) DEFAULT NULL,
  `pp_standard` float(20,2) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_at1` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_download` (
  `pjdo_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสดาวน์โหลด',
  `pro_id` int(11) DEFAULT NULL COMMENT 'โครงการวิจัย',
  `researcher_id` int(11) DEFAULT NULL COMMENT 'ผู้ดาวน์โหลด',
  `uti_id` int(11) DEFAULT NULL COMMENT 'การนำไปใช้ประโยชน์',
  `pjdo_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่ดาวน์โหลด',
  `pjdo_ip` char(15) DEFAULT NULL COMMENT 'หมายเลข IP',
  PRIMARY KEY (`pjdo_id`) USING BTREE,
  KEY `fkuti_id` (`uti_id`) USING BTREE,
  KEY `fkPro_id` (`pro_id`) USING BTREE,
  KEY `fkResearcher_id` (`researcher_id`) USING BTREE,
  CONSTRAINT `fkPro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON UPDATE CASCADE,
  CONSTRAINT `fkResearcher_id` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkuti_id` FOREIGN KEY (`uti_id`) REFERENCES `rdb_project_utilization` (`uti_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสเอกสาร',
  `pro_id` int(11) NOT NULL COMMENT 'โครงการวิจัย',
  `rf_files` varchar(100) NOT NULL COMMENT 'ไฟล์เอกสาร',
  `rf_filesname` varchar(100) NOT NULL COMMENT 'ชื่อเอกสาร',
  `rf_download` int(11) DEFAULT NULL COMMENT 'จำนวนการดาวน์โหลด',
  `rf_note` varchar(255) DEFAULT NULL COMMENT 'หมายเหตุ',
  `rf_files_show` char(1) DEFAULT NULL COMMENT 'การเปิดเผยข้อมูล',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `prf_safe_name` varchar(255) DEFAULT NULL,
  `prf_path` varchar(255) DEFAULT NULL,
  `prf_size` bigint(20) unsigned DEFAULT NULL,
  `prf_mime_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `rf_pro_id` (`pro_id`) USING BTREE,
  CONSTRAINT `rf_pro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_personnel` (
  `pp_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `year_id` int(11) DEFAULT NULL COMMENT 'ปีงบประมาณ',
  `department_id` int(11) DEFAULT NULL COMMENT 'คณะ',
  `depcat_id` int(11) DEFAULT NULL COMMENT 'ประเภทหลักสูตร',
  `pp_num` float DEFAULT NULL COMMENT 'จำนวนอาจารย์',
  `pp_standard` float DEFAULT NULL COMMENT 'สัดส่วนงบประมาณ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pp_id`) USING BTREE,
  KEY `year_id` (`year_id`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `depcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `department_id` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `depcat_id` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `year_id` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_personnel_dep` (
  `ppd_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `year_id` int(11) DEFAULT NULL COMMENT 'ปีงบประมาณ',
  `department_id` int(11) DEFAULT NULL COMMENT 'คณะ',
  `depcou_id` int(11) DEFAULT NULL COMMENT 'หลักสูตร',
  `major_id` int(11) DEFAULT NULL COMMENT 'สาขาวิชา',
  `depcat_id` int(11) DEFAULT NULL COMMENT 'ประเภทหลักสูตร',
  `pp_num` float DEFAULT NULL COMMENT 'จำนวนอาจารย์',
  `pp_standard` float DEFAULT NULL COMMENT 'สัดส่วนงบประมาณ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`ppd_id`) USING BTREE,
  KEY `year_id` (`year_id`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `depcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `rdb_project_personnel_dep_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_personnel_dep_ibfk_2` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_personnel_dep_ibfk_3` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_position` (
  `position_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทผู้รับผิดชอบ',
  `position_nameTH` varchar(255) NOT NULL COMMENT 'ประเภทผู้รับผิดชอบ',
  `position_desc` varchar(255) DEFAULT NULL COMMENT 'รายละเอียดผู้รับผิดชอบ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`position_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_status` (
  `ps_id` int(11) NOT NULL AUTO_INCREMENT,
  `ps_icon` varchar(50) DEFAULT NULL,
  `ps_name` varchar(255) DEFAULT NULL,
  `ps_color` char(10) DEFAULT NULL,
  `ps_rank` int(11) DEFAULT NULL COMMENT 'ลำดับที่',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`ps_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_types` (
  `pt_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภททุนอุดหนุน',
  `year_id` int(11) DEFAULT NULL COMMENT 'ปีงบประมาณ พ.ศ.',
  `pt_name` varchar(255) NOT NULL COMMENT 'ทุนอุดหนุน',
  `pt_for` char(1) DEFAULT NULL COMMENT 'ทุนฯ สำหรับ',
  `pt_created` char(1) DEFAULT NULL COMMENT 'ทุนฯ สร้างโดย',
  `pt_type` char(1) DEFAULT NULL COMMENT 'คำนวณงบประมาณ',
  `pt_utz` char(1) DEFAULT '0' COMMENT 'คำนวณนำไปใช้ประโยชน์',
  `pt_note` varchar(255) DEFAULT NULL COMMENT 'รายละเอียดทุน',
  `pttg_id` int(11) NOT NULL COMMENT 'รหัสประเภทงบประมาณ',
  `master_pt_id` bigint(20) unsigned DEFAULT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pt_id`) USING BTREE,
  KEY `pt_id_index` (`pt_id`) USING BTREE,
  KEY `fk_yearid` (`year_id`) USING BTREE,
  KEY `fk_pttgid` (`pttg_id`) USING BTREE,
  KEY `rdb_project_types_master_pt_id_index` (`master_pt_id`),
  CONSTRAINT `fk_pttgid` FOREIGN KEY (`pttg_id`) REFERENCES `rdb_project_types_group` (`pttg_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_yearid` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_types_group` (
  `pttg_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทงบประมาณ',
  `pttg_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อประเภทงบประมาณ',
  `pttg_group` char(1) DEFAULT NULL COMMENT 'กลุ่มประเภท',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pttg_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_types_master` (
  `master_pt_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `master_pt_name` varchar(255) NOT NULL,
  `pttg_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to rdb_project_types_group',
  `order_index` int(11) NOT NULL DEFAULT 0,
  `color_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`master_pt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rdb_project_types_sub` (
  `pts_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทโครงการ',
  `pt_id` int(11) DEFAULT NULL COMMENT 'ประเภททุน',
  `pts_name` varchar(255) NOT NULL COMMENT 'ประเภทโครงการ',
  `pts_file` varchar(100) DEFAULT NULL COMMENT 'ไฟล์ประกาศทุน',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `pts_safe_name` varchar(255) DEFAULT NULL,
  `pts_path` varchar(255) DEFAULT NULL,
  `pts_size` bigint(20) unsigned DEFAULT NULL,
  `pts_mime_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`pts_id`) USING BTREE,
  KEY `pt_id_index` (`pts_id`) USING BTREE,
  KEY `rdb_project_types_sub_ibfk_1` (`pt_id`) USING BTREE,
  CONSTRAINT `rdb_project_types_sub_ibfk_1` FOREIGN KEY (`pt_id`) REFERENCES `rdb_project_types` (`pt_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_utilization` (
  `uti_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสการนำไปใช้ประโยชน์',
  `uti_nameTH` varchar(255) DEFAULT NULL COMMENT 'การนำไปใช้ประโยชน์',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`uti_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_utilize` (
  `utz_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสการนำไปใช้ประโยชน์',
  `pro_id` int(11) DEFAULT NULL COMMENT 'รหัสโครงการวิจัย',
  `utz_year_id` int(11) DEFAULT NULL COMMENT 'ปีพ.ศ. ที่นำไปใช้ประโยชน์',
  `utz_year_bud` int(11) DEFAULT NULL COMMENT 'ปีงบประมาณ พ.ศ. ที่นำไปใช้ประโยชน์',
  `utz_year_edu` int(11) DEFAULT NULL COMMENT 'ปีการศึกษา ที่นำไปใช้ประโยชน์',
  `utz_date` char(10) DEFAULT NULL COMMENT 'วันที่นำไปใช้ประโยชน์',
  `utz_leading` varchar(255) DEFAULT NULL COMMENT 'ผู้นำไปใช้ประโยชน์',
  `utz_leading_position` varchar(255) DEFAULT NULL COMMENT 'ตำแหน่งผู้นำไปใช้ประโยชน์',
  `utz_department_name` varchar(255) DEFAULT NULL COMMENT 'หน่วยงานที่นำไปใช้ประโยชน์',
  `chw_id` int(11) DEFAULT NULL COMMENT 'ที่อยู่หน่วยงาน',
  `utz_group` varchar(20) DEFAULT NULL COMMENT 'นำไปใช้ประโยชน์ในเชิง',
  `utz_group_qa` char(1) DEFAULT NULL COMMENT 'กลุ่มตามงานประกันฯ',
  `utz_detail` mediumtext DEFAULT NULL COMMENT 'รายละเอียด',
  `utz_budget` int(11) DEFAULT NULL COMMENT 'รายได้',
  `utz_count` int(11) DEFAULT 0,
  `utz_countfile` int(11) DEFAULT 0,
  `utz_files` varchar(100) DEFAULT NULL COMMENT 'เอกสารหลักฐาน',
  `data_show` int(11) DEFAULT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `uf_safe_name` varchar(255) DEFAULT NULL,
  `uf_path` varchar(255) DEFAULT NULL,
  `uf_size` bigint(20) unsigned DEFAULT NULL,
  `uf_mime_type` varchar(100) DEFAULT NULL,
  `utz_department_address` mediumtext DEFAULT NULL,
  `workflow_status` varchar(32) NOT NULL DEFAULT 'published',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_remarks` mediumtext DEFAULT NULL,
  `unlock_reason` mediumtext DEFAULT NULL,
  `unlock_requested_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`utz_id`) USING BTREE,
  KEY `utz_pro_id` (`pro_id`) USING BTREE,
  KEY `utz_year_id` (`utz_year_id`) USING BTREE,
  KEY `utz_id` (`utz_id`) USING BTREE,
  KEY `chw_id` (`chw_id`) USING BTREE,
  KEY `idx_util_data_show` (`data_show`),
  CONSTRAINT `chw_id` FOREIGN KEY (`chw_id`) REFERENCES `rdb_changwat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `utz_pro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `utz_year_id` FOREIGN KEY (`utz_year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_utilize_type` (
  `utz_type_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทการนำไปใช้ประโยชน์',
  `utz_typr_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อประเภทการนำไปใช้ประโยชน์',
  `utz_type_index` int(11) DEFAULT NULL COMMENT 'ลำดับ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`utz_type_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_project_work` (
  `researcher_id` int(11) NOT NULL COMMENT 'ผู้รับผิดชอบ',
  `pro_id` int(11) NOT NULL COMMENT 'โครงการวิจัย',
  `ratio` int(11) DEFAULT NULL COMMENT 'สัดส่วนการทำโครงการ',
  `position_id` int(11) DEFAULT NULL COMMENT 'ประเภทผู้รับผิดชอบ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`researcher_id`,`pro_id`) USING BTREE,
  KEY `fk_pw_pro_id` (`pro_id`) USING BTREE,
  KEY `fk_pw_position_id` (`position_id`) USING BTREE,
  KEY `researcher_id` (`researcher_id`) USING BTREE,
  CONSTRAINT `rdb_project_work_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `rdb_project_position` (`position_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_work_ibfk_2` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_work_ibfk_3` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_published` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year_id` int(11) DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `year_edu` int(11) DEFAULT NULL COMMENT 'ปีการศึกษา พ.ศ.',
  `year_bud` int(11) DEFAULT NULL COMMENT 'ปีงบประมาณ พ.ศ.',
  `branch_id` int(11) DEFAULT NULL COMMENT 'สาขา',
  `pubtype_id` int(11) DEFAULT NULL COMMENT 'ประเภทงานตีพิมพ์/เผยแพร่',
  `pub_name` varchar(255) NOT NULL COMMENT 'ชื่อเรื่อง/ผลงาน',
  `pro_id` int(11) DEFAULT NULL,
  `researcher_id` int(11) DEFAULT NULL COMMENT 'ชื่อ-สกุล',
  `department_id` int(11) DEFAULT NULL COMMENT 'สังกัดคณะ',
  `depcou_id` int(11) DEFAULT NULL COMMENT 'หลักสูตร',
  `major_id` int(11) DEFAULT NULL,
  `pub_abstract` mediumtext DEFAULT NULL COMMENT 'บทคัดย่อ',
  `pub_keyword` mediumtext DEFAULT NULL COMMENT 'คำสำคัญ',
  `pub_name_journal` mediumtext NOT NULL COMMENT 'แหล่งตีพิมพ์/เผยแพร่',
  `pub_date` date NOT NULL COMMENT 'วันที่นำเสนอ',
  `pub_date_end` date DEFAULT NULL COMMENT 'วันที่สิ้นสุด',
  `pub_file` varchar(100) DEFAULT NULL COMMENT 'เอกสาร',
  `pub_numfile` int(11) DEFAULT NULL COMMENT 'จำนวนหน้าของเอกสาร',
  `pub_score` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'คะแนน',
  `pub_budget` decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT 'งบประม่าณสนับสนุน',
  `pub_note` mediumtext DEFAULT NULL COMMENT 'หมายเหตุ',
  `pub_download` int(11) DEFAULT NULL,
  `data_show` int(11) DEFAULT NULL,
  `todspace` int(11) DEFAULT 0 COMMENT 'ToDspace',
  `dspace_uuid` char(36) DEFAULT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่แก้ไข',
  `pf_safe_name` varchar(255) DEFAULT NULL,
  `pf_path` varchar(255) DEFAULT NULL,
  `pf_size` bigint(20) unsigned DEFAULT NULL,
  `pf_mime_type` varchar(100) DEFAULT NULL,
  `pub_cover_path` varchar(255) DEFAULT NULL COMMENT 'พาธภาพประกอบหน้าแรกของไฟล์ผลงานตีพิมพ์ (.webp)',
  `workflow_status` varchar(32) NOT NULL DEFAULT 'published',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_remarks` mediumtext DEFAULT NULL,
  `unlock_reason` mediumtext DEFAULT NULL,
  `unlock_requested_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `pub_view_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_pl_year_id` (`year_id`) USING BTREE,
  KEY `fk_pl_branch_id` (`branch_id`) USING BTREE,
  KEY `fk_pl_pro_id` (`pro_id`) USING BTREE,
  KEY `fk_pl_researcher_id` (`researcher_id`) USING BTREE,
  KEY `fk_pl_department_id` (`department_id`) USING BTREE,
  KEY `fk_pl_major_id` (`major_id`) USING BTREE,
  KEY `fk_pl_year_edu` (`year_edu`) USING BTREE,
  KEY `fk_pl_year_bug` (`year_bud`) USING BTREE,
  KEY `fk_pub_depcou_id` (`depcou_id`) USING BTREE,
  KEY `idx_pub_data_show_date` (`data_show`,`pub_date`),
  KEY `idx_pub_type_id` (`pubtype_id`),
  KEY `idx_pub_data_show_year` (`data_show`,`year_id`),
  CONSTRAINT `fk_pl_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `rdb_published_branch` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_department_id` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_major_id` FOREIGN KEY (`major_id`) REFERENCES `rdb_dep_major` (`maj_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_pro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_researcher_id` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_year_bug` FOREIGN KEY (`year_bud`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_year_edu` FOREIGN KEY (`year_edu`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_year_id` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pub_depcou_id` FOREIGN KEY (`depcou_id`) REFERENCES `rdb_department_course` (`depcou_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_published_branch` (
  `branch_id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(255) NOT NULL COMMENT 'สาขา',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`branch_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_published_branchper` (
  `branchper_id` int(11) NOT NULL AUTO_INCREMENT,
  `year_id` int(11) DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `branch_id` int(11) DEFAULT NULL COMMENT 'กลุ่มสาขา',
  `branchper_percent` float(5,2) NOT NULL COMMENT 'สัดส่วนร้อยละ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`branchper_id`) USING BTREE,
  KEY `yea_id` (`year_id`) USING BTREE,
  KEY `bra_id` (`branch_id`) USING BTREE,
  CONSTRAINT `fk_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `rdb_published_branch` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_year_id` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_published_checkyear` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `year_id` int(11) DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `rdbyearedu_start` date DEFAULT NULL COMMENT 'เริ่มปีการศึกษา',
  `rdbyearedu_end` date DEFAULT NULL COMMENT 'สิ้นสุดปีการศึกษา',
  `rdbyearbud_start` date DEFAULT NULL COMMENT 'เริ่มปีงบประมาณ',
  `rdbyearbud_end` date DEFAULT NULL COMMENT 'สิ้นสุดปีงบประมาณ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_year` (`year_id`) USING BTREE,
  CONSTRAINT `fk_year` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_published_personnel` (
  `personnel_id` int(11) NOT NULL AUTO_INCREMENT,
  `year_id` int(11) DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `department_id` int(11) DEFAULT NULL COMMENT 'คณะ',
  `depcat_id` int(11) DEFAULT NULL COMMENT 'ประเภทหลักสูตร',
  `personnel_num` float NOT NULL COMMENT 'จำนวนอาจารย์ ปี พ.ศ.',
  `personnel_numedu` float NOT NULL COMMENT 'จำนวนอาจารย์ ปีการศึกษา พ.ศ.',
  `personnel_numbud` float NOT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`personnel_id`) USING BTREE,
  KEY `fk_personnel_year_id` (`year_id`) USING BTREE,
  KEY `fk_personnel_department_id` (`department_id`) USING BTREE,
  KEY `fk_pubpdepcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `fk_personnel_department_id` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_personnel_year_id` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pubpdepcat_id` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_published_personnel_dep` (
  `perpd_id` int(11) NOT NULL AUTO_INCREMENT,
  `year_id` int(11) DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `department_id` int(11) DEFAULT NULL COMMENT 'คณะ',
  `depcou_id` int(11) DEFAULT NULL COMMENT 'หลักสูตร',
  `major_id` int(11) DEFAULT NULL COMMENT 'สาขาวิชา',
  `depcat_id` int(11) DEFAULT NULL COMMENT 'ประเภทหลักสูตร',
  `personnel_num` float NOT NULL COMMENT 'จำนวนอาจารย์ ปี พ.ศ.',
  `personnel_numedu` float NOT NULL COMMENT 'จำนวนอาจารย์ ปีการศึกษา พ.ศ.',
  `personnel_numbud` float NOT NULL,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`perpd_id`) USING BTREE,
  KEY `fk_personnel_year_id` (`year_id`) USING BTREE,
  KEY `fk_personnel_department_id` (`department_id`) USING BTREE,
  KEY `fk_pubpdepcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `rdb_published_personnel_dep_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_published_personnel_dep_ibfk_2` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_published_personnel_dep_ibfk_3` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_published_type` (
  `pubtype_id` int(11) NOT NULL AUTO_INCREMENT,
  `pubtype_group` varchar(255) DEFAULT NULL COMMENT 'ประเภทการนำเสนอ',
  `pubtype_grouptype` varchar(255) DEFAULT NULL COMMENT 'ระดับ',
  `pubtype_subgroup` varchar(255) DEFAULT NULL COMMENT 'ชื่อประเภทย่อย',
  `pubtype_score` decimal(5,2) DEFAULT 1.00 COMMENT 'คะแนน',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pubtype_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_published_type_author` (
  `pubta_id` int(11) NOT NULL AUTO_INCREMENT,
  `pubta_nameTH` varchar(50) DEFAULT NULL COMMENT 'ชื่อประเภท',
  `pubta_nameEN` varchar(50) DEFAULT NULL COMMENT 'ชื่อประเภท',
  `pubta_score` decimal(5,2) DEFAULT 0.00 COMMENT 'คะแนน',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pubta_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_published_work` (
  `researcher_id` int(11) NOT NULL COMMENT 'ผู้รับผิดชอบ',
  `published_id` int(11) NOT NULL COMMENT 'โครงการวิจัย',
  `pubta_id` int(11) DEFAULT NULL COMMENT 'ประเภทผู้รับผิดชอบ',
  `pubw_main` int(11) DEFAULT 0 COMMENT 'หน่วยงานหลัก',
  `pubw_bud` int(11) DEFAULT 0 COMMENT 'ผู้เบิกงบประมาณสนับสนุน',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`researcher_id`,`published_id`) USING BTREE,
  KEY `researcher_id` (`researcher_id`) USING BTREE,
  KEY `published_id` (`published_id`) USING BTREE,
  KEY `pubta_id` (`pubta_id`) USING BTREE,
  CONSTRAINT `rdb_published_work_ibfk_1` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`),
  CONSTRAINT `rdb_published_work_ibfk_2` FOREIGN KEY (`published_id`) REFERENCES `rdb_published` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_researcher` (
  `researcher_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสผู้ใช้งาน',
  `researcher_codeid` varchar(32) DEFAULT NULL COMMENT 'รหัสประจำตัวผู้ใช้งาน',
  `tea_code` varchar(10) DEFAULT NULL COMMENT 'รหัสพนักงาน',
  `department_id` int(11) DEFAULT NULL COMMENT 'คณะ/สำนัก',
  `depcat_id` int(11) DEFAULT NULL COMMENT 'กลุ่มสาขาทางวิชาการ',
  `depcou_id` int(11) DEFAULT NULL COMMENT 'หลักสูตร',
  `maj_id` int(11) DEFAULT NULL COMMENT 'แผนก/สาขา',
  `researcher_gender` char(1) DEFAULT NULL COMMENT 'เพศ',
  `prefix_id` int(11) DEFAULT NULL COMMENT 'คำนำหน้า',
  `researcher_fname` varchar(255) NOT NULL COMMENT 'ชื่อผู้ใช้งาน',
  `researcher_lname` varchar(255) NOT NULL COMMENT 'นามสกุลผู้ใช้งาน',
  `researcher_fnameEN` varchar(255) DEFAULT NULL COMMENT 'ชื่อผู้ใช้งาน (อังกฤษ)',
  `researcher_lnameEN` varchar(255) DEFAULT NULL COMMENT 'นามสกุลผู้ใช้งาน (อังกฤษ)',
  `restatus_id` int(11) DEFAULT NULL COMMENT 'รหัสสถานะพนักงาน',
  `researcher_birthdate` date DEFAULT NULL COMMENT 'วัน/เดือน/ปี เกิด',
  `researcher_address` mediumtext DEFAULT NULL COMMENT 'ที่อยู่',
  `researcher_workaddress` mediumtext DEFAULT NULL COMMENT 'ที่อยู่ที่ทำงาน',
  `researcher_email` varchar(255) DEFAULT NULL COMMENT 'อีเมลล์',
  `researcher_tel` char(15) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์',
  `researcher_mobile` char(15) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์มือถือ',
  `researcher_fax` char(15) DEFAULT NULL COMMENT 'เบอร์แฟกซ์',
  `researcher_picture` varchar(255) DEFAULT NULL,
  `scopus_authorId` char(13) DEFAULT NULL COMMENT 'Scopus Author Id',
  `orcid` char(16) DEFAULT NULL COMMENT 'ORCID',
  `researcher_note` varchar(255) DEFAULT NULL COMMENT 'หมายเหตุ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `rf_safe_name` varchar(255) DEFAULT NULL,
  `rf_path` varchar(255) DEFAULT NULL,
  `rf_size` bigint(20) unsigned DEFAULT NULL,
  `rf_mime_type` varchar(100) DEFAULT NULL,
  `researcher_hindex` int(11) NOT NULL DEFAULT 0,
  `scopus_synced_at` timestamp NULL DEFAULT NULL,
  `data_show` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`researcher_id`) USING BTREE,
  KEY `fk_rs_department_id` (`department_id`) USING BTREE,
  KEY `fk_rs_prefix_id` (`prefix_id`) USING BTREE,
  KEY `rdb_research_maj_id` (`maj_id`) USING BTREE,
  KEY `restatus_id` (`restatus_id`) USING BTREE,
  KEY `depcouid` (`depcou_id`) USING BTREE,
  KEY `researcher_codeid` (`researcher_codeid`) USING BTREE,
  KEY `rdb_researcher_data_show_index` (`data_show`),
  CONSTRAINT `rdb_researcher_ibfk_1` FOREIGN KEY (`depcou_id`) REFERENCES `rdb_department_course` (`depcou_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_researcher_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_researcher_ibfk_3` FOREIGN KEY (`prefix_id`) REFERENCES `rdb_prefix` (`prefix_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_researcher_ibfk_4` FOREIGN KEY (`maj_id`) REFERENCES `rdb_dep_major` (`maj_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_researcher_ibfk_5` FOREIGN KEY (`restatus_id`) REFERENCES `rdb_researcher_status` (`restatus_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_researcher_education` (
  `reedu_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสการศึกษา',
  `researcher_id` int(11) DEFAULT NULL COMMENT 'นักวิจัย',
  `reedu_status` varchar(255) DEFAULT NULL COMMENT 'สถานภาพการศึกษา',
  `reedu_year` int(11) DEFAULT NULL COMMENT 'ปีที่จบ',
  `reedu_type` varchar(255) DEFAULT NULL COMMENT 'ระดับการศึกษา',
  `reedu_name` varchar(255) DEFAULT NULL COMMENT 'สถานศึกษา',
  `reedu_department` varchar(255) DEFAULT NULL COMMENT 'คณะ',
  `reedu_major` varchar(255) DEFAULT NULL COMMENT 'สาขา',
  `reedu_cational` varchar(255) DEFAULT NULL COMMENT 'วุฒิการศึกษา',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`reedu_id`) USING BTREE,
  KEY `fk_reedu_researcher_id` (`researcher_id`) USING BTREE,
  CONSTRAINT `fk_reedu_researcher_id` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_researcher_status` (
  `restatus_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสสถานะพนักงาน',
  `restatus_name` varchar(255) DEFAULT NULL COMMENT 'สถานะพนักงาน',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`restatus_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_review_assignment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` bigint(20) unsigned NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `researcher_id` varchar(50) DEFAULT NULL,
  `template_id` bigint(20) unsigned NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `decline_reason` text DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `result_decision` varchar(50) DEFAULT NULL,
  `total_score` decimal(6,2) DEFAULT NULL,
  `max_possible_score` decimal(6,2) DEFAULT NULL,
  `score_percentage` decimal(5,2) DEFAULT NULL,
  `general_comment` longtext DEFAULT NULL,
  `annotated_file_name` varchar(255) DEFAULT NULL,
  `annotated_file_path` varchar(500) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rdb_review_assignment_submission_id_index` (`submission_id`),
  KEY `rdb_review_assignment_reviewer_id_index` (`reviewer_id`),
  KEY `rdb_review_assignment_researcher_id_index` (`researcher_id`),
  KEY `rdb_review_assignment_template_id_index` (`template_id`),
  KEY `rdb_review_assignment_status_index` (`status`),
  CONSTRAINT `rdb_review_assignment_submission_id_foreign` FOREIGN KEY (`submission_id`) REFERENCES `rdb_review_submission` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rdb_review_assignment_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `rdb_review_template` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rdb_review_clarification` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` bigint(20) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_role` varchar(50) NOT NULL,
  `action_type` varchar(50) NOT NULL DEFAULT 'clarification',
  `message` text NOT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rdb_review_clarification_submission_id_index` (`submission_id`),
  KEY `rdb_review_clarification_user_id_index` (`user_id`),
  CONSTRAINT `rdb_review_clarification_submission_id_foreign` FOREIGN KEY (`submission_id`) REFERENCES `rdb_review_submission` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rdb_review_criteria` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint(20) unsigned NOT NULL,
  `category` varchar(150) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `item_type` varchar(50) NOT NULL DEFAULT 'hybrid',
  `scale_max` int(10) unsigned DEFAULT NULL,
  `scale_step` int(10) unsigned NOT NULL DEFAULT 1,
  `weight` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `order_seq` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rdb_review_criteria_template_id_index` (`template_id`),
  CONSTRAINT `rdb_review_criteria_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `rdb_review_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rdb_review_score` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `assignment_id` bigint(20) unsigned NOT NULL,
  `criteria_id` bigint(20) unsigned NOT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `pass_status` varchar(30) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rdb_review_score_assignment_id_index` (`assignment_id`),
  KEY `rdb_review_score_criteria_id_index` (`criteria_id`),
  CONSTRAINT `rdb_review_score_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `rdb_review_assignment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rdb_review_score_criteria_id_foreign` FOREIGN KEY (`criteria_id`) REFERENCES `rdb_review_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rdb_review_submission` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pro_id` int(11) NOT NULL,
  `round_number` int(11) NOT NULL DEFAULT 1,
  `submission_type` varchar(50) NOT NULL DEFAULT 'initial_draft',
  `submission_title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `file_mime` varchar(100) DEFAULT NULL,
  `page_count` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'submitted',
  `admin_summary` text DEFAULT NULL,
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rdb_review_submission_pro_id_index` (`pro_id`),
  KEY `rdb_review_submission_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rdb_review_template` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `evaluation_mode` varchar(50) NOT NULL DEFAULT 'hybrid',
  `scale_max` int(11) NOT NULL DEFAULT 5,
  `pass_threshold` decimal(5,2) NOT NULL DEFAULT 70.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rdb_strategic` (
  `strategic_id` int(11) NOT NULL AUTO_INCREMENT,
  `year_id` int(11) DEFAULT NULL,
  `strategic_nameTH` varchar(255) DEFAULT NULL,
  `strategic_nameEN` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`strategic_id`),
  KEY `year_id` (`year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rdb_training` (
  `tra_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสโครงการ',
  `tra_name` varchar(255) DEFAULT NULL COMMENT 'หัวข้อโครงการ',
  `tra_description` mediumtext DEFAULT NULL COMMENT 'รายละเอียดโครงการ',
  `tra_property` varchar(255) DEFAULT NULL COMMENT 'คุณสมบัติผู้เข้าร่วมโครงการ',
  `tra_fee` int(11) DEFAULT 0 COMMENT 'ค่าลงทะเบียน',
  `tra_datetimestart` datetime DEFAULT NULL COMMENT 'วัน/เวลา เริ่มโครงการ',
  `tra_datetimeend` datetime DEFAULT NULL COMMENT 'วัน/เวลา สิ้นโครงการ',
  `tra_place` varchar(255) DEFAULT NULL COMMENT 'สถานที่จัดโครงการ',
  `tra_applicant` int(11) DEFAULT NULL COMMENT 'จำนวนผู้รับร่วมสูงสุด',
  `tra_note` varchar(255) DEFAULT NULL COMMENT 'หมายเหตุ',
  `tra_url` varchar(255) DEFAULT NULL COMMENT 'รายละเอียดเพิ่มเติม',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`tra_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_training_register` (
  `treg_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสผู้เข้าร่วม',
  `tra_id` int(11) DEFAULT NULL COMMENT 'รหัสโครงการ',
  `treg_perfix` varchar(255) DEFAULT NULL COMMENT 'คำนำหน้า',
  `treg_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อ - สกุล',
  `treg_department` varchar(255) DEFAULT NULL COMMENT 'หน่วยงาน/คณะ',
  `treg_position` varchar(255) DEFAULT NULL COMMENT 'ตำแหน่ง',
  `treg_address` varchar(255) DEFAULT NULL COMMENT 'ที่อยู่',
  `treg_tel` varchar(255) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์',
  `treg_email` varchar(255) DEFAULT NULL COMMENT 'อีเมล์',
  `treg_session` varchar(50) DEFAULT NULL COMMENT 'รหัสตรวจสอบ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`treg_id`) USING BTREE,
  KEY `fk_tra_id` (`tra_id`) USING BTREE,
  CONSTRAINT `fk_tra_id` FOREIGN KEY (`tra_id`) REFERENCES `rdb_training` (`tra_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `rdb_year` (
  `year_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสปีงบประมาณ',
  `year_name` int(11) DEFAULT NULL COMMENT 'ปีงบประมาณ',
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`year_id`) USING BTREE,
  KEY `year_id` (`year_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `research_coferenceinthai` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `con_id` varchar(20) DEFAULT '0' COMMENT 'รหัสประจำการประชุม',
  `con_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อการประชุม',
  `con_detail` mediumtext DEFAULT NULL COMMENT 'รายละเอียดการประชุม',
  `con_even_date` varchar(255) DEFAULT NULL COMMENT 'วันที่จัดการประชุม',
  `con_sub_deadline` varchar(255) DEFAULT NULL COMMENT 'วันสุดท้ายของการส่งผลงานเข้าร่วมการประชุม',
  `con_venue` mediumtext DEFAULT NULL COMMENT 'สถานที่จัดการประชุม',
  `con_website` mediumtext DEFAULT NULL COMMENT 'เว็บไซต์หลักของการประชุม',
  `con_img` varchar(255) DEFAULT NULL,
  `con_site_img` varchar(255) DEFAULT NULL,
  `con_count` int(11) DEFAULT 0 COMMENT 'จำนวนผู้เข้าชม',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `con_safe_name` varchar(255) DEFAULT NULL,
  `con_path` varchar(255) DEFAULT NULL,
  `con_size` bigint(20) unsigned DEFAULT NULL,
  `con_mime_type` varchar(100) DEFAULT NULL,
  `data_show` int(11) NOT NULL DEFAULT 1,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `file_mime_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `research_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสข่าว',
  `news_id` int(11) DEFAULT NULL COMMENT 'รหัสอ้างอิง จากระบบ NRIIS',
  `news_type` varchar(255) DEFAULT NULL COMMENT 'ประเภทข่าว',
  `news_name` mediumtext DEFAULT NULL COMMENT 'หัวข้อข่าว',
  `news_img` varchar(255) DEFAULT NULL COMMENT 'ภาพประกอบข่าว',
  `news_date` varchar(255) DEFAULT NULL COMMENT 'วันที่นำเสนอ',
  `news_event_start` date DEFAULT NULL COMMENT 'วันที่เปิดรับ',
  `news_event_end` date DEFAULT NULL COMMENT 'วันที่ปิดรับ',
  `news_event_guarantee` date DEFAULT NULL COMMENT 'วันที่หน่วยงานรับรอง',
  `news_detail` longtext DEFAULT NULL COMMENT 'รายละเอียดของข่าว',
  `news_reference` varchar(50) DEFAULT NULL COMMENT 'แหล่งอ้างอิงของข่าว',
  `news_link` varchar(255) DEFAULT NULL COMMENT 'ลิงค์เชื่อมโยงข่าว',
  `news_count` int(11) DEFAULT 0 COMMENT 'จำนวนผู้เข้าชม',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'วันที่แก้ไข',
  `news_safe_name` varchar(255) DEFAULT NULL,
  `news_path` varchar(255) DEFAULT NULL,
  `news_size` bigint(20) unsigned DEFAULT NULL,
  `news_mime_type` varchar(100) DEFAULT NULL,
  `data_show` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `social_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `provider` varchar(255) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `data` mediumtext DEFAULT NULL,
  `code` varchar(32) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `account_unique` (`provider`,`client_id`) USING BTREE,
  UNIQUE KEY `account_unique_code` (`code`) USING BTREE,
  KEY `fk_user_account` (`user_id`) USING BTREE,
  CONSTRAINT `social_account_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`),
  KEY `system_settings_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `token` (
  `user_id` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `created_at` int(11) NOT NULL,
  `type` smallint(6) NOT NULL,
  UNIQUE KEY `token_unique` (`user_id`,`code`,`type`) USING BTREE,
  CONSTRAINT `token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `researcher_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telegram_chat_id` varchar(64) DEFAULT NULL COMMENT 'Telegram Chat ID สำหรับรับการแจ้งเตือนส่วนบุคคล',
  `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'การตั้งค่าช่องทางและหัวข้อการแจ้งเตือนส่วนบุคคล (Mail & Telegram)' CHECK (json_valid(`notification_preferences`)),
  `status` char(2) DEFAULT NULL,
  `onboarding_status` varchar(20) NOT NULL DEFAULT 'approved',
  `onboarding_department_id` int(10) unsigned DEFAULT NULL,
  `onboarding_prefix_id` int(10) unsigned DEFAULT NULL,
  `onboarding_fname` varchar(100) DEFAULT NULL,
  `onboarding_lname` varchar(100) DEFAULT NULL,
  `onboarding_fname_en` varchar(100) DEFAULT NULL,
  `onboarding_lname_en` varchar(100) DEFAULT NULL,
  `onboarding_tel` varchar(50) DEFAULT NULL,
  `onboarding_position` varchar(100) DEFAULT NULL,
  `onboarding_submitted_at` timestamp NULL DEFAULT NULL,
  `onboarding_approved_at` timestamp NULL DEFAULT NULL,
  `onboarding_approved_by` bigint(20) unsigned DEFAULT NULL,
  `onboarding_reject_reason` mediumtext DEFAULT NULL,
  `password_hash` varchar(60) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `auth_key` varchar(32) NOT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `account_activation_token` varchar(255) DEFAULT NULL,
  `confirmed_at` int(11) DEFAULT NULL,
  `unconfirmed_email` varchar(255) DEFAULT NULL,
  `blocked_at` int(11) DEFAULT NULL,
  `registration_ip` varchar(45) DEFAULT NULL,
  `flags` int(11) NOT NULL DEFAULT 0,
  `user_created` int(11) DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int(11) DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` int(11) DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `rdb_resrarcher_researcher_id_fk` (`researcher_id`) USING BTREE,
  KEY `user_telegram_chat_id_index` (`telegram_chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `user_role_scopes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_name` varchar(64) NOT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  `domain_key` varchar(32) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_role_scopes_user_id_is_active_index` (`user_id`,`is_active`),
  KEY `user_role_scopes_user_id_index` (`user_id`),
  KEY `user_role_scopes_role_name_index` (`role_name`),
  KEY `user_role_scopes_department_id_index` (`department_id`),
  KEY `user_role_scopes_domain_key_index` (`domain_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
