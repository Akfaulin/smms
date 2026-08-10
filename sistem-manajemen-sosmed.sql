/*
SQLyog Community v13.3.1 (64 bit)
MySQL - 8.0.30 : Database - sistem_manajemen_sosmed
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sistem_manajemen_sosmed` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `sistem_manajemen_sosmed`;

/*Table structure for table `ai_generation_log` */

DROP TABLE IF EXISTS `ai_generation_log`;

CREATE TABLE `ai_generation_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `fitur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `prompt_input` text COLLATE utf8mb4_general_ci NOT NULL,
  `output` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ai_log_content_id` (`content_id`),
  KEY `fk_ai_log_user_id` (`user_id`),
  CONSTRAINT `fk_ai_log_content_id` FOREIGN KEY (`content_id`) REFERENCES `content_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ai_log_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `ai_generation_log` */

insert  into `ai_generation_log`(`id`,`content_id`,`user_id`,`fitur`,`prompt_input`,`output`,`created_at`) values 
(1,2,3,'caption_gen','Kamu adalah asisten copywriter media sosial profesional. Tolong buatkan 1 draft caption yang menarik (termasuk hashtag yang relevan) berdasarkan informasi berikut:\n\n- Judul/Topik: Cihuyy\n- Platform Target: Instagram\n- Catatan/Brief: \n\nAturan:\n1. Sesuaikan gaya bahasa (tone) dengan karakteristik platform (Instagram).\n2. Jangan buat kalimat pembuka seperti \'Tentu, ini dia\'. Langsung berikan captionnya.\n','Fitur AI belum dikonfigurasi. Mohon isi GEMINI_API_KEY di file .env.','2026-08-05 03:45:07');

/*Table structure for table `bukti_upload` */

DROP TABLE IF EXISTS `bukti_upload`;

CREATE TABLE `bukti_upload` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int unsigned NOT NULL,
  `platform_id` int unsigned DEFAULT NULL,
  `link_postingan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `uploaded_by` int unsigned NOT NULL,
  `uploaded_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `fk_bukti_platform_id` (`platform_id`),
  KEY `fk_bukti_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_bukti_content_id` FOREIGN KEY (`content_id`) REFERENCES `content_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bukti_platform_id` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_bukti_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `bukti_upload` */

insert  into `bukti_upload`(`id`,`content_id`,`platform_id`,`link_postingan`,`uploaded_by`,`uploaded_at`) values 
(1,2,NULL,'https://www.youtube.com/',4,'2026-08-05 03:47:15'),
(2,3,NULL,'https://instagram.com/p/C8921_SMMS_AI',4,'2026-08-05 05:27:46'),
(3,10,NULL,'https://tiktok.com/@smms_official/video/739120349',4,'2026-08-05 05:27:46');

/*Table structure for table `content_plan` */

DROP TABLE IF EXISTS `content_plan`;

