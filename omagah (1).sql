/*
SQLyog Community v13.1.3  (64 bit)
MySQL - 10.4.32-MariaDB-log : Database - ci4
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ci4` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `ci4`;

/*Table structure for table `ai_generation_log` */

DROP TABLE IF EXISTS `ai_generation_log`;

CREATE TABLE `ai_generation_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `fitur` varchar(50) NOT NULL,
  `prompt_input` text NOT NULL,
  `output` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ai_log_content_id` (`content_id`),
  KEY `fk_ai_log_user_id` (`user_id`),
  CONSTRAINT `fk_ai_log_content_id` FOREIGN KEY (`content_id`) REFERENCES `content_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ai_log_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `ai_generation_log` */

insert  into `ai_generation_log`(`id`,`content_id`,`user_id`,`fitur`,`prompt_input`,`output`,`created_at`) values 
(2,NULL,3,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"lomba kemerdekaan\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Gagal memanggil API AI. Periksa konfigurasi API Key.','2026-08-11 05:29:32'),
(3,NULL,3,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"Diskon kemerdekaan\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Gagal memanggil API AI. Periksa konfigurasi API Key.','2026-08-11 05:30:05'),
(4,NULL,3,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"diskon kemerdekaan\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Gagal memanggil API AI. Periksa konfigurasi API Key.','2026-08-11 05:31:38'),
(5,NULL,3,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"diskon kemerdekaan\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Gagal memanggil API AI. Periksa konfigurasi API Key.','2026-08-11 05:31:57'),
(6,NULL,3,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"diskon kemerdekaan\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Halo! Sebagai Strategis Konten Media Sosial, berikut adalah 3 ide konten kreatif Instagram yang dirancang untuk meningkatkan *engagement* sekaligus *sales* selama periode','2026-08-11 05:33:17'),
(7,NULL,3,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"hari besar idul adha\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Halo! Sebagai Strategis Konten Media Sosial, berikut adalah **3 ide konten kreatif Instagram** untuk menyambut Hari Raya Idul Ad','2026-08-11 05:35:31'),
(8,NULL,3,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"idul adha\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Halo! Sebagai Strategis Konten Media Sosial, berikut adalah 3 ide konten kreatif Instagram untuk momen **Idul Adha** yang dirancang untuk meningkatkan *engagement*, *reach* (jangkauan), dan *saves* (simpanan):\n\n---\n\n### **Ide 1: Edukasi & Tips Praktis (Format: Instagram Reels)**\n\n* **Judul Konten:** \"Stop! Jangan Langsung Cuci Daging Kurban! Ini Cara Simpan Awet 6 Bulan ?✨\"\n* **Konsep & Visual:**\n  * **Visual:** Video singkat berdurasi 15-30 detik dengan *editing* cepat dan estetis.\n  * **Alur:** \n    1. *Hook* visual memperlihatkan orang yang hampir mencuci daging di bawah kran (diberi tanda silang merah besar).\n    2. Langkah-langkah penyimpan yang benar (Lap pakai tisu dapur -> potong per porsi -> masukkan wadah kedap udara -> masuk *chiller* dulu -> baru ke *freezer*).\n    3. Gunakan *text on-screen* yang tebal dan musik yang sedang *trending*.\n* **Call to Action (CTA):** \"Klik tombol **SAVE** biar gak lupa pas pembagian daging nanti! Kirim juga ke grup keluarga kamu ya! ?\"\n\n---\n\n### **Ide 2: Mitos vs Fakta / Kesehatan (Format: Carousel Post)**\n\n* **Judul Konten:** \"Daging Kambing Bikin Darah Tinggi Naik? Mitos vs Faktanya! ?❌\"\n* **Konsep & Visual:**\n  * **Visual:** Mikroblog/Carousel 5 Slide dengan desain grafis yang bersih dan warna cerah.\n    * **Slide 1:** Judul provokatif + Ilustrasi kambing dan alat tensi darah.\n    * **Slide 2:** Mitos (Kolesterol kambing lebih tinggi dari sapi) vs Fakta (Kalori & lemak kambing sebenarnya lebih rendah dari sapi).\n    * **Slide 3:** Penyebab asli tensi naik saat Idul Adha (Bumbu santan berlebih, garam, & gorengan).\n    * **Slide 4:** Tips makan daging tetap aman (Imbangi dengan sayur & es jeruk tanpa gula).\n    * **Slide 5:** Ringkasan & CTA.\n* **Call to Action (CTA):** \"Tag temen kamu yang selalu takut makan daging kambing! Kamu sendiri Tim Kambing atau Tim Sapi nih? Tulis di kolom komentar! ?\"\n\n---\n\n### **Ide 3: Relatable / Humor (Format: Meme Carousel / Single Post POV)**\n\n* **Judul Konten:** \"Tipe-Tipe Orang Pas Dapet Daging Kurban, Kamu Nomor Berapa? ?\"\n* **Konsep & Visual:**\n  * **Visual:** Seri foto komedi / gambar ilustrasi yang menggambarkan beberapa tipe orang:\n    1. **Si Masterchef:** Daging datang, langsung bikin Sate, Rendang, dan Tongseng dalam sehari.\n    2. **Si Kolektor Freezer:** Daging disimpan sampai Idul Adha tahun depan.\n    3. **Si Kipas Sate:** Gak mau ikut motong daging, tapi paling semangat pas acara bakar-bakar.\n    4. **Si Tim Penimbang:** Fokus ngitung pembagian lemak vs daging murni.\n* **Call to Action (CTA):** \"Absen dong, kamu tipe nomor berapa? Tulis di komentar dan tag \'Si Kipas Sate\' di geng kamu! ⬇️\"\n\n---\n\n**Tips Tambahan dari Strategis:**\n* **Waktu Posting Optimal:** Posting H-1 Idul Adha (untuk ide tips simpan daging) dan Hari H jam 16.00–19.00 (saat orang-orang selesai memotong daging dan mulai bersantai sambil *scrolling* HP).','2026-08-11 05:38:41'),
(9,NULL,7,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"idul adha\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Halo! Sebagai Social Media Content Strategist, berikut adalah **3 ide konten kreatif Instagram** untuk menyambut Idul Adha yang dirancang untuk meningkatkan *engagement* (likes, saves, shares, dan komentar):\n\n---\n\n### **Ide 1: Edukasi / Edu-tainment (Format: Carousel)**\n\n* **Judul Konten:** \"Jangan Langsung Dicuci! 5 Trik Simpan Daging Kurban Awet Berbulan-bulan ?✨\"\n* **Konsep / Visual:** \n  * **Slide 1 (Cover):** Foto/ilustrasi daging segar dengan teks judul yang *bold* dan stiker tanda silang merah di atas kran air (memancing rasa penasaran).\n  * **Slide 2-5:** Infografis bersih dan estetis berisi langkah praktis (misal: potong per porsi, jangan dicuci dulu, bungkus kedap udara, masuk chiller baru freezer).\n* **Call to Action (CTA):** \n  \" *Save* postingan ini sekarang biar gak bingung pas daging kurban sampai di rumah nanti! Siapa yang biasanya langsung cuci dagingnya? Coba ngaku di kolom komentar! ?\"\n\n---\n\n### **Ide 2: Kuliner / Tips Praktis (Format: Instagram Reels)**\n\n* **Judul Konten:** \"Bebas Bau Prengus! Rahasia Bikin Daging Kambing Empuk & Maknyus ??\"\n* **Konsep / Visual:** \n  * Video *fast-paced* (15-30 detik) dengan audio tren. \n  * Menampilkan POV *step-by-step* cara menghilangkan bau kambing menggunakan bahan alami (daun jeruk, nanas, atau rempah) hingga menjadi hidangan yang menggiurkan (*food porn shot* di akhir video).\n* **Call to Action (CTA):** \n  \"*Share* video ini ke \'Chef\' keluarga kamu yang mau masak daging kurban besok! Menu kambing favoritmu apa nih, Tongseng atau Sate?\"\n\n---\n\n### **Ide 3: Relatable / Komedi (Format: Reels Skit / Meme Carousel)**\n\n* **Judul Konten:** \"Tipe-Tipe Warga Pas Pembagian Daging Kurban, Kamu Nomor Berapa? ?\"\n* **Konsep / Visual:** \n  * Video akting pendek (skit) atau kompilasi gambar membumi yang memperlihatkan tipe warga saat Idul Adha:\n    1. Panitia RT yang super sibuk pakai toa.\n    2. Warga yang siap pantau dari pagi padahal kupon belum dibagi.\n    3. Tim penonton garis keras anak-anak.\n    4. Yang dapet bagian \'jeroan\' tapi tetep bersyukur.\n* **Call to Action (CTA):** \n  \"Tag temen atau tetangga kamu yang mirip banget sama salah satu tipe di atas! Selamat Hari Raya Idul Adha semuanya! ?✨\"\n\n---\n\n**Tips Tambahan Strategis:** \n* Unggah **Ide 1** pada H-2/H-1 Idul Adha (saat orang siap-siap menerima daging).\n* Unggah **Ide 2 & 3** pada Hari H atau H+1 Idul Adha untuk memanfaatkan momen puncak perayaan.','2026-08-11 05:43:35'),
(10,NULL,7,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"idul adha\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Halo! Sebagai seorang Strategis Konten, berikut adalah 3 ide konten Instagram yang kreatif, relevan, dan berpotensi menghasilkan *engagement* tinggi untuk momen **IdulAdha**:\n\n---\n\n### **Ide 1: Edukasi & Edu-tainment (Format: Carousel / Reels)**\n*Sangat cocok untuk produk F&B, alat dapur, atau brand lifestyle.*\n\n* **Judul Konten:** \"Anti-Gagal! 4 Trik Bikin Daging Qurban Empuk & Bebas Bau Prengus ?✨\"\n* **Konsep/Visual Ringkas:** \n  Visual bersih dan menarik (sebelum vs sesudah). Menampilkan *step-by-step* praktis mengolah daging (misal: penggunaan nanas/daun pepaya, jangan dicuci langsung, teknik memotong melintang serat). Gunakan teks *overlay* yang singkat dan mudah dibaca.\n* **Call to Action (CTA):** \"Save postingan ini buat panduan masak besok! Kamu paling suka dagingnya diolah jadi apa nih?\"\n\n---\n\n### **Ide 2: Relatable / Humor Relatabel (Format: Instagram Reels)**\n*Sangat cocok untuk meningkatkan reach, menggaet audiens muda, dan berpotensi viral.*\n\n* **Judul Konten:** \"Tipe-Tipe Orang Pas Hari Raya Iduladha, Kamu Nomor Berapa? ?\"\n* **Konsep/Visual Ringkas:** \n  Video POV/skit singkat berdurasi 15-30 detik mengaitkan karakter populer:\n  1. *Si Paling Panitia* (Sibuk dari subuh pakai baju koko).\n  2. *Si MasterChef Dadakan* (Langsung siap dengan bumbu sate).\n  3. *Si Nunggu Kiriman* (Cuma rebahan nunggu daging datang).\n  Menggunakan *audio trending* yang lucu dan transisi yang cepat.\n* **Call to Action (CTA):** \"Tag temen kamu yang karakternya \'Si Paling MasterChef\'! Jangan lupa kirim satenya ke mimin ya ?\"\n\n---\n\n### **Ide 3: Emotional & Brand Value (Format: Single Post / Photo Dump)**\n*Sangat cocok untuk meningkatkan brand affinity, kepercayaan, dan menyampaikan pesan kebaikan.*\n\n* **Judul Konten:** \"Lebih dari Sekadar Daging: Arti \'Mengorbankan\' di Masa Kini ?\"\n* **Konsep/Visual Ringkas:** \n  Foto-foto estetis dengan *tone* hangat (*warm earthy tones*). Menampilkan momen kebersamaan: senyum panitia qurban, proses pembagian ke masyarakat, dan momen makan bersama keluarga. Teks *caption* bercerita (*storytelling*) tentang esensi berbagi dan menyisihkan sebagian harta untuk kebahagiaan orang lain.\n* **Call to Action (CTA):** \"Momen Iduladha apa yang paling kamu rindukan tiap tahunnya? Ceritakan di kolom komentar, yuk!\"\n\n---\n\n**Tips Tambahan Strategis:**\n* **Waktu Posting Terbaik:** H-1 Iduladha (untuk ide edukasi) dan Hari H s/d H+2 (untuk ide hiburan & emosional).\n* **Hashtag Utama:** #IdulAdha2024 #ResepDagingQurban #TipsDagingEmpuk #MomenBerbagi #IdulAdhaStory','2026-08-11 05:44:49'),
(11,NULL,7,'idea_gen','Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform Instagram berdasarkan topik/produk: \"idul adha\".\n\nUntuk setiap ide, berikan:\n1. Judul Konten yang Menarik\n2. Konsep/Visual Ringkas\n3. Call to Action (CTA)\n\nFormat output dengan rapi, singkat, dan mudah dipahami.','Halo! Sebagai Strategis Konten, fokus utama kita untuk momen Idul Adha di Instagram adalah **relevansi, edukasi yang praktis, dan interaksi (engagement)**. \n\nBerikut adalah 3 ide konten kreatif yang dirancang untuk meningkatkan *saves*, *shares*, dan *comments*:\n\n---\n\n### **Ide 1: Edukasi & Solutif (Format: Reels/Carousel)**\n\n* **Judul Konten:** *\"Daging Qurban Alot? Ini 4 Bahan Alami Pembuat Daging Empuk Seketika!\"*\n* **Konsep/Visual Ringkas:** \n  Video Reels durasi singkat (15-30 detik) atau Carousel grafik yang estetis. Menampilkan cara mengempukkan daging menggunakan bahan dapur (daun pepaya, nanas, baking soda, atau jahe). Visual memperlihatkan *before-after* tekstur daging yang empuk dan siap dimasak.\n* **Call to Action (CTA):** \n  *\"Save postingan ini biar nggak bingung pas ngolah daging besok! Bahan mana yang paling sering kamu pakai?\"*\n\n---\n\n### **Ide 2: Interaktif & Relatable (Format: Feed Carousel/Single Image)**\n\n* **Judul Konten:** *\"Debat Idul Adha: Tim Sate Nyeleneh vs Tim Rendang/Gule Standard?\"*\n* **Konsep/Visual Ringkas:** \n  Visual *split-screen* beresolusi tinggi dengan foto makanan yang sangat menggugah selera (*food porn*). Sebelah kiri foto Sate Daging dengan bumbu kecap/kacang, sebelah kanan foto Gule/Rendang hangat. Desain menggunakan warna khas Idul Adha (hijau/emas) atau warna *bold* yang mencolok di *explore*.\n* **Call to Action (CTA):** \n  *\"Ketik \'SATE\' atau \'GULE\' di kolom komentar! Kita lihat tim mana yang paling mendominasi tahun ini ?\"*\n\n---\n\n### **Ide 3: Kesehatan & Lifestyle (Format: Carousel)**\n\n* **Judul Konten:** *\"Makan Daging Tanpa Was-Was: 5 Minuman Penawar Kolesterol Setelah Makan Daging\"*\n* **Konsep/Visual Ringkas:** \n  Carousel berisi 5 slide minimalis dan segar. Mengedukasi penonton tentang minuman pembasmi rasa \"eneg\" dan penurun risiko kolesterol (seperti Es Jeruk Murni, Teh Hijau, Jus Timun, Jus Nanas, dan Air Lemon Warm). Visual menggunakan warna-warna cerah dan *fresh*.\n* **Call to Action (CTA):** \n  *\"Share konten ini ke grup WhatsApp keluarga kamu biar semuanya tetap sehat pas Lebaran nanti!\"*\n\n---\n\n**Tips Tambahan Strategis:**\n* **Waktu Posting Optimal:** H-1 Idul Adha (untuk Ide 1 & 3) dan Hari-H Idul Adha saat siang/sore hari (untuk Ide 2).\n* **Hashtag:** Gunakan kombinasi hashtag luas dan spesifik (#IdulAdha2024 #ResepDagingQurban #TipsDapur #OlahanDaging).','2026-08-11 05:45:16'),
(12,9,4,'caption_gen','Kamu adalah asisten copywriter media sosial profesional. Tolong buatkan 1 draft caption yang menarik (termasuk hashtag yang relevan) berdasarkan informasi berikut:\n\n- Judul/Topik: aoiwhehuioaher\n- Platform Target: Instagram\n- Catatan/Brief: akbfagfagr\n\nAturan:\n1. Sesuaikan gaya bahasa (tone) dengan karakteristik platform (Instagram).\n2. Jangan buat kalimat pembuka seperti \'Tentu, ini dia\'. Langsung berikan captionnya.\n','Kadang hal terbaik datang dari sesuatu yang sulit dijelaskan dengan kata-kata... ✨\n\nSama seperti **Aoiwhehuioaher**, sebuah konsep baru yang siap bikin hari-harimu jadi lebih bermakna. Kami paham kamu selalu cari sesuatu yang beda, fresh, dan pas buat gaya hidupmu. \n\nBukan sekadar tren biasa, ini tentang menemukan kenyamanan dan inspirasi baru di setiap momen. ?\n\nCoba tebak di kolom komentar, menurut kamu apa sih hal pertama yang terlintas saat dengar nama ini? ?\n\n---\n#NewVibes #DailyInspiration #LifestyleGoals #InspirasiHarian #TrendingNow #ExplorePage #GoodVibesOnly #CreativeCommunity #AestheticPosts #InstagramMarketing','2026-08-11 07:14:30'),
(13,9,4,'caption_gen','Kamu adalah asisten copywriter media sosial profesional. Tolong buatkan 1 draft caption yang menarik (termasuk hashtag yang relevan) berdasarkan informasi berikut:\n\n- Judul/Topik: aoiwhehuioaher\n- Platform Target: Instagram\n- Catatan/Brief: akbfagfagr\n\nAturan:\n1. Sesuaikan gaya bahasa (tone) dengan karakteristik platform (Instagram).\n2. Jangan buat kalimat pembuka seperti \'Tentu, ini dia\'. Langsung berikan captionnya.\n','Ada sesuatu yang baru dan siap bikin hari-harimu jadi jauh lebih seru! ✨\n\nPernah nggak sih ngerasa butuh solusi yang simpel tapi dampaknya berasa banget? Nah, kali ini kami punya sesuatu yang spesial banget khusus buat kamu. ?\n\nGak cuma sekadar menarik, tapi ini dirancang buat bantu kamu [masukkan manfaat utama dari produk/topik kamu] dengan cara yang jauh lebih praktis dan efisien. \n\nPenasaran mau coba atau tahu detail selengkapnya? ?\n\n? **Drop pendapat kamu di kolom komentar sekarang, atau klik link di bio untuk informasi lengkapnya ya!**\n\n---\n#InspirasiHariIni #InfoTerkini #GayaHidupModern #RekomendasiTerbaik #TrendingInstagram #ContentCreatorIndonesia #SpesialUntukmu #LangkahBaru','2026-08-11 07:15:20');

/*Table structure for table `brand_assets` */

DROP TABLE IF EXISTS `brand_assets`;

CREATE TABLE `brand_assets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama_aset` varchar(200) NOT NULL,
  `kategori` enum('logo','palette','font','template','foto_produk','ikon') NOT NULL DEFAULT 'template',
  `nilai_atau_url` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuat_oleh` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `brand_assets` */

