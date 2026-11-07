-- MariaDB dump 10.19  Distrib 10.11.16-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: loan_export
-- ------------------------------------------------------
-- Server version	10.11.16-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_activity_logs`
--

DROP TABLE IF EXISTS `admin_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_activity_logs_admin_id_foreign` (`admin_id`),
  KEY `admin_activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `admin_activity_logs_action_index` (`action`),
  KEY `admin_activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `admin_activity_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_activity_logs`
--

/*!40000 ALTER TABLE `admin_activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_activity_logs` ENABLE KEYS */;

--
-- Table structure for table `admin_permissions`
--

DROP TABLE IF EXISTS `admin_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_permissions_slug_unique` (`slug`),
  KEY `admin_permissions_group_index` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_permissions`
--

/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES
(1,'View dashboard','dashboard.view','dashboard','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(2,'View customers','users.view','users','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(3,'Edit / block customers','users.manage','users','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(4,'Verify NID documents','kyc.review','users','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(5,'View applications & loans','loans.view','loans','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(6,'Review applications / request documents','loans.review','loans','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(7,'Approve / reject applications','loans.approve','loans','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(8,'Create loan agreements','loans.agreement','loans','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(9,'Record disbursements','loans.disburse','loans','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(10,'Record manual repayments','loans.repayments','loans','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(11,'Manage loan packages & durations','products.manage','loans','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(12,'View payments','payments.view','payments','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(13,'Re-verify payments with gateway','payments.verify','payments','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(14,'Refund payments','payments.refund','payments','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(15,'Export payment reports','payments.export','payments','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(16,'Loan settings','settings.loan','content','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(17,'Website customization & SEO','settings.website','content','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(18,'Manage reviews','reviews.manage','content','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(19,'Manage FAQ & announcements','content.manage','content','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(20,'Handle support tickets','tickets.manage','content','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(21,'Manage admin users & roles','admins.manage','system','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(22,'View activity & login logs','logs.view','system','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(23,'Database backups','backups.manage','system','2026-11-07 12:36:51','2026-11-07 12:36:51'),
(24,'Maintenance mode & system settings','system.manage','system','2026-11-07 12:36:51','2026-11-07 12:36:51');
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;

--
-- Table structure for table `admin_role_permission`
--

DROP TABLE IF EXISTS `admin_role_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_role_permission` (
  `admin_role_id` bigint(20) unsigned NOT NULL,
  `admin_permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`admin_role_id`,`admin_permission_id`),
  KEY `admin_role_permission_admin_permission_id_foreign` (`admin_permission_id`),
  CONSTRAINT `admin_role_permission_admin_permission_id_foreign` FOREIGN KEY (`admin_permission_id`) REFERENCES `admin_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admin_role_permission_admin_role_id_foreign` FOREIGN KEY (`admin_role_id`) REFERENCES `admin_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_role_permission`
--

/*!40000 ALTER TABLE `admin_role_permission` DISABLE KEYS */;
INSERT INTO `admin_role_permission` VALUES
(1,1),
(1,2),
(1,3),
(1,4),
(1,5),
(1,6),
(1,7),
(1,8),
(1,9),
(1,10),
(1,11),
(1,12),
(1,13),
(1,14),
(1,15),
(1,16),
(1,17),
(1,18),
(1,19),
(1,20),
(1,21),
(1,22),
(1,23),
(1,24),
(2,1),
(2,2),
(2,3),
(2,4),
(2,5),
(2,6),
(2,7),
(2,8),
(2,9),
(2,10),
(2,11),
(2,12),
(2,13),
(2,14),
(2,15),
(2,16),
(2,17),
(2,18),
(2,19),
(2,20),
(2,22),
(3,1),
(3,2),
(3,4),
(3,5),
(3,6),
(3,7),
(3,8),
(3,20),
(4,1),
(4,2),
(4,5),
(4,9),
(4,10),
(4,12),
(4,13),
(4,14),
(4,15),
(5,1),
(5,2),
(5,5),
(5,12),
(5,18),
(5,19),
(5,20);
/*!40000 ALTER TABLE `admin_role_permission` ENABLE KEYS */;

--
-- Table structure for table `admin_roles`
--

DROP TABLE IF EXISTS `admin_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_roles`
--

/*!40000 ALTER TABLE `admin_roles` DISABLE KEYS */;
INSERT INTO `admin_roles` VALUES
(1,'Super Admin','super_admin',NULL,'2026-11-07 12:36:51','2026-11-07 12:36:51'),
(2,'Manager','manager',NULL,'2026-11-07 12:36:51','2026-11-07 12:36:51'),
(3,'Loan Officer','loan_officer',NULL,'2026-11-07 12:36:51','2026-11-07 12:36:51'),
(4,'Payment Manager','payment_manager',NULL,'2026-11-07 12:36:51','2026-11-07 12:36:51'),
(5,'Support Agent','support_agent',NULL,'2026-11-07 12:36:52','2026-11-07 12:36:52');
/*!40000 ALTER TABLE `admin_roles` ENABLE KEYS */;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'info',
  `audience` varchar(20) NOT NULL DEFAULT 'all',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `question_bn` varchar(255) DEFAULT NULL,
  `answer` text NOT NULL,
  `answer_bn` text DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES
(1,'Who can apply?','কে আবেদন করতে পারবেন?','Bangladeshi citizens aged 18+ with a valid NID, an active mobile number and a regular income.','১৮ বছর বা তার বেশি বয়সী বাংলাদেশি নাগরিক, যাঁদের বৈধ এনআইডি, সক্রিয় মোবাইল নম্বর ও নিয়মিত আয় আছে।',0,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(2,'Which documents do I need?','কী কী কাগজ লাগবে?','Clear photos of the front and back of your NID. A selfie may be requested.','আপনার এনআইডির সামনের ও পেছনের স্পষ্ট ছবি। প্রয়োজনে একটি সেলফি চাওয়া হতে পারে।',1,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(3,'Do I have to pay anything to apply?','আবেদন করতে কি কোনো টাকা দিতে হয়?','No. Applying is free. Any processing fee is shown in the agreement before you accept, and it is never required before approval.','না। আবেদন সম্পূর্ণ বিনামূল্যে। কোনো প্রসেসিং ফি থাকলে তা চুক্তিতে আগেই দেখানো হয় এবং অনুমোদনের আগে কখনো চাওয়া হয় না।',2,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(4,'Is approval guaranteed?','ঋণ পাওয়া কি নিশ্চিত?','No. Every application is reviewed individually. Payments of any fee do not guarantee approval.','না। প্রতিটি আবেদন আলাদাভাবে যাচাই করা হয়। কোনো ফি পরিশোধ করলেই ঋণ নিশ্চিত হয় না।',3,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(5,'How do I pay installments?','কিস্তি কীভাবে দেব?','From your dashboard, open the loan and tap \"Pay Installment\". You can pay with bKash, Nagad, Rocket and other methods.','ড্যাশবোর্ড থেকে ঋণটি খুলে \"কিস্তি পরিশোধ করুন\" চাপুন। বিকাশ, নগদ, রকেটসহ বিভিন্ন মাধ্যমে পরিশোধ করা যায়।',4,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(6,'Is my NID information safe?','আমার এনআইডির তথ্য কি নিরাপদ?','Yes. NID numbers are encrypted and images are stored privately. Only authorised staff can view them.','হ্যাঁ। এনআইডি নম্বর এনক্রিপ্ট করা থাকে এবং ছবিগুলো গোপনে সংরক্ষিত হয়। শুধু অনুমোদিত কর্মকর্তারা দেখতে পারেন।',5,1,'2026-11-07 12:36:52','2026-11-07 12:36:52');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;

--
-- Table structure for table `loan_agreements`
--

DROP TABLE IF EXISTS `loan_agreements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_agreements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agreement_number` varchar(30) NOT NULL,
  `loan_application_id` bigint(20) unsigned NOT NULL,
  `content` longtext NOT NULL,
  `principal` decimal(14,2) NOT NULL,
  `months` smallint(5) unsigned NOT NULL,
  `interest_rate` decimal(6,2) NOT NULL,
  `total_payable` decimal(14,2) NOT NULL,
  `installment_amount` decimal(14,2) NOT NULL,
  `processing_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fee_collection` varchar(20) NOT NULL DEFAULT 'deduct',
  `accepted_at` timestamp NULL DEFAULT NULL,
  `accepted_ip` varchar(45) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_agreements_agreement_number_unique` (`agreement_number`),
  UNIQUE KEY `loan_agreements_loan_application_id_unique` (`loan_application_id`),
  KEY `loan_agreements_created_by_foreign` (`created_by`),
  CONSTRAINT `loan_agreements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loan_agreements_loan_application_id_foreign` FOREIGN KEY (`loan_application_id`) REFERENCES `loan_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_agreements`
--

/*!40000 ALTER TABLE `loan_agreements` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_agreements` ENABLE KEYS */;

--
-- Table structure for table `loan_applications`
--

DROP TABLE IF EXISTS `loan_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(30) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `loan_product_id` bigint(20) unsigned NOT NULL,
  `loan_term_id` bigint(20) unsigned DEFAULT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `present_address` text NOT NULL,
  `permanent_address` text NOT NULL,
  `occupation` varchar(255) NOT NULL,
  `monthly_income` decimal(14,2) NOT NULL,
  `purpose` varchar(500) NOT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `requested_amount` decimal(14,2) NOT NULL,
  `requested_months` smallint(5) unsigned NOT NULL,
  `interest_rate` decimal(6,2) NOT NULL,
  `total_interest` decimal(14,2) NOT NULL,
  `total_payable` decimal(14,2) NOT NULL,
  `installment_amount` decimal(14,2) NOT NULL,
  `processing_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `approved_amount` decimal(14,2) DEFAULT NULL,
  `approved_months` smallint(5) unsigned DEFAULT NULL,
  `approved_interest_rate` decimal(6,2) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'submitted',
  `admin_note` text DEFAULT NULL,
  `internal_note` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `terms_accepted_at` timestamp NULL DEFAULT NULL,
  `terms_accepted_ip` varchar(45) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_applications_reference_unique` (`reference`),
  KEY `loan_applications_loan_product_id_foreign` (`loan_product_id`),
  KEY `loan_applications_loan_term_id_foreign` (`loan_term_id`),
  KEY `loan_applications_reviewed_by_foreign` (`reviewed_by`),
  KEY `loan_applications_user_id_status_index` (`user_id`,`status`),
  KEY `loan_applications_status_index` (`status`),
  CONSTRAINT `loan_applications_loan_product_id_foreign` FOREIGN KEY (`loan_product_id`) REFERENCES `loan_products` (`id`),
  CONSTRAINT `loan_applications_loan_term_id_foreign` FOREIGN KEY (`loan_term_id`) REFERENCES `loan_terms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loan_applications_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loan_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_applications`
--

/*!40000 ALTER TABLE `loan_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_applications` ENABLE KEYS */;

--
-- Table structure for table `loan_installments`
--

DROP TABLE IF EXISTS `loan_installments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_installments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `installment_number` smallint(5) unsigned NOT NULL,
  `due_date` date NOT NULL,
  `principal_component` decimal(14,2) NOT NULL,
  `interest_component` decimal(14,2) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `late_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `reminder_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_installments_loan_id_installment_number_unique` (`loan_id`,`installment_number`),
  KEY `loan_installments_due_date_index` (`due_date`),
  KEY `loan_installments_status_index` (`status`),
  CONSTRAINT `loan_installments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_installments`
--

/*!40000 ALTER TABLE `loan_installments` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_installments` ENABLE KEYS */;

--
-- Table structure for table `loan_products`
--

DROP TABLE IF EXISTS `loan_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `name_bn` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `description_bn` text DEFAULT NULL,
  `icon` varchar(40) NOT NULL DEFAULT 'wallet',
  `min_amount` decimal(14,2) NOT NULL,
  `max_amount` decimal(14,2) NOT NULL,
  `repayment_frequency` varchar(20) NOT NULL DEFAULT 'monthly',
  `processing_fee_type` varchar(10) NOT NULL DEFAULT 'percent',
  `processing_fee_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fee_collection` varchar(20) NOT NULL DEFAULT 'deduct',
  `fee_refundable` tinyint(1) NOT NULL DEFAULT 0,
  `fee_purpose` text DEFAULT NULL,
  `fee_refund_policy` text DEFAULT NULL,
  `late_fee_per_installment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grace_days` int(10) unsigned NOT NULL DEFAULT 3,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_products_slug_unique` (`slug`),
  KEY `loan_products_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_products`
--

/*!40000 ALTER TABLE `loan_products` DISABLE KEYS */;
INSERT INTO `loan_products` VALUES
(1,'Personal Loan','ব্যক্তিগত ঋণ','personal-loan','For personal and family needs.','ব্যক্তিগত ও পারিবারিক প্রয়োজনে।','user-round',5000.00,50000.00,'monthly','percent',1.00,'deduct',0,'Covers application processing, KYC verification and documentation.','Deducted from the disbursed amount only when the loan is disbursed. Nothing is charged if the application is rejected.',0.00,3,1,0,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(2,'Small Business Loan','ক্ষুদ্র ব্যবসা ঋণ','small-business-loan','Working capital for small shops and businesses.','ছোট দোকান ও ব্যবসার মূলধনের জন্য।','store',10000.00,100000.00,'monthly','percent',1.50,'deduct',0,'Covers application processing, KYC verification and documentation.','Deducted from the disbursed amount only when the loan is disbursed. Nothing is charged if the application is rejected.',0.00,3,1,1,'2026-11-07 12:36:52','2026-11-07 12:36:52');
/*!40000 ALTER TABLE `loan_products` ENABLE KEYS */;

--
-- Table structure for table `loan_repayments`
--

DROP TABLE IF EXISTS `loan_repayments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_repayments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `loan_installment_id` bigint(20) unsigned DEFAULT NULL,
  `payment_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` varchar(30) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NOT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_repayments_payment_transaction_id_unique` (`payment_transaction_id`),
  KEY `loan_repayments_loan_id_foreign` (`loan_id`),
  KEY `loan_repayments_loan_installment_id_foreign` (`loan_installment_id`),
  KEY `loan_repayments_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `loan_repayments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loan_repayments_loan_installment_id_foreign` FOREIGN KEY (`loan_installment_id`) REFERENCES `loan_installments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loan_repayments_payment_transaction_id_foreign` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loan_repayments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_repayments`
--

/*!40000 ALTER TABLE `loan_repayments` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_repayments` ENABLE KEYS */;

--
-- Table structure for table `loan_terms`
--

DROP TABLE IF EXISTS `loan_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_terms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_product_id` bigint(20) unsigned NOT NULL,
  `months` smallint(5) unsigned NOT NULL,
  `interest_rate` decimal(6,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_terms_loan_product_id_months_unique` (`loan_product_id`,`months`),
  CONSTRAINT `loan_terms_loan_product_id_foreign` FOREIGN KEY (`loan_product_id`) REFERENCES `loan_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_terms`
--

/*!40000 ALTER TABLE `loan_terms` DISABLE KEYS */;
INSERT INTO `loan_terms` VALUES
(1,1,3,10.00,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(2,1,6,11.00,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(3,1,9,12.00,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(4,1,12,13.00,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(5,2,6,11.00,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(6,2,9,12.00,1,'2026-11-07 12:36:52','2026-11-07 12:36:52'),
(7,2,12,12.50,1,'2026-11-07 12:36:52','2026-11-07 12:36:52');
/*!40000 ALTER TABLE `loan_terms` ENABLE KEYS */;

--
-- Table structure for table `loans`
--

DROP TABLE IF EXISTS `loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_number` varchar(30) NOT NULL,
  `loan_application_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `principal` decimal(14,2) NOT NULL,
  `interest_rate` decimal(6,2) NOT NULL,
  `months` smallint(5) unsigned NOT NULL,
  `total_interest` decimal(14,2) NOT NULL,
  `total_payable` decimal(14,2) NOT NULL,
  `installment_amount` decimal(14,2) NOT NULL,
  `processing_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `disbursed_amount` decimal(14,2) NOT NULL,
  `total_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_late_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `disbursement_method` varchar(30) NOT NULL,
  `disbursement_account` varchar(255) DEFAULT NULL,
  `disbursement_reference` varchar(255) NOT NULL,
  `disbursed_at` timestamp NOT NULL,
  `first_due_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `completed_at` timestamp NULL DEFAULT NULL,
  `disbursed_by` bigint(20) unsigned DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loans_loan_number_unique` (`loan_number`),
  UNIQUE KEY `loans_loan_application_id_unique` (`loan_application_id`),
  UNIQUE KEY `loans_disbursement_reference_unique` (`disbursement_reference`),
  KEY `loans_user_id_foreign` (`user_id`),
  KEY `loans_disbursed_by_foreign` (`disbursed_by`),
  KEY `loans_status_index` (`status`),
  CONSTRAINT `loans_disbursed_by_foreign` FOREIGN KEY (`disbursed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loans_loan_application_id_foreign` FOREIGN KEY (`loan_application_id`) REFERENCES `loan_applications` (`id`),
  CONSTRAINT `loans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loans`
--

/*!40000 ALTER TABLE `loans` DISABLE KEYS */;
/*!40000 ALTER TABLE `loans` ENABLE KEYS */;

--
-- Table structure for table `login_histories`
--

DROP TABLE IF EXISTS `login_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `login_identifier` varchar(255) DEFAULT NULL,
  `successful` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `login_histories_user_id_foreign` (`user_id`),
  KEY `login_histories_created_at_index` (`created_at`),
  CONSTRAINT `login_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_histories`
--

/*!40000 ALTER TABLE `login_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_histories` ENABLE KEYS */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_00_000000_create_admin_roles_table',1),
(2,'0001_01_01_000000_create_users_table',1),
(3,'0001_01_01_000001_create_cache_table',1),
(4,'0001_01_01_000002_create_jobs_table',1),
(5,'2026_11_07_000001_create_user_profiles_and_documents_tables',1),
(6,'2026_11_07_000002_create_loan_products_and_terms_tables',1),
(7,'2026_11_07_000003_create_loan_applications_table',1),
(8,'2026_11_07_000004_create_loans_tables',1),
(9,'2026_11_07_000005_create_payment_tables',1),
(10,'2026_11_07_000006_create_content_tables',1),
(11,'2026_11_07_000007_create_support_and_audit_tables',1),
(12,'2026_11_07_071655_make_email_optional_and_drop_otp_codes',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;

--
-- Table structure for table `payment_logs`
--

DROP TABLE IF EXISTS `payment_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(50) NOT NULL,
  `level` varchar(10) NOT NULL DEFAULT 'info',
  `message` varchar(255) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `payment_logs_payment_transaction_id_foreign` (`payment_transaction_id`),
  KEY `payment_logs_event_index` (`event`),
  CONSTRAINT `payment_logs_payment_transaction_id_foreign` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_logs`
--

/*!40000 ALTER TABLE `payment_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_logs` ENABLE KEYS */;

--
-- Table structure for table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(40) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `loan_application_id` bigint(20) unsigned DEFAULT NULL,
  `loan_id` bigint(20) unsigned DEFAULT NULL,
  `loan_installment_id` bigint(20) unsigned DEFAULT NULL,
  `purpose` varchar(30) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'BDT',
  `gateway` varchar(30) NOT NULL DEFAULT 'uddoktapay',
  `status` varchar(20) NOT NULL DEFAULT 'initiated',
  `gateway_invoice_id` varchar(255) DEFAULT NULL,
  `gateway_transaction_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(40) DEFAULT NULL,
  `sender_number` varchar(30) DEFAULT NULL,
  `gateway_fee` decimal(14,2) DEFAULT NULL,
  `charged_amount` decimal(14,2) DEFAULT NULL,
  `payment_url` varchar(1000) DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `paid_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `failure_reason` varchar(255) DEFAULT NULL,
  `refunded_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `refund_reason` varchar(255) DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `refunded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_transactions_reference_unique` (`reference`),
  UNIQUE KEY `payment_transactions_gateway_invoice_id_unique` (`gateway_invoice_id`),
  UNIQUE KEY `payment_transactions_gateway_transaction_id_unique` (`gateway_transaction_id`),
  KEY `payment_transactions_loan_application_id_foreign` (`loan_application_id`),
  KEY `payment_transactions_loan_id_foreign` (`loan_id`),
  KEY `payment_transactions_loan_installment_id_foreign` (`loan_installment_id`),
  KEY `payment_transactions_refunded_by_foreign` (`refunded_by`),
  KEY `payment_transactions_user_id_status_index` (`user_id`,`status`),
  KEY `payment_transactions_purpose_index` (`purpose`),
  KEY `payment_transactions_status_index` (`status`),
  CONSTRAINT `payment_transactions_loan_application_id_foreign` FOREIGN KEY (`loan_application_id`) REFERENCES `loan_applications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_transactions_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_transactions_loan_installment_id_foreign` FOREIGN KEY (`loan_installment_id`) REFERENCES `loan_installments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_transactions_refunded_by_foreign` FOREIGN KEY (`refunded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_transactions`
--

/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `loan_id` bigint(20) unsigned DEFAULT NULL,
  `display_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` text NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_loan_id_foreign` (`loan_id`),
  KEY `reviews_is_published_index` (`is_published`),
  CONSTRAINT `reviews_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;

--
-- Table structure for table `support_ticket_replies`
--

DROP TABLE IF EXISTS `support_ticket_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_ticket_replies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `support_ticket_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `is_staff` tinyint(1) NOT NULL DEFAULT 0,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_ticket_replies_support_ticket_id_foreign` (`support_ticket_id`),
  KEY `support_ticket_replies_user_id_foreign` (`user_id`),
  CONSTRAINT `support_ticket_replies_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_replies`
--

/*!40000 ALTER TABLE `support_ticket_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_ticket_replies` ENABLE KEYS */;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(30) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `category` varchar(30) NOT NULL DEFAULT 'general',
  `priority` varchar(10) NOT NULL DEFAULT 'normal',
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_tickets_ticket_number_unique` (`ticket_number`),
  KEY `support_tickets_user_id_foreign` (`user_id`),
  KEY `support_tickets_assigned_to_foreign` (`assigned_to`),
  KEY `support_tickets_status_index` (`status`),
  CONSTRAINT `support_tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(30) NOT NULL DEFAULT 'loan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`),
  KEY `system_settings_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;

--
-- Table structure for table `user_documents`
--

DROP TABLE IF EXISTS `user_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `nid_number` text NOT NULL,
  `nid_hash` varchar(64) NOT NULL,
  `front_image_path` varchar(255) NOT NULL,
  `back_image_path` varchar(255) NOT NULL,
  `selfie_path` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_documents_user_id_foreign` (`user_id`),
  KEY `user_documents_reviewed_by_foreign` (`reviewed_by`),
  KEY `user_documents_nid_hash_index` (`nid_hash`),
  KEY `user_documents_status_index` (`status`),
  CONSTRAINT `user_documents_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_documents`
--

/*!40000 ALTER TABLE `user_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_documents` ENABLE KEYS */;

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `present_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `monthly_income` decimal(14,2) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profiles`
--

/*!40000 ALTER TABLE `user_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_profiles` ENABLE KEYS */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `admin_role_id` bigint(20) unsigned DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `locale` varchar(5) NOT NULL DEFAULT 'bn',
  `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences`)),
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `blocked_reason` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `terms_accepted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_admin_role_id_foreign` (`admin_role_id`),
  KEY `users_is_blocked_index` (`is_blocked`),
  CONSTRAINT `users_admin_role_id_foreign` FOREIGN KEY (`admin_role_id`) REFERENCES `admin_roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Super Admin','01700000000','mahtabamanmahid@gmail.com','2026-11-07 12:36:55','2026-11-07 12:36:55','$2y$12$INAKW08ncErAmZsoVtsUJemL2f8kGlN3XgPnXixoa/hecS1h7wGMm',1,NULL,'bn',NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-11-07 12:36:55','2026-11-07 12:36:55',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

--
-- Table structure for table `website_settings`
--

DROP TABLE IF EXISTS `website_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `website_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `group` varchar(30) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `website_settings_key_unique` (`key`),
  KEY `website_settings_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_settings`
--

/*!40000 ALTER TABLE `website_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `website_settings` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-07 18:37:10
