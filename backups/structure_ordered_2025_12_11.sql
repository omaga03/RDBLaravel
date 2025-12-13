-- MySQL dump 10.13  Distrib 8.4.6, for Win64 (x86_64)
--
-- Host: localhost    Database: rdbv2
-- ------------------------------------------------------
-- Server version	8.4.6

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auth_rule`
--

DROP TABLE IF EXISTS `auth_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_rule` (
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_item`
--

DROP TABLE IF EXISTS `auth_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_item` (
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` int NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `rule_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE,
  KEY `rule_name` (`rule_name`) USING BTREE,
  KEY `idx-auth_item-type` (`type`) USING BTREE,
  CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_assignment`
--

DROP TABLE IF EXISTS `auth_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `user_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` int DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`) USING BTREE,
  CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_item_child`
--

DROP TABLE IF EXISTS `auth_item_child`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_item_child` (
  `parent` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `child` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`,`child`) USING BTREE,
  KEY `child` (`child`) USING BTREE,
  CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `journal_status`
--

DROP TABLE IF EXISTS `journal_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_status` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `jou_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อบทความ',
  `jou_respon` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อผู้ส่ง',
  `jou_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'อีเมลล์',
  `jou_files` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เอกสารแนบ',
  `jou_filesname` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อเอกสารแนบ',
  `jou_status` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สถานะ',
  `jou_note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รายละเอียด',
  `data_show` int DEFAULT NULL,
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `journalotherpage`
--

DROP TABLE IF EXISTS `journalotherpage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journalotherpage` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `article_id` int DEFAULT NULL COMMENT 'รหัสบทความ',
  `submission_file_id` int DEFAULT NULL COMMENT 'รหัสเอกสาร',
  `file_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อเอกสาร',
  `setting_value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อบทความ',
  `authorsall` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ผู้แต่ง',
  `issues_setting_value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'วารสาร',
  `credated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่สร้าง',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5962 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `parent` int DEFAULT NULL,
  `route` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `order` int DEFAULT NULL,
  `data` blob,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent` (`parent`) USING BTREE,
  CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration` (
  `version` varchar(180) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `apply_time` int DEFAULT NULL,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_changwat`
--