insert  into `brand_assets`(`id`,`nama_aset`,`kategori`,`nilai_atau_url`,`keterangan`,`dibuat_oleh`,`created_at`,`updated_at`) values 
(1,'Primary Royal Blue','palette','#2563eb','Warna utama tombol, header, dan elemen visual dominan brand.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33'),
(2,'Secondary Electric Purple','palette','#7c3aed','Warna aksen AI, fitur inovasi, dan badge khusus.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33'),
(3,'Emerald Green Success','palette','#059669','Warna status published, sukses, dan penawaran hemat.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33'),
(4,'Dark Slate Neutral','palette','#0f172a','Warna teks utama, background dark mode, dan border tegas.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33'),
(5,'DM Sans (Primary Font Body)','font','https://fonts.google.com/specimen/DM+Sans','Font utama untuk teks body, caption, dan UI elemen.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33'),
(6,'Outfit (Display Font Heading)','font','https://fonts.google.com/specimen/Outfit','Font khusus untuk judul banner, poster, dan headline promosi.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33'),
(7,'Master Canva Template Feed Instagram 1080x1080','template','https://canva.com','Preset 20+ slide postingan Instagram Feed siap pakai.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33'),
(8,'Figma Kit Story & TikTok Video Frame 1080x1920','template','https://figma.com','Design kit resmi untuk layout Story IG dan cover video TikTok.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33'),
(9,'Logo Resmi SMMS HD Transparent (.PNG)','logo','/images/logo-placeholder.png','Logo resolusi tinggi dengan latar belakang transparan.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33'),
(10,'Icon Pack Vector Brand (.SVG)','ikon','/images/icons-pack.zip','Kumpulan ikon vektor tema sosial media & bisnis.',NULL,'2026-08-11 05:18:33','2026-08-11 05:18:33');

