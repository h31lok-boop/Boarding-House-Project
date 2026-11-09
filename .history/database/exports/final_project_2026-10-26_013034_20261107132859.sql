-- BoardMatch database export
-- Database: final_project
-- Generated: 2026-10-26T01:30:34+00:00
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

DROP TABLE IF EXISTS `accreditations`;
CREATE TABLE `accreditations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `boarding_house_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `decision_log` text COLLATE utf8mb4_unicode_ci,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accreditations_boarding_house_id_foreign` (`boarding_house_id`),
  CONSTRAINT `accreditations_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `admin_invite_codes`;
CREATE TABLE `admin_invite_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `used_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_invite_codes_code_unique` (`code`),
  KEY `admin_invite_codes_used_by_foreign` (`used_by`),
  CONSTRAINT `admin_invite_codes_used_by_foreign` FOREIGN KEY (`used_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `amenities`;
CREATE TABLE `amenities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `amenities_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `amenities` (`id`, `name`, `icon`, `created_at`, `updated_at`) VALUES
('1', 'Wi-Fi', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('2', 'Air Conditioning', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('3', 'Laundry Area', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('4', 'Kitchen Access', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('5', 'Study Area', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('6', 'CCTV', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('7', 'Parking', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('8', 'Water Included', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('9', 'Electricity Included', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('10', 'Security Guard', NULL, '2026-08-13 05:44:09', '2026-08-13 05:44:09'),
('11', 'Study Table', NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('12', 'Kitchen', NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('13', 'Secure Gate', NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11');

DROP TABLE IF EXISTS `approvals`;
CREATE TABLE `approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `boarding_house_id` bigint unsigned NOT NULL,
  `reviewer_id` bigint unsigned NOT NULL,
  `decision` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approvals_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `approvals_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `approvals_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approvals_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `approvals` (`id`, `boarding_house_id`, `reviewer_id`, `decision`, `remarks`, `reviewed_at`, `created_at`, `updated_at`) VALUES
('1', '1', '1', 'approved', 'Seeded approval for capstone management module.', '2026-08-13 05:44:10', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('2', '2', '1', 'approved', 'Seeded approval for capstone management module.', '2026-08-13 05:44:10', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('3', '3', '1', 'approved', 'Seeded approval for capstone management module.', '2026-08-13 05:44:10', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('4', '4', '1', 'approved', 'Seeded approval for capstone management module.', '2026-08-13 05:44:10', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('5', '5', '1', 'approved', 'Seeded approval for capstone management module.', '2026-08-13 05:44:10', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('6', '6', '1', 'approved', 'Seeded approval for capstone management module.', '2026-08-13 05:44:10', '2026-08-13 05:44:10', '2026-08-13 05:44:10');

DROP TABLE IF EXISTS `barangays`;
CREATE TABLE `barangays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `psgc_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barangay_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barangay_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_id` bigint unsigned NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barangays_barangay_code_unique` (`barangay_code`),
  UNIQUE KEY `barangays_psgc_code_unique` (`psgc_code`),
  KEY `barangays_city_id_foreign` (`city_id`),
  CONSTRAINT `barangays_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities_municipalities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `barangays` (`id`, `psgc_code`, `barangay_code`, `barangay_name`, `city_id`, `latitude`, `longitude`) VALUES
('1', '112405001', '112405001', 'Aplaya', '1', '6.74830000', '125.35470000'),
('2', '112405002', '112405002', 'Balabag', '1', '6.74110000', '125.35010000'),
('3', '112405003', '112405003', 'Dawis', '1', '6.74490000', '125.35620000'),
('4', '112405004', '112405004', 'Dichosa', '1', '6.75120000', '125.34990000'),
('5', '112405005', '112405005', 'Goma', '1', '6.73980000', '125.34350000'),
('6', '112405006', '112405006', 'Igpit', '1', '6.75770000', '125.36010000'),
('7', '112405007', '112405007', 'Kiagot', '1', '6.76350000', '125.34120000'),
('8', '112405008', '112405008', 'Matti', '1', '6.75874000', '125.30909000'),
('9', NULL, 'DSSCP3M001', 'Purok 3, Matti', '1', '6.76450000', '125.31350000'),
('10', NULL, 'DSSCMAH001', 'Mahayahay', '1', '6.77400000', '125.32550000'),
('11', NULL, 'DSSCTDM001', 'Tres de Mayo', '1', '6.78100000', '125.33300000'),
('12', NULL, 'DSSCPOB001', 'Poblacion / City Proper', '1', '6.74850000', '125.34450000'),
('13', '112402001', '112402001', 'Buhangin', '2', '7.11900000', '125.64700000'),
('14', '112402002', '112402002', 'Matina', '2', '7.05820000', '125.59410000'),
('15', '137404001', '137404001', 'Bagumbayan', '3', '14.63120000', '121.07480000'),
('16', '137404002', '137404002', 'Commonwealth', '3', '14.70300000', '121.08620000');

DROP TABLE IF EXISTS `boarding_house_amenities`;
CREATE TABLE `boarding_house_amenities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `boarding_house_id` bigint unsigned NOT NULL,
  `amenity_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `boarding_house_amenities_boarding_house_id_amenity_id_unique` (`boarding_house_id`,`amenity_id`),
  KEY `boarding_house_amenities_amenity_id_foreign` (`amenity_id`),
  CONSTRAINT `boarding_house_amenities_amenity_id_foreign` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `boarding_house_amenities_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `boarding_house_amenities` (`id`, `boarding_house_id`, `amenity_id`, `created_at`, `updated_at`) VALUES
('1', '1', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('2', '1', '2', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('3', '1', '6', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('4', '1', '5', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('5', '1', '3', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('6', '2', '3', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('7', '2', '4', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('8', '2', '5', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('9', '2', '6', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('10', '2', '7', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('11', '3', '9', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('12', '3', '4', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('13', '3', '3', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('14', '3', '2', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('15', '3', '6', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('16', '4', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('17', '4', '2', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('18', '4', '6', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('19', '4', '3', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('20', '4', '10', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('21', '5', '4', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('22', '5', '7', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('23', '5', '8', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('24', '5', '3', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('25', '5', '6', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('26', '6', '3', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('27', '6', '7', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('28', '6', '10', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('29', '6', '8', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('30', '6', '6', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('31', '7', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('32', '7', '11', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('33', '7', '12', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('34', '8', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('35', '8', '3', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('36', '8', '12', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('37', '9', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('38', '9', '5', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('39', '9', '12', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('40', '9', '13', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('41', '10', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('42', '10', '12', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('43', '10', '11', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('44', '11', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('45', '11', '12', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('46', '11', '3', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('47', '12', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('48', '12', '12', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('49', '12', '5', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('50', '12', '6', '2026-08-13 05:44:11', '2026-08-13 05:44:11');

DROP TABLE IF EXISTS `boarding_house_applications`;
CREATE TABLE `boarding_house_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `boarding_house_applications_user_id_foreign` (`user_id`),
  KEY `boarding_house_applications_boarding_house_id_foreign` (`boarding_house_id`),
  CONSTRAINT `boarding_house_applications_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `boarding_house_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `boarding_house_images`;
CREATE TABLE `boarding_house_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `boarding_house_id` bigint unsigned NOT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `boarding_house_images_boarding_house_id_foreign` (`boarding_house_id`),
  CONSTRAINT `boarding_house_images_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `boarding_house_images` (`id`, `boarding_house_id`, `image_path`, `image_label`, `is_primary`, `sort_order`, `created_at`, `updated_at`) VALUES
('25', '1', 'boarding-houses/35T7jWVzmBpZiNcOvwvRMVRpeTTMOWzvZCbR6pwk.jpg', '7b139935-7ecc-4499-8191-4aa3031e756e', '0', '0', '2026-08-13 07:41:01', '2026-08-13 07:47:27'),
('26', '1', 'boarding-houses/umdOEJRLunQ0LkMSytXnXFgsjZNQRMJO6CO05SJt.jpg', 'a81009cf-f65c-4366-8853-c9f4e697827e', '0', '1', '2026-08-13 07:41:23', '2026-08-13 07:47:27'),
('27', '1', 'boarding-houses/yG80DvmEouMWHdFxLdqncqo93bjYD4qsMahNLaeX.jpg', 'c7c33e40-c697-46d1-bb9c-7228f8132010', '0', '2', '2026-08-13 07:41:35', '2026-08-13 07:47:27'),
('28', '1', 'boarding-houses/u5LBpKfNkn8ZmtZPr8r9qeuuV4X0Z7avWHJZDllm.jpg', 'ad2470d2-692d-4252-97eb-b3743cc35061', '0', '3', '2026-08-13 07:41:50', '2026-08-13 07:47:27'),
('30', '1', 'boarding-houses/LcCPh7DsG0NMX1QD1elPc7Lw4l8NQiCNshbgmHMZ.jpg', '93fb2a84-3030-4820-a1e4-67bd5d908019', '0', '4', '2026-08-13 07:44:50', '2026-08-13 07:47:27'),
('31', '6', 'boarding-houses/BROXgZIUaoBNXtkrImYw9v7NRQAfH2KxnUdlcYi8.jpg', 'c424c437-635d-4666-b536-6133ced143eb', '0', '0', '2026-08-13 07:58:37', '2026-08-13 07:59:24'),
('32', '6', 'boarding-houses/dgbFITNBdMwOm1jy25YhNc74Rxz57wJgXeaQeK1z.jpg', 'c424c437-635d-4666-b536-6133ced143eb', '0', '1', '2026-08-13 07:59:24', '2026-08-13 07:59:24'),
('33', '2', 'boarding-houses/krw7Mr77KebYdl9D8pFPhK8yerufpkNh8JGnN7ZL.jpg', 'e7021624-cac2-40a3-8663-e2f46d3590eb', '1', '4', '2026-08-13 08:06:58', '2026-08-13 08:07:10'),
('34', '2', 'boarding-houses/elbqTKFOTWmUCGLR6YU4mmS3L2y6yvaAbY7OsX4i.jpg', '0393bddc-c7dd-4803-bb7b-ca1cca6a15e2', '0', '5', '2026-08-13 08:07:21', '2026-08-13 08:07:21'),
('35', '2', 'boarding-houses/KGMYHAlp10EIJcknBIzWKXEy7ZQVn3Z1kgaDNNLd.jpg', 'c0834814-5c3a-41af-a854-31111b0f715e', '0', '6', '2026-08-13 08:07:41', '2026-08-13 08:07:41'),
('36', '2', 'boarding-houses/Ct0ZFC8xbig56mZz7JLoWvibgcBB7X8NtuiZvjil.jpg', 'a8e8f367-d9f4-47ee-87b9-3f259b7bf6a0', '0', '7', '2026-08-13 08:07:53', '2026-08-13 08:07:53'),
('37', '2', 'boarding-houses/XdsaNo8f94RlXuTGQFKpstNLmEB7PLfogNz25bCK.jpg', '535723f2-d606-4d2b-bd79-8ade8f4982c1', '0', '8', '2026-08-13 08:08:02', '2026-08-13 08:08:02'),
('38', '2', 'boarding-houses/nh5Sxyy7lt3UTh3TXx7Kz0F8mvesfGaPdnaCs1Ex.jpg', '76cb5d40-2a2b-4a49-b648-6c720050f7f6', '0', '9', '2026-08-13 08:08:15', '2026-08-13 08:08:15'),
('39', '2', 'boarding-houses/xDdvhIyL7kSP8DqXJMZnUcwGEdu037jlZoZRyAwe.jpg', '1ab39d47-1b8f-4030-90ed-992d4f92f9cb', '0', '10', '2026-08-13 08:08:25', '2026-08-13 08:08:25'),
('40', '2', 'boarding-houses/0Qt3GdPD069YkdOuUltA0xjifZSv359M5yPxIJE5.jpg', '6df1f24f-4d72-441b-a9c0-46a7792ee391', '0', '11', '2026-08-13 08:08:35', '2026-08-13 08:08:35'),
('41', '2', 'boarding-houses/KqwskLyYE0OIDPiJuMlwpso3W5vWP4QKUyOaMj7i.jpg', 'dc2b318a-c9f1-4206-984f-851ef67af7bb', '0', '12', '2026-08-13 08:09:21', '2026-08-13 08:09:21'),
('44', '3', 'boarding-houses/pbvlzugSmJFcLMrXxB4HwJKnmxybJ5isYeEkw6fJ.jpg', '9a0a20b8-0f3b-40b5-b5ab-8facd7d38229', '1', '1', '2026-08-13 08:30:17', '2026-08-13 08:30:17'),
('45', '3', 'boarding-houses/xqWsbs5STRmg2znIc0TVskHNzAdjedZFiZ4fFAGL.jpg', 'c6636c28-e97f-48b6-a866-56851fe2ec8b', '0', '2', '2026-08-13 08:30:17', '2026-08-13 08:30:17'),
('46', '3', 'boarding-houses/tEk4MPpIXtyYr23FbQkk2W7amPn0AQQoz42vnrFM.jpg', 'b88f6c10-b7ac-4a72-8f51-9a445636a498', '0', '3', '2026-08-13 08:30:17', '2026-08-13 08:30:17'),
('47', '3', 'boarding-houses/SX2TIQlK4XjZRdva9uQsVjBakGnDUuEuOuMiIcO9.jpg', '7a5596c5-949b-413b-9530-085181214b3f', '0', '4', '2026-08-13 08:30:17', '2026-08-13 08:30:17'),
('48', '3', 'boarding-houses/5XSVJtpArysGHX3xb923aj7snYJT4QYrXpglTR9F.jpg', 'e108f583-93b5-4905-bad1-6bb9b338e033', '0', '5', '2026-08-13 08:30:17', '2026-08-13 08:30:17'),
('49', '3', 'boarding-houses/59KnMj1B1dHgpXT3duHotA8rkXrHKWXxLUHULohl.jpg', '1642db19-2ce6-4377-a66f-4e2147ab6163', '0', '6', '2026-08-13 08:30:17', '2026-08-13 08:30:17'),
('50', '4', 'boarding-houses/Z6xcpR1pCjCz9kuyarEQzB1QEXDapxA10OJHhWF6.jpg', 'be432439-c797-4541-9fc1-fad5b3460466', '1', '4', '2026-08-13 08:41:14', '2026-08-13 08:41:29'),
('51', '4', 'boarding-houses/zR7yt5T6ShNks5BJZ2mr1bvMaWKENK0zqkM1oYqR.jpg', 'bd4f9bff-a35e-4b91-bf24-3f73209589a1', '0', '5', '2026-08-13 08:41:14', '2026-08-13 08:41:14'),
('52', '4', 'boarding-houses/KX0gJnaNab1Di8ShWs0rUy5Ktm0bXXIsokqWhMO8.jpg', '5aebf0cd-9bd2-43d9-a132-59d3be5753db', '0', '6', '2026-08-13 08:41:14', '2026-08-13 08:41:14'),
('53', '4', 'boarding-houses/94yCftLPUpHGPFxi9xpo5ksjWf872a2E7wvMDMBa.jpg', '6a30966a-ad84-4844-80dc-dfa0a9bf8b19', '0', '7', '2026-08-13 08:41:14', '2026-08-13 08:41:14'),
('54', '4', 'boarding-houses/n9FvUCA54E2x53lrCfxy7mPHHnUZ8fDw82VewUsY.jpg', '8ab62e4c-b433-4989-973b-2fb9256a12b8', '0', '8', '2026-08-13 08:41:14', '2026-08-13 08:41:14'),
('55', '5', 'boarding-houses/s3lFrmahejwy56tq2a2wBhZwskvwFIWIlUW3bEOq.jpg', '0db285cd-44f2-47d1-95bc-0f40199fc076', '1', '1', '2026-08-13 08:49:19', '2026-08-13 08:49:19'),
('56', '5', 'boarding-houses/UZz37Jq9WpW2xUiyBqQ25UsFYiikAADCfgky7YLa.jpg', 'beb2ddd3-1562-4292-90c8-a607a9c46c97', '0', '2', '2026-08-13 08:49:19', '2026-08-13 08:49:19'),
('57', '5', 'boarding-houses/SJSUaZ133LhsSgjP53y3264jMJF00vtoIl5RrQLZ.jpg', '995a122f-1066-4315-ae54-ddfb15e1d24e', '0', '3', '2026-08-13 08:49:19', '2026-08-13 08:49:19'),
('58', '5', 'boarding-houses/0oCy1lG3TU3jI3wCmpaVFieyLnvRKXsY2O8gtysS.jpg', 'e9ab5e47-73b6-4d2f-bbdb-5e33eff61177', '0', '4', '2026-08-13 08:49:19', '2026-08-13 08:49:19');

DROP TABLE IF EXISTS `boarding_house_matches`;
CREATE TABLE `boarding_house_matches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `match_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `match_reasons` json DEFAULT NULL,
  `score_breakdown` json DEFAULT NULL,
  `ai_explanation` text COLLATE utf8mb4_unicode_ci,
  `ai_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ai_generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bh_matches_user_house_unique` (`user_id`,`boarding_house_id`),
  KEY `boarding_house_matches_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `bh_matches_user_score_index` (`user_id`,`match_score`),
  CONSTRAINT `boarding_house_matches_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `boarding_house_matches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `boarding_house_matches` (`id`, `user_id`, `boarding_house_id`, `match_score`, `match_reasons`, `score_breakdown`, `ai_explanation`, `ai_model`, `ai_generated_at`, `created_at`, `updated_at`) VALUES
('1', '7', '6', '62.00', '[\"Matches your preferred budget range.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.3833, \"weight\": 0.05, \"weighted_score\": 0.0192}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.85, \"weight\": 0.25, \"weighted_score\": 0.2125}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.25, \"weight\": 0.15, \"weighted_score\": 0.0375}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.7507, \"weight\": 0.1, \"weighted_score\": 0.0751}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('2', '7', '12', '75.00', '[\"Matches your preferred budget range.\", \"Matches your preferred amenities\", \"Includes preferred amenities: CCTV, Kitchen, Study Area.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.6667, \"weight\": 0.05, \"weighted_score\": 0.0333}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.85, \"weight\": 0.25, \"weighted_score\": 0.2125}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 1, \"weight\": 0.15, \"weighted_score\": 0.15}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.8007, \"weight\": 0.1, \"weighted_score\": 0.0801}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('3', '7', '3', '69.00', '[\"Matches your preferred budget range.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.3833, \"weight\": 0.05, \"weighted_score\": 0.0192}, \"budget\": {\"label\": \"Budget Match\", \"score\": 1, \"weight\": 0.25, \"weighted_score\": 0.25}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.7507, \"weight\": 0.1, \"weighted_score\": 0.0751}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('4', '7', '1', '70.00', '[\"Matches your preferred budget range.\", \"Matches your preferred amenities\", \"Includes preferred amenities: CCTV, Study Area, Wi-Fi.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.3833, \"weight\": 0.05, \"weighted_score\": 0.0192}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.85, \"weight\": 0.25, \"weighted_score\": 0.2125}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.75, \"weight\": 0.15, \"weighted_score\": 0.1125}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.8563, \"weight\": 0.1, \"weighted_score\": 0.0856}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('5', '7', '9', '71.00', '[\"Matches your preferred budget range.\", \"Matches your preferred amenities\", \"Includes preferred amenities: Kitchen, Study Area, Wi-Fi.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.6667, \"weight\": 0.05, \"weighted_score\": 0.0333}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.85, \"weight\": 0.25, \"weighted_score\": 0.2125}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.75, \"weight\": 0.15, \"weighted_score\": 0.1125}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.8007, \"weight\": 0.1, \"weighted_score\": 0.0801}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('6', '7', '2', '73.00', '[\"Matches your preferred budget range.\", \"Matches your preferred amenities\", \"Includes preferred amenities: CCTV, Kitchen Access, Study Area.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.3833, \"weight\": 0.05, \"weighted_score\": 0.0192}, \"budget\": {\"label\": \"Budget Match\", \"score\": 1, \"weight\": 0.25, \"weighted_score\": 0.25}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.75, \"weight\": 0.15, \"weighted_score\": 0.1125}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.7507, \"weight\": 0.1, \"weighted_score\": 0.0751}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('7', '7', '4', '63.00', '[\"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.3833, \"weight\": 0.05, \"weighted_score\": 0.0192}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.7255, \"weight\": 0.25, \"weighted_score\": 0.1814}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.7507, \"weight\": 0.1, \"weighted_score\": 0.0751}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('8', '7', '10', '68.00', '[\"Matches your preferred budget range.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.6667, \"weight\": 0.05, \"weighted_score\": 0.0333}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.85, \"weight\": 0.25, \"weighted_score\": 0.2125}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.8007, \"weight\": 0.1, \"weighted_score\": 0.0801}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('9', '7', '7', '68.00', '[\"Matches your preferred budget range.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.6667, \"weight\": 0.05, \"weighted_score\": 0.0333}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.85, \"weight\": 0.25, \"weighted_score\": 0.2125}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.8007, \"weight\": 0.1, \"weighted_score\": 0.0801}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('10', '7', '5', '55.00', '[\"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.3833, \"weight\": 0.05, \"weighted_score\": 0.0192}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.4118, \"weight\": 0.25, \"weighted_score\": 0.1029}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.7625, \"weight\": 0.1, \"weighted_score\": 0.0763}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('11', '7', '8', '67.00', '[\"Matches your preferred budget range.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.6667, \"weight\": 0.05, \"weighted_score\": 0.0333}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.85, \"weight\": 0.25, \"weighted_score\": 0.2125}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.7868, \"weight\": 0.1, \"weighted_score\": 0.0787}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38'),
('12', '7', '11', '68.00', '[\"Matches your preferred budget range.\", \"House rules fit your lifestyle preferences.\", \"Has available rooms or slots.\"]', '{\"other\": {\"label\": \"Other Preferences\", \"score\": 0.6667, \"weight\": 0.05, \"weighted_score\": 0.0333}, \"budget\": {\"label\": \"Budget Match\", \"score\": 0.85, \"weight\": 0.25, \"weighted_score\": 0.2125}, \"location\": {\"label\": \"Location Match\", \"score\": 0.5, \"weight\": 0.2, \"weighted_score\": 0.1}, \"amenities\": {\"label\": \"Amenities Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"lifestyle\": {\"label\": \"House Rules / Lifestyle\", \"score\": 0.8007, \"weight\": 0.1, \"weighted_score\": 0.0801}, \"room_type\": {\"label\": \"Room Type Match\", \"score\": 0.5, \"weight\": 0.15, \"weighted_score\": 0.075}, \"availability\": {\"label\": \"Availability\", \"score\": 1, \"weight\": 0.1, \"weighted_score\": 0.1}}', NULL, NULL, NULL, '2026-08-13 07:50:20', '2026-08-13 12:43:38');

DROP TABLE IF EXISTS `boarding_house_photos`;
CREATE TABLE `boarding_house_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint unsigned DEFAULT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `photo_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `boarding_house_photos_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `boarding_house_photos_owner_id_foreign` (`owner_id`),
  CONSTRAINT `boarding_house_photos_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `boarding_house_photos_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `boarding_house_services`;
CREATE TABLE `boarding_house_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `boarding_house_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `billing_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'per_use',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `boarding_house_services_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `boarding_house_services_is_active_index` (`is_active`),
  CONSTRAINT `boarding_house_services_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `boarding_houses`;
CREATE TABLE `boarding_houses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `owner_profile_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Boarding House',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_of_ownership` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_address` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `region_id` bigint unsigned DEFAULT NULL,
  `province_id` bigint unsigned DEFAULT NULL,
  `city_id` bigint unsigned DEFAULT NULL,
  `barangay_id` bigint unsigned DEFAULT NULL,
  `barangay` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nearby_landmark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distance_from_dssc` decimal(6,2) DEFAULT NULL,
  `is_near_dssc` tinyint(1) NOT NULL DEFAULT '0',
  `location_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approximate',
  `price` decimal(10,2) DEFAULT NULL,
  `available_rooms` int unsigned NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `house_rules` text COLLATE utf8mb4_unicode_ci,
  `capacity` int unsigned NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `approval_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approval_date` date DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `landlord_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_payment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exterior_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cr_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kitchen_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `boarding_houses_slug_unique` (`slug`),
  KEY `idx_boarding_houses_owner_id` (`owner_id`),
  KEY `idx_boarding_houses_status` (`status`),
  KEY `idx_boarding_houses_approval_status` (`approval_status`),
  KEY `idx_boarding_houses_is_active` (`is_active`),
  KEY `idx_boarding_houses_city_id` (`city_id`),
  KEY `idx_boarding_houses_barangay_id` (`barangay_id`),
  KEY `idx_boarding_houses_slug` (`slug`),
  KEY `boarding_houses_user_id_foreign` (`user_id`),
  KEY `boarding_houses_is_near_dssc_index` (`is_near_dssc`),
  CONSTRAINT `boarding_houses_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `boarding_houses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `boarding_houses` (`id`, `owner_id`, `user_id`, `owner_profile_id`, `created_at`, `updated_at`, `name`, `slug`, `address`, `proof_of_ownership`, `full_address`, `latitude`, `longitude`, `region_id`, `province_id`, `city_id`, `barangay_id`, `barangay`, `nearby_landmark`, `distance_from_dssc`, `is_near_dssc`, `location_status`, `price`, `available_rooms`, `description`, `house_rules`, `capacity`, `is_active`, `approval_status`, `status`, `approval_date`, `approved_by`, `rejection_reason`, `landlord_info`, `contact_name`, `contact_person`, `contact_phone`, `contact_number`, `monthly_payment`, `exterior_image`, `room_image`, `cr_image`, `kitchen_image`, `featured_image`) VALUES
('1', '2', NULL, '1', '2026-08-13 05:44:10', '2026-08-13 07:47:27', 'Cornejo Boarding House', 'sunrise-student-boarding-house-1', 'Matti, Digos City, Davao del Sur', NULL, 'Purok 1, Aplaya, Digos City, Davao del Sur', '6.7483000', '125.3547000', '11', '1', '1', '1', 'Matti', NULL, '5.17', '0', 'approximate', '2800.00', '7', 'Budget-friendly, near schools, with quiet study zones and stable internet.', 'Observe quiet hours from 10:00 PM to 6:00 AM. Keep shared spaces clean.', '24', '1', 'approved', 'approved', '2026-08-13', '1', NULL, 'Jani Dela Cruz', 'Jani Dela Cruz', 'Jani Dela Cruz', '09170000002', '09170000002', '1000', NULL, NULL, NULL, NULL, 'boarding-houses/35T7jWVzmBpZiNcOvwvRMVRpeTTMOWzvZCbR6pwk.jpg'),
('2', '3', NULL, '2', '2026-08-13 05:44:10', '2026-08-13 08:07:10', 'Greenleaf Bedspace and Dorm', 'greenleaf-bedspace-and-dorm-2', 'Purok 2, Balabag, Digos City, Davao del Sur', NULL, 'Purok 2, Balabag, Digos City, Davao del Sur', '6.7411000', '125.3501000', '11', '1', '1', '2', NULL, NULL, NULL, '0', 'approximate', '3200.00', '7', 'Affordable dorm setup with shared kitchen, laundry access, and CCTV security.', 'Observe quiet hours from 10:00 PM to 6:00 AM. Keep shared spaces clean.', '24', '1', 'approved', 'approved', '2026-08-13', '1', NULL, NULL, 'Maria Santos', 'Maria Santos', '09170000003', '09170000003', '3200', NULL, NULL, NULL, NULL, 'boarding-houses/krw7Mr77KebYdl9D8pFPhK8yerufpkNh8JGnN7ZL.jpg'),
('3', '4', NULL, '3', '2026-08-13 05:44:10', '2026-08-13 08:30:17', 'CityWalk Boarding Suites', 'citywalk-boarding-suites-3', 'Purok 3, Dawis, Digos City, Davao del Sur', NULL, 'Purok 3, Dawis, Digos City, Davao del Sur', '6.7449000', '125.3562000', '11', '1', '1', '3', NULL, NULL, NULL, '0', 'approximate', '4500.00', '7', 'Mid-range rooms close to transport routes and commercial establishments.', 'Observe quiet hours from 10:00 PM to 6:00 AM. Keep shared spaces clean.', '24', '1', 'approved', 'approved', '2026-08-13', '1', NULL, NULL, 'Roberto Cruz', 'Roberto Cruz', '09170000004', '09170000004', '4500', NULL, NULL, NULL, NULL, 'boarding-houses/pbvlzugSmJFcLMrXxB4HwJKnmxybJ5isYeEkw6fJ.jpg'),
('4', '5', NULL, '4', '2026-08-13 05:44:10', '2026-08-13 08:41:29', 'Haven Point Boarding Residence', 'haven-point-boarding-residence-4', 'Purok 4, Dichosa, Digos City, Davao del Sur', NULL, 'Purok 4, Dichosa, Digos City, Davao del Sur', '6.7512000', '125.3499000', '11', '1', '1', '4', NULL, NULL, NULL, '0', 'approximate', '5200.00', '6', 'Modern boarding space with curated amenities and strong safety policies.', 'Observe quiet hours from 10:00 PM to 6:00 AM. Keep shared spaces clean.', '24', '1', 'approved', 'approved', '2026-08-13', '1', NULL, NULL, 'Elena Villanueva', 'Elena Villanueva', '09170000005', '09170000005', '5200', NULL, NULL, NULL, NULL, 'boarding-houses/Z6xcpR1pCjCz9kuyarEQzB1QEXDapxA10OJHhWF6.jpg'),
('5', '6', NULL, '5', '2026-08-13 05:44:10', '2026-08-13 08:49:19', 'MetroNest Boarding Hub', 'metronest-boarding-hub-5', 'Purok 5, Goma, Digos City, Davao del Sur', NULL, 'Purok 5, Goma, Digos City, Davao del Sur', '6.7398000', '125.3435000', '11', '1', '1', '5', NULL, NULL, NULL, '0', 'approximate', '6000.00', '7', 'Premium room options with semi-furnished setup and co-working corner.', 'Observe quiet hours from 10:00 PM to 6:00 AM. Keep shared spaces clean.', '24', '1', 'approved', 'approved', '2026-08-13', '1', NULL, NULL, 'Daniel Reyes', 'Daniel Reyes', '09170000006', '09170000006', '6000', NULL, NULL, NULL, NULL, 'boarding-houses/s3lFrmahejwy56tq2a2wBhZwskvwFIWIlUW3bEOq.jpg'),
('6', '2', NULL, '1', '2026-08-13 05:44:10', '2026-08-13 07:59:24', 'Alisoso Boarding Stay', 'casa-digos-boarding-stay-6', 'Purok 6, Igpit, Digos City, Davao del Sur', NULL, 'Purok 6, Igpit, Digos City, Davao del Sur', '6.7577000', '125.3601000', '11', '1', '1', '6', 'Igpit', NULL, '5.63', '0', 'approximate', '3500.00', '7', 'Accessible location with practical facilities for students and workers.', 'Observe quiet hours from 10:00 PM to 6:00 AM. Keep shared spaces clean.', '24', '1', 'approved', 'approved', '2026-08-13', '1', NULL, 'Jani Dela Cruz', 'Jani Dela Cruz', 'Jani Dela Cruz', '09170000002', '09170000002', '1000', NULL, NULL, NULL, NULL, 'boarding-houses/BROXgZIUaoBNXtkrImYw9v7NRQAfH2KxnUdlcYi8.jpg'),
('7', NULL, NULL, NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'Matti Student Boarding House', 'matti-student-boarding-house', 'Matti, Digos City', NULL, 'Matti, Digos City, Davao del Sur', '6.7626000', '125.3114000', '11', '1', '1', '8', 'Matti', 'DSSC Main Campus', '0.50', '1', 'approximate', '1500.00', '4', 'Sample student listing near DSSC Main Campus. Coordinates are approximate and intended for testing only.', 'Observe quiet study hours. Keep shared spaces clean. No smoking indoors.', '8', '1', 'approved', 'approved', '2026-08-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1500', NULL, NULL, NULL, NULL, NULL),
('8', NULL, NULL, NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'Purok 3 Boarding House', 'purok-3-boarding-house', 'Purok 3, Matti, Digos City', NULL, 'Purok 3, Matti, Digos City, Davao del Sur', '6.7645000', '125.3135000', '11', '1', '1', '9', 'Purok 3, Matti', 'DSSC Main Campus', '0.80', '1', 'approximate', '1200.00', '5', 'Sample student listing near DSSC Main Campus. Coordinates are approximate and intended for testing only.', 'Observe quiet study hours. Keep shared spaces clean. No smoking indoors.', '10', '1', 'approved', 'approved', '2026-08-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1200', NULL, NULL, NULL, NULL, NULL),
('9', NULL, NULL, NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'DSSC Ladies Boarding House', 'dssc-ladies-boarding-house', 'Near DSSC Main Campus, Matti, Digos City', NULL, 'Near DSSC Main Campus, Matti, Digos City, Davao del Sur', '6.7560000', '125.3137000', '11', '1', '1', '8', 'Matti', 'DSSC Main Campus', '0.60', '1', 'approximate', '1500.00', '4', 'Sample student listing near DSSC Main Campus. Coordinates are approximate and intended for testing only.', 'Observe quiet study hours. Keep shared spaces clean. No smoking indoors.', '8', '1', 'approved', 'approved', '2026-08-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1500', NULL, NULL, NULL, NULL, NULL),
('10', NULL, NULL, NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'Mahayahay Student Home', 'mahayahay-student-home', 'Mahayahay, Digos City', NULL, 'Mahayahay, Digos City, Davao del Sur', '6.7740000', '125.3255000', '11', '1', '1', '10', 'Mahayahay', 'DSSC Main Campus', '2.50', '1', 'approximate', '2000.00', '3', 'Sample student listing near DSSC Main Campus. Coordinates are approximate and intended for testing only.', 'Observe quiet study hours. Keep shared spaces clean. No smoking indoors.', '6', '1', 'approved', 'approved', '2026-08-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2000', NULL, NULL, NULL, NULL, NULL),
('11', NULL, NULL, NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'Tres de Mayo Boarding House', 'tres-de-mayo-boarding-house', 'Tres de Mayo, Digos City', NULL, 'Tres de Mayo, Digos City, Davao del Sur', '6.7810000', '125.3330000', '11', '1', '1', '11', 'Tres de Mayo', 'DSSC Main Campus', '3.50', '1', 'approximate', '1800.00', '4', 'Sample student listing near DSSC Main Campus. Coordinates are approximate and intended for testing only.', 'Observe quiet study hours. Keep shared spaces clean. No smoking indoors.', '8', '1', 'approved', 'approved', '2026-08-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1800', NULL, NULL, NULL, NULL, NULL),
('12', NULL, NULL, NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'City Proper Student Dorm', 'city-proper-student-dorm', 'Digos City Proper', NULL, 'Digos City Proper, Davao del Sur', '6.7485000', '125.3445000', '11', '1', '1', '12', 'Poblacion / City Proper', 'DSSC Main Campus', '4.00', '1', 'approximate', '2500.00', '3', 'Sample student listing near DSSC Main Campus. Coordinates are approximate and intended for testing only.', 'Observe quiet study hours. Keep shared spaces clean. No smoking indoors.', '6', '1', 'approved', 'approved', '2026-08-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2500', NULL, NULL, NULL, NULL, NULL);

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `boarding_house_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `booking_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reservation',
  `payment_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `receipt_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_receipt_number_unique` (`receipt_number`),
  KEY `idx_bookings_user_id` (`user_id`),
  KEY `idx_bookings_room_id` (`room_id`),
  KEY `idx_bookings_status` (`status`),
  KEY `idx_bookings_start_date` (`start_date`),
  KEY `bookings_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `bookings_reservation_id_foreign` (`reservation_id`),
  CONSTRAINT `bookings_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `bookings` (`id`, `created_at`, `updated_at`, `room_id`, `user_id`, `status`, `start_date`, `end_date`, `notes`, `boarding_house_id`, `reservation_id`, `booking_type`, `payment_status`, `payment_method`, `total_amount`, `receipt_number`) VALUES
('1', '2026-08-13 05:44:11', '2026-08-13 05:44:11', '1', '7', 'Confirmed', '2026-07-23', '2027-01-13', 'Sample active booking for Hazel.', NULL, NULL, 'reservation', 'unpaid', NULL, '0.00', NULL);

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `chatbot_logs`;
CREATE TABLE `chatbot_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cities_municipalities`;
CREATE TABLE `cities_municipalities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `psgc_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province_id` bigint unsigned NOT NULL,
  `city_type` enum('city','municipality') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'municipality',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cities_municipalities_city_code_unique` (`city_code`),
  UNIQUE KEY `cities_municipalities_psgc_code_unique` (`psgc_code`),
  KEY `cities_municipalities_province_id_foreign` (`province_id`),
  CONSTRAINT `cities_municipalities_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `cities_municipalities` (`id`, `psgc_code`, `city_code`, `city_name`, `province_id`, `city_type`) VALUES
('1', '112405000', '112405', 'Digos City', '1', 'city'),
('2', '112402000', '112402', 'Davao City', '1', 'city'),
('3', '137404000', '137404', 'Quezon City', '2', 'city');

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_profile_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorites_user_id_boarding_house_id_unique` (`user_id`,`boarding_house_id`),
  KEY `idx_favorites_user_id` (`user_id`),
  KEY `idx_favorites_tenant_profile_id` (`tenant_profile_id`),
  KEY `idx_favorites_boarding_house_id` (`boarding_house_id`),
  CONSTRAINT `favorites_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `favorites` (`id`, `tenant_profile_id`, `user_id`, `boarding_house_id`, `notes`, `created_at`, `updated_at`) VALUES
('1', '1', '7', '1', 'Sample saved listing for Hazel.', '2026-08-13 05:44:11', '2026-08-13 05:44:11');

DROP TABLE IF EXISTS `incidents`;
CREATE TABLE `incidents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `severity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Low',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Open',
  `reported_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incidents_room_id_foreign` (`room_id`),
  KEY `incidents_user_id_foreign` (`user_id`),
  CONSTRAINT `incidents_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `incidents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inquiry_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenant_profile_id` bigint unsigned DEFAULT NULL,
  `owner_profile_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `room_category_id` bigint unsigned DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `priority` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `replied_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inquiries_inquiry_number_unique` (`inquiry_number`),
  KEY `idx_inquiries_user_id` (`user_id`),
  KEY `idx_inquiries_tenant_profile_id` (`tenant_profile_id`),
  KEY `idx_inquiries_owner_profile_id` (`owner_profile_id`),
  KEY `idx_inquiries_boarding_house_id` (`boarding_house_id`),
  KEY `idx_inquiries_status` (`status`),
  KEY `idx_inquiries_created_at` (`created_at`),
  CONSTRAINT `inquiries_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inquiries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `inquiries` (`id`, `inquiry_number`, `tenant_profile_id`, `owner_profile_id`, `user_id`, `boarding_house_id`, `room_category_id`, `message`, `status`, `priority`, `replied_at`, `created_at`, `updated_at`) VALUES
('1', 'INQ-HAZEL-001', '1', '1', '7', '1', NULL, 'Can I confirm the Wi-Fi schedule and quiet hours for my room?', 'replied', 'normal', '2026-08-10 05:44:11', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('2', 'INQ-HAZEL-002', '1', '2', '7', '2', NULL, 'Do you still have a bedspace available for next month?', 'pending', 'normal', NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('3', 'INQ-HAZEL-003', '1', '1', '7', '6', NULL, 'Is laundry access included in the monthly rent?', 'replied', 'low', '2026-08-13 06:00:06', '2026-08-13 05:44:11', '2026-08-13 06:00:06');

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `boarding_house_id` bigint unsigned NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `landmark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `locations_boarding_house_id_foreign` (`boarding_house_id`),
  CONSTRAINT `locations_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `maintenance_requests`;
CREATE TABLE `maintenance_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `issue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Open',
  `description` text COLLATE utf8mb4_unicode_ci,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_requests_room_id_foreign` (`room_id`),
  KEY `maintenance_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `maintenance_requests_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2026_01_22_140608_create_boarding_houses_table', '1'),
('5', '2026_01_22_140618_create_rooms_table', '1'),
('6', '2026_01_22_140627_create_bookings_table', '1'),
('7', '2026_01_22_140635_create_osas_validations_table', '1'),
('8', '2026_01_22_140653_create_chatbot_logs_table', '1'),
('9', '2026_01_22_152044_create_admin_table', '1'),
('10', '2026_01_29_000000_add_registration_fields_to_users_table', '1'),
('11', '2026_01_29_051541_create_permission_tables', '1'),
('12', '2026_01_30_000000_update_boarding_houses_table', '1'),
('13', '2026_01_30_000001_create_boarding_houses_table_if_missing', '1'),
('14', '2026_01_30_120000_add_boarding_house_id_to_users_table', '1'),
('15', '2026_01_30_130000_create_boarding_house_applications_table', '1'),
('16', '2026_02_01_000000_add_archiving_to_users_table', '1'),
('17', '2026_02_01_120000_create_validation_tasks_table', '1'),
('18', '2026_02_01_120100_create_validation_records_table', '1'),
('19', '2026_02_01_120200_create_validation_findings_table', '1'),
('20', '2026_02_01_120300_create_validation_evidence_table', '1'),
('21', '2026_02_01_120400_create_accreditations_table', '1'),
('22', '2026_02_03_120000_add_columns_to_rooms_table', '1'),
('23', '2026_02_03_120100_add_columns_to_bookings_table', '1'),
('24', '2026_02_03_120200_create_maintenance_requests_table', '1'),
('25', '2026_02_03_120300_create_incidents_table', '1'),
('26', '2026_02_03_120400_create_notices_table', '1'),
('27', '2026_02_22_000000_add_owner_details_to_boarding_houses_table', '1'),
('28', '2026_02_22_000002_add_details_to_rooms_table', '1'),
('29', '2026_02_22_000003_add_profile_image_to_users_table', '1'),
('30', '2026_02_23_000004_add_tenant_notification_columns_to_users_table', '1'),
('31', '2026_03_06_120000_add_capstone_fields_to_boarding_houses_and_rooms', '1'),
('32', '2026_03_06_120100_create_capstone_feature_tables', '1'),
('33', '2026_03_07_000000_create_location_reference_tables', '1'),
('34', '2026_03_07_010000_add_phase_schema_compatibility', '1'),
('35', '2026_03_07_020000_add_performance_indexes', '1'),
('36', '2026_05_25_190000_create_tenant_match_profiles_table', '1'),
('37', '2026_05_25_200000_create_roommate_match_requests_table', '1'),
('38', '2026_05_25_210000_make_user_role_nullable_for_spatie_roles', '1'),
('39', '2026_05_25_220000_add_preferred_amenities_to_tenant_match_profiles_table', '1'),
('40', '2026_05_25_230000_normalize_roles_to_admin_and_user', '1'),
('41', '2026_05_29_000001_create_admin_invite_codes_table', '1'),
('42', '2026_05_29_000002_fix_tenant_profiles_nullable_columns', '1'),
('43', '2026_05_29_000003_fix_inquiries_tenant_profile_id_nullable', '1'),
('44', '2026_05_29_000004_expand_payments_status_enum', '1'),
('45', '2026_05_29_000005_create_tenant_payment_methods_table', '1'),
('46', '2026_06_03_000001_add_username_to_users_table', '1'),
('47', '2026_06_04_150000_add_owner_registration_fields_and_boarding_house_photos_table', '1'),
('48', '2026_06_05_000001_create_support_requests_table', '1'),
('49', '2026_06_05_000002_add_user_settings_fields', '1'),
('50', '2026_06_05_000003_create_notifications_table', '1'),
('51', '2026_06_05_000004_add_reference_id_to_notifications_table', '1'),
('52', '2026_06_07_000001_complete_role_based_registration_schema', '1'),
('53', '2026_06_11_000003_create_tenant_preferences_and_boarding_house_matches', '1'),
('54', '2026_06_18_000001_create_user_preferences_table', '1'),
('55', '2026_06_19_000001_add_dssc_location_fields', '1'),
('56', '2026_06_19_000002_add_deepseek_fields_to_boarding_house_matches', '1'),
('57', '2026_06_19_000003_consolidate_boarding_house_images', '1'),
('58', '2026_06_19_000004_normalize_boarding_house_image_covers', '1'),
('59', '2026_06_19_000005_add_location_metadata_to_boarding_houses', '1'),
('60', '2026_06_20_000001_create_payment_receipts_table', '1'),
('61', '2026_06_20_000002_add_transaction_id_and_nullable_receipt_to_payment_receipts_table', '1'),
('62', '2026_07_06_120000_add_reservation_lifecycle_fields', '1'),
('63', '2026_07_21_000000_add_edit_fields_to_reservations_table', '1'),
('64', '2026_07_21_000001_add_preferences_to_users_table', '1'),
('65', '2026_07_23_002152_update_jani_email_to_owner', '1'),
('66', '2026_08_02_000001_add_matchmaking_booking_payment_fields', '1'),
('67', '2026_08_02_000002_create_boarding_house_services_tables', '1'),
('68', '2026_08_10_000001_add_paymongo_payment_gateway', '1'),
('69', '2026_08_12_000001_add_moderation_status_to_reviews', '1');

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
('1', 'App\\Models\\User', '1'),
('2', 'App\\Models\\User', '2'),
('2', 'App\\Models\\User', '3'),
('2', 'App\\Models\\User', '4'),
('2', 'App\\Models\\User', '5'),
('2', 'App\\Models\\User', '6'),
('3', 'App\\Models\\User', '7'),
('3', 'App\\Models\\User', '8'),
('3', 'App\\Models\\User', '9'),
('3', 'App\\Models\\User', '10'),
('3', 'App\\Models\\User', '11');

DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_by` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `audience` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'All Tenants',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Open',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notices_created_by_foreign` (`created_by`),
  CONSTRAINT `notices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `notices` (`id`, `created_by`, `title`, `body`, `audience`, `status`, `scheduled_at`, `created_at`, `updated_at`) VALUES
('1', '2', 'Payment reminder', 'Monthly rent is due every 5th day of the month.', 'All Tenants', 'Open', '2026-08-12 05:44:11', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('2', '2', 'Quiet hours update', 'Quiet hours are observed from 10:00 PM to 6:00 AM.', 'All Tenants', 'Open', '2026-08-09 05:44:11', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('3', '2', 'Room availability update', 'Several approved boarding houses have available rooms this week.', 'Students', 'Open', '2026-08-13 05:44:11', '2026-08-13 05:44:11', '2026-08-13 05:44:11');

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `reference_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notifications_user_id_type_reference_id_unique` (`user_id`,`type`,`reference_id`),
  KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`),
  KEY `notifications_user_id_type_index` (`user_id`,`type`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `reference_id`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
('1', '7', 'inquiry', 'Property Owner replied', 'mhugup', '{\"sender_name\": \"Jani Dela Cruz\", \"sender_role\": \"Property Owner\", \"reference_id\": \"inquiry:3\"}', 'inquiry:3', '1', '2026-08-13 08:18:06', '2026-08-13 06:00:06', '2026-08-13 08:18:06');

DROP TABLE IF EXISTS `osas_validations`;
CREATE TABLE `osas_validations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `owner_profiles`;
CREATE TABLE `owner_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `boarding_house_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `boarding_house_address` text COLLATE utf8mb4_unicode_ci,
  `contact_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_types` text COLLATE utf8mb4_unicode_ci,
  `monthly_rent_range` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amenities` text COLLATE utf8mb4_unicode_ci,
  `house_rules` text COLLATE utf8mb4_unicode_ci,
  `proof_of_ownership` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_permit_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_id_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_id_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `verified_by` bigint unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gcash_account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gcash_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gcash_api_key` text COLLATE utf8mb4_unicode_ci,
  `paymongo_public_key` text COLLATE utf8mb4_unicode_ci,
  `paymongo_secret_key` text COLLATE utf8mb4_unicode_ci,
  `paymongo_webhook_secret` text COLLATE utf8mb4_unicode_ci,
  `paymongo_enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `owner_profiles_user_id_unique` (`user_id`),
  KEY `owner_profiles_verified_by_foreign` (`verified_by`),
  CONSTRAINT `owner_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `owner_profiles_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `owner_profiles` (`id`, `user_id`, `company_name`, `boarding_house_name`, `boarding_house_address`, `contact_number`, `room_types`, `monthly_rent_range`, `amenities`, `house_rules`, `proof_of_ownership`, `business_permit_number`, `valid_id_type`, `valid_id_number`, `valid_id_file`, `verification_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`, `gcash_account_name`, `gcash_number`, `gcash_api_key`, `paymongo_public_key`, `paymongo_secret_key`, `paymongo_webhook_secret`, `paymongo_enabled`) VALUES
('1', '2', 'Jani Boarding House Office', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'BPN-2', 'other', 'OWN-2', 'auto-owner-id.txt', 'verified', '1', '2026-08-13 05:44:08', '2026-08-13 05:44:08', '2026-08-13 05:44:08', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('2', '3', 'Santos Student Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'BPN-3', 'other', 'OWN-3', 'auto-owner-id.txt', 'verified', '1', '2026-08-13 05:44:08', '2026-08-13 05:44:08', '2026-08-13 05:44:08', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('3', '4', 'Cruz Boarding Services', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'BPN-4', 'other', 'OWN-4', 'auto-owner-id.txt', 'verified', '1', '2026-08-13 05:44:08', '2026-08-13 05:44:08', '2026-08-13 05:44:08', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('4', '5', 'Villanueva Residences', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'BPN-5', 'other', 'OWN-5', 'auto-owner-id.txt', 'verified', '1', '2026-08-13 05:44:08', '2026-08-13 05:44:08', '2026-08-13 05:44:08', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('5', '6', 'Reyes Dormitory Management', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'BPN-6', 'other', 'OWN-6', 'auto-owner-id.txt', 'verified', '1', '2026-08-13 05:44:08', '2026-08-13 05:44:08', '2026-08-13 05:44:08', NULL, NULL, NULL, NULL, NULL, NULL, '1');

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payment_receipts`;
CREATE TABLE `payment_receipts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `booking_id` bigint unsigned DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date NOT NULL,
  `receipt_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending_review','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_review',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_id` bigint unsigned DEFAULT NULL,
  `receipt_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_receipts_receipt_number_unique` (`receipt_number`),
  KEY `payment_receipts_reviewed_by_foreign` (`reviewed_by`),
  KEY `payment_receipts_user_id_status_index` (`user_id`,`status`),
  KEY `payment_receipts_booking_id_status_index` (`booking_id`,`status`),
  KEY `payment_receipts_created_at_index` (`created_at`),
  KEY `payment_receipts_payment_id_foreign` (`payment_id`),
  CONSTRAINT `payment_receipts_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_receipts_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_receipts_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_receipts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `status` enum('pending','paid','unpaid','overdue','confirmed','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rent',
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_receipt_number_unique` (`receipt_number`),
  KEY `idx_payments_tenant_id` (`tenant_id`),
  KEY `idx_payments_boarding_house_id` (`boarding_house_id`),
  KEY `idx_payments_status` (`status`),
  KEY `idx_payments_due_date` (`due_date`),
  CONSTRAINT `payments_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `payments` (`id`, `tenant_id`, `boarding_house_id`, `amount`, `due_date`, `paid_at`, `status`, `reference_no`, `notes`, `created_at`, `updated_at`, `payment_method`, `payment_type`, `reference_number`, `receipt_number`) VALUES
('1', '1', '1', '3500.00', '2026-07-05', NULL, 'overdue', 'PAY-HAZEL-001', 'Sample overdue monthly rent.', '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'gcash', 'rent', 'PAY-HAZEL-001', NULL),
('2', '1', '1', '3500.00', '2026-08-05', NULL, 'overdue', 'PAY-HAZEL-002', 'Sample current billing record.', '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'gcash', 'rent', 'PAY-HAZEL-002', NULL),
('3', '1', '1', '3500.00', '2026-09-05', NULL, 'pending', 'PAY-HAZEL-003', 'Sample upcoming payment reminder.', '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'cash', 'rent', 'PAY-HAZEL-003', NULL);

DROP TABLE IF EXISTS `paymongo_checkouts`;
CREATE TABLE `paymongo_checkouts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `owner_id` bigint unsigned DEFAULT NULL,
  `boarding_house_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `payment_id` bigint unsigned DEFAULT NULL,
  `checkout_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paymongo_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PHP',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `checkout_url` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT NULL,
  `raw_payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paymongo_checkouts_reference_number_unique` (`reference_number`),
  UNIQUE KEY `paymongo_checkouts_checkout_session_id_unique` (`checkout_session_id`),
  KEY `paymongo_checkouts_user_id_foreign` (`user_id`),
  KEY `paymongo_checkouts_tenant_id_foreign` (`tenant_id`),
  KEY `paymongo_checkouts_owner_id_foreign` (`owner_id`),
  KEY `paymongo_checkouts_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `paymongo_checkouts_reservation_id_foreign` (`reservation_id`),
  KEY `paymongo_checkouts_payment_id_foreign` (`payment_id`),
  KEY `paymongo_checkouts_paymongo_payment_id_index` (`paymongo_payment_id`),
  KEY `paymongo_checkouts_status_index` (`status`),
  CONSTRAINT `paymongo_checkouts_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `paymongo_checkouts_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `paymongo_checkouts_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `paymongo_checkouts_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `paymongo_checkouts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `paymongo_checkouts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `provinces`;
CREATE TABLE `provinces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `psgc_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `region_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provinces_province_code_unique` (`province_code`),
  UNIQUE KEY `provinces_psgc_code_unique` (`psgc_code`),
  KEY `provinces_region_id_foreign` (`region_id`),
  CONSTRAINT `provinces_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `provinces` (`id`, `psgc_code`, `province_code`, `province_name`, `region_id`) VALUES
('1', '112400000', '1124', 'Davao del Sur', '11'),
('2', '137400000', '1374', 'Metro Manila', '16');

DROP TABLE IF EXISTS `regions`;
CREATE TABLE `regions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `psgc_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `region_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `region_short_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `regions_region_code_unique` (`region_code`),
  UNIQUE KEY `regions_psgc_code_unique` (`psgc_code`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `regions` (`id`, `psgc_code`, `region_code`, `region_name`, `region_short_name`) VALUES
('1', '010000000', '01', 'Ilocos Region', 'Region I'),
('2', '020000000', '02', 'Cagayan Valley', 'Region II'),
('3', '030000000', '03', 'Central Luzon', 'Region III'),
('4', '040000000', '04', 'CALABARZON', 'Region IV-A'),
('5', '050000000', '05', 'Bicol Region', 'Region V'),
('6', '060000000', '06', 'Western Visayas', 'Region VI'),
('7', '070000000', '07', 'Central Visayas', 'Region VII'),
('8', '080000000', '08', 'Eastern Visayas', 'Region VIII'),
('9', '090000000', '09', 'Zamboanga Peninsula', 'Region IX'),
('10', '100000000', '10', 'Northern Mindanao', 'Region X'),
('11', '110000000', '11', 'Davao Region', 'Region XI'),
('12', '120000000', '12', 'SOCCSKSARGEN', 'Region XII'),
('13', '130000000', '13', 'Caraga', 'Region XIII'),
('14', '140000000', '14', 'Bangsamoro Autonomous Region in Muslim Mindanao', 'BARMM'),
('15', '150000000', '15', 'Cordillera Administrative Region', 'CAR'),
('16', '160000000', '16', 'National Capital Region', 'NCR');

DROP TABLE IF EXISTS `reservation_services`;
CREATE TABLE `reservation_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `boarding_house_service_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_services_unique` (`reservation_id`,`boarding_house_service_id`),
  KEY `reservation_services_boarding_house_service_id_foreign` (`boarding_house_service_id`),
  CONSTRAINT `reservation_services_boarding_house_service_id_foreign` FOREIGN KEY (`boarding_house_service_id`) REFERENCES `boarding_house_services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_services_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `occupants` int unsigned DEFAULT NULL,
  `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terms_accepted_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `room_released_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `house_rules` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `booking_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reservation',
  `priority_rank` tinyint unsigned NOT NULL DEFAULT '2',
  `payment_method` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservations_user_id_foreign` (`user_id`),
  KEY `reservations_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `reservations_room_id_foreign` (`room_id`),
  KEY `reservations_priority_rank_index` (`priority_rank`),
  CONSTRAINT `reservations_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `reservations` (`id`, `user_id`, `boarding_house_id`, `room_id`, `check_in_date`, `check_out_date`, `due_date`, `occupants`, `emergency_contact_name`, `emergency_contact_number`, `total_amount`, `payment_status`, `terms_accepted_at`, `expires_at`, `approved_at`, `expired_at`, `room_released_at`, `status`, `notes`, `house_rules`, `created_at`, `updated_at`, `booking_type`, `priority_rank`, `payment_method`, `payment_reference`) VALUES
('1', '7', '1', '1', '2026-07-23', '2027-01-13', NULL, NULL, NULL, NULL, '0.00', NULL, NULL, NULL, NULL, NULL, NULL, 'confirmed', 'Confirmed sample reservation for the current room.', NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'reservation', '2', NULL, NULL),
('2', '7', '2', NULL, '2026-08-27', '2027-02-13', NULL, NULL, NULL, NULL, '0.00', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'Pending sample reservation for comparison.', NULL, '2026-08-13 05:44:11', '2026-08-13 05:44:11', 'reservation', '2', NULL, NULL);

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `reviews_status_index` (`status`),
  CONSTRAINT `reviews_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `reviews` (`id`, `user_id`, `boarding_house_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`) VALUES
('1', '7', '1', '5', 'Clean room, fast internet, and quiet enough for studying.', 'approved', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('2', '7', '2', '4', 'Good location and helpful owner. I am still comparing room options.', 'pending', '2026-08-13 05:44:11', '2026-08-13 05:44:11');

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
('1', 'admin', 'web', '2026-08-13 05:44:04', '2026-08-13 05:44:04'),
('2', 'owner', 'web', '2026-08-13 05:44:04', '2026-08-13 05:44:04'),
('3', 'user', 'web', '2026-08-13 05:44:04', '2026-08-13 05:44:04');

DROP TABLE IF EXISTS `room_categories`;
CREATE TABLE `room_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `boarding_house_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `capacity` int unsigned NOT NULL DEFAULT '1',
  `monthly_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_rooms` int unsigned NOT NULL DEFAULT '0',
  `available_rooms` int unsigned NOT NULL DEFAULT '0',
  `occupied_rooms` int unsigned NOT NULL DEFAULT '0',
  `reserved_rooms` int unsigned NOT NULL DEFAULT '0',
  `maintenance_rooms` int unsigned NOT NULL DEFAULT '0',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_categories_boarding_house_id_name_unique` (`boarding_house_id`,`name`),
  CONSTRAINT `room_categories_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `room_categories` (`id`, `boarding_house_id`, `name`, `description`, `capacity`, `monthly_rate`, `total_rooms`, `available_rooms`, `occupied_rooms`, `reserved_rooms`, `maintenance_rooms`, `is_available`, `created_at`, `updated_at`) VALUES
('1', '1', 'Standard', 'Standard room package', '2', '2800.00', '6', '4', '2', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('2', '1', 'Deluxe', 'Deluxe room package', '2', '4000.00', '4', '3', '1', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('3', '2', 'Standard', 'Standard room package', '2', '3200.00', '6', '4', '2', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('4', '2', 'Deluxe', 'Deluxe room package', '2', '4400.00', '4', '3', '1', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('5', '3', 'Standard', 'Standard room package', '2', '4500.00', '6', '4', '2', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('6', '3', 'Deluxe', 'Deluxe room package', '2', '5700.00', '4', '3', '1', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('7', '4', 'Standard', 'Standard room package', '2', '5200.00', '6', '3', '3', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('8', '4', 'Deluxe', 'Deluxe room package', '2', '6400.00', '4', '3', '1', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('9', '5', 'Standard', 'Standard room package', '2', '6000.00', '6', '4', '2', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('10', '5', 'Deluxe', 'Deluxe room package', '2', '7200.00', '4', '3', '1', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('11', '6', 'Standard', 'Standard room package', '2', '3500.00', '6', '4', '2', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('12', '6', 'Deluxe', 'Deluxe room package', '2', '4700.00', '4', '3', '1', '0', '0', '1', '2026-08-13 05:44:10', '2026-08-13 05:44:10'),
('13', '7', 'Shared Room', 'Sample room category for DSSC-area testing.', '2', '1500.00', '4', '4', '0', '0', '0', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('14', '8', 'Bed Space', 'Sample room category for DSSC-area testing.', '2', '1200.00', '5', '5', '0', '0', '0', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('15', '9', 'Shared Room', 'Sample room category for DSSC-area testing.', '2', '1500.00', '4', '4', '0', '0', '0', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('16', '10', 'Shared Room', 'Sample room category for DSSC-area testing.', '2', '2000.00', '3', '3', '0', '0', '0', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('17', '10', 'Solo Room', 'Sample room category for DSSC-area testing.', '1', '3500.00', '3', '2', '0', '0', '0', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('18', '11', 'Bed Space', 'Sample room category for DSSC-area testing.', '2', '1800.00', '4', '4', '0', '0', '0', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('19', '11', 'Shared Room', 'Sample room category for DSSC-area testing.', '2', '3000.00', '4', '3', '0', '0', '0', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('20', '12', 'Solo Room', 'Sample room category for DSSC-area testing.', '1', '2500.00', '3', '3', '0', '0', '0', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('21', '12', 'Shared Room', 'Sample room category for DSSC-area testing.', '2', '4000.00', '3', '2', '0', '0', '0', '1', '2026-08-13 05:44:11', '2026-08-13 05:44:11');

DROP TABLE IF EXISTS `roommate_match_requests`;
CREATE TABLE `roommate_match_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint unsigned NOT NULL,
  `recipient_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message` text COLLATE utf8mb4_unicode_ci,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roommate_match_requests_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `roommate_match_requests_sender_id_status_index` (`sender_id`,`status`),
  KEY `roommate_match_requests_recipient_id_status_index` (`recipient_id`,`status`),
  CONSTRAINT `roommate_match_requests_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `roommate_match_requests_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roommate_match_requests_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_category_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `boarding_house_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Available',
  `capacity` int unsigned NOT NULL DEFAULT '1',
  `available_slots` int unsigned NOT NULL DEFAULT '1',
  `amenities` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rooms_boarding_house_id` (`boarding_house_id`),
  KEY `idx_rooms_status` (`status`),
  KEY `idx_rooms_available_slots` (`available_slots`),
  KEY `idx_rooms_room_category_id` (`room_category_id`),
  KEY `idx_rooms_room_no` (`room_no`),
  KEY `idx_rooms_room_number` (`room_number`),
  CONSTRAINT `rooms_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `rooms` (`id`, `room_category_id`, `created_at`, `updated_at`, `boarding_house_id`, `name`, `status`, `capacity`, `available_slots`, `amenities`, `image_url`, `room_no`, `room_number`, `room_name`, `price`, `description`, `image`) VALUES
('1', '1', NULL, '2026-08-13 05:44:10', '1', 'Standard Room 1', 'Available', '2', '1', NULL, NULL, 'S-01', 'S-01', 'Standard Room 1', '2800.00', 'Seeded room for mapping and comparison demo.', NULL),
('2', '1', NULL, '2026-08-13 05:44:10', '1', 'Standard Room 2', 'Available', '2', '1', NULL, NULL, 'S-02', 'S-02', 'Standard Room 2', '2800.00', 'Seeded room for mapping and comparison demo.', NULL),
('3', '1', NULL, '2026-08-13 05:44:10', '1', 'Standard Room 3', 'Available', '2', '1', NULL, NULL, 'S-03', 'S-03', 'Standard Room 3', '2800.00', 'Seeded room for mapping and comparison demo.', NULL),
('4', '1', NULL, '2026-08-13 05:44:10', '1', 'Standard Room 4', 'Available', '2', '1', NULL, NULL, 'S-04', 'S-04', 'Standard Room 4', '2800.00', 'Seeded room for mapping and comparison demo.', NULL),
('5', '1', NULL, '2026-08-13 05:44:10', '1', 'Standard Room 5', 'Occupied', '2', '0', NULL, NULL, 'S-05', 'S-05', 'Standard Room 5', '2800.00', 'Seeded room for mapping and comparison demo.', NULL),
('6', '1', NULL, '2026-08-13 05:44:10', '1', 'Standard Room 6', 'Occupied', '2', '0', NULL, NULL, 'S-06', 'S-06', 'Standard Room 6', '2800.00', 'Seeded room for mapping and comparison demo.', NULL),
('7', '2', NULL, '2026-08-13 05:44:10', '1', 'Deluxe Room 1', 'Available', '2', '1', NULL, NULL, 'D-01', 'D-01', 'Deluxe Room 1', '4000.00', 'Seeded room for mapping and comparison demo.', NULL),
('8', '2', NULL, '2026-08-13 05:44:10', '1', 'Deluxe Room 2', 'Available', '2', '1', NULL, NULL, 'D-02', 'D-02', 'Deluxe Room 2', '4000.00', 'Seeded room for mapping and comparison demo.', NULL),
('9', '2', NULL, '2026-08-13 05:44:10', '1', 'Deluxe Room 3', 'Available', '2', '1', NULL, NULL, 'D-03', 'D-03', 'Deluxe Room 3', '4000.00', 'Seeded room for mapping and comparison demo.', NULL),
('10', '2', NULL, '2026-08-13 05:44:10', '1', 'Deluxe Room 4', 'Occupied', '2', '0', NULL, NULL, 'D-04', 'D-04', 'Deluxe Room 4', '4000.00', 'Seeded room for mapping and comparison demo.', NULL),
('11', '3', NULL, '2026-08-13 05:44:10', '2', 'Standard Room 1', 'Available', '2', '1', NULL, NULL, 'S-01', 'S-01', 'Standard Room 1', '3200.00', 'Seeded room for mapping and comparison demo.', NULL),
('12', '3', NULL, '2026-08-13 05:44:10', '2', 'Standard Room 2', 'Available', '2', '1', NULL, NULL, 'S-02', 'S-02', 'Standard Room 2', '3200.00', 'Seeded room for mapping and comparison demo.', NULL),
('13', '3', NULL, '2026-08-13 05:44:10', '2', 'Standard Room 3', 'Available', '2', '1', NULL, NULL, 'S-03', 'S-03', 'Standard Room 3', '3200.00', 'Seeded room for mapping and comparison demo.', NULL),
('14', '3', NULL, '2026-08-13 05:44:10', '2', 'Standard Room 4', 'Available', '2', '1', NULL, NULL, 'S-04', 'S-04', 'Standard Room 4', '3200.00', 'Seeded room for mapping and comparison demo.', NULL),
('15', '3', NULL, '2026-08-13 05:44:10', '2', 'Standard Room 5', 'Occupied', '2', '0', NULL, NULL, 'S-05', 'S-05', 'Standard Room 5', '3200.00', 'Seeded room for mapping and comparison demo.', NULL),
('16', '3', NULL, '2026-08-13 05:44:10', '2', 'Standard Room 6', 'Occupied', '2', '0', NULL, NULL, 'S-06', 'S-06', 'Standard Room 6', '3200.00', 'Seeded room for mapping and comparison demo.', NULL),
('17', '4', NULL, '2026-08-13 05:44:10', '2', 'Deluxe Room 1', 'Available', '2', '1', NULL, NULL, 'D-01', 'D-01', 'Deluxe Room 1', '4400.00', 'Seeded room for mapping and comparison demo.', NULL),
('18', '4', NULL, '2026-08-13 05:44:10', '2', 'Deluxe Room 2', 'Available', '2', '1', NULL, NULL, 'D-02', 'D-02', 'Deluxe Room 2', '4400.00', 'Seeded room for mapping and comparison demo.', NULL),
('19', '4', NULL, '2026-08-13 05:44:10', '2', 'Deluxe Room 3', 'Available', '2', '1', NULL, NULL, 'D-03', 'D-03', 'Deluxe Room 3', '4400.00', 'Seeded room for mapping and comparison demo.', NULL),
('20', '4', NULL, '2026-08-13 05:44:10', '2', 'Deluxe Room 4', 'Occupied', '2', '0', NULL, NULL, 'D-04', 'D-04', 'Deluxe Room 4', '4400.00', 'Seeded room for mapping and comparison demo.', NULL),
('21', '5', NULL, '2026-08-13 05:44:10', '3', 'Standard Room 1', 'Available', '2', '1', NULL, NULL, 'S-01', 'S-01', 'Standard Room 1', '4500.00', 'Seeded room for mapping and comparison demo.', NULL),
('22', '5', NULL, '2026-08-13 05:44:10', '3', 'Standard Room 2', 'Available', '2', '1', NULL, NULL, 'S-02', 'S-02', 'Standard Room 2', '4500.00', 'Seeded room for mapping and comparison demo.', NULL),
('23', '5', NULL, '2026-08-13 05:44:10', '3', 'Standard Room 3', 'Available', '2', '1', NULL, NULL, 'S-03', 'S-03', 'Standard Room 3', '4500.00', 'Seeded room for mapping and comparison demo.', NULL),
('24', '5', NULL, '2026-08-13 05:44:10', '3', 'Standard Room 4', 'Available', '2', '1', NULL, NULL, 'S-04', 'S-04', 'Standard Room 4', '4500.00', 'Seeded room for mapping and comparison demo.', NULL),
('25', '5', NULL, '2026-08-13 05:44:10', '3', 'Standard Room 5', 'Occupied', '2', '0', NULL, NULL, 'S-05', 'S-05', 'Standard Room 5', '4500.00', 'Seeded room for mapping and comparison demo.', NULL),
('26', '5', NULL, '2026-08-13 05:44:10', '3', 'Standard Room 6', 'Occupied', '2', '0', NULL, NULL, 'S-06', 'S-06', 'Standard Room 6', '4500.00', 'Seeded room for mapping and comparison demo.', NULL),
('27', '6', NULL, '2026-08-13 05:44:10', '3', 'Deluxe Room 1', 'Available', '2', '1', NULL, NULL, 'D-01', 'D-01', 'Deluxe Room 1', '5700.00', 'Seeded room for mapping and comparison demo.', NULL),
('28', '6', NULL, '2026-08-13 05:44:10', '3', 'Deluxe Room 2', 'Available', '2', '1', NULL, NULL, 'D-02', 'D-02', 'Deluxe Room 2', '5700.00', 'Seeded room for mapping and comparison demo.', NULL),
('29', '6', NULL, '2026-08-13 05:44:10', '3', 'Deluxe Room 3', 'Available', '2', '1', NULL, NULL, 'D-03', 'D-03', 'Deluxe Room 3', '5700.00', 'Seeded room for mapping and comparison demo.', NULL),
('30', '6', NULL, '2026-08-13 05:44:10', '3', 'Deluxe Room 4', 'Occupied', '2', '0', NULL, NULL, 'D-04', 'D-04', 'Deluxe Room 4', '5700.00', 'Seeded room for mapping and comparison demo.', NULL),
('31', '7', NULL, '2026-08-13 05:44:10', '4', 'Standard Room 1', 'Available', '2', '1', NULL, NULL, 'S-01', 'S-01', 'Standard Room 1', '5200.00', 'Seeded room for mapping and comparison demo.', NULL),
('32', '7', NULL, '2026-08-13 05:44:10', '4', 'Standard Room 2', 'Available', '2', '1', NULL, NULL, 'S-02', 'S-02', 'Standard Room 2', '5200.00', 'Seeded room for mapping and comparison demo.', NULL),
('33', '7', NULL, '2026-08-13 05:44:10', '4', 'Standard Room 3', 'Available', '2', '1', NULL, NULL, 'S-03', 'S-03', 'Standard Room 3', '5200.00', 'Seeded room for mapping and comparison demo.', NULL),
('34', '7', NULL, '2026-08-13 05:44:10', '4', 'Standard Room 4', 'Occupied', '2', '0', NULL, NULL, 'S-04', 'S-04', 'Standard Room 4', '5200.00', 'Seeded room for mapping and comparison demo.', NULL),
('35', '7', NULL, '2026-08-13 05:44:10', '4', 'Standard Room 5', 'Occupied', '2', '0', NULL, NULL, 'S-05', 'S-05', 'Standard Room 5', '5200.00', 'Seeded room for mapping and comparison demo.', NULL),
('36', '7', NULL, '2026-08-13 05:44:10', '4', 'Standard Room 6', 'Occupied', '2', '0', NULL, NULL, 'S-06', 'S-06', 'Standard Room 6', '5200.00', 'Seeded room for mapping and comparison demo.', NULL),
('37', '8', NULL, '2026-08-13 05:44:10', '4', 'Deluxe Room 1', 'Available', '2', '1', NULL, NULL, 'D-01', 'D-01', 'Deluxe Room 1', '6400.00', 'Seeded room for mapping and comparison demo.', NULL),
('38', '8', NULL, '2026-08-13 05:44:10', '4', 'Deluxe Room 2', 'Available', '2', '1', NULL, NULL, 'D-02', 'D-02', 'Deluxe Room 2', '6400.00', 'Seeded room for mapping and comparison demo.', NULL),
('39', '8', NULL, '2026-08-13 05:44:10', '4', 'Deluxe Room 3', 'Available', '2', '1', NULL, NULL, 'D-03', 'D-03', 'Deluxe Room 3', '6400.00', 'Seeded room for mapping and comparison demo.', NULL),
('40', '8', NULL, '2026-08-13 05:44:10', '4', 'Deluxe Room 4', 'Occupied', '2', '0', NULL, NULL, 'D-04', 'D-04', 'Deluxe Room 4', '6400.00', 'Seeded room for mapping and comparison demo.', NULL),
('41', '9', NULL, '2026-08-13 05:44:10', '5', 'Standard Room 1', 'Available', '2', '1', NULL, NULL, 'S-01', 'S-01', 'Standard Room 1', '6000.00', 'Seeded room for mapping and comparison demo.', NULL),
('42', '9', NULL, '2026-08-13 05:44:10', '5', 'Standard Room 2', 'Available', '2', '1', NULL, NULL, 'S-02', 'S-02', 'Standard Room 2', '6000.00', 'Seeded room for mapping and comparison demo.', NULL),
('43', '9', NULL, '2026-08-13 05:44:10', '5', 'Standard Room 3', 'Available', '2', '1', NULL, NULL, 'S-03', 'S-03', 'Standard Room 3', '6000.00', 'Seeded room for mapping and comparison demo.', NULL),
('44', '9', NULL, '2026-08-13 05:44:10', '5', 'Standard Room 4', 'Available', '2', '1', NULL, NULL, 'S-04', 'S-04', 'Standard Room 4', '6000.00', 'Seeded room for mapping and comparison demo.', NULL),
('45', '9', NULL, '2026-08-13 05:44:10', '5', 'Standard Room 5', 'Occupied', '2', '0', NULL, NULL, 'S-05', 'S-05', 'Standard Room 5', '6000.00', 'Seeded room for mapping and comparison demo.', NULL),
('46', '9', NULL, '2026-08-13 05:44:10', '5', 'Standard Room 6', 'Occupied', '2', '0', NULL, NULL, 'S-06', 'S-06', 'Standard Room 6', '6000.00', 'Seeded room for mapping and comparison demo.', NULL),
('47', '10', NULL, '2026-08-13 05:44:10', '5', 'Deluxe Room 1', 'Available', '2', '1', NULL, NULL, 'D-01', 'D-01', 'Deluxe Room 1', '7200.00', 'Seeded room for mapping and comparison demo.', NULL),
('48', '10', NULL, '2026-08-13 05:44:10', '5', 'Deluxe Room 2', 'Available', '2', '1', NULL, NULL, 'D-02', 'D-02', 'Deluxe Room 2', '7200.00', 'Seeded room for mapping and comparison demo.', NULL),
('49', '10', NULL, '2026-08-13 05:44:10', '5', 'Deluxe Room 3', 'Available', '2', '1', NULL, NULL, 'D-03', 'D-03', 'Deluxe Room 3', '7200.00', 'Seeded room for mapping and comparison demo.', NULL),
('50', '10', NULL, '2026-08-13 05:44:10', '5', 'Deluxe Room 4', 'Occupied', '2', '0', NULL, NULL, 'D-04', 'D-04', 'Deluxe Room 4', '7200.00', 'Seeded room for mapping and comparison demo.', NULL),
('51', '11', NULL, '2026-08-13 05:44:10', '6', 'Standard Room 1', 'Available', '2', '1', NULL, NULL, 'S-01', 'S-01', 'Standard Room 1', '3500.00', 'Seeded room for mapping and comparison demo.', NULL),
('52', '11', NULL, '2026-08-13 05:44:10', '6', 'Standard Room 2', 'Available', '2', '1', NULL, NULL, 'S-02', 'S-02', 'Standard Room 2', '3500.00', 'Seeded room for mapping and comparison demo.', NULL),
('53', '11', NULL, '2026-08-13 05:44:10', '6', 'Standard Room 3', 'Available', '2', '1', NULL, NULL, 'S-03', 'S-03', 'Standard Room 3', '3500.00', 'Seeded room for mapping and comparison demo.', NULL),
('54', '11', NULL, '2026-08-13 05:44:10', '6', 'Standard Room 4', 'Available', '2', '1', NULL, NULL, 'S-04', 'S-04', 'Standard Room 4', '3500.00', 'Seeded room for mapping and comparison demo.', NULL),
('55', '11', NULL, '2026-08-13 05:44:10', '6', 'Standard Room 5', 'Occupied', '2', '0', NULL, NULL, 'S-05', 'S-05', 'Standard Room 5', '3500.00', 'Seeded room for mapping and comparison demo.', NULL),
('56', '11', NULL, '2026-08-13 05:44:10', '6', 'Standard Room 6', 'Occupied', '2', '0', NULL, NULL, 'S-06', 'S-06', 'Standard Room 6', '3500.00', 'Seeded room for mapping and comparison demo.', NULL),
('57', '12', NULL, '2026-08-13 05:44:10', '6', 'Deluxe Room 1', 'Available', '2', '1', NULL, NULL, 'D-01', 'D-01', 'Deluxe Room 1', '4700.00', 'Seeded room for mapping and comparison demo.', NULL),
('58', '12', NULL, '2026-08-13 05:44:10', '6', 'Deluxe Room 2', 'Available', '2', '1', NULL, NULL, 'D-02', 'D-02', 'Deluxe Room 2', '4700.00', 'Seeded room for mapping and comparison demo.', NULL),
('59', '12', NULL, '2026-08-13 05:44:10', '6', 'Deluxe Room 3', 'Available', '2', '1', NULL, NULL, 'D-03', 'D-03', 'Deluxe Room 3', '4700.00', 'Seeded room for mapping and comparison demo.', NULL),
('60', '12', NULL, '2026-08-13 05:44:10', '6', 'Deluxe Room 4', 'Occupied', '2', '0', NULL, NULL, 'D-04', 'D-04', 'Deluxe Room 4', '4700.00', 'Seeded room for mapping and comparison demo.', NULL);

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `support_requests`;
CREATE TABLE `support_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `concern_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `screenshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_requests_user_id_foreign` (`user_id`),
  KEY `support_requests_status_created_at_index` (`status`,`created_at`),
  KEY `support_requests_concern_type_index` (`concern_type`),
  CONSTRAINT `support_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tenant_match_profiles`;
CREATE TABLE `tenant_match_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `budget_min` decimal(10,2) DEFAULT NULL,
  `budget_max` decimal(10,2) DEFAULT NULL,
  `gender_preference` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no_preference',
  `sleep_schedule` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `study_habits` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cleanliness_level` tinyint unsigned DEFAULT NULL,
  `noise_tolerance` tinyint unsigned DEFAULT NULL,
  `smoking_preference` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drinking_preference` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pets_preference` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internet_usage` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hobbies` json DEFAULT NULL,
  `preferred_amenity_ids` json DEFAULT NULL,
  `additional_notes` text COLLATE utf8mb4_unicode_ci,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `social_style` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cooking_habit` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_schedule` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_preference` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sharing_style` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_roommate_traits` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_match_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `tenant_match_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tenant_match_profiles` (`id`, `user_id`, `budget_min`, `budget_max`, `gender_preference`, `sleep_schedule`, `study_habits`, `cleanliness_level`, `noise_tolerance`, `smoking_preference`, `drinking_preference`, `pets_preference`, `internet_usage`, `hobbies`, `preferred_amenity_ids`, `additional_notes`, `completed_at`, `created_at`, `updated_at`, `social_style`, `cooking_habit`, `work_schedule`, `guest_preference`, `sharing_style`, `preferred_roommate_traits`) VALUES
('1', '7', '2800.00', '4500.00', 'no_preference', 'balanced', 'quiet_focus', '4', '2', 'non_smoker_only', 'occasional_ok', 'no_pets', 'heavy', '[\"reading\", \"coding\", \"music\"]', '[6, 4, 5, 1]', 'Prefers a quiet room with stable internet and clean shared spaces.', '2026-08-13 05:44:11', '2026-08-13 05:44:11', '2026-08-13 05:44:11', NULL, NULL, NULL, NULL, NULL, NULL);

DROP TABLE IF EXISTS `tenant_payment_methods`;
CREATE TABLE `tenant_payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `last_four` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cardholder_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_payment_methods_user_id_foreign` (`user_id`),
  CONSTRAINT `tenant_payment_methods_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tenant_preferences`;
CREATE TABLE `tenant_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `family_monthly_income` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_allowance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_rental_budget_min` decimal(10,2) DEFAULT NULL,
  `preferred_rental_budget_max` decimal(10,2) DEFAULT NULL,
  `preferred_locations` json DEFAULT NULL,
  `distance_from_school` decimal(5,2) DEFAULT NULL,
  `room_type` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `study_habits` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sleeping_schedule` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cleanliness_level` tinyint unsigned DEFAULT NULL,
  `noise_tolerance` tinyint unsigned DEFAULT NULL,
  `safety_preferences` json DEFAULT NULL,
  `amenities` json DEFAULT NULL,
  `lifestyle_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_preferences_user_id_unique` (`user_id`),
  CONSTRAINT `tenant_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tenant_profiles`;
CREATE TABLE `tenant_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `student_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_university` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_year_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rental_budget` decimal(10,2) DEFAULT NULL,
  `lifestyle_information` text COLLATE utf8mb4_unicode_ci,
  `course_or_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_id_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_id_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_by` bigint unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `preferred_language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'english',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_profiles_user_id_unique` (`user_id`),
  KEY `tenant_profiles_verified_by_foreign` (`verified_by`),
  CONSTRAINT `tenant_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tenant_profiles_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tenant_profiles` (`id`, `user_id`, `student_id`, `school_company`, `school_university`, `course_year_level`, `preferred_location`, `rental_budget`, `lifestyle_information`, `course_or_position`, `valid_id_type`, `valid_id_number`, `valid_id_file`, `emergency_contact_name`, `emergency_contact_number`, `id_verified`, `verified_by`, `verified_at`, `preferred_language`, `created_at`, `updated_at`) VALUES
('1', '7', 'TEN-HAZEL-0001', 'Davao del Sur State College', NULL, NULL, NULL, NULL, NULL, 'BSIT Student', 'school_id', 'SID-HAZEL-0001', 'seed-tenant-id-hazel.txt', 'Hazel Emergency Contact', '09981234567', '1', '2', '2026-08-13 05:44:11', 'english', '2026-08-13 05:44:11', '2026-08-13 05:44:11'),
('2', '8', 'TEN-8', 'Davao del Sur State College', NULL, NULL, NULL, NULL, NULL, 'BSIT Student', 'other', 'TENANT-8', 'auto-tenant-id.txt', 'Emergency Contact', '09990000000', '1', '1', '2026-08-13 05:44:08', 'english', '2026-08-13 05:44:08', '2026-08-13 05:44:08'),
('3', '9', 'TEN-9', 'Davao del Sur State College', NULL, NULL, NULL, NULL, NULL, 'BSIT Student', 'other', 'TENANT-9', 'auto-tenant-id.txt', 'Emergency Contact', '09990000000', '1', '1', '2026-08-13 05:44:08', 'english', '2026-08-13 05:44:08', '2026-08-13 05:44:08'),
('4', '10', 'TEN-10', 'Davao del Sur State College', NULL, NULL, NULL, NULL, NULL, 'BSIT Student', 'other', 'TENANT-10', 'auto-tenant-id.txt', 'Emergency Contact', '09990000000', '1', '1', '2026-08-13 05:44:08', 'english', '2026-08-13 05:44:08', '2026-08-13 05:44:08'),
('5', '11', 'TEN-11', 'Davao del Sur State College', NULL, NULL, NULL, NULL, NULL, 'BSIT Student', 'other', 'TENANT-11', 'auto-tenant-id.txt', 'Emergency Contact', '09990000000', '1', '1', '2026-08-13 05:44:08', 'english', '2026-08-13 05:44:08', '2026-08-13 05:44:08');

DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `move_in_date` date DEFAULT NULL,
  `move_out_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenants_user_id_foreign` (`user_id`),
  KEY `tenants_boarding_house_id_foreign` (`boarding_house_id`),
  KEY `tenants_room_id_foreign` (`room_id`),
  CONSTRAINT `tenants_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tenants_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tenants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tenants` (`id`, `user_id`, `boarding_house_id`, `room_id`, `move_in_date`, `move_out_date`, `status`, `created_at`, `updated_at`) VALUES
('1', '7', '1', '1', '2026-07-23', NULL, 'active', '2026-08-13 05:44:11', '2026-08-13 05:44:11');

DROP TABLE IF EXISTS `user_notification_preferences`;
CREATE TABLE `user_notification_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `sms_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `booking_reminders` tinyint(1) NOT NULL DEFAULT '1',
  `promotions_updates` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_notification_preferences_user_id_unique` (`user_id`),
  CONSTRAINT `user_notification_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `user_notification_preferences` (`id`, `user_id`, `email_notifications`, `sms_notifications`, `booking_reminders`, `promotions_updates`, `created_at`, `updated_at`) VALUES
('1', '7', '1', '1', '1', '0', '2026-08-13 08:17:43', '2026-08-13 08:17:43');

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE `user_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `family_monthly_income` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_allowance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_rental_budget` decimal(10,2) DEFAULT NULL,
  `preferred_rental_budget_min` decimal(10,2) DEFAULT NULL,
  `preferred_rental_budget_max` decimal(10,2) DEFAULT NULL,
  `preferred_locations` json DEFAULT NULL,
  `preferred_landmark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distance_from_school` decimal(5,2) DEFAULT NULL,
  `room_type` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `study_habits` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sleeping_schedule` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cleanliness_level` tinyint unsigned DEFAULT NULL,
  `noise_tolerance` tinyint unsigned DEFAULT NULL,
  `safety_preferences` json DEFAULT NULL,
  `amenities` json DEFAULT NULL,
  `lifestyle_notes` text COLLATE utf8mb4_unicode_ci,
  `profile_completion` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_preferences_user_id_unique` (`user_id`),
  CONSTRAINT `user_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `move_in_date` date DEFAULT NULL,
  `room_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `archived_at` timestamp NULL DEFAULT NULL,
  `boarding_house_id` bigint unsigned DEFAULT NULL,
  `notify_payment_reminders` tinyint(1) NOT NULL DEFAULT '1',
  `notify_booking_updates` tinyint(1) NOT NULL DEFAULT '1',
  `notify_ticket_updates` tinyint(1) NOT NULL DEFAULT '1',
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sms_two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `account_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `show_profile_to_owners` tinyint(1) NOT NULL DEFAULT '1',
  `allow_owner_messages` tinyint(1) NOT NULL DEFAULT '1',
  `allow_matchmaking_data` tinyint(1) NOT NULL DEFAULT '1',
  `preferences` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_is_active` (`is_active`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_boarding_house_id` (`boarding_house_id`),
  CONSTRAINT `users_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `users` (`id`, `name`, `username`, `email`, `email_verified_at`, `password`, `password_hash`, `remember_token`, `created_at`, `updated_at`, `role`, `phone`, `contact_number`, `profile_image`, `institution_name`, `move_in_date`, `room_number`, `is_active`, `status`, `is_archived`, `archived_at`, `boarding_house_id`, `notify_payment_reminders`, `notify_booking_updates`, `notify_ticket_updates`, `first_name`, `last_name`, `date_of_birth`, `gender`, `phone_number`, `current_address`, `profile_photo`, `sms_two_factor_enabled`, `account_status`, `show_profile_to_owners`, `allow_owner_messages`, `allow_matchmaking_data`, `preferences`) VALUES
('1', 'BoardMatch Administrator', 'admin', 'admin@example.com', '2026-08-13 05:44:05', '$2y$12$YBMDwZgc8wmlTq8KmLn9UuA8X6dbihDa2yKVS30IRS19L3bQY.8Sy', '$2y$12$YBMDwZgc8wmlTq8KmLn9UuA8X6dbihDa2yKVS30IRS19L3bQY.8Sy', NULL, '2026-08-13 05:44:05', '2026-08-13 05:44:05', 'admin', '09170000001', '09170000001', NULL, NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL),
('2', 'Jani Dela Cruz', 'owner', 'owner@example.com', '2026-08-13 05:44:05', '$2y$12$.PLXjQGFzHUNMjLiKvUMx./4thbD6WhF6Dxgul47mDlKO4YwuHEYG', '$2y$12$.PLXjQGFzHUNMjLiKvUMx./4thbD6WhF6Dxgul47mDlKO4YwuHEYG', NULL, '2026-08-13 05:44:05', '2026-08-13 05:44:05', 'owner', '09170000002', '09170000002', NULL, NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL),
('3', 'Maria Santos', 'owner2', 'owner2@boardmatch.test', '2026-08-13 05:44:05', '$2y$12$TC82oBIlvlLRxc9K4EyWr.ZclgIal1fQip9hd2cDiJYJPL54E9OJ6', '$2y$12$TC82oBIlvlLRxc9K4EyWr.ZclgIal1fQip9hd2cDiJYJPL54E9OJ6', NULL, '2026-08-13 05:44:05', '2026-08-13 05:44:05', 'owner', '09170000003', '09170000003', NULL, NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL),
('4', 'Roberto Cruz', 'owner3', 'owner3@boardmatch.test', '2026-08-13 05:44:06', '$2y$12$UiWzeQGpJkbqcOVLeuv6BObEHZ1jP5yGEO06/jU2jZ/S4zZ9o5Yjm', '$2y$12$UiWzeQGpJkbqcOVLeuv6BObEHZ1jP5yGEO06/jU2jZ/S4zZ9o5Yjm', NULL, '2026-08-13 05:44:06', '2026-08-13 08:22:07', 'owner', '09170000004', '09170000004', 'profile-photos/qJf2AY6eWgNlFNDg3M24924FQQgRaQrtWVJSmIeW.jpg', NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, '09170000004', NULL, 'profile-photos/qJf2AY6eWgNlFNDg3M24924FQQgRaQrtWVJSmIeW.jpg', '0', 'Active', '1', '1', '1', NULL),
('5', 'Elena Villanueva', 'owner4', 'owner4@boardmatch.test', '2026-08-13 05:44:06', '$2y$12$.0Egja6uAAYkjHuG9EiMQOGm9JGgBoMdjR0lteEuoqGbWIVbwc2Ue', '$2y$12$.0Egja6uAAYkjHuG9EiMQOGm9JGgBoMdjR0lteEuoqGbWIVbwc2Ue', NULL, '2026-08-13 05:44:06', '2026-08-13 05:44:06', 'owner', '09170000005', '09170000005', NULL, NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL),
('6', 'Daniel Reyes', 'owner5', 'owner5@boardmatch.test', '2026-08-13 05:44:06', '$2y$12$XpoRCHXgkGy2jfBmSedgeuRLRwActLaFvZ8LkiODEA1UgXnqs17bO', '$2y$12$XpoRCHXgkGy2jfBmSedgeuRLRwActLaFvZ8LkiODEA1UgXnqs17bO', NULL, '2026-08-13 05:44:06', '2026-08-13 05:44:06', 'owner', '09170000006', '09170000006', NULL, NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL),
('7', 'Hazel Reyes', 'tenant', 'tenant@example.com', '2026-08-13 05:44:07', '$2y$12$x2zj1wbd57SUD9/W.CY5UedM9IUb0w/mOvkU4ykghNUuvztO6fdmS', '$2y$12$x2zj1wbd57SUD9/W.CY5UedM9IUb0w/mOvkU4ykghNUuvztO6fdmS', NULL, '2026-08-13 05:44:07', '2026-08-13 05:44:11', 'user', '09171000001', '09171000001', NULL, 'Sunrise Student Boarding House', '2026-07-23', 'S-01', '1', 'active', '0', NULL, '1', '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL),
('8', 'Carlo Mendoza', 'tenant2', 'tenant2@boardmatch.test', '2026-08-13 05:44:07', '$2y$12$3FQKq/YXoqaUScBHDVDmfOJ6llSv3x26wVkJ47S2p.SsOFJeRsuiO', '$2y$12$3FQKq/YXoqaUScBHDVDmfOJ6llSv3x26wVkJ47S2p.SsOFJeRsuiO', NULL, '2026-08-13 05:44:07', '2026-08-13 05:44:07', 'user', '09171000002', '09171000002', NULL, NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL),
('9', 'Mae Dela Peña', 'tenant3', 'tenant3@boardmatch.test', '2026-08-13 05:44:07', '$2y$12$WpSh1hph8VZSDLV0i2sj0uRAafE7r4LzOdXj7930K2wrT3ciJGnXm', '$2y$12$WpSh1hph8VZSDLV0i2sj0uRAafE7r4LzOdXj7930K2wrT3ciJGnXm', NULL, '2026-08-13 05:44:07', '2026-08-13 05:44:07', 'user', '09171000003', '09171000003', NULL, NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL),
('10', 'John Bautista', 'tenant4', 'tenant4@boardmatch.test', '2026-08-13 05:44:08', '$2y$12$8jZTbs1WZu6a/9mtYjhWGu6BTjtTUwADChIDBtimy1RCZmxhx3SMS', '$2y$12$8jZTbs1WZu6a/9mtYjhWGu6BTjtTUwADChIDBtimy1RCZmxhx3SMS', NULL, '2026-08-13 05:44:08', '2026-08-13 05:44:08', 'user', '09171000004', '09171000004', NULL, NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL),
('11', 'Angela Ramos', 'tenant5', 'tenant5@boardmatch.test', '2026-08-13 05:44:08', '$2y$12$FbEvsIGsOdCnHZB9AtXAAe8FSvaUrYDDhMZdjpITzh91iIB0lETZa', '$2y$12$FbEvsIGsOdCnHZB9AtXAAe8FSvaUrYDDhMZdjpITzh91iIB0lETZa', NULL, '2026-08-13 05:44:08', '2026-08-13 05:44:08', 'user', '09171000005', '09171000005', NULL, NULL, NULL, NULL, '1', 'active', '0', NULL, NULL, '1', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'Active', '1', '1', '1', NULL);

DROP TABLE IF EXISTS `validation_evidence`;
CREATE TABLE `validation_evidence` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` bigint unsigned NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `validation_evidence_record_id_foreign` (`record_id`),
  KEY `validation_evidence_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `validation_evidence_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `validation_records` (`id`),
  CONSTRAINT `validation_evidence_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `validation_findings`;
CREATE TABLE `validation_findings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `validation_findings_record_id_foreign` (`record_id`),
  CONSTRAINT `validation_findings_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `validation_records` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `validation_records`;
CREATE TABLE `validation_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `validation_task_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `validation_records_validation_task_id_foreign` (`validation_task_id`),
  CONSTRAINT `validation_records_validation_task_id_foreign` FOREIGN KEY (`validation_task_id`) REFERENCES `validation_tasks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `validation_tasks`;
CREATE TABLE `validation_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `validator_id` bigint unsigned NOT NULL,
  `boarding_house_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assigned',
  `scheduled_at` date DEFAULT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `validation_tasks_validator_id_foreign` (`validator_id`),
  KEY `validation_tasks_boarding_house_id_foreign` (`boarding_house_id`),
  CONSTRAINT `validation_tasks_boarding_house_id_foreign` FOREIGN KEY (`boarding_house_id`) REFERENCES `boarding_houses` (`id`),
  CONSTRAINT `validation_tasks_validator_id_foreign` FOREIGN KEY (`validator_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