CREATE TABLE `content_plan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `judul_konten` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `caption` text COLLATE utf8mb4_general_ci,
  `tanggal_publish` date DEFAULT NULL,
  `jenis_konten_id` int unsigned DEFAULT NULL,
  `content_type_id` int unsigned DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ide_diajukan',
  `dibuat_oleh` int unsigned NOT NULL,
  `assigned_designer` int unsigned DEFAULT NULL,
  `assigned_uploader` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `dibuat_oleh` (`dibuat_oleh`),
  KEY `jenis_konten_id` (`jenis_konten_id`),
  KEY `content_type_id` (`content_type_id`),
  KEY `fk_cp_designer` (`assigned_designer`),
  KEY `fk_cp_uploader` (`assigned_uploader`),
  CONSTRAINT `fk_cp_content_type` FOREIGN KEY (`content_type_id`) REFERENCES `content_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cp_designer` FOREIGN KEY (`assigned_designer`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cp_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_cp_jenis_konten` FOREIGN KEY (`jenis_konten_id`) REFERENCES `jenis_konten` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cp_uploader` FOREIGN KEY (`assigned_uploader`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `content_plan` */

insert  into `content_plan`(`id`,`judul_konten`,`deskripsi`,`caption`,`tanggal_publish`,`jenis_konten_id`,`content_type_id`,`status`,`dibuat_oleh`,`assigned_designer`,`assigned_uploader`,`created_at`,`updated_at`) values 
(1,'Test','',NULL,'2026-08-14',3,2,'revisi',1,NULL,NULL,'2026-08-05 03:03:54','2026-08-05 03:43:19'),
(2,'Cihuyy','',NULL,'2026-08-14',3,4,'published',3,2,4,'2026-08-05 03:28:10','2026-08-05 03:47:15'),
(3,'Peluncuran Fitur Baru AI Assistant Q3','Konten promosi fitur AI assistant teranyar untuk membantu tim meng-generate caption.','Capek bikin caption dari nol? ✨ Sekarang SMMS sudah dilengkapi AI Assistant! Buat caption menarik untuk Instagram dan TikTok dalam hitungan detik. Coba sekarang!','2026-08-05',2,2,'published',3,3,4,'2026-08-05 05:27:25','2026-08-05 05:27:25'),
(4,'5 Tips Mengoptimalkan Bio Instagram Usaha','Konten edukasi mengenai elemen penting di Bio IG agar mengkonversi pengunjung jadi pembeli.','Bio IG kamu masih sepi pembeli? ? Simak 5 elemen wajib ini:\\n1. Niche Jelas\\n2. Call to Action\\n3. Link Utama\\nSimpan postingan ini ya!','2026-08-10',2,1,'in_design',3,3,4,'2026-08-05 05:27:46','2026-08-05 05:27:46'),
(5,'Promo Spesial Diskon Kemerdekaan 17 Agustus','Visual banner promo diskon 45% khusus hari Kemerdekaan Republik Indonesia.','MERDEKA! ?? Dapatkan diskon 45% untuk semua paket langganan tahunan. Gunakan kode: MERDEKA45. Berlaku hingga 17 Agustus!','2026-08-17',3,2,'review_design',3,3,4,'2026-08-05 05:27:46','2026-08-05 05:27:46'),
(6,'Tutorial Singkat Penggunaan Fitur Content Plan','Video reels tutorial 30 detik mengajarkan alur pengajuan ide hingga publish.','Biar kerjaan tim medsos makin efisien, begini cara koordinasi ide pakai SMMS! Nggak ada lagi drama lupa posting ?','2026-08-12',1,1,'revisi',3,3,4,'2026-08-05 05:27:46','2026-08-05 05:27:46'),
(7,'Behind The Scene Suasana Kerja Tim Kreatif','Foto/video santai momen brainstorming tim kreatif di kantor.','Di balik postingan yang aesthetic, ada tim yang heboh diskusi ide tiap pagi ☕️ Mana momen BTS favorit kalian?','2026-08-15',3,4,'acc_ide',3,3,4,'2026-08-05 05:27:46','2026-08-05 05:27:46'),
(8,'Quotes Motivasi Produktivitas Senin Pagi','Kata motivasi pendek untuk menyapa audiens di awal minggu.','Awal minggu baru, semangat baru! Fokus pada langkah kecil hari ini. Happy Monday! ?','2026-08-18',3,3,'ide_diajukan',3,3,4,'2026-08-05 05:27:46','2026-08-05 05:27:46'),
(9,'Infografis Tren Social Media Marketing 2026','Thread & Infografis mengenai pergeseran algoritma dan tren short-video.','Tren konten apa yang paling mendominasi di 2026? Geser slide untuk pelajari data lengkapnya! ?','2026-08-20',2,1,'acc_final',2,3,4,'2026-08-05 05:27:46','2026-08-05 05:27:46'),
(10,'Video Reels Giveaway Perayaan 10K Follower','Konten giveaway dengan hadiah saldo e-wallet untuk followers aktif.','GIVEAWAY TIME! ? Terima kasih 10.000 followers! Mau dapat saldo Gopay 500rb? Tulis harapanmu di kolom komentar dan tag 3 temen kamu!','2026-08-01',1,4,'published',3,3,4,'2026-08-05 05:27:46','2026-08-05 05:27:46');

/*Table structure for table `content_platforms` */

DROP TABLE IF EXISTS `content_platforms`;

CREATE TABLE `content_platforms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int unsigned NOT NULL,
  `platform_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id_platform_id` (`content_id`,`platform_id`),
  KEY `fk_cplat_platform_id` (`platform_id`),
  CONSTRAINT `fk_cplat_content_id` FOREIGN KEY (`content_id`) REFERENCES `content_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cplat_platform_id` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `content_platforms` */

insert  into `content_platforms`(`id`,`content_id`,`platform_id`) values 
(1,1,1),
(2,2,4);

/*Table structure for table `content_status_log` */

DROP TABLE IF EXISTS `content_status_log`;

CREATE TABLE `content_status_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int unsigned NOT NULL,
  `status_lama` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_baru` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int unsigned NOT NULL,
  `catatan` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_csl_content_id` FOREIGN KEY (`content_id`) REFERENCES `content_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_csl_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `content_status_log` */