/*Table structure for table `bukti_upload` */

DROP TABLE IF EXISTS `bukti_upload`;

CREATE TABLE `bukti_upload` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(11) unsigned NOT NULL,
  `platform_id` int(11) unsigned DEFAULT NULL,
  `link_postingan` varchar(255) NOT NULL,
  `uploaded_by` int(11) unsigned NOT NULL,
  `uploaded_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `fk_bukti_platform_id` (`platform_id`),
  KEY `fk_bukti_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_bukti_content_id` FOREIGN KEY (`content_id`) REFERENCES `content_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bukti_platform_id` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_bukti_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `bukti_upload` */

insert  into `bukti_upload`(`id`,`content_id`,`platform_id`,`link_postingan`,`uploaded_by`,`uploaded_at`) values 
(1,1,NULL,'https://instagram.com/p/C8921_SMMS_AI',5,'2026-08-05 05:46:01'),
(2,8,NULL,'https://tiktok.com/@smms_official/video/739120349',5,'2026-08-05 05:46:02');

/*Table structure for table `content_plan` */

DROP TABLE IF EXISTS `content_plan`;

CREATE TABLE `content_plan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `judul_konten` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `tanggal_publish` date DEFAULT NULL,
  `jenis_konten_id` int(11) unsigned DEFAULT NULL,
  `content_type_id` int(11) unsigned DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'ide_diajukan',
  `dibuat_oleh` int(11) unsigned NOT NULL,
  `assigned_designer` int(11) unsigned DEFAULT NULL,
  `assigned_uploader` int(11) unsigned DEFAULT NULL,
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
  CONSTRAINT `fk_cp_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cp_jenis_konten` FOREIGN KEY (`jenis_konten_id`) REFERENCES `jenis_konten` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cp_uploader` FOREIGN KEY (`assigned_uploader`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `content_plan` */

insert  into `content_plan`(`id`,`judul_konten`,`deskripsi`,`caption`,`tanggal_publish`,`jenis_konten_id`,`content_type_id`,`status`,`dibuat_oleh`,`assigned_designer`,`assigned_uploader`,`created_at`,`updated_at`) values 
(1,'Peluncuran Fitur Baru AI Assistant Q3','Konten promosi fitur AI assistant teranyar untuk membantu tim meng-generate caption.','Capek bikin caption dari nol? ✨ Sekarang SMMS sudah dilengkapi AI Assistant! Buat caption menarik untuk Instagram dan TikTok dalam hitungan detik. Coba sekarang!','2026-08-05',2,2,'published',4,4,5,'2026-08-05 05:46:01','2026-08-05 05:46:01'),
(2,'5 Tips Mengoptimalkan Bio Instagram Usaha','Konten edukasi mengenai elemen penting di Bio IG agar mengkonversi pengunjung jadi pembeli.','Bio IG kamu masih sepi pembeli? ? Simak 5 elemen wajib ini:\\n1. Niche Jelas\\n2. Call to Action\\n3. Link Utama\\nSimpan postingan ini ya!','2026-08-10',2,1,'in_design',4,4,5,'2026-08-05 05:46:01','2026-08-05 05:46:01'),
(3,'Promo Spesial Diskon Kemerdekaan 17 Agustus','Visual banner promo diskon 45% khusus hari Kemerdekaan Republik Indonesia.','MERDEKA! ?? Dapatkan diskon 45% untuk semua paket langganan tahunan. Gunakan kode: MERDEKA45. Berlaku hingga 17 Agustus!','2026-08-17',3,2,'review_design',4,4,5,'2026-08-05 05:46:01','2026-08-05 05:46:01'),
(4,'Tutorial Singkat Penggunaan Fitur Content Plan','Video reels tutorial 30 detik mengajarkan alur pengajuan ide hingga publish.','Biar kerjaan tim medsos makin efisien, begini cara koordinasi ide pakai SMMS! Nggak ada lagi drama lupa posting ?','2026-08-12',1,1,'revisi',4,4,5,'2026-08-05 05:46:01','2026-08-05 05:46:01'),
(5,'Behind The Scene Suasana Kerja Tim Kreatif','Foto/video santai momen brainstorming tim kreatif di kantor.','Di balik postingan yang aesthetic, ada tim yang heboh diskusi ide tiap pagi ☕️ Mana momen BTS favorit kalian?','2026-08-15',3,4,'acc_ide',4,4,5,'2026-08-05 05:46:01','2026-08-05 05:46:01'),
(6,'Quotes Motivasi Produktivitas Senin Pagi','Kata motivasi pendek untuk menyapa audiens di awal minggu.','Awal minggu baru, semangat baru! Fokus pada langkah kecil hari ini. Happy Monday! ?','2026-08-18',3,3,'ide_diajukan',4,4,5,'2026-08-05 05:46:01','2026-08-05 05:46:01'),
(7,'Infografis Tren Social Media Marketing 2026','Thread & Infografis mengenai pergeseran algoritma dan tren short-video.','Tren konten apa yang paling mendominasi di 2026? Geser slide untuk pelajari data lengkapnya! ?','2026-08-20',2,1,'acc_final',3,4,5,'2026-08-05 05:46:01','2026-08-05 05:46:01'),
(8,'Video Reels Giveaway Perayaan 10K Follower','Konten giveaway dengan hadiah saldo e-wallet untuk followers aktif.','GIVEAWAY TIME! ? Terima kasih 10.000 followers! Mau dapat saldo Gopay 500rb? Tulis harapanmu di kolom komentar dan tag 3 temen kamu!','2026-08-01',1,4,'published',4,4,5,'2026-08-05 05:46:01','2026-08-05 05:46:01'),
(9,'aoiwhehuioaher','akbfagfagr','Ada sesuatu yang baru dan siap bikin hari-harimu jadi jauh lebih seru! ✨\n\nPernah nggak sih ngerasa butuh solusi yang simpel tapi dampaknya berasa banget? Nah, kali ini kami punya sesuatu yang spesial banget khusus buat kamu. ?\n\nGak cuma sekadar menarik, tapi ini dirancang buat bantu kamu [masukkan manfaat utama dari produk/topik kamu] dengan cara yang jauh lebih praktis dan efisien. \n\nPenasaran mau coba atau tahu detail selengkapnya? ?\n\n? **Drop pendapat kamu di kolom komentar sekarang, atau klik link di bio untuk informasi lengkapnya ya!**\n\n---\n#InspirasiHariIni #InfoTerkini #GayaHidupModern #RekomendasiTerbaik #TrendingInstagram #ContentCreatorIndonesia #SpesialUntukmu #LangkahBaru','2026-08-13',2,2,'in_design',7,4,5,'2026-08-11 07:13:38','2026-08-11 07:15:20');

/*Table structure for table `content_platforms` */

DROP TABLE IF EXISTS `content_platforms`;

CREATE TABLE `content_platforms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(11) unsigned NOT NULL,
  `platform_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id_platform_id` (`content_id`,`platform_id`),
  KEY `fk_cplat_platform_id` (`platform_id`),
  CONSTRAINT `fk_cplat_content_id` FOREIGN KEY (`content_id`) REFERENCES `content_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cplat_platform_id` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `content_platforms` */