DROP TABLE IF EXISTS `rdb_changwat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_changwat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ta_id` int DEFAULT NULL COMMENT 'รหัสตำบล',
  `tambon_t` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ตำบลภาษาไทย',
  `tambon_e` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ตำบลอังกฤษ',
  `am_id` int DEFAULT NULL COMMENT 'รหัสอำเภอ',
  `amphoe_t` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'อำเภอภาษาไทย',
  `amphoe_e` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'อำเภอภาษาอังกฤษ',
  `ch_id` int DEFAULT NULL COMMENT 'รหัสจังหวัด',
  `changwat_t` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'จังหวัดภาษาไทย',
  `changwat_e` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'จังหวัดภาษาอังกฤษ',
  `lat` float DEFAULT NULL COMMENT 'ค่าละติจูด',
  `long` float DEFAULT NULL COMMENT 'ค่าลองติจูด',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7368 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_dateevent_type`
--

DROP TABLE IF EXISTS `rdb_dateevent_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_dateevent_type` (
  `evt_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภท',
  `evt_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ประเภทกิจกรรม',
  `evt_color` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สีประจำกิจกรรม',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`evt_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_dateevent`
--

DROP TABLE IF EXISTS `rdb_dateevent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_dateevent` (
  `ev_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทกิจกรรม',
  `evt_id` int DEFAULT NULL COMMENT 'รหัสกิจกรรม',
  `ev_title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'กิจกรรม',
  `ev_detail` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'รายละเอียด',
  `ev_datestart` date DEFAULT NULL COMMENT 'วันที่เริ่ม',
  `ev_timestart` time DEFAULT NULL COMMENT 'เวลาเริ่ม',
  `ev_dateend` date DEFAULT NULL COMMENT 'วันที่สิ้นสุด',
  `ev_timeend` time DEFAULT NULL COMMENT 'เวลาสิ้นสุด',
  `ev_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เว็บไซต์อ้างอิง',
  `ev_status` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สถานะ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`ev_id`) USING BTREE,
  KEY `evt_id` (`evt_id`) USING BTREE,
  CONSTRAINT `evt_id` FOREIGN KEY (`evt_id`) REFERENCES `rdb_dateevent_type` (`evt_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_department_category`
--

DROP TABLE IF EXISTS `rdb_department_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_department_category` (
  `depcat_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสกลุ่มสาขา',
  `depcat_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'กลุ่มสาขา',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`depcat_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_department_type`
--

DROP TABLE IF EXISTS `rdb_department_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_department_type` (
  `tdepartment_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทคณะ',
  `tdepartment_nameTH` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ชื่อประเภท(ไทย)',
  `tdepartment_nameEN` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อประเภท(อังกฤษ)',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`tdepartment_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_department`
--

DROP TABLE IF EXISTS `rdb_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_department` (
  `department_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสคณะ',
  `tdepartment_id` int DEFAULT NULL COMMENT 'ประเภท',
  `department_code` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department_nameTH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ชื่อคณะ(ไทย)',
  `department_nameEN` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อคณะ(อังกฤษ)',
  `department_color` char(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สีประจำคณะ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`department_id`) USING BTREE,
  KEY `fk_dep_tdepartment_id` (`tdepartment_id`) USING BTREE,
  KEY `department_id_index` (`department_id`) USING BTREE,
  CONSTRAINT `rdb_department_ibfk_1` FOREIGN KEY (`tdepartment_id`) REFERENCES `rdb_department_type` (`tdepartment_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_department_course`
--

DROP TABLE IF EXISTS `rdb_department_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_department_course` (
  `depcou_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสหลักสูตร',
  `department_id` int DEFAULT NULL COMMENT 'คณะ',
  `depcat_id` int DEFAULT NULL COMMENT 'ประเภท',
  `cou_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หลักสูตร',
  `cou_name_sh` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ตัวอย่อหลักสูตร',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`depcou_id`) USING BTREE,
  KEY `fb_dep_id` (`department_id`) USING BTREE,
  KEY `fb_depcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `fb_dep_id` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fb_depcat_id` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_dep_major`
--

DROP TABLE IF EXISTS `rdb_dep_major`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_dep_major` (
  `maj_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `depcou_id` int DEFAULT NULL COMMENT 'หลักสูตร',
  `maj_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'รหัสสาขาวิชา',
  `department_id` int DEFAULT NULL COMMENT 'รหัสคณะ',
  `maj_nameTH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ชื่อสาขาวิชา(ไทย)',
  `maj_nameEN` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อสาขาวิชา(อังกฤษ)',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`maj_id`,`maj_code`) USING BTREE,
  KEY `fk_maj_department_id` (`department_id`) USING BTREE,
  KEY `maj_id` (`maj_id`) USING BTREE,
  KEY `depcou_id` (`depcou_id`) USING BTREE,
  CONSTRAINT `depcou_id` FOREIGN KEY (`depcou_id`) REFERENCES `rdb_department_course` (`depcou_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_dep_major_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_dip_type`
--

DROP TABLE IF EXISTS `rdb_dip_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_dip_type` (
  `dipt_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `dipt_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อประเภท',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`dipt_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_groupproject`
--

DROP TABLE IF EXISTS `rdb_groupproject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_groupproject` (
  `pgroup_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทโครงการ',
  `pgroup_nameTH` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT 'ประเภทโครงการ',
  `pgroup_nameEN` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pgroup_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_prefix`
--

DROP TABLE IF EXISTS `rdb_prefix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_prefix` (
  `prefix_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสคำนำหน้า',
  `prefix_nameTH` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'คำนำหน้า(ตัวเต็ม)',
  `prefix_abbreviationTH` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'คำนำหน้า(ตัวย่อ)',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`prefix_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_chart`
--

DROP TABLE IF EXISTS `rdb_project_chart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_chart` (
  `year_id` int DEFAULT NULL,
  `year_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `pt_id` int DEFAULT NULL,
  `pt_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `department_nameTH` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department_color` varchar(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `count_group` int DEFAULT NULL,
  `count_project` int DEFAULT NULL,
  `sum_budget_group` float(20,2) DEFAULT NULL,
  `sum_budget_project` float(20,2) DEFAULT NULL,
  `count_ps` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `show_chart` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_chart_2`
--

DROP TABLE IF EXISTS `rdb_project_chart_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_chart_2` (
  `year_id` int DEFAULT NULL,
  `year_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `pt_id` int DEFAULT NULL,
  `pt_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `department_nameTH` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department_color` varchar(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `count_group` int DEFAULT NULL,
  `count_project` int DEFAULT NULL,
  `sum_budget_group` float(20,2) DEFAULT NULL,
  `sum_budget_project` float(20,2) DEFAULT NULL,
  `count_ps` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `show_chart` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_chart_budget`
--

DROP TABLE IF EXISTS `rdb_project_chart_budget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_chart_budget` (
  `year_id` int DEFAULT NULL,
  `year_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `department_nameTH` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `pt_id` int DEFAULT NULL,
  `pt_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department_color` char(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `sumallbudget` float(20,2) DEFAULT NULL,
  `pp_num` float(20,2) DEFAULT NULL,
  `pp_standard` float(20,2) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_at1` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_chart_budget_2`
--

DROP TABLE IF EXISTS `rdb_project_chart_budget_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_chart_budget_2` (
  `year_id` int DEFAULT NULL,
  `year_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `department_nameTH` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `pt_id` int DEFAULT NULL,
  `pt_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department_color` char(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `sumallbudget` float(20,2) DEFAULT NULL,
  `pp_num` float(20,2) DEFAULT NULL,
  `pp_standard` float(20,2) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_at1` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_position`
--

DROP TABLE IF EXISTS `rdb_project_position`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_position` (
  `position_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทผู้รับผิดชอบ',
  `position_nameTH` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ประเภทผู้รับผิดชอบ',
  `position_desc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รายละเอียดผู้รับผิดชอบ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`position_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_status`
--

DROP TABLE IF EXISTS `rdb_project_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_status` (
  `ps_id` int NOT NULL AUTO_INCREMENT,
  `ps_icon` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ps_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ps_color` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ps_rank` int DEFAULT NULL COMMENT 'ลำดับที่',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`ps_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_type`
--

DROP TABLE IF EXISTS `rdb_project_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_type` (
  `pt_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภททุนอุดหนุน',
  `pt_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ทุนอุดหนุน',
  `pt_for` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ทุนฯ สำหรับ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pt_id`) USING BTREE,
  KEY `pt_id_index` (`pt_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_types_group`
--

DROP TABLE IF EXISTS `rdb_project_types_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_types_group` (
  `pttg_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทงบประมาณ',
  `pttg_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อประเภทงบประมาณ',
  `pttg_group` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'กลุ่มประเภท',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pttg_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_utilization`
--

DROP TABLE IF EXISTS `rdb_project_utilization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_utilization` (
  `uti_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสการนำไปใช้ประโยชน์',
  `uti_nameTH` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'การนำไปใช้ประโยชน์',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`uti_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_utilize_type`
--

DROP TABLE IF EXISTS `rdb_project_utilize_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_utilize_type` (
  `utz_type_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทการนำไปใช้ประโยชน์',
  `utz_typr_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อประเภทการนำไปใช้ประโยชน์',
  `utz_type_index` int DEFAULT NULL COMMENT 'ลำดับ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`utz_type_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_published_branch`
--

DROP TABLE IF EXISTS `rdb_published_branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_published_branch` (
  `branch_id` int NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'สาขา',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`branch_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_published_type`
--

DROP TABLE IF EXISTS `rdb_published_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_published_type` (
  `pubtype_id` int NOT NULL AUTO_INCREMENT,
  `pubtype_group` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ประเภทการนำเสนอ',
  `pubtype_grouptype` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ระดับ',
  `pubtype_subgroup` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อประเภทย่อย',
  `pubtype_score` decimal(5,2) DEFAULT '1.00' COMMENT 'คะแนน',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pubtype_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_published_type_author`
--

DROP TABLE IF EXISTS `rdb_published_type_author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_published_type_author` (
  `pubta_id` int NOT NULL AUTO_INCREMENT,
  `pubta_nameTH` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อประเภท',
  `pubta_nameEN` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อประเภท',
  `pubta_score` decimal(5,2) DEFAULT '0.00' COMMENT 'คะแนน',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pubta_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_researcher_status`
--

DROP TABLE IF EXISTS `rdb_researcher_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_researcher_status` (
  `restatus_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสสถานะพนักงาน',
  `restatus_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สถานะพนักงาน',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`restatus_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_researcher`
--

DROP TABLE IF EXISTS `rdb_researcher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_researcher` (
  `researcher_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสผู้ใช้งาน',
  `researcher_codeid` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รหัสประจำตัวผู้ใช้งาน',
  `tea_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รหัสพนักงาน',
  `department_id` int DEFAULT NULL COMMENT 'คณะ/สำนัก',
  `depcat_id` int DEFAULT NULL COMMENT 'กลุ่มสาขาทางวิชาการ',
  `depcou_id` int DEFAULT NULL COMMENT 'หลักสูตร',
  `maj_id` int DEFAULT NULL COMMENT 'แผนก/สาขา',
  `researcher_gender` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เพศ',
  `prefix_id` int DEFAULT NULL COMMENT 'คำนำหน้า',
  `researcher_fname` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ชื่อผู้ใช้งาน',
  `researcher_lname` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'นามสกุลผู้ใช้งาน',
  `researcher_fnameEN` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อผู้ใช้งาน (อังกฤษ)',
  `researcher_lnameEN` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'นามสกุลผู้ใช้งาน (อังกฤษ)',
  `restatus_id` int DEFAULT NULL COMMENT 'รหัสสถานะพนักงาน',
  `researcher_birthdate` date DEFAULT NULL COMMENT 'วัน/เดือน/ปี เกิด',
  `researcher_address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'ที่อยู่',
  `researcher_workaddress` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'ที่อยู่ที่ทำงาน',
  `researcher_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'อีเมลล์',
  `researcher_tel` char(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เบอร์โทรศัพท์',
  `researcher_mobile` char(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เบอร์โทรศัพท์มือถือ',
  `researcher_fax` char(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เบอร์แฟกซ์',
  `researcher_picture` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รูปประจำตัว',
  `scopus_authorId` char(13) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Scopus Author Id',
  `orcid` char(16) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ORCID',
  `researcher_note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หมายเหตุ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`researcher_id`) USING BTREE,
  KEY `fk_rs_department_id` (`department_id`) USING BTREE,
  KEY `fk_rs_prefix_id` (`prefix_id`) USING BTREE,
  KEY `rdb_research_maj_id` (`maj_id`) USING BTREE,
  KEY `restatus_id` (`restatus_id`) USING BTREE,
  KEY `depcouid` (`depcou_id`) USING BTREE,
  KEY `researcher_codeid` (`researcher_codeid`) USING BTREE,
  CONSTRAINT `rdb_researcher_ibfk_1` FOREIGN KEY (`depcou_id`) REFERENCES `rdb_department_course` (`depcou_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_researcher_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_researcher_ibfk_3` FOREIGN KEY (`prefix_id`) REFERENCES `rdb_prefix` (`prefix_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_researcher_ibfk_5` FOREIGN KEY (`restatus_id`) REFERENCES `rdb_researcher_status` (`restatus_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4405 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_researcher_education`
--

DROP TABLE IF EXISTS `rdb_researcher_education`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_researcher_education` (
  `reedu_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสการศึกษา',
  `researcher_id` int DEFAULT NULL COMMENT 'นักวิจัย',
  `reedu_status` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สถานภาพการศึกษา',
  `reedu_year` int DEFAULT NULL COMMENT 'ปีที่จบ',
  `reedu_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ระดับการศึกษา',
  `reedu_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สถานศึกษา',
  `reedu_department` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'คณะ',
  `reedu_major` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สาขา',
  `reedu_cational` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'วุฒิการศึกษา',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`reedu_id`) USING BTREE,
  KEY `fk_reedu_researcher_id` (`researcher_id`) USING BTREE,
  CONSTRAINT `fk_reedu_researcher_id` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_training`
--

DROP TABLE IF EXISTS `rdb_training`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_training` (
  `tra_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสโครงการ',
  `tra_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หัวข้อโครงการ',
  `tra_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'รายละเอียดโครงการ',
  `tra_property` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'คุณสมบัติผู้เข้าร่วมโครงการ',
  `tra_fee` int DEFAULT '0' COMMENT 'ค่าลงทะเบียน',
  `tra_datetimestart` datetime DEFAULT NULL COMMENT 'วัน/เวลา เริ่มโครงการ',
  `tra_datetimeend` datetime DEFAULT NULL COMMENT 'วัน/เวลา สิ้นโครงการ',
  `tra_place` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สถานที่จัดโครงการ',
  `tra_applicant` int DEFAULT NULL COMMENT 'จำนวนผู้รับร่วมสูงสุด',
  `tra_note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หมายเหตุ',
  `tra_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รายละเอียดเพิ่มเติม',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`tra_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_training_register`
--

DROP TABLE IF EXISTS `rdb_training_register`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_training_register` (
  `treg_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสผู้เข้าร่วม',
  `tra_id` int DEFAULT NULL COMMENT 'รหัสโครงการ',
  `treg_perfix` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'คำนำหน้า',
  `treg_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อ - สกุล',
  `treg_department` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หน่วยงาน/คณะ',
  `treg_position` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ตำแหน่ง',
  `treg_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ที่อยู่',
  `treg_tel` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เบอร์โทรศัพท์',
  `treg_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'อีเมล์',
  `treg_session` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รหัสตรวจสอบ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`treg_id`) USING BTREE,
  KEY `fk_tra_id` (`tra_id`) USING BTREE,
  CONSTRAINT `fk_tra_id` FOREIGN KEY (`tra_id`) REFERENCES `rdb_training` (`tra_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_year`
--

DROP TABLE IF EXISTS `rdb_year`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_year` (
  `year_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสปีงบประมาณ',
  `year_name` int DEFAULT NULL COMMENT 'ปีงบประมาณ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`year_id`) USING BTREE,
  KEY `year_id` (`year_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_personnel`
--

DROP TABLE IF EXISTS `rdb_project_personnel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_personnel` (
  `pp_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `year_id` int DEFAULT NULL COMMENT 'ปีงบประมาณ',
  `department_id` int DEFAULT NULL COMMENT 'คณะ',
  `depcat_id` int DEFAULT NULL COMMENT 'ประเภทหลักสูตร',
  `pp_num` float DEFAULT NULL COMMENT 'จำนวนอาจารย์',
  `pp_standard` float DEFAULT NULL COMMENT 'สัดส่วนงบประมาณ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pp_id`) USING BTREE,
  KEY `year_id` (`year_id`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `depcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `department_id` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `depcat_id` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `year_id` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_personnel_dep`
--

DROP TABLE IF EXISTS `rdb_project_personnel_dep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_personnel_dep` (
  `ppd_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `year_id` int DEFAULT NULL COMMENT 'ปีงบประมาณ',
  `department_id` int DEFAULT NULL COMMENT 'คณะ',
  `depcou_id` int DEFAULT NULL COMMENT 'หลักสูตร',
  `major_id` int DEFAULT NULL COMMENT 'สาขาวิชา',
  `depcat_id` int DEFAULT NULL COMMENT 'ประเภทหลักสูตร',
  `pp_num` float DEFAULT NULL COMMENT 'จำนวนอาจารย์',
  `pp_standard` float DEFAULT NULL COMMENT 'สัดส่วนงบประมาณ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`ppd_id`) USING BTREE,
  KEY `year_id` (`year_id`) USING BTREE,
  KEY `department_id` (`department_id`) USING BTREE,
  KEY `depcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `rdb_project_personnel_dep_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_personnel_dep_ibfk_2` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_personnel_dep_ibfk_3` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_types`
--

DROP TABLE IF EXISTS `rdb_project_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_types` (
  `pt_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภททุนอุดหนุน',
  `year_id` int DEFAULT NULL COMMENT 'ปีงบประมาณ พ.ศ.',
  `pt_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ทุนอุดหนุน',
  `pt_for` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ทุนฯ สำหรับ',
  `pt_created` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ทุนฯ สร้างโดย',
  `pt_type` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'คำนวณงบประมาณ',
  `pt_utz` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '0' COMMENT 'คำนวณนำไปใช้ประโยชน์',
  `pt_note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รายละเอียดทุน',
  `pttg_id` int NOT NULL COMMENT 'รหัสประเภทงบประมาณ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pt_id`) USING BTREE,
  KEY `pt_id_index` (`pt_id`) USING BTREE,
  KEY `fk_yearid` (`year_id`) USING BTREE,
  KEY `fk_pttgid` (`pttg_id`) USING BTREE,
  CONSTRAINT `fk_pttgid` FOREIGN KEY (`pttg_id`) REFERENCES `rdb_project_types_group` (`pttg_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_yearid` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_types_sub`
--

DROP TABLE IF EXISTS `rdb_project_types_sub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_types_sub` (
  `pts_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสประเภทโครงการ',
  `pt_id` int DEFAULT NULL COMMENT 'ประเภททุน',
  `pts_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ประเภทโครงการ',
  `pts_file` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ไฟล์ประกาศทุน',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`pts_id`) USING BTREE,
  KEY `pt_id_index` (`pts_id`) USING BTREE,
  KEY `rdb_project_types_sub_ibfk_1` (`pt_id`) USING BTREE,
  CONSTRAINT `rdb_project_types_sub_ibfk_1` FOREIGN KEY (`pt_id`) REFERENCES `rdb_project_types` (`pt_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_published_branchper`
--

DROP TABLE IF EXISTS `rdb_published_branchper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_published_branchper` (
  `branchper_id` int NOT NULL AUTO_INCREMENT,
  `year_id` int DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `branch_id` int DEFAULT NULL COMMENT 'กลุ่มสาขา',
  `branchper_percent` float(5,2) NOT NULL COMMENT 'สัดส่วนร้อยละ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`branchper_id`) USING BTREE,
  KEY `yea_id` (`year_id`) USING BTREE,
  KEY `bra_id` (`branch_id`) USING BTREE,
  CONSTRAINT `fk_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `rdb_published_branch` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_year_id` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_published_checkyear`
--

DROP TABLE IF EXISTS `rdb_published_checkyear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_published_checkyear` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `year_id` int DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `rdbyearedu_start` date DEFAULT NULL COMMENT 'เริ่มปีการศึกษา',
  `rdbyearedu_end` date DEFAULT NULL COMMENT 'สิ้นสุดปีการศึกษา',
  `rdbyearbud_start` date DEFAULT NULL COMMENT 'เริ่มปีงบประมาณ',
  `rdbyearbud_end` date DEFAULT NULL COMMENT 'สิ้นสุดปีงบประมาณ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_year` (`year_id`) USING BTREE,
  CONSTRAINT `fk_year` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_published_personnel`
--

DROP TABLE IF EXISTS `rdb_published_personnel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_published_personnel` (
  `personnel_id` int NOT NULL AUTO_INCREMENT,
  `year_id` int DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `department_id` int DEFAULT NULL COMMENT 'คณะ',
  `depcat_id` int DEFAULT NULL COMMENT 'ประเภทหลักสูตร',
  `personnel_num` float NOT NULL COMMENT 'จำนวนอาจารย์ ปี พ.ศ.',
  `personnel_numedu` float NOT NULL COMMENT 'จำนวนอาจารย์ ปีการศึกษา พ.ศ.',
  `personnel_numbud` float NOT NULL,
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`personnel_id`) USING BTREE,
  KEY `fk_personnel_year_id` (`year_id`) USING BTREE,
  KEY `fk_personnel_department_id` (`department_id`) USING BTREE,
  KEY `fk_pubpdepcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `fk_personnel_department_id` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_personnel_year_id` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pubpdepcat_id` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_published_personnel_dep`
--

DROP TABLE IF EXISTS `rdb_published_personnel_dep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_published_personnel_dep` (
  `perpd_id` int NOT NULL AUTO_INCREMENT,
  `year_id` int DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `department_id` int DEFAULT NULL COMMENT 'คณะ',
  `depcou_id` int DEFAULT NULL COMMENT 'หลักสูตร',
  `major_id` int DEFAULT NULL COMMENT 'สาขาวิชา',
  `depcat_id` int DEFAULT NULL COMMENT 'ประเภทหลักสูตร',
  `personnel_num` float NOT NULL COMMENT 'จำนวนอาจารย์ ปี พ.ศ.',
  `personnel_numedu` float NOT NULL COMMENT 'จำนวนอาจารย์ ปีการศึกษา พ.ศ.',
  `personnel_numbud` float NOT NULL,
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`perpd_id`) USING BTREE,
  KEY `fk_personnel_year_id` (`year_id`) USING BTREE,
  KEY `fk_personnel_department_id` (`department_id`) USING BTREE,
  KEY `fk_pubpdepcat_id` (`depcat_id`) USING BTREE,
  CONSTRAINT `rdb_published_personnel_dep_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_published_personnel_dep_ibfk_2` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_published_personnel_dep_ibfk_3` FOREIGN KEY (`depcat_id`) REFERENCES `rdb_department_category` (`depcat_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_strategic`
--

DROP TABLE IF EXISTS `rdb_strategic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_strategic` (
  `strategic_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสยุทธศาสตร์',
  `year_id` int DEFAULT NULL COMMENT 'ปีงบประมาณ',
  `strategic_nameTH` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ชื่อยุทธศาสตร์(ไทย)',
  `strategic_nameEN` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อยุทธศาสตร์(อังกฤษ)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`strategic_id`) USING BTREE,
  KEY `fk_str_year_id` (`year_id`) USING BTREE,
  CONSTRAINT `rdb_strategic_ibfk_1` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project`
--

DROP TABLE IF EXISTS `rdb_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project` (
  `pro_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสโครงการ',
  `pro_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รหัสโครงการวิจัย',
  `pgroup_id` int DEFAULT NULL COMMENT 'ประเภทโครงการ',
  `pt_id` int DEFAULT NULL COMMENT 'ประเภททุนอุดหนุน',
  `pts_id` int DEFAULT NULL COMMENT 'ประเภทโครงการ',
  `pro_nameTH` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ชื่อโครงการ(ไทย)',
  `pro_nameEN` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'ชื่อโครงการ(อังกฤษ)',
  `department_id` int DEFAULT NULL COMMENT 'สังกัดคณะ',
  `depcou_id` int DEFAULT NULL COMMENT 'หลักสูตร',
  `major_id` int DEFAULT NULL,
  `year_id` int DEFAULT NULL COMMENT 'ปีงบประมาณ',
  `pro_abstract` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'บทคัดย่อ',
  `pro_reference` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'แหล่งอา้งอิง',
  `pro_date_start` date DEFAULT NULL COMMENT 'วันที่เริ่มโครงการ',
  `pro_date_end` date DEFAULT NULL COMMENT 'วันที่สิ้นสุดโครงการ',
  `strategic_id` int DEFAULT NULL COMMENT 'แผนยุทธศาสตร์',
  `pro_budget` float(10,2) NOT NULL COMMENT 'งบประมาณ',
  `pro_keyword` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'คำสำคัญ',
  `pro_abstract_file` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ไฟล์บทคัดย่อ',
  `pro_file` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รหัสเอกสาร',
  `pro_file_show` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '0' COMMENT 'ปกปิดข้อมูลเล่มรายงานวิจัย',
  `ps_id` int DEFAULT NULL COMMENT 'สถานะโครงการ',
  `pro_page_num` char(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'จำนวนหน้าเล่มรายงาน',
  `pro_finish` date DEFAULT NULL COMMENT 'วันที่ปิดโครงการ',
  `pro_group` int DEFAULT NULL COMMENT 'รหัสแผนโครงการ',
  `pro_count_page` int DEFAULT '0' COMMENT 'จำนวนการเปิดดู',
  `pro_count_abs` int DEFAULT '0' COMMENT 'จำนวนการเปิดบทคัดย่อ',
  `pro_count_full` int DEFAULT '0' COMMENT 'จำนวนการเปิดเล่มรายงาน',
  `data_show` int DEFAULT NULL COMMENT 'การแสดงข้อมูล',
  `pro_note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หมายเหตุ',
  `library_in` int DEFAULT '0' COMMENT 'การนำเข้าห้องสมุด',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
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
  CONSTRAINT `fk_depcou_id` FOREIGN KEY (`depcou_id`) REFERENCES `rdb_department_course` (`depcou_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_2` FOREIGN KEY (`pgroup_id`) REFERENCES `rdb_groupproject` (`pgroup_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_3` FOREIGN KEY (`pt_id`) REFERENCES `rdb_project_types` (`pt_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_4` FOREIGN KEY (`strategic_id`) REFERENCES `rdb_strategic` (`strategic_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_5` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_ibfk_7` FOREIGN KEY (`ps_id`) REFERENCES `rdb_project_status` (`ps_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2739 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_dip`
--

DROP TABLE IF EXISTS `rdb_dip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_dip` (
  `dip_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `dip_type` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สถานะ',
  `dipt_id` int DEFAULT NULL COMMENT 'ประเภท',
  `dip_files` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ไฟล์เอกสาร',
  `dip_startdate` date DEFAULT NULL COMMENT 'วันที่ออกสิทธิบัตร',
  `dip_enddate` date DEFAULT NULL COMMENT 'วันหมดอายุ',
  `pro_id` int DEFAULT NULL COMMENT 'จากผลงานวิจัย',
  `dip_request_number` int DEFAULT NULL COMMENT 'เลขที่คำขอ',
  `dip_request_date` date DEFAULT NULL COMMENT 'วันที่ขอ',
  `dip_request_dateget` date DEFAULT NULL COMMENT 'วันที่รับคำขอ',
  `dip_number` int DEFAULT NULL COMMENT 'เลขที่',
  `dip_publication_date` date DEFAULT NULL COMMENT 'วันที่ประกาศ',
  `dip_publication_no` int DEFAULT NULL,
  `dip_patent_number` int DEFAULT NULL COMMENT 'เลขที่สิทธิบัตร',
  `dip_data1_datestart` date DEFAULT NULL COMMENT 'วันที่จดทะเบียน',
  `dip_data1_files` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เอกสารประกาศโฆษณา',
  `dip_data2_patent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ผู้ขอจดทะเบียน',
  `researcher_id` int DEFAULT NULL COMMENT 'ผู้ประดิษฐ์/ออกแบบ',
  `dip_data2_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อผลิตภัณฑ์/สิ่งประดิษฐ์',
  `dip_data2_agent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ตัวแทน',
  `dip_data2_status` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สถานะสุดท้าย',
  `dip_data2_dateend` date DEFAULT NULL COMMENT 'วันที่ตามสถานะ',
  `dip_data2_conclusion` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'บทสรุปการประดิษฐ์',
  `dip_data2_files_con` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ไฟล์เอกสาร',
  `dip_data2_assertion` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'ข้อถือสิทธิ์ (ข้อที่หนึ่ง)',
  `dip_data2_tag` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'แท็ก',
  `dip_data3_allassertion` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'ข้อถือสิทธฺ์ (ทั้งหมด)',
  `dip_data3_filesass1` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เอกสารข้อถือสิทธิ์',
  `dip_data3_filesass2` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เอกสารแบบพิมพ์คำขอ',
  `dip_data3_filesass3` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'อกสารรายละเอียดการประดิษฐ์',
  `dip_data3_drawing_picture` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ภาพเขียน',
  `dip_data_forms_request` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'แบบพิมพ์คำขอ',
  `dip_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'อ้างอิง',
  `dip_note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หมายเหตุ',
  `data_show` int DEFAULT NULL,
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`dip_id`) USING BTREE,
  KEY `fkdippro_id` (`pro_id`) USING BTREE,
  KEY `fkdipdipt_id` (`dipt_id`) USING BTREE,
  KEY `fkdipresearcher_id` (`researcher_id`) USING BTREE,
  CONSTRAINT `fkdipdipt_id` FOREIGN KEY (`dipt_id`) REFERENCES `rdb_dip_type` (`dipt_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fkdippro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkdipresearcher_id` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_nacc`
--

DROP TABLE IF EXISTS `rdb_nacc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_nacc` (
  `nacc_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสการเปิดเผย',
  `pro_id` int NOT NULL COMMENT 'รหัสโครงการวิจัย',
  `nacc_files` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'เอกสารแนบ',
  `nacc_note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หมายเหตุ',
  `nacc_download` int DEFAULT NULL COMMENT 'จำนวนการดาวน์โหลด',
  `data_show` int DEFAULT NULL,
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`nacc_id`) USING BTREE,
  KEY `fk_nacc_proid` (`pro_id`) USING BTREE,
  CONSTRAINT `fk_nacc_proid` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_budget`
--

DROP TABLE IF EXISTS `rdb_project_budget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_budget` (
  `ckb_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสตรวจสอบ',
  `pro_id` int DEFAULT NULL COMMENT 'รหัสโครงการ',
  `ckb_annuity` int DEFAULT NULL COMMENT 'งวดที่ 1',
  `ckb_note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หมายเหตุ',
  `ckb_status` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'สถานะ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`ckb_id`) USING BTREE,
  KEY `pro_id` (`pro_id`) USING BTREE,
  KEY `ckb_id` (`ckb_id`) USING BTREE,
  CONSTRAINT `pro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2734 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_download`
--

DROP TABLE IF EXISTS `rdb_project_download`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_download` (
  `pjdo_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสดาวน์โหลด',
  `pro_id` int DEFAULT NULL COMMENT 'โครงการวิจัย',
  `researcher_id` int DEFAULT NULL COMMENT 'ผู้ดาวน์โหลด',
  `uti_id` int DEFAULT NULL COMMENT 'การนำไปใช้ประโยชน์',
  `pjdo_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่ดาวน์โหลด',
  `pjdo_ip` char(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หมายเลข IP',
  PRIMARY KEY (`pjdo_id`) USING BTREE,
  KEY `fkuti_id` (`uti_id`) USING BTREE,
  KEY `fkPro_id` (`pro_id`) USING BTREE,
  KEY `fkResearcher_id` (`researcher_id`) USING BTREE,
  CONSTRAINT `fkPro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON UPDATE CASCADE,
  CONSTRAINT `fkResearcher_id` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkuti_id` FOREIGN KEY (`uti_id`) REFERENCES `rdb_project_utilization` (`uti_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5011 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_files`
--

DROP TABLE IF EXISTS `rdb_project_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_files` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสเอกสาร',
  `pro_id` int NOT NULL COMMENT 'โครงการวิจัย',
  `rf_files` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ไฟล์เอกสาร',
  `rf_filesname` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ชื่อเอกสาร',
  `rf_download` int DEFAULT NULL COMMENT 'จำนวนการดาวน์โหลด',
  `rf_note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หมายเหตุ',
  `rf_files_show` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'การเปิดเผยข้อมูล',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `rf_pro_id` (`pro_id`) USING BTREE,
  CONSTRAINT `rf_pro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2446 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_utilize`
--

DROP TABLE IF EXISTS `rdb_project_utilize`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_utilize` (
  `utz_id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสการนำไปใช้ประโยชน์',
  `pro_id` int DEFAULT NULL COMMENT 'รหัสโครงการวิจัย',
  `utz_year_id` int DEFAULT NULL COMMENT 'ปีพ.ศ. ที่นำไปใช้ประโยชน์',
  `utz_year_bud` int DEFAULT NULL COMMENT 'ปีงบประมาณ พ.ศ. ที่นำไปใช้ประโยชน์',
  `utz_year_edu` int DEFAULT NULL COMMENT 'ปีการศึกษา ที่นำไปใช้ประโยชน์',
  `utz_date` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'วันที่นำไปใช้ประโยชน์',
  `utz_leading` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ผู้นำไปใช้ประโยชน์',
  `utz_leading_position` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ตำแหน่งผู้นำไปใช้ประโยชน์',
  `utz_department_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'หน่วยงานที่นำไปใช้ประโยชน์',
  `chw_id` int DEFAULT NULL COMMENT 'ที่อยู่หน่วยงาน',
  `utz_group` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'นำไปใช้ประโยชน์ในเชิง',
  `utz_group_qa` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'กลุ่มตามงานประกันฯ',
  `utz_detail` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'รายละเอียด',
  `utz_budget` int DEFAULT NULL COMMENT 'รายได้',
  `utz_count` int DEFAULT '0',
  `utz_countfile` int DEFAULT '0',
  `utz_files` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เอกสารหลักฐาน',
  `data_show` int DEFAULT NULL,
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`utz_id`) USING BTREE,
  KEY `utz_pro_id` (`pro_id`) USING BTREE,
  KEY `utz_year_id` (`utz_year_id`) USING BTREE,
  KEY `utz_id` (`utz_id`) USING BTREE,
  KEY `chw_id` (`chw_id`) USING BTREE,
  CONSTRAINT `chw_id` FOREIGN KEY (`chw_id`) REFERENCES `rdb_changwat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `utz_pro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `utz_year_id` FOREIGN KEY (`utz_year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1036 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_project_work`
--

DROP TABLE IF EXISTS `rdb_project_work`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_project_work` (
  `researcher_id` int NOT NULL COMMENT 'ผู้รับผิดชอบ',
  `pro_id` int NOT NULL COMMENT 'โครงการวิจัย',
  `ratio` int DEFAULT NULL COMMENT 'สัดส่วนการทำโครงการ',
  `position_id` int DEFAULT NULL COMMENT 'ประเภทผู้รับผิดชอบ',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`researcher_id`,`pro_id`) USING BTREE,
  KEY `fk_pw_pro_id` (`pro_id`) USING BTREE,
  KEY `fk_pw_position_id` (`position_id`) USING BTREE,
  KEY `researcher_id` (`researcher_id`) USING BTREE,
  CONSTRAINT `rdb_project_work_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `rdb_project_position` (`position_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_work_ibfk_2` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rdb_project_work_ibfk_3` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_published`
--

DROP TABLE IF EXISTS `rdb_published`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_published` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year_id` int DEFAULT NULL COMMENT 'ปี พ.ศ.',
  `year_edu` int DEFAULT NULL COMMENT 'ปีการศึกษา พ.ศ.',
  `year_bud` int DEFAULT NULL COMMENT 'ปีงบประมาณ พ.ศ.',
  `branch_id` int DEFAULT NULL COMMENT 'สาขา',
  `pubtype_id` int DEFAULT NULL COMMENT 'ประเภทงานตีพิมพ์/เผยแพร่',
  `pub_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ชื่อเรื่อง/ผลงาน',
  `pro_id` int DEFAULT NULL,
  `researcher_id` int DEFAULT NULL COMMENT 'ชื่อ-สกุล',
  `department_id` int DEFAULT NULL COMMENT 'สังกัดคณะ',
  `depcou_id` int DEFAULT NULL COMMENT 'หลักสูตร',
  `major_id` int DEFAULT NULL,
  `pub_abstract` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'บทคัดย่อ',
  `pub_keyword` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'คำสำคัญ',
  `pub_name_journal` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'แหล่งตีพิมพ์/เผยแพร่',
  `pub_date` date NOT NULL COMMENT 'วันที่นำเสนอ',
  `pub_date_end` date DEFAULT NULL COMMENT 'วันที่สิ้นสุด',
  `pub_file` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เอกสาร',
  `pub_score` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'คะแนน',
  `pub_budget` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'งบประม่าณสนับสนุน',
  `pub_note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'หมายเหตุ',
  `pub_download` int DEFAULT NULL,
  `data_show` int DEFAULT NULL,
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่แก้ไข',
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
  CONSTRAINT `fk_pl_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `rdb_published_branch` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_department_id` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_pro_id` FOREIGN KEY (`pro_id`) REFERENCES `rdb_project` (`pro_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_researcher_id` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_year_bug` FOREIGN KEY (`year_bud`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_year_edu` FOREIGN KEY (`year_edu`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_year_id` FOREIGN KEY (`year_id`) REFERENCES `rdb_year` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pub_depcou_id` FOREIGN KEY (`depcou_id`) REFERENCES `rdb_department_course` (`depcou_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1963 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rdb_published_work`
--

DROP TABLE IF EXISTS `rdb_published_work`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdb_published_work` (
  `researcher_id` int NOT NULL COMMENT 'ผู้รับผิดชอบ',
  `published_id` int NOT NULL COMMENT 'โครงการวิจัย',
  `pubta_id` int DEFAULT NULL COMMENT 'ประเภทผู้รับผิดชอบ',
  `pubw_main` int DEFAULT '0' COMMENT 'หน่วยงานหลัก',
  `pubw_bud` int DEFAULT '0' COMMENT 'ผู้เบิกงบประมาณสนับสนุน',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`researcher_id`,`published_id`) USING BTREE,
  KEY `researcher_id` (`researcher_id`) USING BTREE,
  KEY `published_id` (`published_id`) USING BTREE,
  KEY `pubta_id` (`pubta_id`) USING BTREE,
  CONSTRAINT `rdb_published_work_ibfk_1` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`),
  CONSTRAINT `rdb_published_work_ibfk_2` FOREIGN KEY (`published_id`) REFERENCES `rdb_published` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `research_coferenceinthai`
--

DROP TABLE IF EXISTS `research_coferenceinthai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `research_coferenceinthai` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัส',
  `con_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '0' COMMENT 'รหัสประจำการประชุม',
  `con_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อการประชุม',
  `con_detail` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'รายละเอียดการประชุม',
  `con_even_date` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'วันที่จัดการประชุม',
  `con_sub_deadline` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'วันสุดท้ายของการส่งผลงานเข้าร่วมการประชุม',
  `con_venue` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'สถานที่จัดการประชุม',
  `con_website` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'เว็บไซต์หลักของการประชุม',
  `con_img` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'แผ่นภาพประชาสัมพันธ์',
  `con_site_img` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'แผ่นภาพประชาสัมพันธ์ภายในเว็บ',
  `con_count` int DEFAULT '0' COMMENT 'จำนวนผู้เข้าชม',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3048 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `research_news`
--

DROP TABLE IF EXISTS `research_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `research_news` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'รหัสข่าว',
  `news_id` int DEFAULT NULL COMMENT 'รหัสอ้างอิง จากระบบ NRIIS',
  `news_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ประเภทข่าว',
  `news_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'หัวข้อข่าว',
  `news_img` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ภาพประกอบข่าว',
  `news_date` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'วันที่นำเสนอ',
  `news_event_start` date DEFAULT NULL COMMENT 'วันที่เปิดรับ',
  `news_event_end` date DEFAULT NULL COMMENT 'วันที่ปิดรับ',
  `news_event_guarantee` date DEFAULT NULL COMMENT 'วันที่หน่วยงานรับรอง',
  `news_detail` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'รายละเอียดของข่าว',
  `news_reference` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'แหล่งอ้างอิงของข่าว',
  `news_link` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ลิงค์เชื่อมโยงข่าว',
  `news_count` int DEFAULT '0' COMMENT 'จำนวนผู้เข้าชม',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=863 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `session` (
  `id` char(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `expire` int NOT NULL,
  `data` longblob NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `test_viewprojectalldata`
--

DROP TABLE IF EXISTS `test_viewprojectalldata`;
/*!50001 DROP VIEW IF EXISTS `test_viewprojectalldata`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `test_viewprojectalldata` AS SELECT 
 1 AS `pro_id`,
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `pro_code`,
 1 AS `pgroup_id`,
 1 AS `pgroup_nameTH`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `pts_id`,
 1 AS `pts_name`,
 1 AS `pro_nameTH`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `depcou_id`,
 1 AS `cou_name`,
 1 AS `major_id`,
 1 AS `maj_nameEN`,
 1 AS `pro_abstract`,
 1 AS `pro_keyword`,
 1 AS `pro_budget`,
 1 AS `pro_file`,
 1 AS `ps_id`,
 1 AS `ps_name`,
 1 AS `pro_group`,
 1 AS `data_show`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `researcher_id` int DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `status` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `password_hash` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `auth_key` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `account_activation_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `confirmed_at` int DEFAULT NULL,
  `unconfirmed_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `blocked_at` int DEFAULT NULL,
  `registration_ip` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `updated_at` int NOT NULL,
  `flags` int NOT NULL DEFAULT '0',
  `user_created` int DEFAULT NULL COMMENT 'สร้างโดย',
  `user_updated` int DEFAULT NULL COMMENT 'แก้ไขโดย',
  `created_at` int DEFAULT NULL COMMENT 'วันที่สร้าง',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `rdb_resrarcher_researcher_id_fk` (`researcher_id`) USING BTREE,
  CONSTRAINT `rdb_resrarcher_researcher_id_fk` FOREIGN KEY (`researcher_id`) REFERENCES `rdb_researcher` (`researcher_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4405 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profile` (
  `user_id` int NOT NULL COMMENT 'รหัสผู้ใช้งาน',
  `prefix_id` int DEFAULT NULL COMMENT 'รหัสคำนำหน้า',
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ชื่อ - สกุล',
  `gender` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เพศ',
  `department_id` int DEFAULT NULL COMMENT 'รหัสคณะ',
  `branch_id` int DEFAULT NULL COMMENT 'รหัสสาขาวิชา',
  `birthdate` date DEFAULT NULL COMMENT 'วันเกิด',
  `address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ที่อยู่ (ทะเบียนบ้าน)',
  `workaddress` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'ที่อยู่ (ทำงาน/ติดต่อได้สะดวก)',
  `tel` char(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เบอร์โทรศัพท์บ้าน/ทำงาน',
  `mobile` char(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เบอร์โทรศัพท์มือถือ',
  `fax` char(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'เบอร์แฟกซ์',
  `picture` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'รูปประจำตัว',
  `public_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `gravatar_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `gravatar_id` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'วันที่สร้าง',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่แก้ไข',
  PRIMARY KEY (`user_id`) USING BTREE,
  KEY `fk_prefix_id` (`prefix_id`) USING BTREE,
  KEY `fk_department_id` (`department_id`) USING BTREE,
  CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `rdb_department` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `profile_ibfk_2` FOREIGN KEY (`prefix_id`) REFERENCES `rdb_prefix` (`prefix_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `profile_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `social_account`
--

DROP TABLE IF EXISTS `social_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_account` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `client_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `code` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `created_at` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `account_unique` (`provider`,`client_id`) USING BTREE,
  UNIQUE KEY `account_unique_code` (`code`) USING BTREE,
  KEY `fk_user_account` (`user_id`) USING BTREE,
  CONSTRAINT `social_account_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `token`
--

DROP TABLE IF EXISTS `token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `token` (
  `user_id` int NOT NULL,
  `code` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `created_at` int NOT NULL,
  `type` smallint NOT NULL,
  UNIQUE KEY `token_unique` (`user_id`,`code`,`type`) USING BTREE,
  CONSTRAINT `token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `view_changwat`
--

DROP TABLE IF EXISTS `view_changwat`;
/*!50001 DROP VIEW IF EXISTS `view_changwat`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_changwat` AS SELECT 
 1 AS `id`,
 1 AS `ADM3_PCODE`,
 1 AS `ADM3_TH`,
 1 AS `tambon_e`,
 1 AS `am_id`,
 1 AS `ADM2_TH`,
 1 AS `amphoe_e`,
 1 AS `ch_id`,
 1 AS `changwat_t`,
 1 AS `changwat_e`,
 1 AS `lat`,
 1 AS `long`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_dateevent`
--

DROP TABLE IF EXISTS `view_dateevent`;
/*!50001 DROP VIEW IF EXISTS `view_dateevent`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_dateevent` AS SELECT 
 1 AS `ev_id`,
 1 AS `evt_id`,
 1 AS `ev_title`,
 1 AS `ev_detail`,
 1 AS `ev_datestart`,
 1 AS `ev_timestart`,
 1 AS `ev_dateend`,
 1 AS `ev_timeend`,
 1 AS `ev_url`,
 1 AS `ev_status`,
 1 AS `dateevents`,
 1 AS `ctodate`,
 1 AS `created_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_dip_all`
--

DROP TABLE IF EXISTS `view_dip_all`;
/*!50001 DROP VIEW IF EXISTS `view_dip_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_dip_all` AS SELECT 
 1 AS `dip_id`,
 1 AS `dip_data1_datestart`,
 1 AS `dipt_name`,
 1 AS `researcher_fullname`,
 1 AS `department_nameTH`,
 1 AS `dip_data2_status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_project_checkdata`
--

DROP TABLE IF EXISTS `view_project_checkdata`;
/*!50001 DROP VIEW IF EXISTS `view_project_checkdata`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_project_checkdata` AS SELECT 
 1 AS `pro_id`,
 1 AS `pgroup_id`,
 1 AS `pro_group`,
 1 AS `year_id`,
 1 AS `pt_id`,
 1 AS `department_id`,
 1 AS `major_id`,
 1 AS `ps_id`,
 1 AS `researcher_id`,
 1 AS `ck_files`,
 1 AS `ck_utilize`,
 1 AS `ck_published`,
 1 AS `ck_budget`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_project_group_all`
--

DROP TABLE IF EXISTS `view_project_group_all`;
/*!50001 DROP VIEW IF EXISTS `view_project_group_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_project_group_all` AS SELECT 
 1 AS `pro_group`,
 1 AS `sumgroup`,
 1 AS `group_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_project_published`
--

DROP TABLE IF EXISTS `view_project_published`;
/*!50001 DROP VIEW IF EXISTS `view_project_published`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_project_published` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `personnel_num`,
 1 AS `personnel_numedu`,
 1 AS `personnel_numbud`,
 1 AS `branchper_percent`,
 1 AS `branch_id`,
 1 AS `branch_name`,
 1 AS `depcat_id`,
 1 AS `depcat_name`,
 1 AS `personnel_numall`,
 1 AS `personnel_numalledu`,
 1 AS `personnel_numallbud`,
 1 AS `countpub_score`,
 1 AS `countpub_scoreedu`,
 1 AS `countpub_scorebud`,
 1 AS `sumpub_score`,
 1 AS `sumpub_scoreedu`,
 1 AS `sumpub_scorebud`,
 1 AS `sumpub_budget`,
 1 AS `sumpub_budgetedu`,
 1 AS `sumpub_budgetbud`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_project_published_dep`
--

DROP TABLE IF EXISTS `view_project_published_dep`;
/*!50001 DROP VIEW IF EXISTS `view_project_published_dep`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_project_published_dep` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `department_id`,
 1 AS `depcou_id`,
 1 AS `cou_name`,
 1 AS `maj_id`,
 1 AS `maj_nameTH`,
 1 AS `personnel_num`,
 1 AS `personnel_numedu`,
 1 AS `personnel_numbud`,
 1 AS `personnel_numall`,
 1 AS `personnel_numalledu`,
 1 AS `personnel_numallbud`,
 1 AS `branch_id`,
 1 AS `branch_name`,
 1 AS `branchper_percent`,
 1 AS `countpub_score`,
 1 AS `countpub_scoreedu`,
 1 AS `countpub_scorebud`,
 1 AS `sumpub_score`,
 1 AS `sumpub_scoreedu`,
 1 AS `sumpub_scorebud`,
 1 AS `sumpub_budget`,
 1 AS `sumpub_budgetedu`,
 1 AS `sumpub_budgetbud`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_project_work_all`
--

DROP TABLE IF EXISTS `view_project_work_all`;
/*!50001 DROP VIEW IF EXISTS `view_project_work_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_project_work_all` AS SELECT 
 1 AS `pro_id`,
 1 AS `researcherid`,
 1 AS `researchercodeid`,
 1 AS `prefixnameTH`,
 1 AS `respon_name`,
 1 AS `majnameTH`,
 1 AS `departmentnameTH`,
 1 AS `ratio`,
 1 AS `positionnameTH`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_project_all`
--

DROP TABLE IF EXISTS `view_project_all`;
/*!50001 DROP VIEW IF EXISTS `view_project_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_project_all` AS SELECT 
 1 AS `pro_id`,
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `pgroup_id`,
 1 AS `pgroup_nameTH`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `pts_id`,
 1 AS `pts_name`,
 1 AS `pro_nameTH`,
 1 AS `pro_nameEN`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `major_id`,
 1 AS `maj_nameTH`,
 1 AS `pro_abstract`,
 1 AS `pro_reference`,
 1 AS `pro_date_start`,
 1 AS `pro_date_end`,
 1 AS `strategic_id`,
 1 AS `strategic_nameTH`,
 1 AS `pro_budget`,
 1 AS `pro_keyword`,
 1 AS `pro_abstract_file`,
 1 AS `pro_file`,
 1 AS `pro_file_show`,
 1 AS `pro_page_num`,
 1 AS `pro_finish`,
 1 AS `pro_group`,
 1 AS `researcherid`,
 1 AS `prefixnameTH`,
 1 AS `respon_name`,
 1 AS `majnameTH`,
 1 AS `departmentnameTH`,
 1 AS `ratio`,
 1 AS `positionnameTH`,
 1 AS `ps_id`,
 1 AS `ps_name`,
 1 AS `ps_rank`,
 1 AS `pro_code`,
 1 AS `user_updated`,
 1 AS `user_created`,
 1 AS `data_show`,
 1 AS `sumgroup`,
 1 AS `pro_count_page`,
 1 AS `pro_note`,
 1 AS `pt_for`,
 1 AS `pt_created`,
 1 AS `pt_type`,
 1 AS `pt_utz`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_published_work_all`
--

DROP TABLE IF EXISTS `view_published_work_all`;
/*!50001 DROP VIEW IF EXISTS `view_published_work_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_published_work_all` AS SELECT 
 1 AS `pub_id`,
 1 AS `researcherid`,
 1 AS `researchercodeid`,
 1 AS `prefixnameTH`,
 1 AS `respon_name`,
 1 AS `majnameTH`,
 1 AS `departmentnameTH`,
 1 AS `pubtaname`,
 1 AS `pubwmain`,
 1 AS `pubwbud`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_published_all`
--

DROP TABLE IF EXISTS `view_published_all`;
/*!50001 DROP VIEW IF EXISTS `view_published_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_published_all` AS SELECT 
 1 AS `id`,
 1 AS `pro_id`,
 1 AS `year_name`,
 1 AS `year_edu`,
 1 AS `year_bud`,
 1 AS `pubtypesubgroup`,
 1 AS `pub_name`,
 1 AS `researcher_id`,
 1 AS `researcher_code`,
 1 AS `researcher_name`,
 1 AS `pubtaname`,
 1 AS `pubwmain`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `pub_name_journal`,
 1 AS `pub_date`,
 1 AS `pub_date_end`,
 1 AS `pub_score`,
 1 AS `pub_note`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `testpublishjsondep`
--

DROP TABLE IF EXISTS `testpublishjsondep`;
/*!50001 DROP VIEW IF EXISTS `testpublishjsondep`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `testpublishjsondep` AS SELECT 
 1 AS `id`,
 1 AS `pro_id`,
 1 AS `pro_data`,
 1 AS `year_name`,
 1 AS `year_edu`,
 1 AS `year_bud`,
 1 AS `pubtypesubgroup`,
 1 AS `pub_name`,
 1 AS `AUTHORS`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `pub_name_journal`,
 1 AS `pub_date`,
 1 AS `pub_date_end`,
 1 AS `pub_score`,
 1 AS `pub_note`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_researcher`
--

DROP TABLE IF EXISTS `view_researcher`;
/*!50001 DROP VIEW IF EXISTS `view_researcher`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_researcher` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `pro_id`,
 1 AS `pgroup_id`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `researcher_id`,
 1 AS `researcher_fname`,
 1 AS `researcher_lname`,
 1 AS `position_id`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `depcou_id`,
 1 AS `cou_name`,
 1 AS `maj_id`,
 1 AS `maj_nameTH`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_researcher_count`
--

DROP TABLE IF EXISTS `view_researcher_count`;
/*!50001 DROP VIEW IF EXISTS `view_researcher_count`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_researcher_count` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `pgroup_id`,
 1 AS `researcher_id`,
 1 AS `researcher_fname`,
 1 AS `researcher_lname`,
 1 AS `position_id`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `depcou_id`,
 1 AS `cou_name`,
 1 AS `maj_id`,
 1 AS `maj_nameTH`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_researcher_frist_project`
--

DROP TABLE IF EXISTS `view_researcher_frist_project`;
/*!50001 DROP VIEW IF EXISTS `view_researcher_frist_project`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_researcher_frist_project` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `pro_id`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `researcher_id`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `maj_id`,
 1 AS `maj_nameTH`,
 1 AS `depcou_id`,
 1 AS `cou_name`,
 1 AS `pt_for`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `research_frist_project`
--

DROP TABLE IF EXISTS `research_frist_project`;
/*!50001 DROP VIEW IF EXISTS `research_frist_project`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `research_frist_project` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `depcou_id`,
 1 AS `cou_name`,
 1 AS `maj_id`,
 1 AS `maj_nameTH`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `conut_frist_project`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_respon_group`
--

DROP TABLE IF EXISTS `view_respon_group`;
/*!50001 DROP VIEW IF EXISTS `view_respon_group`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_respon_group` AS SELECT 
 1 AS `pro_group`,
 1 AS `respon_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_respon_group_all`
--

DROP TABLE IF EXISTS `view_respon_group_all`;
/*!50001 DROP VIEW IF EXISTS `view_respon_group_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_respon_group_all` AS SELECT 
 1 AS `pro_group`,
 1 AS `respon_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_tnrr`
--

DROP TABLE IF EXISTS `view_tnrr`;
/*!50001 DROP VIEW IF EXISTS `view_tnrr`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_tnrr` AS SELECT 
 1 AS `pro_id`,
 1 AS `newid`,
 1 AS `year_name`,
 1 AS `pro_nameTH`,
 1 AS `pro_nameEN`,
 1 AS `department_nameTH`,
 1 AS `pro_abstract`,
 1 AS `pro_budget`,
 1 AS `pro_keyword`,
 1 AS `prefixnameTH`,
 1 AS `respon_name`,
 1 AS `departmentnameTH`,
 1 AS `positionnameTH`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize`
--

DROP TABLE IF EXISTS `view_utilize`;
/*!50001 DROP VIEW IF EXISTS `view_utilize`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize` AS SELECT 
 1 AS `utz_year_id`,
 1 AS `year_name`,
 1 AS `utz_type_id`,
 1 AS `utz_typr_name`,
 1 AS `department_id`,
 1 AS `depcou_id`,
 1 AS `maj_id`,
 1 AS `countutzpro`,
 1 AS `countutz`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize_bud`
--

DROP TABLE IF EXISTS `view_utilize_bud`;
/*!50001 DROP VIEW IF EXISTS `view_utilize_bud`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize_bud` AS SELECT 
 1 AS `utz_year_bud`,
 1 AS `year_name`,
 1 AS `utz_type_id`,
 1 AS `utz_typr_name`,
 1 AS `department_id`,
 1 AS `countutzpro`,
 1 AS `countutz`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize_edu`
--

DROP TABLE IF EXISTS `view_utilize_edu`;
/*!50001 DROP VIEW IF EXISTS `view_utilize_edu`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize_edu` AS SELECT 
 1 AS `utz_year_edu`,
 1 AS `year_name`,
 1 AS `utz_type_id`,
 1 AS `utz_typr_name`,
 1 AS `department_id`,
 1 AS `countutzpro`,
 1 AS `countutz`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize_edu_qa`
--

DROP TABLE IF EXISTS `view_utilize_edu_qa`;
/*!50001 DROP VIEW IF EXISTS `view_utilize_edu_qa`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize_edu_qa` AS SELECT 
 1 AS `utz_year_edu`,
 1 AS `year_name`,
 1 AS `utz_type_id`,
 1 AS `utz_typr_name`,
 1 AS `department_id`,
 1 AS `countutzpro`,
 1 AS `countutz`,
 1 AS `countdep`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize_groupall`
--

DROP TABLE IF EXISTS `view_utilize_groupall`;
/*!50001 DROP VIEW IF EXISTS `view_utilize_groupall`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize_groupall` AS SELECT 
 1 AS `utz_id`,
 1 AS `utz_group`,
 1 AS `namegroupall`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize_all`
--

DROP TABLE IF EXISTS `view_utilize_all`;
/*!50001 DROP VIEW IF EXISTS `view_utilize_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize_all` AS SELECT 
 1 AS `utz_id`,
 1 AS `pro_id`,
 1 AS `utz_year_id`,
 1 AS `utz_year_name`,
 1 AS `utz_year_bud`,
 1 AS `bud_year_name`,
 1 AS `utz_year_edu`,
 1 AS `edu_year_name`,
 1 AS `utz_date`,
 1 AS `utz_department_name`,
 1 AS `chw_id`,
 1 AS `utz_group`,
 1 AS `namegroupall`,
 1 AS `utz_files`,
 1 AS `year_id`,
 1 AS `pro_year_name`,
 1 AS `pt_id`,
 1 AS `pro_nameTH`,
 1 AS `department_id`,
 1 AS `pro_department_nameTH`,
 1 AS `pgroup_id`,
 1 AS `pt_for`,
 1 AS `pt_created`,
 1 AS `pt_type`,
 1 AS `pt_utz`,
 1 AS `respon_name`,
 1 AS `departmentnameTH`,
 1 AS `positionnameTH`,
 1 AS `ta_id`,
 1 AS `tambon_t`,
 1 AS `am_id`,
 1 AS `amphoe_t`,
 1 AS `ch_id`,
 1 AS `changwat_t`,
 1 AS `lat`,
 1 AS `long`,
 1 AS `utz_leading`,
 1 AS `utz_leading_position`,
 1 AS `utz_group_qa`,
 1 AS `utz_detail`,
 1 AS `utz_budget`,
 1 AS `data_show`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize_all_latlong`
--

DROP TABLE IF EXISTS `view_utilize_all_latlong`;
/*!50001 DROP VIEW IF EXISTS `view_utilize_all_latlong`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize_all_latlong` AS SELECT 
 1 AS `utz_id`,
 1 AS `pro_id`,
 1 AS `utz_year_id`,
 1 AS `year_id_name`,
 1 AS `utz_year_bud`,
 1 AS `year_bud_name`,
 1 AS `utz_date`,
 1 AS `utz_leading`,
 1 AS `utz_leading_position`,
 1 AS `utz_department_name`,
 1 AS `chw_id`,
 1 AS `ta_id`,
 1 AS `tambon_t`,
 1 AS `am_id`,
 1 AS `amphoe_t`,
 1 AS `ch_id`,
 1 AS `changwat_t`,
 1 AS `lat`,
 1 AS `long`,
 1 AS `utz_group`,
 1 AS `namegroupall`,
 1 AS `utz_detail`,
 1 AS `utz_files`,
 1 AS `data_show`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize_pro_year`
--

DROP TABLE IF EXISTS `view_utilize_pro_year`;
/*!50001 DROP VIEW IF EXISTS `view_utilize_pro_year`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize_pro_year` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `utz_type_id`,
 1 AS `utz_typr_name`,
 1 AS `countutzpro`,
 1 AS `countutz`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize_report`
--

DROP TABLE IF EXISTS `view_utilize_report`;
/*!50001 DROP VIEW IF EXISTS `view_utilize_report`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize_report` AS SELECT 
 1 AS `utz_id`,
 1 AS `pro_id`,
 1 AS `utz_year_id`,
 1 AS `utz_year_bud`,
 1 AS `utz_year_edu`,
 1 AS `utz_date`,
 1 AS `utz_department_name`,
 1 AS `chw_id`,
 1 AS `utz_group`,
 1 AS `namegroupall`,
 1 AS `utz_files`,
 1 AS `year_id`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `pts_name`,
 1 AS `pro_nameTH`,
 1 AS `department_id`,
 1 AS `year_name`,
 1 AS `department_nameTH`,
 1 AS `ta_id`,
 1 AS `tambon_t`,
 1 AS `am_id`,
 1 AS `amphoe_t`,
 1 AS `ch_id`,
 1 AS `changwat_t`,
 1 AS `lat`,
 1 AS `long`,
 1 AS `utz_leading`,
 1 AS `utz_leading_position`,
 1 AS `utz_group_qa`,
 1 AS `utz_detail`,
 1 AS `utz_budget`,
 1 AS `data_show`,
 1 AS `prefixnameTH`,
 1 AS `respon_name`,
 1 AS `departmentnameTH`,
 1 AS `positionnameTH`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utilize_year`
--

DROP TABLE IF EXISTS `view_utilize_year`;
/*!50001 DROP VIEW IF EXISTS `view_utilize_year`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utilize_year` AS SELECT 
 1 AS `utz_year_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utz`
--

DROP TABLE IF EXISTS `view_utz`;
/*!50001 DROP VIEW IF EXISTS `view_utz`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utz` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `cou_utz`,
 1 AS `cou_pro`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utz_bud_plan`
--

DROP TABLE IF EXISTS `view_utz_bud_plan`;
/*!50001 DROP VIEW IF EXISTS `view_utz_bud_plan`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utz_bud_plan` AS SELECT 
 1 AS `pro_year_name`,
 1 AS `pro_nameTH`,
 1 AS `respon_name`,
 1 AS `departmentnameTH`,
 1 AS `utz_date`,
 1 AS `namegroupall`,
 1 AS `utz_department_name`,
 1 AS `tambon_t`,
 1 AS `amphoe_t`,
 1 AS `changwat_t`,
 1 AS `utz_leading`,
 1 AS `utz_leading_position`,
 1 AS `utz_detail`,
 1 AS `utz_id`,
 1 AS `pro_id`,
 1 AS `pt_utz`,
 1 AS `utz_year_id`,
 1 AS `utz_year_name`,
 1 AS `utz_year_bud`,
 1 AS `bud_year_name`,
 1 AS `utz_year_edu`,
 1 AS `edu_year_name`,
 1 AS `chw_id`,
 1 AS `utz_group`,
 1 AS `utz_files`,
 1 AS `year_id`,
 1 AS `pt_id`,
 1 AS `pgroup_id`,
 1 AS `pt_for`,
 1 AS `pt_created`,
 1 AS `pt_type`,
 1 AS `positionnameTH`,
 1 AS `ta_id`,
 1 AS `am_id`,
 1 AS `ch_id`,
 1 AS `lat`,
 1 AS `long`,
 1 AS `utz_group_qa`,
 1 AS `utz_budget`,
 1 AS `data_show`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utz_edu`
--

DROP TABLE IF EXISTS `view_utz_edu`;
/*!50001 DROP VIEW IF EXISTS `view_utz_edu`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utz_edu` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `cou_utzall`,
 1 AS `cou_utz`,
 1 AS `cou_pro`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_utz_edu_qa`
--

DROP TABLE IF EXISTS `view_utz_edu_qa`;
/*!50001 DROP VIEW IF EXISTS `view_utz_edu_qa`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_utz_edu_qa` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `cou_utzall`,
 1 AS `cou_utz`,
 1 AS `cou_pro`,
 1 AS `cou_dep`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_year`
--

DROP TABLE IF EXISTS `view_year`;
/*!50001 DROP VIEW IF EXISTS `view_year`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_year` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_researcher_all`
--

DROP TABLE IF EXISTS `view_researcher_all`;
/*!50001 DROP VIEW IF EXISTS `view_researcher_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_researcher_all` AS SELECT 
 1 AS `year_id`,
 1 AS `year_name`,
 1 AS `department_id`,
 1 AS `department_nameTH`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `count_researcher`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_zcount_coferenceinthai`
--

DROP TABLE IF EXISTS `view_zcount_coferenceinthai`;
/*!50001 DROP VIEW IF EXISTS `view_zcount_coferenceinthai`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_zcount_coferenceinthai` AS SELECT 
 1 AS `fyear`,
 1 AS `ym`,
 1 AS `cnews`,
 1 AS `ccount`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_zcount_news`
--

DROP TABLE IF EXISTS `view_zcount_news`;
/*!50001 DROP VIEW IF EXISTS `view_zcount_news`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_zcount_news` AS SELECT 
 1 AS `fyear`,
 1 AS `ym`,
 1 AS `cnews`,
 1 AS `ccount`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_zcount_rdbdownloas`
--

DROP TABLE IF EXISTS `view_zcount_rdbdownloas`;
/*!50001 DROP VIEW IF EXISTS `view_zcount_rdbdownloas`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_zcount_rdbdownloas` AS SELECT 
 1 AS `fyear`,
 1 AS `ym`,
 1 AS `ccount`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `viewutzeduqadata`
--

DROP TABLE IF EXISTS `viewutzeduqadata`;
/*!50001 DROP VIEW IF EXISTS `viewutzeduqadata`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `viewutzeduqadata` AS SELECT 
 1 AS `utz_id`,
 1 AS `department_nameTH`,
 1 AS `year_name`,
 1 AS `pro_nameTH`,
 1 AS `pgroup_id`,
 1 AS `pt_id`,
 1 AS `pt_name`,
 1 AS `prefixnameTH`,
 1 AS `respon_name`,
 1 AS `data_show`,
 1 AS `utz_department_name`,
 1 AS `utz_detail`,
 1 AS `utz_leading`,
 1 AS `utz_leading_position`,
 1 AS `utz_date`,
 1 AS `utz_year_edu`,
 1 AS `utz_group_qa`,
 1 AS `chw_id`,
 1 AS `tambon_t`,
 1 AS `amphoe_t`,
 1 AS `changwat_t`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `test_viewprojectalldata`
--

/*!50001 DROP VIEW IF EXISTS `test_viewprojectalldata`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `test_viewprojectalldata` AS select `rdb_project`.`pro_id` AS `pro_id`,`rdb_project`.`year_id` AS `year_id`,(select `rdb_year`.`year_name` from `rdb_year` where (`rdb_year`.`year_id` = `rdb_project`.`year_id`)) AS `year_name`,`rdb_project`.`pro_code` AS `pro_code`,`rdb_project`.`pgroup_id` AS `pgroup_id`,(select `rdb_groupproject`.`pgroup_nameTH` from `rdb_groupproject` where (`rdb_groupproject`.`pgroup_id` = `rdb_project`.`pgroup_id`)) AS `pgroup_nameTH`,`rdb_project`.`pt_id` AS `pt_id`,(select `rdb_project_types`.`pt_name` from `rdb_project_types` where (`rdb_project_types`.`pt_id` = `rdb_project`.`pt_id`)) AS `pt_name`,`rdb_project`.`pts_id` AS `pts_id`,(select `rdb_project_types_sub`.`pts_name` from `rdb_project_types_sub` where (`rdb_project_types_sub`.`pts_id` = `rdb_project`.`pts_id`)) AS `pts_name`,`rdb_project`.`pro_nameTH` AS `pro_nameTH`,`rdb_project`.`department_id` AS `department_id`,(select `rdb_department`.`department_nameTH` from `rdb_department` where (`rdb_department`.`department_id` = `rdb_project`.`department_id`)) AS `department_nameTH`,`rdb_project`.`depcou_id` AS `depcou_id`,(select `rdb_department_course`.`cou_name` from `rdb_department_course` where (`rdb_department_course`.`depcou_id` = `rdb_project`.`depcou_id`)) AS `cou_name`,`rdb_project`.`major_id` AS `major_id`,(select `rdb_dep_major`.`maj_nameEN` from `rdb_dep_major` where (`rdb_dep_major`.`maj_id` = `rdb_project`.`major_id`)) AS `maj_nameEN`,`rdb_project`.`pro_abstract` AS `pro_abstract`,`rdb_project`.`pro_keyword` AS `pro_keyword`,`rdb_project`.`pro_budget` AS `pro_budget`,`rdb_project`.`pro_file` AS `pro_file`,`rdb_project`.`ps_id` AS `ps_id`,(select `rdb_project_status`.`ps_name` from `rdb_project_status` where (`rdb_project_status`.`ps_id` = `rdb_project`.`ps_id`)) AS `ps_name`,`rdb_project`.`pro_group` AS `pro_group`,`rdb_project`.`data_show` AS `data_show` from `rdb_project` where ((`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6)) order by `rdb_project`.`pro_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_changwat`
--

/*!50001 DROP VIEW IF EXISTS `view_changwat`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_changwat` AS select `rdb_changwat`.`id` AS `id`,concat('TH',`rdb_changwat`.`ta_id`) AS `ADM3_PCODE`,`rdb_changwat`.`tambon_t` AS `ADM3_TH`,`rdb_changwat`.`tambon_e` AS `tambon_e`,`rdb_changwat`.`am_id` AS `am_id`,`rdb_changwat`.`amphoe_t` AS `ADM2_TH`,`rdb_changwat`.`amphoe_e` AS `amphoe_e`,`rdb_changwat`.`ch_id` AS `ch_id`,`rdb_changwat`.`changwat_t` AS `changwat_t`,`rdb_changwat`.`changwat_e` AS `changwat_e`,`rdb_changwat`.`lat` AS `lat`,`rdb_changwat`.`long` AS `long` from `rdb_changwat` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_dateevent`
--

/*!50001 DROP VIEW IF EXISTS `view_dateevent`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_dateevent` AS select `rdb_dateevent`.`ev_id` AS `ev_id`,`rdb_dateevent`.`evt_id` AS `evt_id`,`rdb_dateevent`.`ev_title` AS `ev_title`,`rdb_dateevent`.`ev_detail` AS `ev_detail`,`rdb_dateevent`.`ev_datestart` AS `ev_datestart`,`rdb_dateevent`.`ev_timestart` AS `ev_timestart`,`rdb_dateevent`.`ev_dateend` AS `ev_dateend`,`rdb_dateevent`.`ev_timeend` AS `ev_timeend`,`rdb_dateevent`.`ev_url` AS `ev_url`,`rdb_dateevent`.`ev_status` AS `ev_status`,(case when ((date_format(now(),'%Y-%m-%d') >= `rdb_dateevent`.`ev_datestart`) and (date_format(now(),'%Y-%m-%d') <= `rdb_dateevent`.`ev_dateend`)) then (to_days(`rdb_dateevent`.`ev_dateend`) - to_days(date_format(now(),'%Y-%m-%d'))) else 9999 end) AS `dateevents`,(case when ((to_days(`rdb_dateevent`.`ev_datestart`) - to_days(date_format(now(),'%Y-%m-%d'))) >= 0) then (to_days(`rdb_dateevent`.`ev_datestart`) - to_days(date_format(now(),'%Y-%m-%d'))) else 9999 end) AS `ctodate`,`rdb_dateevent`.`created_at` AS `created_at` from `rdb_dateevent` order by (case when ((date_format(now(),'%Y-%m-%d') >= `rdb_dateevent`.`ev_datestart`) and (date_format(now(),'%Y-%m-%d') <= `rdb_dateevent`.`ev_dateend`)) then (to_days(`rdb_dateevent`.`ev_dateend`) - to_days(date_format(now(),'%Y-%m-%d'))) else 9999 end),(case when ((to_days(`rdb_dateevent`.`ev_datestart`) - to_days(date_format(now(),'%Y-%m-%d'))) >= 0) then (to_days(`rdb_dateevent`.`ev_datestart`) - to_days(date_format(now(),'%Y-%m-%d'))) else 9999 end),`rdb_dateevent`.`ev_datestart`,`rdb_dateevent`.`ev_dateend` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_dip_all`
--

/*!50001 DROP VIEW IF EXISTS `view_dip_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_dip_all` AS select `rdb_dip`.`dip_id` AS `dip_id`,`rdb_dip`.`dip_data1_datestart` AS `dip_data1_datestart`,`rdb_dip_type`.`dipt_name` AS `dipt_name`,concat(`rdb_prefix`.`prefix_nameTH`,`rdb_researcher`.`researcher_fname`,' ',`rdb_researcher`.`researcher_lname`) AS `researcher_fullname`,`rdb_department`.`department_nameTH` AS `department_nameTH`,`rdb_dip`.`dip_data2_status` AS `dip_data2_status` from ((((`rdb_dip` left join `rdb_dip_type` on((`rdb_dip`.`dipt_id` = `rdb_dip_type`.`dipt_id`))) left join `rdb_researcher` on((`rdb_dip`.`researcher_id` = `rdb_researcher`.`researcher_id`))) left join `rdb_department` on((`rdb_researcher`.`department_id` = `rdb_department`.`department_id`))) left join `rdb_prefix` on((`rdb_researcher`.`prefix_id` = `rdb_prefix`.`prefix_id`))) order by `rdb_dip`.`dip_data1_datestart` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_project_checkdata`
--

/*!50001 DROP VIEW IF EXISTS `view_project_checkdata`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_project_checkdata` AS select `rdb_project`.`pro_id` AS `pro_id`,`rdb_project`.`pgroup_id` AS `pgroup_id`,`rdb_project`.`pro_group` AS `pro_group`,`rdb_project`.`year_id` AS `year_id`,`rdb_project`.`pt_id` AS `pt_id`,`rdb_project`.`department_id` AS `department_id`,`rdb_project`.`major_id` AS `major_id`,`rdb_project`.`ps_id` AS `ps_id`,(case when (`rdb_project`.`pgroup_id` = 1) then (select `b`.`researcher_id` from `rdb_project_work` `b` where ((`b`.`pro_id` = `rdb_project`.`pro_id`) and (`b`.`position_id` = 1))) when (`rdb_project`.`pgroup_id` in (2,3)) then (select `c`.`researcher_id` from `rdb_project_work` `c` where ((`c`.`pro_id` = `rdb_project`.`pro_id`) and (`c`.`position_id` = 2))) end) AS `researcher_id`,(case when (`rdb_project`.`pro_file` is not null) then 0 else 1 end) AS `ck_files`,(case when (`rdb_project`.`year_id` <= 7) then 0 when ((`rdb_project`.`pgroup_id` in (1,2)) and ((select `a`.`pro_id` from `rdb_project_utilize` `a` where `a`.`pro_id` in (select `subg`.`pro_id` from `rdb_project` `subg` where (`subg`.`pro_group` = `rdb_project`.`pro_group`)) limit 1) is not null)) then 0 when ((select `a`.`pro_id` from `rdb_project_utilize` `a` where (`a`.`pro_id` = `rdb_project`.`pro_id`) group by `a`.`pro_id`) is not null) then 0 else 1 end) AS `ck_utilize`,(case when (`rdb_project`.`year_id` <= 6) then 0 when (`rdb_project`.`pt_id` = 5) then 0 when ((`rdb_project`.`pgroup_id` in (1,2)) and ((select `a`.`pro_id` from `rdb_published` `a` where `a`.`pro_id` in (select `subg`.`pro_id` from `rdb_project` `subg` where (`subg`.`pro_group` = `rdb_project`.`pro_group`)) limit 1) is not null)) then 0 when ((select `a`.`pro_id` from `rdb_published` `a` where (`a`.`pro_id` = `rdb_project`.`pro_id`) group by `a`.`pro_id`) is not null) then 0 else 1 end) AS `ck_published`,(select `b`.`ckb_annuity` from `rdb_project_budget` `b` where (`b`.`pro_id` = `rdb_project`.`pro_id`)) AS `ck_budget` from `rdb_project` where ((`rdb_project`.`pt_id` not in (11,12)) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6)) order by `rdb_project`.`pro_id` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_project_group_all`
--

/*!50001 DROP VIEW IF EXISTS `view_project_group_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_project_group_all` AS select `rpj`.`pro_group` AS `pro_group`,sum(`rpj`.`pro_budget`) AS `sumgroup`,group_concat(concat(convert(ifnull(`rpj`.`pro_id`,'--') using utf8mb4)) order by `rpj`.`pgroup_id` ASC,`rpj`.`pro_id` ASC separator '|') AS `group_id` from ((`rdb_project` `rpj` left join `rdb_department` `rd` on((`rpj`.`department_id` = `rd`.`department_id`))) left join `rdb_dep_major` `rdm` on((`rpj`.`major_id` = `rdm`.`maj_id`))) where (`rpj`.`pro_group` is not null) group by `rpj`.`pro_group` order by `rpj`.`pro_group` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_project_published`
--

/*!50001 DROP VIEW IF EXISTS `view_project_published`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_project_published` AS select `rdb_year`.`year_id` AS `year_id`,`rdb_year`.`year_name` AS `year_name`,`rdb_department`.`department_id` AS `department_id`,`rdb_department`.`department_nameTH` AS `department_nameTH`,ifnull(`rdb_published_personnel`.`personnel_num`,0) AS `personnel_num`,ifnull(`rdb_published_personnel`.`personnel_numedu`,0) AS `personnel_numedu`,ifnull(`rdb_published_personnel`.`personnel_numbud`,0) AS `personnel_numbud`,ifnull(`rdb_published_branchper`.`branchper_percent`,0) AS `branchper_percent`,`rdb_published_branch`.`branch_id` AS `branch_id`,`rdb_published_branch`.`branch_name` AS `branch_name`,`rdb_department_category`.`depcat_id` AS `depcat_id`,`rdb_department_category`.`depcat_name` AS `depcat_name`,(select sum(`rdb_published_personnel`.`personnel_num`) from `rdb_published_personnel` where (`rdb_published_personnel`.`year_id` = `rdb_year`.`year_id`)) AS `personnel_numall`,(select sum(`rdb_published_personnel`.`personnel_numedu`) from `rdb_published_personnel` where (`rdb_published_personnel`.`year_id` = `rdb_year`.`year_id`)) AS `personnel_numalledu`,(select sum(`rdb_published_personnel`.`personnel_numbud`) from `rdb_published_personnel` where (`rdb_published_personnel`.`year_id` = `rdb_year`.`year_id`)) AS `personnel_numallbud`,round((select count(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published`.`department_id` = `rdb_department`.`department_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`pub_score` <> 0) and (`rdb_published`.`data_show` = 1))),2) AS `countpub_score`,round((select count(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_edu` = `rdb_year`.`year_id`) and (`rdb_published`.`department_id` = `rdb_department`.`department_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`pub_score` <> 0) and (`rdb_published`.`data_show` = 1))),2) AS `countpub_scoreedu`,round((select count(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_bud` = `rdb_year`.`year_id`) and (`rdb_published`.`department_id` = `rdb_department`.`department_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`pub_score` <> 0) and (`rdb_published`.`data_show` = 1))),2) AS `countpub_scorebud`,round(ifnull((select sum(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published`.`department_id` = `rdb_department`.`department_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`pub_score` <> 0) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_score`,round(ifnull((select sum(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_edu` = `rdb_year`.`year_id`) and (`rdb_published`.`department_id` = `rdb_department`.`department_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`pub_score` <> 0) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_scoreedu`,round(ifnull((select sum(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_bud` = `rdb_year`.`year_id`) and (`rdb_published`.`department_id` = `rdb_department`.`department_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`pub_score` <> 0) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_scorebud`,round(ifnull((select sum(`rdb_published`.`pub_budget`) from `rdb_published` where ((`rdb_published`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published`.`department_id` = `rdb_department`.`department_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`pub_score` <> 0) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_budget`,round(ifnull((select sum(`rdb_published`.`pub_budget`) from `rdb_published` where ((`rdb_published`.`year_edu` = `rdb_year`.`year_id`) and (`rdb_published`.`department_id` = `rdb_department`.`department_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`pub_score` <> 0) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_budgetedu`,round(ifnull((select sum(`rdb_published`.`pub_budget`) from `rdb_published` where ((`rdb_published`.`year_bud` = `rdb_year`.`year_id`) and (`rdb_published`.`department_id` = `rdb_department`.`department_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`pub_score` <> 0) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_budgetbud` from ((((((`rdb_year` join `rdb_department_category`) left join `rdb_department_course` on((`rdb_department_category`.`depcat_id` = `rdb_department_course`.`depcat_id`))) left join `rdb_department` on((`rdb_department_course`.`department_id` = `rdb_department`.`department_id`))) left join `rdb_published_personnel` on(((`rdb_department`.`department_id` = `rdb_published_personnel`.`department_id`) and (`rdb_department_category`.`depcat_id` = `rdb_published_personnel`.`depcat_id`) and (`rdb_year`.`year_id` = `rdb_published_personnel`.`year_id`)))) left join `rdb_published_branchper` on(((`rdb_published_personnel`.`depcat_id` = `rdb_published_branchper`.`branch_id`) and (`rdb_year`.`year_id` = `rdb_published_branchper`.`year_id`)))) left join `rdb_published_branch` on((`rdb_published_branchper`.`branch_id` = `rdb_published_branch`.`branch_id`))) where (`rdb_department_category`.`depcat_id` <> 99) group by `rdb_year`.`year_id`,`rdb_department_category`.`depcat_id`,`rdb_department_course`.`department_id` order by `rdb_year`.`year_name` desc,`rdb_department_course`.`department_id`,`rdb_department_category`.`depcat_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_project_published_dep`
--

/*!50001 DROP VIEW IF EXISTS `view_project_published_dep`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_project_published_dep` AS select `rdb_year`.`year_id` AS `year_id`,`rdb_year`.`year_name` AS `year_name`,`rdb_department_course`.`department_id` AS `department_id`,`rdb_department_course`.`depcou_id` AS `depcou_id`,`rdb_department_course`.`cou_name` AS `cou_name`,`rdb_dep_major`.`maj_id` AS `maj_id`,`rdb_dep_major`.`maj_nameTH` AS `maj_nameTH`,ifnull((select `rdb_published_personnel_dep`.`personnel_num` from `rdb_published_personnel_dep` where ((`rdb_published_personnel_dep`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published_personnel_dep`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published_personnel_dep`.`depcat_id` = `rdb_published_branch`.`branch_id`))),0) AS `personnel_num`,ifnull((select `rdb_published_personnel_dep`.`personnel_numedu` from `rdb_published_personnel_dep` where ((`rdb_published_personnel_dep`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published_personnel_dep`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published_personnel_dep`.`depcat_id` = `rdb_published_branch`.`branch_id`))),0) AS `personnel_numedu`,ifnull((select `rdb_published_personnel_dep`.`personnel_numbud` from `rdb_published_personnel_dep` where ((`rdb_published_personnel_dep`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published_personnel_dep`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published_personnel_dep`.`depcat_id` = `rdb_published_branch`.`branch_id`))),0) AS `personnel_numbud`,(select sum(`rdb_published_personnel_dep`.`personnel_num`) from `rdb_published_personnel_dep` where ((`rdb_published_personnel_dep`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published_personnel_dep`.`department_id` = `rdb_dep_major`.`department_id`))) AS `personnel_numall`,(select sum(`rdb_published_personnel_dep`.`personnel_numedu`) from `rdb_published_personnel_dep` where ((`rdb_published_personnel_dep`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published_personnel_dep`.`department_id` = `rdb_dep_major`.`department_id`))) AS `personnel_numalledu`,(select sum(`rdb_published_personnel_dep`.`personnel_numbud`) from `rdb_published_personnel_dep` where ((`rdb_published_personnel_dep`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published_personnel_dep`.`department_id` = `rdb_dep_major`.`department_id`))) AS `personnel_numallbud`,`rdb_published_branch`.`branch_id` AS `branch_id`,`rdb_published_branch`.`branch_name` AS `branch_name`,(select `rdb_published_branchper`.`branchper_percent` from `rdb_published_branchper` where ((`rdb_published_branchper`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published_branchper`.`branch_id` = `rdb_published_branch`.`branch_id`))) AS `branchper_percent`,round((select count(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`data_show` = 1))),2) AS `countpub_score`,round((select count(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_edu` = `rdb_year`.`year_id`) and (`rdb_published`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`data_show` = 1))),2) AS `countpub_scoreedu`,round((select count(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_bud` = `rdb_year`.`year_id`) and (`rdb_published`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`data_show` = 1))),2) AS `countpub_scorebud`,round(ifnull((select sum(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_score`,round(ifnull((select sum(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_edu` = `rdb_year`.`year_id`) and (`rdb_published`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_scoreedu`,round(ifnull((select sum(`rdb_published`.`pub_score`) from `rdb_published` where ((`rdb_published`.`year_bud` = `rdb_year`.`year_id`) and (`rdb_published`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_scorebud`,round(ifnull((select sum(`rdb_published`.`pub_budget`) from `rdb_published` where ((`rdb_published`.`year_id` = `rdb_year`.`year_id`) and (`rdb_published`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_budget`,round(ifnull((select sum(`rdb_published`.`pub_budget`) from `rdb_published` where ((`rdb_published`.`year_edu` = `rdb_year`.`year_id`) and (`rdb_published`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_budgetedu`,round(ifnull((select sum(`rdb_published`.`pub_budget`) from `rdb_published` where ((`rdb_published`.`year_bud` = `rdb_year`.`year_id`) and (`rdb_published`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_published`.`branch_id` = `rdb_published_branch`.`branch_id`) and (`rdb_published`.`data_show` = 1))),0),2) AS `sumpub_budgetbud` from ((`rdb_year` join (`rdb_department_course` left join `rdb_dep_major` on((`rdb_department_course`.`depcou_id` = `rdb_dep_major`.`depcou_id`)))) join `rdb_published_branch`) having ((`personnel_num` > 0) or (`personnel_numedu` > 0) or (`personnel_numbud` > 0)) order by `rdb_year`.`year_name` desc,`rdb_department_course`.`department_id`,`rdb_department_course`.`cou_name`,`rdb_dep_major`.`maj_nameTH`,`rdb_published_branch`.`branch_name` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_project_work_all`
--

/*!50001 DROP VIEW IF EXISTS `view_project_work_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_project_work_all` AS select `rpw`.`pro_id` AS `pro_id`,group_concat(concat(convert(ifnull(`rpw`.`researcher_id`,'--') using utf8mb4)) order by `rpw`.`position_id` ASC,`rpw`.`ratio` DESC separator '|') AS `researcherid`,group_concat(concat(convert(ifnull(`rr`.`researcher_codeid`,'--') using utf8mb4)) order by `rpw`.`position_id` ASC,`rpw`.`ratio` DESC separator '|') AS `researchercodeid`,group_concat(concat(convert(ifnull(`rp`.`prefix_nameTH`,'--') using utf8mb4)) order by `rpw`.`position_id` ASC,`rpw`.`ratio` DESC separator '|') AS `prefixnameTH`,group_concat(concat(convert(ifnull(`rr`.`researcher_fname`,'--') using utf8mb4),' ',convert(ifnull(`rr`.`researcher_lname`,'--') using utf8mb4)) order by `rpw`.`position_id` ASC,`rpw`.`ratio` DESC separator '|') AS `respon_name`,group_concat(concat(convert(ifnull(`rdm`.`maj_nameTH`,'--') using utf8mb4)) order by `rpw`.`position_id` ASC,`rpw`.`ratio` DESC separator '|') AS `majnameTH`,group_concat(concat(convert(ifnull(`rd`.`department_nameTH`,'--') using utf8mb4)) order by `rpw`.`position_id` ASC,`rpw`.`ratio` DESC separator '|') AS `departmentnameTH`,group_concat(concat(convert(ifnull(`rpw`.`ratio`,'--') using utf8mb4)) order by `rpw`.`position_id` ASC,`rpw`.`ratio` DESC separator '|') AS `ratio`,group_concat(concat(convert(ifnull(`rpp`.`position_nameTH`,'--') using utf8mb4)) order by `rpw`.`position_id` ASC,`rpw`.`ratio` DESC separator '|') AS `positionnameTH` from ((((((`rdb_project_work` `rpw` left join `rdb_researcher` `rr` on((`rpw`.`researcher_id` = `rr`.`researcher_id`))) left join `rdb_project` `rpj` on((`rpw`.`pro_id` = `rpj`.`pro_id`))) left join `rdb_project_position` `rpp` on((`rpw`.`position_id` = `rpp`.`position_id`))) left join `rdb_prefix` `rp` on((`rr`.`prefix_id` = `rp`.`prefix_id`))) left join `rdb_department` `rd` on((`rr`.`department_id` = `rd`.`department_id`))) left join `rdb_dep_major` `rdm` on((`rr`.`maj_id` = `rdm`.`maj_id`))) group by `rpw`.`pro_id` order by `rpw`.`pro_id` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_project_all`
--

/*!50001 DROP VIEW IF EXISTS `view_project_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_project_all` AS select `rdb_project`.`pro_id` AS `pro_id`,`rdb_project`.`year_id` AS `year_id`,`rdb_year`.`year_name` AS `year_name`,`rdb_project`.`pgroup_id` AS `pgroup_id`,`rdb_groupproject`.`pgroup_nameTH` AS `pgroup_nameTH`,`rdb_project`.`pt_id` AS `pt_id`,`rdb_project_types`.`pt_name` AS `pt_name`,`rdb_project`.`pts_id` AS `pts_id`,`rdb_project_types_sub`.`pts_name` AS `pts_name`,`rdb_project`.`pro_nameTH` AS `pro_nameTH`,`rdb_project`.`pro_nameEN` AS `pro_nameEN`,`rdb_project`.`department_id` AS `department_id`,`rdb_department`.`department_nameTH` AS `department_nameTH`,`rdb_project`.`major_id` AS `major_id`,`rdb_dep_major`.`maj_nameTH` AS `maj_nameTH`,`rdb_project`.`pro_abstract` AS `pro_abstract`,`rdb_project`.`pro_reference` AS `pro_reference`,`rdb_project`.`pro_date_start` AS `pro_date_start`,`rdb_project`.`pro_date_end` AS `pro_date_end`,`rdb_project`.`strategic_id` AS `strategic_id`,`rdb_strategic`.`strategic_nameTH` AS `strategic_nameTH`,`rdb_project`.`pro_budget` AS `pro_budget`,`rdb_project`.`pro_keyword` AS `pro_keyword`,`rdb_project`.`pro_abstract_file` AS `pro_abstract_file`,`rdb_project`.`pro_file` AS `pro_file`,`rdb_project`.`pro_file_show` AS `pro_file_show`,`rdb_project`.`pro_page_num` AS `pro_page_num`,`rdb_project`.`pro_finish` AS `pro_finish`,`rdb_project`.`pro_group` AS `pro_group`,`view_project_work_all`.`researcherid` AS `researcherid`,`view_project_work_all`.`prefixnameTH` AS `prefixnameTH`,`view_project_work_all`.`respon_name` AS `respon_name`,`view_project_work_all`.`majnameTH` AS `majnameTH`,`view_project_work_all`.`departmentnameTH` AS `departmentnameTH`,`view_project_work_all`.`ratio` AS `ratio`,`view_project_work_all`.`positionnameTH` AS `positionnameTH`,`rdb_project`.`ps_id` AS `ps_id`,`rdb_project_status`.`ps_name` AS `ps_name`,`rdb_project_status`.`ps_rank` AS `ps_rank`,`rdb_project`.`pro_code` AS `pro_code`,`rdb_project`.`user_updated` AS `user_updated`,`rdb_project`.`user_created` AS `user_created`,`rdb_project`.`data_show` AS `data_show`,`view_project_group_all`.`sumgroup` AS `sumgroup`,`rdb_project`.`pro_count_page` AS `pro_count_page`,`rdb_project`.`pro_note` AS `pro_note`,`rdb_project_types`.`pt_for` AS `pt_for`,`rdb_project_types`.`pt_created` AS `pt_created`,`rdb_project_types`.`pt_type` AS `pt_type`,`rdb_project_types`.`pt_utz` AS `pt_utz` from ((((((((((`rdb_project` left join `rdb_year` on((`rdb_project`.`year_id` = `rdb_year`.`year_id`))) left join `rdb_groupproject` on((`rdb_project`.`pgroup_id` = `rdb_groupproject`.`pgroup_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) left join `rdb_department` on((`rdb_project`.`department_id` = `rdb_department`.`department_id`))) left join `rdb_dep_major` on((`rdb_project`.`major_id` = `rdb_dep_major`.`maj_id`))) left join `rdb_strategic` on((`rdb_project`.`strategic_id` = `rdb_strategic`.`strategic_id`))) left join `view_project_work_all` on((`rdb_project`.`pro_id` = `view_project_work_all`.`pro_id`))) left join `rdb_project_status` on((`rdb_project`.`ps_id` = `rdb_project_status`.`ps_id`))) left join `view_project_group_all` on((`rdb_project`.`pro_group` = `view_project_group_all`.`pro_group`))) left join `rdb_project_types_sub` on((`rdb_project`.`pts_id` = `rdb_project_types_sub`.`pts_name`))) where (`rdb_project`.`data_show` = 1) order by `rdb_project`.`pro_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_published_work_all`
--

/*!50001 DROP VIEW IF EXISTS `view_published_work_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_published_work_all` AS select `rpw`.`published_id` AS `pub_id`,group_concat(concat(convert(ifnull(`rpw`.`researcher_id`,'--') using utf8mb4)) order by `rpp`.`pubta_score` DESC,`rpp`.`pubta_id` ASC,`rpw`.`researcher_id` ASC separator '|') AS `researcherid`,group_concat(concat(convert(ifnull(`rr`.`tea_code`,'--') using utf8mb4)) order by `rpp`.`pubta_score` DESC,`rpp`.`pubta_id` ASC,`rpw`.`researcher_id` ASC separator '|') AS `researchercodeid`,group_concat(concat(convert(ifnull(`rp`.`prefix_nameTH`,'--') using utf8mb4)) order by `rpp`.`pubta_score` DESC,`rpp`.`pubta_id` ASC,`rpw`.`researcher_id` ASC separator '|') AS `prefixnameTH`,group_concat(concat(convert(ifnull(`rr`.`researcher_fname`,'--') using utf8mb4),' ',convert(ifnull(`rr`.`researcher_lname`,'--') using utf8mb4)) order by `rpp`.`pubta_score` DESC,`rpp`.`pubta_id` ASC,`rpw`.`researcher_id` ASC separator '|') AS `respon_name`,group_concat(concat(convert(ifnull(`rdm`.`maj_nameTH`,'--') using utf8mb4)) order by `rpp`.`pubta_score` DESC,`rpp`.`pubta_id` ASC,`rpw`.`researcher_id` ASC separator '|') AS `majnameTH`,group_concat(concat(convert(ifnull(`rd`.`department_nameTH`,'--') using utf8mb4)) order by `rpp`.`pubta_score` DESC,`rpp`.`pubta_id` ASC,`rpw`.`researcher_id` ASC separator '|') AS `departmentnameTH`,group_concat(concat(convert(ifnull(`rpp`.`pubta_nameTH`,'--') using utf8mb4)) order by `rpp`.`pubta_score` DESC,`rpp`.`pubta_id` ASC,`rpw`.`researcher_id` ASC separator '|') AS `pubtaname`,group_concat(concat(convert(ifnull(`rpw`.`pubw_main`,'0') using utf8mb4)) order by `rpp`.`pubta_score` DESC,`rpp`.`pubta_id` ASC,`rpw`.`researcher_id` ASC separator '|') AS `pubwmain`,group_concat(concat(convert(ifnull(`rpw`.`pubw_bud`,'0') using utf8mb4)) order by `rpp`.`pubta_score` DESC,`rpp`.`pubta_id` ASC,`rpw`.`researcher_id` ASC separator '|') AS `pubwbud` from ((((((`rdb_published_work` `rpw` left join `rdb_researcher` `rr` on((`rpw`.`researcher_id` = `rr`.`researcher_id`))) left join `rdb_published` `rpj` on((`rpw`.`published_id` = `rpj`.`id`))) left join `rdb_published_type_author` `rpp` on((`rpw`.`pubta_id` = `rpp`.`pubta_id`))) left join `rdb_prefix` `rp` on((`rr`.`prefix_id` = `rp`.`prefix_id`))) left join `rdb_department` `rd` on((`rr`.`department_id` = `rd`.`department_id`))) left join `rdb_dep_major` `rdm` on((`rr`.`maj_id` = `rdm`.`maj_id`))) group by `rpw`.`published_id` order by `rpw`.`published_id` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_published_all`
--

/*!50001 DROP VIEW IF EXISTS `view_published_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_published_all` AS select `rdb_published`.`id` AS `id`,`rdb_published`.`pro_id` AS `pro_id`,(select `rdb_year`.`year_name` from `rdb_year` where (`rdb_year`.`year_id` = `rdb_published`.`year_id`)) AS `year_name`,(select `rdb_year`.`year_name` from `rdb_year` where (`rdb_year`.`year_id` = `rdb_published`.`year_edu`)) AS `year_edu`,(select `rdb_year`.`year_name` from `rdb_year` where (`rdb_year`.`year_id` = `rdb_published`.`year_bud`)) AS `year_bud`,(select concat(`rdb_published_type`.`pubtype_subgroup`,' (',`rdb_published_type`.`pubtype_group`,' - ',`rdb_published_type`.`pubtype_grouptype`,')') from (`rdb_published` `rpb` left join `rdb_published_type` on((`rpb`.`pubtype_id` = `rdb_published_type`.`pubtype_id`))) where (`rpb`.`id` = `rdb_published`.`id`)) AS `pubtypesubgroup`,`rdb_published`.`pub_name` AS `pub_name`,(select `view_published_work_all`.`researcherid` from `view_published_work_all` where (`view_published_work_all`.`pub_id` = `rdb_published`.`id`)) AS `researcher_id`,(select `view_published_work_all`.`researchercodeid` from `view_published_work_all` where (`view_published_work_all`.`pub_id` = `rdb_published`.`id`)) AS `researcher_code`,(select `view_published_work_all`.`respon_name` from `view_published_work_all` where (`view_published_work_all`.`pub_id` = `rdb_published`.`id`)) AS `researcher_name`,(select `view_published_work_all`.`pubtaname` from `view_published_work_all` where (`view_published_work_all`.`pub_id` = `rdb_published`.`id`)) AS `pubtaname`,(select `view_published_work_all`.`pubwmain` from `view_published_work_all` where (`view_published_work_all`.`pub_id` = `rdb_published`.`id`)) AS `pubwmain`,`rdb_published`.`department_id` AS `department_id`,(select `rdb_department`.`department_nameTH` from `rdb_department` where (`rdb_department`.`department_id` = `rdb_published`.`department_id`)) AS `department_nameTH`,`rdb_published`.`pub_name_journal` AS `pub_name_journal`,`rdb_published`.`pub_date` AS `pub_date`,`rdb_published`.`pub_date` AS `pub_date_end`,`rdb_published`.`pub_score` AS `pub_score`,`rdb_published`.`pub_note` AS `pub_note` from `rdb_published` where (`rdb_published`.`data_show` in (0,1)) order by `rdb_published`.`pub_date` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `testpublishjsondep`
--

/*!50001 DROP VIEW IF EXISTS `testpublishjsondep`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `testpublishjsondep` AS select `vpa`.`id` AS `id`,`vpa`.`pro_id` AS `pro_id`,concat(`view_project_all`.`pro_id`,'|',`view_project_all`.`year_name`,'|',`view_project_all`.`pt_name`,'|',`view_project_all`.`pro_nameTH`,'|',`view_project_all`.`pro_budget`,'|',`view_project_all`.`pro_note`) AS `pro_data`,`vpa`.`year_name` AS `year_name`,`vpa`.`year_edu` AS `year_edu`,`vpa`.`year_bud` AS `year_bud`,`vpa`.`pubtypesubgroup` AS `pubtypesubgroup`,`vpa`.`pub_name` AS `pub_name`,group_concat(concat(`rpw`.`published_id`,'|',`rpw`.`researcher_id`,'|',convert(`rr`.`tea_code` using utf8mb4),'|',convert(`rp`.`prefix_nameTH` using utf8mb4),'|',convert(`rr`.`researcher_fname` using utf8mb4),' ',convert(`rr`.`researcher_lname` using utf8mb4),'|',`rd`.`department_nameTH`,'|',convert(`rpp`.`pubta_nameTH` using utf8mb4),'|',`rpw`.`pubw_main`) order by `rpw`.`pubw_main` DESC separator '||') AS `AUTHORS`,`vpa`.`department_id` AS `department_id`,`vpa`.`department_nameTH` AS `department_nameTH`,`vpa`.`pub_name_journal` AS `pub_name_journal`,`vpa`.`pub_date` AS `pub_date`,`vpa`.`pub_date_end` AS `pub_date_end`,`vpa`.`pub_score` AS `pub_score`,`vpa`.`pub_note` AS `pub_note` from ((((((`view_published_all` `vpa` left join `rdb_published_work` `rpw` on((`vpa`.`id` = `rpw`.`published_id`))) left join `rdb_researcher` `rr` on((`rpw`.`researcher_id` = `rr`.`researcher_id`))) left join `rdb_published_type_author` `rpp` on((`rpw`.`pubta_id` = `rpp`.`pubta_id`))) left join `rdb_prefix` `rp` on((`rr`.`prefix_id` = `rp`.`prefix_id`))) left join `rdb_department` `rd` on((`rr`.`department_id` = `rd`.`department_id`))) left join `view_project_all` on((`vpa`.`pro_id` = `view_project_all`.`pro_id`))) where (`vpa`.`department_id` = 2) group by `vpa`.`id` order by `vpa`.`id` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_researcher`
--

/*!50001 DROP VIEW IF EXISTS `view_researcher`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_researcher` AS select `rdb_project`.`year_id` AS `year_id`,`rdb_year`.`year_name` AS `year_name`,`rdb_project_work`.`pro_id` AS `pro_id`,`rdb_project`.`pgroup_id` AS `pgroup_id`,`rdb_project`.`pt_id` AS `pt_id`,`rdb_project_types`.`pt_name` AS `pt_name`,`rdb_project_work`.`researcher_id` AS `researcher_id`,`rdb_researcher`.`researcher_fname` AS `researcher_fname`,`rdb_researcher`.`researcher_lname` AS `researcher_lname`,`rdb_project_work`.`position_id` AS `position_id`,`rdb_researcher`.`department_id` AS `department_id`,`rdb_department`.`department_nameTH` AS `department_nameTH`,`rdb_researcher`.`depcou_id` AS `depcou_id`,`rdb_department_course`.`cou_name` AS `cou_name`,`rdb_researcher`.`maj_id` AS `maj_id`,`rdb_dep_major`.`maj_nameTH` AS `maj_nameTH` from (((((((`rdb_project_work` left join `rdb_project` on((`rdb_project_work`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_year` on((`rdb_project`.`year_id` = `rdb_year`.`year_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) left join `rdb_researcher` on((`rdb_project_work`.`researcher_id` = `rdb_researcher`.`researcher_id`))) left join `rdb_department_course` on((`rdb_researcher`.`depcou_id` = `rdb_department_course`.`depcou_id`))) left join `rdb_dep_major` on((`rdb_researcher`.`maj_id` = `rdb_dep_major`.`maj_id`))) left join `rdb_department` on((`rdb_researcher`.`department_id` = `rdb_department`.`department_id`))) where ((`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6)) order by `rdb_year`.`year_name` desc,`rdb_department`.`department_nameTH` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_researcher_count`
--

/*!50001 DROP VIEW IF EXISTS `view_researcher_count`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_researcher_count` AS select `view_researcher`.`year_id` AS `year_id`,`view_researcher`.`year_name` AS `year_name`,`view_researcher`.`pt_id` AS `pt_id`,`view_researcher`.`pt_name` AS `pt_name`,`view_researcher`.`pgroup_id` AS `pgroup_id`,`view_researcher`.`researcher_id` AS `researcher_id`,`view_researcher`.`researcher_fname` AS `researcher_fname`,`view_researcher`.`researcher_lname` AS `researcher_lname`,`view_researcher`.`position_id` AS `position_id`,`view_researcher`.`department_id` AS `department_id`,`view_researcher`.`department_nameTH` AS `department_nameTH`,`view_researcher`.`depcou_id` AS `depcou_id`,`view_researcher`.`cou_name` AS `cou_name`,`view_researcher`.`maj_id` AS `maj_id`,`view_researcher`.`maj_nameTH` AS `maj_nameTH` from `view_researcher` where (`view_researcher`.`position_id` in (1,2,3)) group by `view_researcher`.`researcher_id`,`view_researcher`.`year_id`,`view_researcher`.`maj_id` order by `view_researcher`.`year_id` desc,`view_researcher`.`department_id`,`view_researcher`.`pt_id`,`view_researcher`.`researcher_fname`,`view_researcher`.`researcher_lname` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_researcher_frist_project`
--

/*!50001 DROP VIEW IF EXISTS `view_researcher_frist_project`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_researcher_frist_project` AS select `rdb_project`.`year_id` AS `year_id`,(select `rdb_year`.`year_name` from `rdb_year` where (`rdb_year`.`year_id` = `rdb_project`.`year_id`)) AS `year_name`,`rdb_project_work`.`pro_id` AS `pro_id`,`rdb_project`.`pt_id` AS `pt_id`,`rdb_project_types`.`pt_name` AS `pt_name`,`rdb_project_work`.`researcher_id` AS `researcher_id`,`rdb_researcher`.`department_id` AS `department_id`,(select `rdb_department`.`department_nameTH` from `rdb_department` where (`rdb_department`.`department_id` = `rdb_researcher`.`department_id`)) AS `department_nameTH`,`rdb_researcher`.`maj_id` AS `maj_id`,(select `rdb_dep_major`.`maj_nameTH` from `rdb_dep_major` where (`rdb_dep_major`.`maj_id` = `rdb_researcher`.`maj_id`)) AS `maj_nameTH`,`rdb_researcher`.`depcou_id` AS `depcou_id`,(select `rdb_department_course`.`cou_name` from `rdb_department_course` where (`rdb_department_course`.`depcou_id` = `rdb_researcher`.`depcou_id`)) AS `cou_name`,`rdb_project_types`.`pt_for` AS `pt_for` from (((`rdb_project_work` left join `rdb_project` on((`rdb_project_work`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_researcher` on((`rdb_project_work`.`researcher_id` = `rdb_researcher`.`researcher_id`))) join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_work`.`position_id` in (1,2)) and ((select count(`a`.`pro_id`) from `rdb_project_work` `a` where ((`a`.`researcher_id` = `rdb_project_work`.`researcher_id`) and (`a`.`pro_id` <= `rdb_project_work`.`pro_id`))) = 1) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_for` = 1)) order by `rdb_project_work`.`pro_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `research_frist_project`
--

/*!50001 DROP VIEW IF EXISTS `research_frist_project`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `research_frist_project` AS select `view_researcher_frist_project`.`year_id` AS `year_id`,`view_researcher_frist_project`.`year_name` AS `year_name`,`view_researcher_frist_project`.`department_id` AS `department_id`,`view_researcher_frist_project`.`department_nameTH` AS `department_nameTH`,`view_researcher_frist_project`.`depcou_id` AS `depcou_id`,`view_researcher_frist_project`.`cou_name` AS `cou_name`,`view_researcher_frist_project`.`maj_id` AS `maj_id`,`view_researcher_frist_project`.`maj_nameTH` AS `maj_nameTH`,`view_researcher_frist_project`.`pt_id` AS `pt_id`,`view_researcher_frist_project`.`pt_name` AS `pt_name`,(select count(`a`.`pro_id`) from `view_researcher_frist_project` `a` where ((`a`.`year_id` = `view_researcher_frist_project`.`year_id`) and (`a`.`maj_id` = `view_researcher_frist_project`.`maj_id`) and (`a`.`pt_id` = `view_researcher_frist_project`.`pt_id`))) AS `conut_frist_project` from (`view_researcher_frist_project` join `rdb_year` on((`rdb_year`.`year_id` = `view_researcher_frist_project`.`year_id`))) group by `view_researcher_frist_project`.`year_name`,`view_researcher_frist_project`.`maj_id`,`view_researcher_frist_project`.`pt_id` order by `view_researcher_frist_project`.`year_name` desc,`view_researcher_frist_project`.`department_nameTH` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_respon_group`
--

/*!50001 DROP VIEW IF EXISTS `view_respon_group`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_respon_group` AS select `rpj`.`pro_group` AS `pro_group`,group_concat(concat(convert(ifnull(`rr`.`researcher_fname`,'--') using utf8mb4),' ',convert(ifnull(`rr`.`researcher_lname`,'--') using utf8mb4)) order by `rpw`.`position_id` ASC separator '|') AS `respon_name` from ((`rdb_project_work` `rpw` left join `rdb_researcher` `rr` on((`rpw`.`researcher_id` = `rr`.`researcher_id`))) left join `rdb_project` `rpj` on((`rpw`.`pro_id` = `rpj`.`pro_id`))) where (`rpj`.`pro_group` is not null) group by `rpw`.`pro_id` order by `rpw`.`pro_id`,`rpw`.`position_id`,`rpw`.`ratio` desc,`rpw`.`position_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_respon_group_all`
--

/*!50001 DROP VIEW IF EXISTS `view_respon_group_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_respon_group_all` AS select `view_respon_group`.`pro_group` AS `pro_group`,group_concat(convert(ifnull(`view_respon_group`.`respon_name`,'--') using utf8mb4) separator ';') AS `respon_name` from `view_respon_group` group by `view_respon_group`.`pro_group` order by `view_respon_group`.`pro_group` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_tnrr`
--

/*!50001 DROP VIEW IF EXISTS `view_tnrr`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_tnrr` AS select `view_project_all`.`pro_id` AS `pro_id`,lpad(`view_project_all`.`pro_id`,8,'10000000') AS `newid`,`view_project_all`.`year_name` AS `year_name`,`view_project_all`.`pro_nameTH` AS `pro_nameTH`,`view_project_all`.`pro_nameEN` AS `pro_nameEN`,`view_project_all`.`department_nameTH` AS `department_nameTH`,`view_project_all`.`pro_abstract` AS `pro_abstract`,`view_project_all`.`pro_budget` AS `pro_budget`,`view_project_all`.`pro_keyword` AS `pro_keyword`,`view_project_all`.`prefixnameTH` AS `prefixnameTH`,`view_project_all`.`respon_name` AS `respon_name`,`view_project_all`.`departmentnameTH` AS `departmentnameTH`,`view_project_all`.`positionnameTH` AS `positionnameTH` from `view_project_all` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize` AS select `rdb_year`.`year_id` AS `utz_year_id`,`rdb_year`.`year_name` AS `year_name`,`rdb_project_utilize_type`.`utz_type_id` AS `utz_type_id`,`rdb_project_utilize_type`.`utz_typr_name` AS `utz_typr_name`,`rdb_dep_major`.`department_id` AS `department_id`,`rdb_dep_major`.`depcou_id` AS `depcou_id`,`rdb_dep_major`.`maj_id` AS `maj_id`,(select count(distinct `rdb_project_utilize`.`pro_id`) from (`rdb_project_utilize` join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) where ((`rdb_project_utilize`.`utz_year_id` = `rdb_year`.`year_id`) and (`rdb_project`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_project_utilize`.`data_show` = 1) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutzpro`,(select count(`rdb_project_utilize`.`utz_id`) from (`rdb_project_utilize` join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) where ((`rdb_project_utilize`.`utz_year_id` = `rdb_year`.`year_id`) and (`rdb_project`.`major_id` = `rdb_dep_major`.`maj_id`) and (`rdb_project_utilize`.`data_show` = 1) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutz` from ((`rdb_project_utilize_type` join `rdb_dep_major`) join `rdb_year`) having (`countutzpro` > 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize_bud`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize_bud`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize_bud` AS select `rdb_year`.`year_id` AS `utz_year_bud`,`rdb_year`.`year_name` AS `year_name`,`rdb_project_utilize_type`.`utz_type_id` AS `utz_type_id`,`rdb_project_utilize_type`.`utz_typr_name` AS `utz_typr_name`,`rdb_department`.`department_id` AS `department_id`,(select count(distinct `rdb_project_utilize`.`pro_id`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_bud` = `rdb_year`.`year_id`) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`department_id` = `rdb_department`.`department_id`) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutzpro`,(select count(`rdb_project_utilize`.`utz_id`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_bud` = `rdb_year`.`year_id`) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`department_id` = `rdb_department`.`department_id`) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutz` from ((`rdb_project_utilize_type` join `rdb_department`) join `rdb_year`) having (`countutzpro` > 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize_edu`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize_edu`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize_edu` AS select `rdb_year`.`year_id` AS `utz_year_edu`,`rdb_year`.`year_name` AS `year_name`,`rdb_project_utilize_type`.`utz_type_id` AS `utz_type_id`,`rdb_project_utilize_type`.`utz_typr_name` AS `utz_typr_name`,`rdb_department`.`department_id` AS `department_id`,(select count(distinct `rdb_project_utilize`.`pro_id`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `rdb_year`.`year_id`) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`department_id` = `rdb_department`.`department_id`) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutzpro`,(select count(`rdb_project_utilize`.`utz_id`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `rdb_year`.`year_id`) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`department_id` = `rdb_department`.`department_id`) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_created` = 1) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutz` from ((`rdb_project_utilize_type` join `rdb_department`) join `rdb_year`) having (`countutzpro` > 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize_edu_qa`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize_edu_qa`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize_edu_qa` AS select `rdb_year`.`year_id` AS `utz_year_edu`,`rdb_year`.`year_name` AS `year_name`,`rdb_project_utilize_type`.`utz_type_id` AS `utz_type_id`,`rdb_project_utilize_type`.`utz_typr_name` AS `utz_typr_name`,`rdb_department`.`department_id` AS `department_id`,(select count(`rdb_project_utilize`.`pro_id`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `rdb_year`.`year_id`) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project_utilize`.`utz_group_qa` = 1) and (`rdb_project`.`department_id` = `rdb_department`.`department_id`) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutzpro`,(select count(distinct `rdb_project_utilize`.`utz_department_name`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `rdb_year`.`year_id`) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project_utilize`.`utz_group_qa` = 1) and (`rdb_project`.`department_id` = `rdb_department`.`department_id`) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutz`,(select count(distinct `rdb_project_utilize`.`utz_department_name`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `rdb_year`.`year_id`) and (`rdb_project_utilize`.`utz_group_qa` = 1) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`pgroup_id` in (1,3)) and `rdb_project`.`department_id` in (select `rdb_department`.`department_id` from `rdb_department` where (`rdb_department`.`tdepartment_id` = 1)) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1))) AS `countdep` from ((`rdb_project_utilize_type` join `rdb_department`) join `rdb_year`) having (`countutzpro` > 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize_groupall`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize_groupall`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize_groupall` AS select `rdb_project_utilize`.`utz_id` AS `utz_id`,`rdb_project_utilize`.`utz_group` AS `utz_group`,group_concat(`rdb_project_utilize_type`.`utz_typr_name` separator ', ') AS `namegroupall` from (`rdb_project_utilize` join `rdb_project_utilize_type` on((0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) group by `rdb_project_utilize`.`utz_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize_all`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize_all` AS select `rdb_project_utilize`.`utz_id` AS `utz_id`,`rdb_project_utilize`.`pro_id` AS `pro_id`,`rdb_project_utilize`.`utz_year_id` AS `utz_year_id`,`yutz`.`year_name` AS `utz_year_name`,`rdb_project_utilize`.`utz_year_bud` AS `utz_year_bud`,`ybud`.`year_name` AS `bud_year_name`,`rdb_project_utilize`.`utz_year_edu` AS `utz_year_edu`,`yedu`.`year_name` AS `edu_year_name`,`rdb_project_utilize`.`utz_date` AS `utz_date`,`rdb_project_utilize`.`utz_department_name` AS `utz_department_name`,`rdb_project_utilize`.`chw_id` AS `chw_id`,`rdb_project_utilize`.`utz_group` AS `utz_group`,`view_utilize_groupall`.`namegroupall` AS `namegroupall`,`rdb_project_utilize`.`utz_files` AS `utz_files`,`rdb_project`.`year_id` AS `year_id`,`ypro`.`year_name` AS `pro_year_name`,`rdb_project`.`pt_id` AS `pt_id`,`rdb_project`.`pro_nameTH` AS `pro_nameTH`,`rdb_project`.`department_id` AS `department_id`,`rdb_department`.`department_nameTH` AS `pro_department_nameTH`,`rdb_project`.`pgroup_id` AS `pgroup_id`,`rdb_project_types`.`pt_for` AS `pt_for`,`rdb_project_types`.`pt_created` AS `pt_created`,`rdb_project_types`.`pt_type` AS `pt_type`,`rdb_project_types`.`pt_utz` AS `pt_utz`,`view_project_work_all`.`respon_name` AS `respon_name`,`view_project_work_all`.`departmentnameTH` AS `departmentnameTH`,`view_project_work_all`.`positionnameTH` AS `positionnameTH`,`rdb_changwat`.`ta_id` AS `ta_id`,`rdb_changwat`.`tambon_t` AS `tambon_t`,`rdb_changwat`.`am_id` AS `am_id`,`rdb_changwat`.`amphoe_t` AS `amphoe_t`,`rdb_changwat`.`ch_id` AS `ch_id`,`rdb_changwat`.`changwat_t` AS `changwat_t`,`rdb_changwat`.`lat` AS `lat`,`rdb_changwat`.`long` AS `long`,`rdb_project_utilize`.`utz_leading` AS `utz_leading`,`rdb_project_utilize`.`utz_leading_position` AS `utz_leading_position`,`rdb_project_utilize`.`utz_group_qa` AS `utz_group_qa`,`rdb_project_utilize`.`utz_detail` AS `utz_detail`,`rdb_project_utilize`.`utz_budget` AS `utz_budget`,`rdb_project_utilize`.`data_show` AS `data_show` from ((((((((((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_year` `yutz` on((`rdb_project_utilize`.`utz_year_id` = `yutz`.`year_id`))) left join `rdb_department` on((`rdb_project`.`department_id` = `rdb_department`.`department_id`))) left join `view_utilize_groupall` on((`rdb_project_utilize`.`utz_id` = `view_utilize_groupall`.`utz_id`))) left join `rdb_changwat` on((`rdb_project_utilize`.`chw_id` = `rdb_changwat`.`id`))) left join `view_project_work_all` on((`rdb_project_utilize`.`pro_id` = `view_project_work_all`.`pro_id`))) left join `rdb_year` `ybud` on((`rdb_project_utilize`.`utz_year_bud` = `ybud`.`year_id`))) left join `rdb_year` `yedu` on((`rdb_project_utilize`.`utz_year_edu` = `yedu`.`year_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) left join `rdb_year` `ypro` on((`rdb_project`.`year_id` = `ypro`.`year_id`))) where ((`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize_all_latlong`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize_all_latlong`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize_all_latlong` AS select `rdb_project_utilize`.`utz_id` AS `utz_id`,`rdb_project_utilize`.`pro_id` AS `pro_id`,`rdb_project_utilize`.`utz_year_id` AS `utz_year_id`,`y`.`year_name` AS `year_id_name`,`rdb_project_utilize`.`utz_year_bud` AS `utz_year_bud`,`yb`.`year_name` AS `year_bud_name`,`rdb_project_utilize`.`utz_date` AS `utz_date`,`rdb_project_utilize`.`utz_leading` AS `utz_leading`,`rdb_project_utilize`.`utz_leading_position` AS `utz_leading_position`,`rdb_project_utilize`.`utz_department_name` AS `utz_department_name`,`rdb_project_utilize`.`chw_id` AS `chw_id`,`rdb_changwat`.`ta_id` AS `ta_id`,`rdb_changwat`.`tambon_t` AS `tambon_t`,`rdb_changwat`.`am_id` AS `am_id`,`rdb_changwat`.`amphoe_t` AS `amphoe_t`,`rdb_changwat`.`ch_id` AS `ch_id`,`rdb_changwat`.`changwat_t` AS `changwat_t`,`rdb_changwat`.`lat` AS `lat`,`rdb_changwat`.`long` AS `long`,`rdb_project_utilize`.`utz_group` AS `utz_group`,`view_utilize_groupall`.`namegroupall` AS `namegroupall`,`rdb_project_utilize`.`utz_detail` AS `utz_detail`,`rdb_project_utilize`.`utz_files` AS `utz_files`,`rdb_project_utilize`.`data_show` AS `data_show` from ((((`rdb_project_utilize` left join `view_utilize_groupall` on((`rdb_project_utilize`.`utz_id` = `view_utilize_groupall`.`utz_id`))) left join `rdb_year` `y` on((`rdb_project_utilize`.`utz_year_id` = `y`.`year_id`))) left join `rdb_year` `yb` on((`rdb_project_utilize`.`utz_year_bud` = `yb`.`year_id`))) left join `rdb_changwat` on((`rdb_project_utilize`.`chw_id` = `rdb_changwat`.`id`))) where (`rdb_project_utilize`.`data_show` = 1) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize_pro_year`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize_pro_year`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize_pro_year` AS select `rdb_year`.`year_id` AS `year_id`,`rdb_year`.`year_name` AS `year_name`,`rdb_department`.`department_id` AS `department_id`,`rdb_department`.`department_nameTH` AS `department_nameTH`,`rdb_project_types`.`pt_id` AS `pt_id`,`rdb_project_types`.`pt_name` AS `pt_name`,`rdb_project_utilize_type`.`utz_type_id` AS `utz_type_id`,`rdb_project_utilize_type`.`utz_typr_name` AS `utz_typr_name`,(select count(distinct `rdb_project_utilize`.`pro_id`) from (`rdb_project_utilize` join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) where ((`rdb_project`.`year_id` = `rdb_year`.`year_id`) and (`rdb_project`.`department_id` = `rdb_department`.`department_id`) and (`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutzpro`,(select count(`rdb_project_utilize`.`utz_id`) from (`rdb_project_utilize` join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) where ((`rdb_project`.`year_id` = `rdb_year`.`year_id`) and (`rdb_project`.`department_id` = `rdb_department`.`department_id`) and (`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`) and (0 <> find_in_set(`rdb_project_utilize_type`.`utz_type_id`,`rdb_project_utilize`.`utz_group`)))) AS `countutz` from (((`rdb_year` join `rdb_project_types` on((`rdb_project_types`.`year_id` = `rdb_year`.`year_id`))) join `rdb_department`) join `rdb_project_utilize_type`) having (`countutzpro` > 0) order by `rdb_year`.`year_name` desc,`rdb_department`.`department_nameTH` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize_report`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize_report`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize_report` AS select `rdb_project_utilize`.`utz_id` AS `utz_id`,`rdb_project_utilize`.`pro_id` AS `pro_id`,`rdb_project_utilize`.`utz_year_id` AS `utz_year_id`,`rdb_project_utilize`.`utz_year_bud` AS `utz_year_bud`,`rdb_project_utilize`.`utz_year_edu` AS `utz_year_edu`,`rdb_project_utilize`.`utz_date` AS `utz_date`,`rdb_project_utilize`.`utz_department_name` AS `utz_department_name`,`rdb_project_utilize`.`chw_id` AS `chw_id`,`rdb_project_utilize`.`utz_group` AS `utz_group`,`view_utilize_groupall`.`namegroupall` AS `namegroupall`,`rdb_project_utilize`.`utz_files` AS `utz_files`,`rdb_project`.`year_id` AS `year_id`,`rdb_project`.`pt_id` AS `pt_id`,`rdb_project_types`.`pt_name` AS `pt_name`,`rdb_project_types_sub`.`pts_name` AS `pts_name`,`rdb_project`.`pro_nameTH` AS `pro_nameTH`,`rdb_project`.`department_id` AS `department_id`,`rdb_year`.`year_name` AS `year_name`,`rdb_department`.`department_nameTH` AS `department_nameTH`,`rdb_changwat`.`ta_id` AS `ta_id`,`rdb_changwat`.`tambon_t` AS `tambon_t`,`rdb_changwat`.`am_id` AS `am_id`,`rdb_changwat`.`amphoe_t` AS `amphoe_t`,`rdb_changwat`.`ch_id` AS `ch_id`,`rdb_changwat`.`changwat_t` AS `changwat_t`,`rdb_changwat`.`lat` AS `lat`,`rdb_changwat`.`long` AS `long`,`rdb_project_utilize`.`utz_leading` AS `utz_leading`,`rdb_project_utilize`.`utz_leading_position` AS `utz_leading_position`,`rdb_project_utilize`.`utz_group_qa` AS `utz_group_qa`,`rdb_project_utilize`.`utz_detail` AS `utz_detail`,`rdb_project_utilize`.`utz_budget` AS `utz_budget`,`rdb_project_utilize`.`data_show` AS `data_show`,`view_project_work_all`.`prefixnameTH` AS `prefixnameTH`,`view_project_work_all`.`respon_name` AS `respon_name`,`view_project_work_all`.`departmentnameTH` AS `departmentnameTH`,`view_project_work_all`.`positionnameTH` AS `positionnameTH` from ((((((((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_year` on((`rdb_project_utilize`.`utz_year_id` = `rdb_year`.`year_id`))) left join `rdb_department` on((`rdb_project`.`department_id` = `rdb_department`.`department_id`))) left join `view_utilize_groupall` on((`rdb_project_utilize`.`utz_id` = `view_utilize_groupall`.`utz_id`))) left join `rdb_changwat` on((`rdb_project_utilize`.`chw_id` = `rdb_changwat`.`id`))) left join `view_project_work_all` on((`rdb_project_utilize`.`pro_id` = `view_project_work_all`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) left join `rdb_project_types_sub` on((`rdb_project_types_sub`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1)) group by `rdb_project_utilize`.`utz_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utilize_year`
--

/*!50001 DROP VIEW IF EXISTS `view_utilize_year`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utilize_year` AS select `rdb_project_utilize`.`utz_year_id` AS `utz_year_id` from `rdb_project_utilize` where (`rdb_project_utilize`.`utz_year_id` <> '') group by `rdb_project_utilize`.`utz_year_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utz`
--

/*!50001 DROP VIEW IF EXISTS `view_utz`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utz` AS select `y`.`year_id` AS `year_id`,`y`.`year_name` AS `year_name`,`d`.`department_id` AS `department_id`,`d`.`department_nameTH` AS `department_nameTH`,(select count(`rdb_project_utilize`.`utz_year_bud`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_bud` = `y`.`year_id`) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`department_id` = `d`.`department_id`) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1))) AS `cou_utz`,(select count(`rdb_project`.`pro_id`) from ((`rdb_project` left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) left join `rdb_project_types_sub` on((`rdb_project`.`pts_id` = `rdb_project_types_sub`.`pts_id`))) where ((`rdb_project`.`year_id` = `y`.`year_id`) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project`.`department_id` = `d`.`department_id`) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6))) AS `cou_pro` from (`rdb_year` `y` join `rdb_department` `d`) where (`d`.`tdepartment_id` = 1) order by `y`.`year_name` desc,`d`.`department_nameTH` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utz_bud_plan`
--

/*!50001 DROP VIEW IF EXISTS `view_utz_bud_plan`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utz_bud_plan` AS select `view_utilize_all`.`pro_year_name` AS `pro_year_name`,`view_utilize_all`.`pro_nameTH` AS `pro_nameTH`,`view_utilize_all`.`respon_name` AS `respon_name`,`view_utilize_all`.`departmentnameTH` AS `departmentnameTH`,`view_utilize_all`.`utz_date` AS `utz_date`,`view_utilize_all`.`namegroupall` AS `namegroupall`,`view_utilize_all`.`utz_department_name` AS `utz_department_name`,`view_utilize_all`.`tambon_t` AS `tambon_t`,`view_utilize_all`.`amphoe_t` AS `amphoe_t`,`view_utilize_all`.`changwat_t` AS `changwat_t`,`view_utilize_all`.`utz_leading` AS `utz_leading`,`view_utilize_all`.`utz_leading_position` AS `utz_leading_position`,`view_utilize_all`.`utz_detail` AS `utz_detail`,`view_utilize_all`.`utz_id` AS `utz_id`,`view_utilize_all`.`pro_id` AS `pro_id`,`view_utilize_all`.`pt_utz` AS `pt_utz`,`view_utilize_all`.`utz_year_id` AS `utz_year_id`,`view_utilize_all`.`utz_year_name` AS `utz_year_name`,`view_utilize_all`.`utz_year_bud` AS `utz_year_bud`,`view_utilize_all`.`bud_year_name` AS `bud_year_name`,`view_utilize_all`.`utz_year_edu` AS `utz_year_edu`,`view_utilize_all`.`edu_year_name` AS `edu_year_name`,`view_utilize_all`.`chw_id` AS `chw_id`,`view_utilize_all`.`utz_group` AS `utz_group`,`view_utilize_all`.`utz_files` AS `utz_files`,`view_utilize_all`.`year_id` AS `year_id`,`view_utilize_all`.`pt_id` AS `pt_id`,`view_utilize_all`.`pgroup_id` AS `pgroup_id`,`view_utilize_all`.`pt_for` AS `pt_for`,`view_utilize_all`.`pt_created` AS `pt_created`,`view_utilize_all`.`pt_type` AS `pt_type`,`view_utilize_all`.`positionnameTH` AS `positionnameTH`,`view_utilize_all`.`ta_id` AS `ta_id`,`view_utilize_all`.`am_id` AS `am_id`,`view_utilize_all`.`ch_id` AS `ch_id`,`view_utilize_all`.`lat` AS `lat`,`view_utilize_all`.`long` AS `long`,`view_utilize_all`.`utz_group_qa` AS `utz_group_qa`,`view_utilize_all`.`utz_budget` AS `utz_budget`,`view_utilize_all`.`data_show` AS `data_show` from `view_utilize_all` where ((`view_utilize_all`.`pro_year_name` = 2566) and (`view_utilize_all`.`pgroup_id` <> 2) and (`view_utilize_all`.`pt_for` = 1) and (`view_utilize_all`.`pt_created` = 1) and (`view_utilize_all`.`data_show` = 1)) group by `view_utilize_all`.`pro_id` order by `view_utilize_all`.`utz_date` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utz_edu`
--

/*!50001 DROP VIEW IF EXISTS `view_utz_edu`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utz_edu` AS select `y`.`year_id` AS `year_id`,`y`.`year_name` AS `year_name`,`d`.`department_id` AS `department_id`,`d`.`department_nameTH` AS `department_nameTH`,(select count(`rdb_project_utilize`.`utz_year_edu`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `y`.`year_id`) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`department_id` = `d`.`department_id`) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1))) AS `cou_utzall`,(select count(`rdb_project_utilize`.`utz_year_edu`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `y`.`year_id`) and (`rdb_project_utilize`.`utz_group_qa` = 1) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`department_id` = `d`.`department_id`) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1))) AS `cou_utz`,(select count(`rdb_project`.`pro_id`) from ((`rdb_project` left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) left join `rdb_project_types_sub` on((`rdb_project`.`pts_id` = `rdb_project_types_sub`.`pts_id`))) where ((`rdb_project`.`year_id` = (`y`.`year_id` + 1)) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1) and (`rdb_project`.`department_id` = `d`.`department_id`) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6))) AS `cou_pro` from (`rdb_year` `y` join `rdb_department` `d`) where (`d`.`tdepartment_id` = 1) having (`cou_pro` <> 0) order by `y`.`year_name` desc,`d`.`department_nameTH` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_utz_edu_qa`
--

/*!50001 DROP VIEW IF EXISTS `view_utz_edu_qa`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_utz_edu_qa` AS select `y`.`year_id` AS `year_id`,`y`.`year_name` AS `year_name`,`d`.`department_id` AS `department_id`,`d`.`department_nameTH` AS `department_nameTH`,(select count(`rdb_project_utilize`.`utz_year_edu`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `y`.`year_id`) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`department_id` = `d`.`department_id`) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1))) AS `cou_utzall`,(select count(`rdb_project_utilize`.`utz_year_edu`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `y`.`year_id`) and (`rdb_project_utilize`.`utz_group_qa` = 1) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`department_id` = `d`.`department_id`) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1))) AS `cou_utz`,(select count(`rdb_project`.`pro_id`) from ((`rdb_project` left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) left join `rdb_project_types_sub` on((`rdb_project`.`pts_id` = `rdb_project_types_sub`.`pts_id`))) where ((`rdb_project`.`year_id` = (`y`.`year_id` + 1)) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1) and (`rdb_project`.`department_id` = `d`.`department_id`) and (`rdb_project`.`pgroup_id` in (1,3)) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6))) AS `cou_pro`,(select count(distinct `rdb_project_utilize`.`utz_department_name`) from ((`rdb_project_utilize` left join `rdb_project` on((`rdb_project_utilize`.`pro_id` = `rdb_project`.`pro_id`))) left join `rdb_project_types` on((`rdb_project`.`pt_id` = `rdb_project_types`.`pt_id`))) where ((`rdb_project_utilize`.`utz_year_edu` = `y`.`year_id`) and (`rdb_project_utilize`.`utz_group_qa` = 1) and (`rdb_project_utilize`.`data_show` = 1) and (`rdb_project`.`pgroup_id` in (1,3)) and `rdb_project`.`department_id` in (select `rdb_department`.`department_id` from `rdb_department` where (`rdb_department`.`tdepartment_id` = 1)) and (`rdb_project`.`data_show` = 1) and (`rdb_project`.`ps_id` <> 6) and (`rdb_project_types`.`pt_type` = 1) and (`rdb_project_types`.`pt_utz` = 1))) AS `cou_dep` from (`rdb_year` `y` join `rdb_department` `d`) where (`d`.`tdepartment_id` = 1) having (`cou_pro` <> 0) order by `y`.`year_name` desc,`d`.`department_nameTH` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_year`
--

/*!50001 DROP VIEW IF EXISTS `view_year`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_year` AS select `rdb_project`.`year_id` AS `year_id`,`rdb_year`.`year_name` AS `year_name` from (`rdb_project` left join `rdb_year` on((`rdb_project`.`year_id` = `rdb_year`.`year_id`))) group by `rdb_project`.`year_id` order by `rdb_project`.`year_id` desc limit 4 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_researcher_all`
--

/*!50001 DROP VIEW IF EXISTS `view_researcher_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_researcher_all` AS select `rdb_year`.`year_id` AS `year_id`,`rdb_year`.`year_name` AS `year_name`,`rdb_department`.`department_id` AS `department_id`,`rdb_department`.`department_nameTH` AS `department_nameTH`,`rdb_project_types`.`pt_id` AS `pt_id`,`rdb_project_types`.`pt_name` AS `pt_name`,(select count(`view_researcher`.`researcher_id`) from `view_researcher` where ((`view_researcher`.`year_id` = `rdb_year`.`year_id`) and (`view_researcher`.`department_id` = `rdb_department`.`department_id`) and (`view_researcher`.`pt_id` = `rdb_project_types`.`pt_id`) and (`view_researcher`.`pgroup_id` in (1,3)) and (`view_researcher`.`position_id` in (1,2)))) AS `count_researcher` from ((`rdb_year` join `rdb_department`) join `rdb_project_types`) where ((`rdb_department`.`tdepartment_id` = 1) and `rdb_year`.`year_id` in (select `view_year`.`year_id` from `view_year`)) having (`count_researcher` <> 0) order by `rdb_year`.`year_name` desc,`rdb_department`.`department_nameTH` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_zcount_coferenceinthai`
--

/*!50001 DROP VIEW IF EXISTS `view_zcount_coferenceinthai`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_zcount_coferenceinthai` AS select (date_format(`research_coferenceinthai`.`created_at`,_utf8mb3'%Y') + 543) AS `fyear`,date_format(`research_coferenceinthai`.`created_at`,_utf8mb3'%Y-%m') AS `ym`,count(`research_coferenceinthai`.`id`) AS `cnews`,sum(`research_coferenceinthai`.`con_count`) AS `ccount` from `research_coferenceinthai` group by date_format(`research_coferenceinthai`.`created_at`,_utf8mb3'%Y-%m') order by `research_coferenceinthai`.`created_at` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_zcount_news`
--

/*!50001 DROP VIEW IF EXISTS `view_zcount_news`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_zcount_news` AS select (date_format(`research_news`.`created_at`,_utf8mb3'%Y') + 543) AS `fyear`,date_format(`research_news`.`created_at`,_utf8mb3'%Y-%m') AS `ym`,count(`research_news`.`id`) AS `cnews`,sum(`research_news`.`news_count`) AS `ccount` from `research_news` group by date_format(`research_news`.`created_at`,_utf8mb3'%Y-%m') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_zcount_rdbdownloas`
--

/*!50001 DROP VIEW IF EXISTS `view_zcount_rdbdownloas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_zcount_rdbdownloas` AS select (date_format(`rdb_project_download`.`pjdo_date`,_utf8mb3'%Y') + 543) AS `fyear`,date_format(`rdb_project_download`.`pjdo_date`,_utf8mb3'%Y-%m') AS `ym`,count(`rdb_project_download`.`pjdo_id`) AS `ccount` from `rdb_project_download` group by date_format(`rdb_project_download`.`pjdo_date`,_utf8mb3'%Y-%m') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `viewutzeduqadata`
--

/*!50001 DROP VIEW IF EXISTS `viewutzeduqadata`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `viewutzeduqadata` AS select `a`.`utz_id` AS `utz_id`,`b`.`department_nameTH` AS `department_nameTH`,`b`.`year_name` AS `year_name`,`b`.`pro_nameTH` AS `pro_nameTH`,`b`.`pgroup_id` AS `pgroup_id`,`b`.`pt_id` AS `pt_id`,`b`.`pt_name` AS `pt_name`,`b`.`prefixnameTH` AS `prefixnameTH`,`b`.`respon_name` AS `respon_name`,`b`.`data_show` AS `data_show`,`a`.`utz_department_name` AS `utz_department_name`,`a`.`utz_detail` AS `utz_detail`,`a`.`utz_leading` AS `utz_leading`,`a`.`utz_leading_position` AS `utz_leading_position`,`a`.`utz_date` AS `utz_date`,`a`.`utz_year_edu` AS `utz_year_edu`,`a`.`utz_group_qa` AS `utz_group_qa`,`a`.`chw_id` AS `chw_id`,`rdb_changwat`.`tambon_t` AS `tambon_t`,`rdb_changwat`.`amphoe_t` AS `amphoe_t`,`rdb_changwat`.`changwat_t` AS `changwat_t` from ((`rdb_project_utilize` `a` join `view_project_all` `b` on((`a`.`pro_id` = `b`.`pro_id`))) join `rdb_changwat` on((`a`.`chw_id` = `rdb_changwat`.`id`))) where ((`a`.`utz_year_edu` = 16) and (`a`.`utz_group_qa` = 1) and (`b`.`pgroup_id` in (1,3)) and (`a`.`data_show` = 1) and (`b`.`data_show` = 1) and (`b`.`pt_utz` <> 0)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-11 23:56:25