insert  into `content_status_log`(`id`,`content_id`,`status_lama`,`status_baru`,`user_id`,`catatan`,`created_at`) values 
(1,1,NULL,'ide_diajukan',1,'Ide baru diajukan.','2026-08-05 03:03:54'),
(2,2,NULL,'ide_diajukan',3,'Ide baru diajukan.','2026-08-05 03:28:10'),
(3,2,'ide_diajukan','acc_ide',2,NULL,'2026-08-05 03:34:34'),
(4,1,'ide_diajukan','revisi',1,'revisi catatan','2026-08-05 03:38:40'),
(5,1,'ide_diajukan','revisi',2,'revisi','2026-08-05 03:43:19'),
(6,2,'acc_ide','in_design',3,NULL,'2026-08-05 03:44:52'),
(7,2,'in_design','review_design',3,NULL,'2026-08-05 03:45:34'),
(8,2,'review_design','acc_final',2,NULL,'2026-08-05 03:46:46'),
(9,2,'acc_final','published',4,NULL,'2026-08-05 03:47:15'),
(10,3,NULL,'ide_diajukan',3,'Pengajuan ide promo AI Assistant','2026-08-05 05:27:25'),
(11,3,'ide_diajukan','acc_ide',2,'Ide menarik, silakan lanjut desain','2026-08-05 05:27:25'),
(12,3,'acc_ide','in_design',3,'Mulai pengerjaan carousel 5 slide','2026-08-05 05:27:25'),
(13,3,'in_design','review_design',3,'Desain slide 1-5 selesai diajukan','2026-08-05 05:27:25'),
(14,3,'review_design','acc_final',2,'Desain & caption sudah oke','2026-08-05 05:27:25'),
(15,3,'acc_final','published',4,'Konten telah diposting di IG Feeds','2026-08-05 05:27:25'),
(16,4,NULL,'ide_diajukan',3,'Ide edukasi Bio IG','2026-08-05 05:27:46'),
(17,4,'ide_diajukan','acc_ide',2,'ACC ide edukasi','2026-08-05 05:27:46'),
(18,4,'acc_ide','in_design',3,'Proses pembuatan visual slide','2026-08-05 05:27:46'),
(19,5,NULL,'ide_diajukan',3,'Pengajuan ide promo 17-an','2026-08-05 05:27:46'),
(20,5,'ide_diajukan','acc_ide',2,'ACC promo kemerdekaan','2026-08-05 05:27:46'),
(21,5,'acc_ide','in_design',3,'Desain poster merah putih','2026-08-05 05:27:46'),
(22,5,'in_design','review_design',3,'Mohon review desain poster promo','2026-08-05 05:27:46'),
(23,6,NULL,'ide_diajukan',3,'Ide video tutorial','2026-08-05 05:27:46'),
(24,6,'ide_diajukan','acc_ide',2,'ACC video Reels','2026-08-05 05:27:46'),
(25,6,'acc_ide','in_design',3,'Editing video Reels','2026-08-05 05:27:46'),
(26,6,'in_design','review_design',3,'Video siap ditinjau','2026-08-05 05:27:46'),
(27,6,'review_design','revisi',2,'Tolong percepat transisi video di detik ke-15 dan perjelas teks subtitle.','2026-08-05 05:27:46'),
(28,7,NULL,'ide_diajukan',3,'Ide konten BTS tim','2026-08-05 05:27:46'),
(29,7,'ide_diajukan','acc_ide',2,'Bagus untuk engagement, silakan foto/video.','2026-08-05 05:27:46'),
(30,8,NULL,'ide_diajukan',3,'Pengajuan ide Quotes Senin','2026-08-05 05:27:46'),
(31,9,NULL,'ide_diajukan',2,'Inisiasi ide riset tren 2026','2026-08-05 05:27:46'),
(32,9,'ide_diajukan','acc_ide',2,'ACC otomatis oleh Manager','2026-08-05 05:27:46'),
(33,9,'acc_ide','in_design',3,'Penyusunan grafik & layout slide','2026-08-05 05:27:46'),
(34,9,'in_design','review_design',3,'Slide infografis lengkap siap ditinjau','2026-08-05 05:27:46'),
(35,9,'review_design','acc_final',2,'Sangat bagus dan akurat. Tinggal scheduling.','2026-08-05 05:27:46'),
(36,10,NULL,'ide_diajukan',3,'Ide Giveaway 10k follower','2026-08-05 05:27:46'),
(37,10,'ide_diajukan','acc_ide',2,'ACC giveaway','2026-08-05 05:27:46'),
(38,10,'acc_ide','in_design',3,'Pengerjaan video reels','2026-08-05 05:27:46'),
(39,10,'in_design','review_design',3,'Review video giveaway','2026-08-05 05:27:46'),
(40,10,'review_design','acc_final',2,'ACC Final','2026-08-05 05:27:46'),
(41,10,'acc_final','published',4,'Telah diunggah di TikTok official','2026-08-05 05:27:46');