insert  into `content_platforms`(`id`,`content_id`,`platform_id`) values 
(1,9,1);

/*Table structure for table `content_status_log` */

DROP TABLE IF EXISTS `content_status_log`;

CREATE TABLE `content_status_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(11) unsigned NOT NULL,
  `status_lama` varchar(30) DEFAULT NULL,
  `status_baru` varchar(30) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_csl_content_id` FOREIGN KEY (`content_id`) REFERENCES `content_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_csl_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `content_status_log` */

insert  into `content_status_log`(`id`,`content_id`,`status_lama`,`status_baru`,`user_id`,`catatan`,`created_at`) values 
(1,1,NULL,'ide_diajukan',4,'Pengajuan ide promo AI Assistant','2026-08-05 05:46:01'),
(2,1,'ide_diajukan','acc_ide',3,'Ide menarik, silakan lanjut desain','2026-08-05 05:46:01'),
(3,1,'acc_ide','in_design',4,'Mulai pengerjaan carousel 5 slide','2026-08-05 05:46:01'),
(4,1,'in_design','review_design',4,'Desain slide 1-5 selesai diajukan','2026-08-05 05:46:01'),
(5,1,'review_design','acc_final',3,'Desain & caption sudah oke','2026-08-05 05:46:01'),
(6,1,'acc_final','published',5,'Konten telah diposting di IG Feeds','2026-08-05 05:46:01'),
(7,2,NULL,'ide_diajukan',4,'Ide edukasi Bio IG','2026-08-05 05:46:01'),
(8,2,'ide_diajukan','acc_ide',3,'ACC ide edukasi','2026-08-05 05:46:01'),
(9,2,'acc_ide','in_design',4,'Proses pembuatan visual slide','2026-08-05 05:46:01'),
(10,3,NULL,'ide_diajukan',4,'Pengajuan ide promo 17-an','2026-08-05 05:46:01'),
(11,3,'ide_diajukan','acc_ide',3,'ACC promo kemerdekaan','2026-08-05 05:46:01'),
(12,3,'acc_ide','in_design',4,'Desain poster merah putih','2026-08-05 05:46:01'),
(13,3,'in_design','review_design',4,'Mohon review desain poster promo','2026-08-05 05:46:01'),
(14,4,NULL,'ide_diajukan',4,'Ide video tutorial','2026-08-05 05:46:01'),
(15,4,'ide_diajukan','acc_ide',3,'ACC video Reels','2026-08-05 05:46:01'),
(16,4,'acc_ide','in_design',4,'Editing video Reels','2026-08-05 05:46:01'),
(17,4,'in_design','review_design',4,'Video siap ditinjau','2026-08-05 05:46:01'),
(18,4,'review_design','revisi',3,'Tolong percepat transisi video di detik ke-15 dan perjelas teks subtitle.','2026-08-05 05:46:01'),
(19,5,NULL,'ide_diajukan',4,'Ide konten BTS tim','2026-08-05 05:46:01'),
(20,5,'ide_diajukan','acc_ide',3,'Bagus untuk engagement, silakan foto/video.','2026-08-05 05:46:01'),
(21,6,NULL,'ide_diajukan',4,'Pengajuan ide Quotes Senin','2026-08-05 05:46:01'),
(22,7,NULL,'ide_diajukan',3,'Inisiasi ide riset tren 2026','2026-08-05 05:46:01'),
(23,7,'ide_diajukan','acc_ide',3,'ACC otomatis oleh Manager','2026-08-05 05:46:01'),
(24,7,'acc_ide','in_design',4,'Penyusunan grafik & layout slide','2026-08-05 05:46:01'),
(25,7,'in_design','review_design',4,'Slide infografis lengkap siap ditinjau','2026-08-05 05:46:01'),
(26,7,'review_design','acc_final',3,'Sangat bagus dan akurat. Tinggal scheduling.','2026-08-05 05:46:01'),
(27,8,NULL,'ide_diajukan',4,'Ide Giveaway 10k follower','2026-08-05 05:46:02'),
(28,8,'ide_diajukan','acc_ide',3,'ACC giveaway','2026-08-05 05:46:02'),
(29,8,'acc_ide','in_design',4,'Pengerjaan video reels','2026-08-05 05:46:02'),
(30,8,'in_design','review_design',4,'Review video giveaway','2026-08-05 05:46:02'),
(31,8,'review_design','acc_final',3,'ACC Final','2026-08-05 05:46:02'),
(32,8,'acc_final','published',5,'Telah diunggah di TikTok official','2026-08-05 05:46:02'),
(33,9,NULL,'ide_diajukan',7,'Ide baru diajukan.','2026-08-11 07:13:38'),
(34,9,'ide_diajukan','acc_ide',3,NULL,'2026-08-11 07:13:52'),
(35,9,'acc_ide','in_design',3,'Otomatis masuk ke status In Design setelah ACC Manager','2026-08-11 07:13:52');

/*Table structure for table `content_types` */

DROP TABLE IF EXISTS `content_types`;

CREATE TABLE `content_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama_type` varchar(50) NOT NULL,
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
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
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
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`version`,`class`,`group`,`namespace`,`time`,`batch`) values 
(1,'2026-08-05-000001','App\\Database\\Migrations\\CreateRoles','default','App',1785908759,1),
(2,'2026-08-05-000002','App\\Database\\Migrations\\CreateUsers','default','App',1785908759,1),
(3,'2026-08-05-000003','App\\Database\\Migrations\\CreatePlatforms','default','App',1785908759,1),
(4,'2026-08-05-000004','App\\Database\\Migrations\\CreateJenisKonten','default','App',1785908759,1),
(5,'2026-08-05-000005','App\\Database\\Migrations\\CreateContentTypes','default','App',1785908759,1),
(6,'2026-08-05-000006','App\\Database\\Migrations\\CreateContentPlan','default','App',1785908759,1),
(7,'2026-08-05-000007','App\\Database\\Migrations\\CreateContentStatusLog','default','App',1785908759,1),
(8,'2026-08-05-000008','App\\Database\\Migrations\\CreateContentPlatforms','default','App',1785908759,1),
(9,'2026-08-05-000009','App\\Database\\Migrations\\CreateBuktiUpload','default','App',1785908759,1),
(10,'2026-08-05-000010','App\\Database\\Migrations\\AddCaptionToContentPlan','default','App',1785908759,1),
(11,'2026-08-05-000011','App\\Database\\Migrations\\CreateAiGenerationLog','default','App',1785908759,1),
(12,'2026-08-10-013849','App\\Database\\Migrations\\CreateNotifications','default','App',1786425504,2),
(13,'2026-08-11-041700','App\\Database\\Migrations\\CreateTrendBankTable','default','App',1786425504,2),
(14,'2026-08-11-041900','App\\Database\\Migrations\\CreateBrandAssetsTable','default','App',1786425504,2);

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `judul` varchar(100) NOT NULL,
  `pesan` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `notifications` */

insert  into `notifications`(`id`,`user_id`,`judul`,`pesan`,`url`,`is_read`,`created_at`) values 
(1,3,'Ide Konten Baru','Ada ide konten baru yang menunggu review Anda: \"aoiwhehuioaher\".','/dashboard/content-plan',0,'2026-08-11 07:13:38'),
(2,7,'Ide Disetujui','Ide konten \"aoiwhehuioaher\" telah disetujui oleh Manager.','/dashboard/content-plan',0,'2026-08-11 07:13:52'),
(3,4,'Tugas Baru Untukmu','Kamu ditugaskan mengerjakan desain untuk konten \"aoiwhehuioaher\".','/dashboard/content-plan',0,'2026-08-11 07:13:52');

/*Table structure for table `platforms` */

DROP TABLE IF EXISTS `platforms`;

CREATE TABLE `platforms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama_platform` varchar(50) NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
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
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `kode_role` varchar(30) NOT NULL,
  `nama_role` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_role` (`kode_role`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `roles` */

insert  into `roles`(`id`,`kode_role`,`nama_role`) values 
(1,'superadmin','Superadmin'),
(2,'owner','Owner'),
(3,'manager','Manager'),
(4,'content_creator','Content Creator'),
(5,'admin_medsos','Admin Media Sosial'),
(9,'creative_team','Creative Team');

/*Table structure for table `trend_bank` */

DROP TABLE IF EXISTS `trend_bank`;

CREATE TABLE `trend_bank` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) NOT NULL,
  `badge` varchar(50) NOT NULL DEFAULT 'Viral',
  `desk` text DEFAULT NULL,
  `example` text DEFAULT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'TikTok & Reels',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `trend_bank` */

