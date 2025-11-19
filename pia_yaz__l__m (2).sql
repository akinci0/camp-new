-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3306
-- Üretim Zamanı: 18 Kas 2025, 14:17:07
-- Sunucu sürümü: 8.3.0
-- PHP Sürümü: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `pia_yazılım`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `basvurular`
--

DROP TABLE IF EXISTS `basvurular`;
CREATE TABLE IF NOT EXISTS `basvurular` (
  `tc_kimlik` varchar(11) COLLATE utf8mb4_turkish_ci NOT NULL,
  `ad_soyad` varchar(200) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `egitim_durumu` enum('lisans','yuksek_lisans','diger') COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `motivasyon_metni` text COLLATE utf8mb4_turkish_ci,
  `ozgecmis_path` varchar(500) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `ogrenci_belgesi_path` varchar(500) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `ip_adresi` varchar(45) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `durum` enum('beklemede','incelendi','kabul','ret') COLLATE utf8mb4_turkish_ci DEFAULT 'beklemede',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tc_kimlik`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `basvurular`
--

INSERT INTO `basvurular` (`tc_kimlik`, `ad_soyad`, `email`, `egitim_durumu`, `motivasyon_metni`, `ozgecmis_path`, `ogrenci_belgesi_path`, `ip_adresi`, `durum`, `created_at`, `updated_at`) VALUES
('10000000000', 'Ayşe Yılmaz', 'ayse.yilmaz@mail.com', 'lisans', 'Yazılıma olan tutkum ve öğrenme isteğimle bu ekibe katılmak istiyorum.', '/path/to/ayse_cv.pdf', '/path/to/ayse_ogrenci.pdf', '192.168.1.101', 'beklemede', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000001', 'Burak Kaya', 'burak.kaya@mail.com', 'yuksek_lisans', 'Veri analitiği alanındaki bilgilerimle projelerinize katkı sağlayabilirim.', '/path/to/burak_cv.pdf', '/path/to/burak_ogrenci.pdf', '192.168.1.102', 'incelendi', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000002', 'Ceren Demir', 'ceren.demir@mail.com', 'lisans', 'Frontend geliştirme becerilerimi kullanarak kullanıcı dostu arayüzler tasarlayabilirim.', '/path/to/ceren_cv.pdf', '/path/to/ceren_ogrenci.pdf', '192.168.1.103', 'beklemede', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000003', 'Deniz Öztürk', 'deniz.ozturk@mail.com', 'diger', 'Proje yönetimi ve ekip çalışması tecrübem bulunmaktadır.', '/path/to/deniz_cv.pdf', '/path/to/deniz_ogrenci.pdf', '192.168.1.104', 'incelendi', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000004', 'Emre Şahin', 'emre.sahin@mail.com', 'lisans', 'Full-stack geliştirme konusunda kendimi sürekli geliştiriyorum.', '/path/to/emre_cv.pdf', '/path/to/emre_ogrenci.pdf', '192.168.1.105', 'beklemede', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000005', 'Fatma Yıldız', 'fatma.yildiz@mail.com', 'yuksek_lisans', 'Makine öğrenimi algoritmaları üzerine derinlemesine bilgi sahibiyim.', '/path/to/fatma_cv.pdf', '/path/to/fatma_ogrenci.pdf', '192.168.1.106', 'kabul', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000006', 'Gökhan Arslan', 'gokhan.arslan@mail.com', 'lisans', 'Mobil uygulama geliştirme (iOS/Android) deneyimim mevcuttur.', '/path/to/gokhan_cv.pdf', '/path/to/gokhan_ogrenci.pdf', '192.168.1.107', 'beklemede', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000007', 'Hande Güneş', 'hande.gunes@mail.com', 'lisans', 'Siber güvenlik alanına özel bir ilgim ve projelere destek verme isteğim var.', '/path/to/hande_cv.pdf', '/path/to/hande_ogrenci.pdf', '192.168.1.108', '', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000008', 'İlker Tekin', 'ilker.tekin@mail.com', 'yuksek_lisans', 'Büyük veri sistemleri ve bulut bilişim konularında uzmanlığım bulunuyor.', '/path/to/ilker_cv.pdf', '/path/to/ilker_ogrenci.pdf', '192.168.1.109', 'beklemede', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000009', 'Jale Kılıç', 'jale.kilic@mail.com', 'lisans', 'Oyun programlama ve Unity/Unreal Engine tecrübem ile ekibe katılmak istiyorum.', '/path/to/jale_cv.pdf', '/path/to/jale_ogrenci.pdf', '192.168.1.110', 'incelendi', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000010', 'Kemal Boz', 'kemal.boz@mail.com', 'diger', 'Finansal yazılımlar konusunda özel bir yetkinliğe sahibim.', '/path/to/kemal_cv.pdf', '/path/to/kemal_ogrenci.pdf', '192.168.1.111', 'beklemede', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000011', 'Leman Sönmez', 'leman.sonmez@mail.com', 'lisans', 'Açık kaynak projelerine aktif katkı sağlama isteğiyle başvuruyorum.', '/path/to/leman_cv.pdf', '/path/to/leman_ogrenci.pdf', '192.168.1.112', 'kabul', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000012', 'Mert Akın', 'mert.akin@mail.com', 'yuksek_lisans', 'Sistem mühendisliği ve DevOps pratiklerine hakimim.', '/path/to/mert_cv.pdf', '/path/to/mert_ogrenci.pdf', '192.168.1.113', '', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000013', 'Narin Çelik', 'narin.celik@mail.com', 'lisans', 'Veritabanı tasarımı ve SQL optimizasyonu alanında yetkinim.', '/path/to/narin_cv.pdf', '/path/to/narin_ogrenci.pdf', '192.168.1.114', 'beklemede', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000014', 'Okan Polat', 'okan.polat@mail.com', 'lisans', 'Python ile otomasyon ve scripting tecrübemle ekibinize destek olmak isterim.', '/path/to/okan_cv.pdf', '/path/to/okan_ogrenci.pdf', '192.168.1.115', 'incelendi', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000015', 'Pınar Can', 'pinar.can@mail.com', 'diger', 'İş zekası (BI) ve raporlama araçlarını etkin kullanıyorum.', '/path/to/pinar_cv.pdf', '/path/to/pinar_ogrenci.pdf', '192.168.1.116', 'beklemede', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000016', 'Ramazan Ece', 'ramazan.ece@mail.com', 'lisans', 'E-ticaret sistemleri geliştirme tecrübem bulunmaktadır.', '/path/to/ramazan_cv.pdf', '/path/to/ramazan_ogrenci.pdf', '192.168.1.117', 'kabul', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000017', 'Selin Aktaş', 'selin.aktas@mail.com', 'yuksek_lisans', 'NLP ve metin madenciliği alanlarında derin araştırmalar yaptım.', '/path/to/selin_cv.pdf', '/path/to/selin_ogrenci.pdf', '192.168.1.118', 'beklemede', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000018', 'Tuna Işık', 'tuna.isik@mail.com', 'lisans', 'JavaScript frameworkleri (React, Vue) ile modern uygulamalar geliştirebilirim.', '/path/to/tuna_cv.pdf', '/path/to/tuna_ogrenci.pdf', '192.168.1.119', '', '2025-11-18 14:15:00', '2025-11-18 14:15:00'),
('10000000019', 'Umut Duman', 'umut.duman@mail.com', 'lisans', 'Test otomasyonu ve kalite güvencesi konularında deneyimliyim.', '/path/to/umut_cv.pdf', '/path/to/umut_ogrenci.pdf', '192.168.1.120', 'incelendi', '2025-11-18 14:15:00', '2025-11-18 14:15:00');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `eski_basvurular`
--

DROP TABLE IF EXISTS `eski_basvurular`;
CREATE TABLE IF NOT EXISTS `eski_basvurular` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tc_kimlik` varchar(11) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `ad_soyad` varchar(200) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `egitim_durumu` enum('lisans','yuksek_lisans','diger') COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `motivasyon_metni` text COLLATE utf8mb4_turkish_ci,
  `ozgecmis_path` varchar(500) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `ogrenci_belgesi_path` varchar(500) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `ip_adresi` varchar(45) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `durum` enum('kabul','ret','iptal') COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `basvuru_tarihi` timestamp NULL DEFAULT NULL,
  `arsiv_tarihi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ing_test_sonuc`
--

DROP TABLE IF EXISTS `ing_test_sonuc`;
CREATE TABLE IF NOT EXISTS `ing_test_sonuc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tc_kimlik` varchar(11) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `seviye` enum('A1','A2','B1','B2') CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `puan` int DEFAULT NULL,
  `test_tarihi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tc_kimlik` (`tc_kimlik`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `projeler`
--

DROP TABLE IF EXISTS `projeler`;
CREATE TABLE IF NOT EXISTS `projeler` (
  `id` int NOT NULL AUTO_INCREMENT,
  `basvuru_tc` varchar(11) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `proje_adi` varchar(255) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `aciklama` text COLLATE utf8mb4_turkish_ci,
  `link` varchar(500) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `basvuru_tc` (`basvuru_tc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