/*Table structure for table `content_types` */

DROP TABLE IF EXISTS `content_types`;

CREATE TABLE `content_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `content_types` */

insert  into `content_types`(`id`,`nama_type`) values 
(1,'Edukasi'),
(2,'Promosi'),
(3,'Hiburan'),
(4,'Inspirasi'),
(5,'Behind the Scene'),
(6,'Testimoni');

/*Table structure for table `jenis_konten` */

DROP TABLE IF EXISTS `jenis_konten`;

CREATE TABLE `jenis_konten` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `jenis_konten` */

insert  into `jenis_konten`(`id`,`nama_jenis`,`keterangan`) values 
(1,'Reels / Video','Konten video pendek atau panjang'),
(2,'Carousel','Slide multi-gambar'),
(3,'Static Post','Gambar tunggal'),
(4,'Story','Konten 24 jam'),
(5,'Thread / Caption','Konten teks panjang'),
(6,'Live','Siaran langsung');

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`version`,`class`,`group`,`namespace`,`time`,`batch`) values 
(1,'2026-08-05-000001','App\\Database\\Migrations\\CreateRoles','default','App',1785894343,1),
(2,'2026-08-05-000002','App\\Database\\Migrations\\CreateUsers','default','App',1785894343,1),
(3,'2026-08-05-000003','App\\Database\\Migrations\\CreatePlatforms','default','App',1785894831,2),
(4,'2026-08-05-000004','App\\Database\\Migrations\\CreateJenisKonten','default','App',1785894831,2),
(5,'2026-08-05-000005','App\\Database\\Migrations\\CreateContentTypes','default','App',1785894831,2),
(6,'2026-08-05-000006','App\\Database\\Migrations\\CreateContentPlan','default','App',1785894831,2),
(7,'2026-08-05-000007','App\\Database\\Migrations\\CreateContentStatusLog','default','App',1785894831,2),
(8,'2026-08-05-000008','App\\Database\\Migrations\\CreateContentPlatforms','default','App',1785896215,3),
(9,'2026-08-05-000009','App\\Database\\Migrations\\CreateBuktiUpload','default','App',1785896514,4),
(10,'2026-08-05-000010','App\\Database\\Migrations\\AddCaptionToContentPlan','default','App',1785897025,5),
(11,'2026-08-05-000011','App\\Database\\Migrations\\CreateAiGenerationLog','default','App',1785897025,5);

/*Table structure for table `platforms` */

DROP TABLE IF EXISTS `platforms`;

CREATE TABLE `platforms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_platform` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `platforms` */

insert  into `platforms`(`id`,`nama_platform`,`status`) values 
(1,'Instagram','aktif'),
(2,'TikTok','aktif'),
(3,'Facebook','aktif'),
(4,'Twitter / X','aktif'),
(5,'YouTube','aktif'),
(6,'LinkedIn','aktif');

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_role` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_role` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_role` (`kode_role`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `roles` */

insert  into `roles`(`id`,`kode_role`,`nama_role`) values 
(1,'superadmin','Superadmin'),
(2,'owner','Owner'),
(3,'manager','Manager'),
(4,'content_creator','Content Creator'),
(5,'admin_medsos','Admin Media Sosial');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role_id` int unsigned DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aktif',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`nama`,`email`,`password`,`role_id`,`status`,`created_at`,`updated_at`) values 
(1,'Superadmin','admin@smm.local','$2y$10$pYX7wjyYClpi8v4nKYmZDO8fOTR484AyF8ZvYj9/m/FovagLNRMTK',1,'aktif','2026-08-05 01:57:56','2026-08-05 05:27:46'),
(2,'manager','manager@smm.local','$2y$10$me5sDUVg9oN0lUISwhdmUuMF38vUAmVWnbKIfA3EGv3knz0NfKvZG',3,'aktif','2026-08-05 03:07:11','2026-08-05 03:07:11'),
(3,'creator','creator@smm.local','$2y$10$.Kl4iwU/WgnUmRSxoKewLO0Ta471HqtvGZrHPWh/WDVj0.B/AxTUq',4,'aktif','2026-08-05 03:13:39','2026-08-05 03:13:39'),
(4,'sosmed','sosmed@smm.local','$2y$10$moplJK5kIbRDzSihrbZNAudLiXs.g81X/zhSMuvNyp0pQrD7hJnLm',5,'aktif','2026-08-05 03:26:22','2026-08-05 03:26:22'),
(5,'owner','owner@smm.local','$2y$10$pQnKml5tWL1vn8o8HVMdaeK2//YHbi/IMRkwPoWqt2rwD0ML3/X/a',2,'aktif','2026-08-05 03:26:43','2026-08-05 03:26:43');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