insert  into `trend_bank`(`id`,`judul`,`badge`,`desk`,`example`,`category`,`status`,`created_at`,`updated_at`) values 
(1,'POV & Problem Relatable','Highly Viral','Tampilkan situasi sehari-hari audiens yang menghibur atau membuat mereka merasa relate.','\"POV: Ketika kamu udah coba 10 cara tapi tetap aja gagal...\"','TikTok & Reels','aktif','2026-08-11 05:18:33','2026-08-11 05:18:33'),
(2,'Stop Scrolling Hook','High Retention','Kalimat pembuka tajam di 3 detik pertama yang menahan audiens agar tidak menggeser layar.','\"Jangan beli ini sebelum kamu dengar rahasia ini!\"','Short Video','aktif','2026-08-11 05:18:33','2026-08-11 05:18:33'),
(3,'Before vs After Transformation','Visual Proof','Perbandingan hasil penggunaan produk/jasa dari kondisi awal ke kondisi memuaskan.','\"Hasil perombakan desain feed sosial media dalam 7 hari...\"','Reels & Carousel','aktif','2026-08-11 05:18:33','2026-08-11 05:18:33'),
(4,'3 Kesalahan Fatal (3 Fatal Mistakes)','Edukasi','Peringatan edukatif yang memicu rasa ingin tahu audiens akan kesalahan yang tidak mereka sadari.','\"3 kesalahan pembuatan ide konten yang buat omset kamu stagnan...\"','Edukasi & Tips','aktif','2026-08-11 05:18:33','2026-08-11 05:18:33'),
(5,'Quick Tutorial Under 15s','High Completion','Tutorial singkat & padat tanpa bertele-tele langsung memberikan solusi berharga.','\"Cara mudah membuat AI Caption dalam 10 detik tanpa aplikasi...\"','Tutorial','aktif','2026-08-11 05:18:33','2026-08-11 05:18:33'),
(6,'Behind The Scenes (BTS)','Build Trust','Perlihatkan proses kerja tim, persiapan pesanan, atau kehebohan kantor.','\"Di balik layar penyiapan promo gajian tim marketing kami...\"','Branding','aktif','2026-08-11 05:18:33','2026-08-11 05:18:33');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) unsigned DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`nama`,`email`,`password`,`role_id`,`status`,`created_at`,`updated_at`) values 
(1,'Superadmin','admin@smm.local','$2y$10$6c.lwu7LydZ.ohSS5zxhUuFTuCDLfvv2vtAWp0FCBlNyIMqPUDqjC',1,'aktif','2026-08-05 05:46:01','2026-08-11 05:18:33'),
(2,'Budi Owner','owner@smm.local','$2y$10$uzj2LkJzs/l2uhH1cydnWu6iLwrz0ErZGkNw7BAHUSGkhzyfv8pWy',2,'aktif','2026-08-05 05:46:01','2026-08-05 05:46:01'),
(3,'Siti Manager','manager@smm.local','$2y$10$CMvgP/ScEuLbSd6f5e7go.YKJTjfIOXsMs0gWZ0zGpkp0YXmiZnRu',3,'aktif','2026-08-05 05:46:01','2026-08-05 05:46:01'),
(4,'Rian Creator','creator@smm.local','$2y$10$tZ1t5xrqtbNSPfgHtnm4wOlR/6/zwn2df6DY6UliXMmmH4G5jDF42',4,'aktif','2026-08-05 05:46:01','2026-08-05 05:46:01'),
(5,'Dewi Sosmed','sosmed@smm.local','$2y$10$6v9AjlRff4FiVclXbu7EtOU2GzXdVx.2ZaXioL/iT5GhGMV4YVc5C',5,'aktif','2026-08-05 05:46:01','2026-08-05 05:46:01'),
(7,'Ramzy','creatif@smm.local','$2y$10$8d4CY7FNJTFpo12flz7dhezIM63YVa1ManyoONwxTXzxpjKwyYU3C',9,'aktif','2026-08-11 05:41:28','2026-08-11 05:41:28');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
