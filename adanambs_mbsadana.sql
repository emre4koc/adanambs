-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 04 Ağu 2026, 13:27:58
-- Sunucu sürümü: 10.6.27-MariaDB-cll-lve
-- PHP Sürümü: 8.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `adanambs_mbsadana`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bildirimler`
--

CREATE TABLE `bildirimler` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mesaj` text NOT NULL,
  `okundu` tinyint(1) NOT NULL DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `disiplin_raporlari`
--

CREATE TABLE `disiplin_raporlari` (
  `id` int(11) NOT NULL,
  `rapor_id` int(11) NOT NULL,
  `rapor_tipi` enum('gozlemci','hakem') NOT NULL,
  `rapor_no` int(11) NOT NULL,
  `dosya_yolu` varchar(500) NOT NULL,
  `olusturma_tarihi` timestamp NULL DEFAULT current_timestamp(),
  `durum` enum('beklemede','onaylandi','reddedildi') DEFAULT 'beklemede',
  `onaylayan_id` int(11) DEFAULT NULL,
  `onay_tarihi` datetime DEFAULT NULL,
  `red_notu` text DEFAULT NULL,
  `degistirildi` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dogum_gunu_mailleri`
--

CREATE TABLE `dogum_gunu_mailleri` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mail_tarihi` date NOT NULL,
  `gonderim_zamani` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `duyurular`
--

CREATE TABLE `duyurular` (
  `id` int(11) NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `icerik` text NOT NULL,
  `tarih` date DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `arsiv` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `duyurular`
--

INSERT INTO `duyurular` (`id`, `baslik`, `icerik`, `tarih`, `olusturma_tarihi`, `arsiv`) VALUES
(24, 'ESAME LİSTELERİ HAKKINDA', 'SAYIN HOCALARIMIZ \r\nTFF AMATÖR SİSTEMDE HANGİ KATAGORİ OLURSA OLSUN KAYITLI MAÇLARIN ESAME LİSTELERİ ONAYLANMAYAN HİÇ BİR MAÇ OYNATILMAYACAKTIR.', NULL, '2026-05-07 19:14:34', 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `duyuru_okundu`
--

CREATE TABLE `duyuru_okundu` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `duyuru_id` int(11) NOT NULL,
  `okunma_tarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Tablo döküm verisi `duyuru_okundu`
--

INSERT INTO `duyuru_okundu` (`id`, `user_id`, `duyuru_id`, `okunma_tarihi`) VALUES
(1, 1047, 24, '2026-08-04 13:11:00'),
(2, 2587, 24, '2026-08-04 13:11:57');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `egitimler`
--

CREATE TABLE `egitimler` (
  `id` int(11) NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `tip` enum('video','sunum') NOT NULL,
  `dosya_yolu` varchar(500) NOT NULL,
  `dosya_boyutu` int(11) DEFAULT NULL,
  `yukleme_tarihi` datetime DEFAULT current_timestamp(),
  `yukleyen_id` int(11) DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `goruntulenme_sayisi` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `egitimler`
--

INSERT INTO `egitimler` (`id`, `baslik`, `aciklama`, `tip`, `dosya_yolu`, `dosya_boyutu`, `yukleme_tarihi`, `yukleyen_id`, `aktif`, `goruntulenme_sayisi`) VALUES
(1, 'Mhk Talimatı', 'TFF Yönetim Kurulu\'nun 30.12.2025 tarih ve 65 sayılı toplantısında alınan kararla Merkez Hakem Kurulu Talimatı\'nda değişiklik yapılmıştır.', 'sunum', 'uploads/egitimler/6989f61fe17ea_1770649119.pdf', 809165, '2026-02-09 17:58:39', 1047, 1, 66),
(3, 'Hareketlenme ve Yer Alma', '2026 Ocak Ayı İl Eğitimi Kapsamında Sunu', 'sunum', 'uploads/egitimler/698a3f87112fe_1770667911.pptx', 57273702, '2026-02-09 23:11:51', 1047, 1, 58),
(4, '2026 Ocak MHK Talimatı', '2026 Ocak Ayı İl Eğitimi Kapsamında MHK Talimatı', 'sunum', 'uploads/egitimler/698a466a27046_1770669674.pptx', 71316, '2026-02-09 23:41:14', 1047, 1, 64),
(5, 'Teknik Alan Yönetiminde 4. Hakemin Rolü', '2026 Ocak Ayı İl Eğitimi Kapsamında Sunu', 'sunum', 'uploads/egitimler/698a469cdcf6c_1770669724.pptx', 120937, '2026-02-09 23:42:04', 1047, 1, 104),
(7, 'TEKNİK ALAN YÖNETİMİ', 'Şubat il eğitimi kapsamında', 'sunum', 'uploads/egitimler/699ebf25dd97b_1772011301.pptx', 75164, '2026-02-25 12:21:41', 1047, 1, 23),
(8, 'KOLLARIN KURAL DIŞI KULLANIMI', 'Şubat il eğitimi kapsamında', 'sunum', 'uploads/egitimler/1.DERS KOLLARIN KURAL DIŞI KULLANIMI.pptx', 739548867, '2026-02-25 12:35:42', 1047, 1, 28),
(9, 'DİSİPLİNE EDİLMEZSE', 'Şubat il eğitimi kapsamında', 'video', 'uploads/egitimler/DİSİPLİNE EDİLMEZSE.mp4', 100000000, '2026-02-25 12:50:41', 1047, 1, 28),
(10, 'HIZLI DİSİPLİN KARARI 2', 'Şubat il eğitimi kapsamında', 'video', 'uploads/egitimler/HIZLI DİSİPLİN KARARI 2.mp4', 40000000, '2026-02-25 12:50:41', 1047, 1, 17),
(11, 'HIZLI DİSİPLİN KARARI 3', 'Şubat il eğitimi kapsamında', 'video', 'uploads/egitimler/HIZLI DİSİPLİN KARARI 3.mp4', 6000000, '2026-02-25 12:50:41', 1047, 1, 11),
(12, 'HIZLI DİSİPLİN KARARI', 'Şubat il eğitimi kapsamında', 'video', 'uploads/egitimler/HIZLI DİSİPLİN KARARI.mp4', 70000000, '2026-02-25 12:50:41', 1047, 1, 11),
(13, 'İKİNCİ SARI DEĞİL DOĞRUDAN KIRMIZI OLMA', 'Şubat il eğitimi kapsamında', 'video', 'uploads/egitimler/İKİNCİ SARI DEĞİL DOĞRUDAN KIRMIZI OLMA.mp4', 150000000, '2026-02-25 12:50:41', 1047, 1, 17),
(14, 'YARDIMCI HAKEM 1', 'Şubat il eğitimi kapsamında', 'video', 'uploads/egitimler/YARDIMCI HAKEM 1.mp4', 30000000, '2026-02-25 12:50:41', 1047, 1, 19),
(15, 'YARDIMCI HAKEM 2', 'Şubat il eğitimi kapsamında', 'video', 'uploads/egitimler/YARDIMCI HAKEM 2.mp4', 120000000, '2026-02-25 12:50:41', 1047, 1, 15);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `egitim_goruntulemeler`
--

CREATE TABLE `egitim_goruntulemeler` (
  `id` int(11) NOT NULL,
  `egitim_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goruntulenme_tarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `klasmanlar`
--

CREATE TABLE `klasmanlar` (
  `id` int(11) NOT NULL,
  `ad` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `klasmanlar`
--

INSERT INTO `klasmanlar` (`id`, `ad`) VALUES
(14, 'Aday Hakem'),
(16, 'Bölgesel Gözlemci'),
(11, 'Bölgesel Hakem'),
(12, 'Bölgesel Yardımcı Hakem'),
(17, 'İl Gözlemcisi'),
(13, 'İl Hakemi'),
(15, 'Klasman Gözlemcisi'),
(9, 'Klasman Hakemi'),
(10, 'Klasman Yardımcı Hakemi'),
(7, 'Üst Klasman Hakemi'),
(8, 'Üst Klasman Yardımcı Hakemi');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ligler`
--

CREATE TABLE `ligler` (
  `id` int(11) NOT NULL,
  `ad` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `ligler`
--

INSERT INTO `ligler` (`id`, `ad`) VALUES
(13, 'SÜPER AMATÖR LİG'),
(14, '1. AMATÖR LİG'),
(15, '2. AMATÖR LİG'),
(16, 'U-18'),
(17, 'U-17'),
(18, 'U-16'),
(19, 'U-15'),
(20, 'U-14'),
(21, 'U-13'),
(22, 'U-12'),
(23, 'U-11'),
(24, 'MASTERLER LİGİ'),
(46, 'TEST LİGİ'),
(48, 'U-19 BÖLGESEL GELİŞİM LİGİ'),
(49, 'U-17 YAŞ FUTBOL PLAYOFF'),
(50, 'U-13 YAŞ FUTBOL LİGİ'),
(51, 'ADANA MASTERLER LİGİ'),
(52, 'U-16 TÜRKİYE ŞAMPİYONASI'),
(53, 'U-17 BÖLGESEL GELİŞİM LİGİ'),
(54, '2. KÜME BÜYÜKLER LİGİ'),
(55, 'U-19 ELİT A LİGİ'),
(56, 'ADANA BÖLGE ADLİYESİ FUTBOL'),
(57, 'KOÇ TOPLULUĞU SPOR ŞENLİĞİ'),
(58, 'AOSB FUTBOL TURNUVASI'),
(59, 'U-15 YAŞ FUTBOL PLAYOFF'),
(60, 'U-11 YAŞ FUTBOL LİGİ'),
(61, 'KOZAN / OKULLARARASI KÜÇÜK ERKEK FUTSAL'),
(62, 'OKULLARARASI KÜÇÜK ERKEK FUTSAL'),
(63, 'U-18 YAŞ FUTBOL LİGİ'),
(64, 'U-17 ELİT A LİGİ'),
(65, 'U-18 YAŞ FUTBOL 2.LİGİ'),
(66, 'KOZAN / O.ARASI K. ERKEK FUTSAL YARI FİNAL'),
(68, 'Lig Adı'),
(69, '1. KÜME BÜYÜKLER LİGİ'),
(70, 'U16 GELİŞİM LİGİ'),
(71, 'U17 GELİŞİM LİGİ'),
(72, 'AV. SAVAŞ BEDİR TURNUVASI'),
(73, 'U14 GELİŞİM LİGİ'),
(74, 'U15 GELİŞİM LİGİ'),
(75, 'U19 GELİŞİM LİGİ'),
(76, '21. KÜME BÜYÜKLER LİGİ'),
(77, 'PLAJ FUTBOLU'),
(78, '1. AMATÖR BÜYÜKLER LİGİ'),
(79, 'U18 YAŞ FUTBOL LİGİ'),
(80, 'U14 YAŞ FUTBOL LİGİ'),
(81, 'U12 YAŞ FUTBOL LİGİ'),
(82, 'U16 YAŞ FUTBOL LİGİ'),
(83, 'U16 GEİŞİM LİGİ'),
(84, 'KAYIŞLI FUTBOL TURNUVASI'),
(85, 'MÜRSELOĞLU FUTBOL URNUVASI'),
(86, 'KADINLAR 1. LİG FUTBOL'),
(87, 'U16 GEİLŞİM LİGİ'),
(88, 'AV. SAVAŞ BEDİR TURNUVASI - YARI FİNAL'),
(89, 'SAMET GÜDÜK TURNUVASI'),
(90, 'YUMURTALIK TURNUVASI'),
(91, 'SÜPER AMATÖR'),
(92, 'SÜPER AMATÖR LİGİ'),
(93, 'AV. SAVAŞ BEDİR TURNUVASI - FİNAL'),
(94, 'AV. SAVAŞ BEDİR TURNUVASI - 3.\'LÜK'),
(95, '1. KÜME BÜYÜKLERLİGİ'),
(96, 'ASMMO FUTBOL TURNUVASI'),
(97, 'KADINLAR 1. LİG'),
(98, 'EÜAŞ FUTBOL TURNUVASI'),
(99, 'MEHMET SELİM KİRAZ TURNUVASI'),
(100, 'MEHMET SELİM KİRAZ TURNUVASI'),
(101, 'KADINLAR 1. LİGİ'),
(102, 'FUTSAL (4 MAÇ)'),
(103, 'FUTSAL (6 MAÇ)'),
(104, 'FUTSAL (5 MAÇ)'),
(105, 'EÜAŞ FUTBOL PLAYOFF'),
(106, 'FUTSAL'),
(107, 'U-16 GELİŞİM LİGİ'),
(108, 'U-17 GELİŞİM LİGİ'),
(109, '1. KÜME BÜYÜKLER FUTBOL'),
(110, 'ÇİPP SPOR ŞENLİKLERİ'),
(111, 'U-16 YAŞ FUTBOL LİGİ'),
(112, 'ÇİPP SPOR ŞENLİKLERİ YARI FİNAL/FİNAL'),
(113, 'OKULLARARASI A GENÇ KIZ FUTSAL ( 3 MAÇ )'),
(114, 'OKULLARARASI A GENÇ ERKEK FUTSAL ( 3 MAÇ )'),
(115, 'OKULLARARASI A GENÇ ERKEK FUTSAL (4 MAÇ)'),
(116, 'OKULLARARASI A GENÇ ERKEK FUTSAL (2 MAÇ)'),
(117, 'EÜAŞ FUTBOL YARI FİNAL'),
(118, 'U12 FUTBOL PLAY-OFF'),
(119, 'EÜAŞ FUTBOL YARIFİNAL'),
(120, 'KADINLAR 1.LİG FUTBOL'),
(121, 'U14 GELİŞİM İGİ'),
(122, 'U12 YAŞ FUTBOL LİGİ PLAYOFF'),
(123, 'OKULLARARASI A GENÇ ERKEK FUTSAL ( 4 MAÇ )'),
(124, 'KREDİ YURTLAR'),
(125, 'EÜAŞ FUTBOL - 3.\'LÜK MAÇI'),
(126, 'EÜAŞ FUTBOL -  FİNAL'),
(127, 'OKULLARARASI A GENÇ KIZ FUTSAL (3 MAÇ)'),
(128, 'OKULLARARASI A GENÇ ERKEK FUTSAL (3 MAÇ)'),
(129, 'ASMMO FUTBOL TURNUVASI ÇEYREK FİNAL'),
(130, 'MEHMET SELİM KİRAZ TURNUVASI - ÇEYREK FİNAL'),
(131, 'U12 YAŞ FUTBOL PLAYOFF'),
(132, 'OKULLARARASI GENÇ ERKEK FUTSAL ( 4 MAÇ )'),
(133, 'OKULLARARASI YILDIZ ERKEK FUTBOL'),
(134, 'OKULLARARASI GENÇ ERKEK FUTSAL ( 3 MAÇ )'),
(135, 'MİLLİ TAKIM SEÇMELERİ'),
(136, 'EÜAŞ FUTBOL - FİNAL'),
(137, 'U12 YAŞ FUTBOL - YARI FİNAL'),
(138, 'OKULLARARASI GENÇ KIZ FUTSAL ( 3 MAÇ )'),
(139, 'ASMMO FUTBOL TURNUVASI YARI FİNAL'),
(140, 'MEHMET SELİM KİRAZ TURNUVASI - YARI FİNAL'),
(141, 'KAYIŞLI FUTBOL TURNUVASI - 3.\'LÜK'),
(142, 'KAYIŞLI FUTBOL TURNUVASI - FİNAL'),
(143, 'U12 YAŞ FUTBOL 3.\'LÜK'),
(144, 'U12 YAŞ FUTBOL FİNAL'),
(145, 'YILDIZ ERKEKLER'),
(146, 'MEHMET SELİM KİRAZ TURNUVASI - FİNAL'),
(147, 'MEHMET SELİM KİRAZ TURNUVASI - 3.\'LÜK'),
(148, 'YILDIZ KIZLAR'),
(149, 'OKULLAR ARASI GENÇ KIZLAR FUTSAL (6 MAÇ)'),
(150, 'ASMMO FUTBOL TURNUVASI 3.LÜK'),
(151, 'ASMMO FUTBOL TURNUVASI FİNAL'),
(152, 'OKULLARARASI A GENÇ KIZ- ERKEK FUTSAL (5 MAÇ)'),
(153, 'OKULLARARASI YILDIZ KIZ FUTBOL'),
(154, 'OKULLARARASI YILDIZ ERKEK FUTBOL-ELEME'),
(155, 'OKULLARARASI YILDIZ ERKEK FUTBOL-YARI FİNAL'),
(156, 'OKULLARARASI A GENÇ KIZ FUTSAL (4 MAÇ)'),
(157, '5 OCAK KURTULUŞ KUPASI'),
(158, 'U17 KIZLAR GELİŞİM LİGİ'),
(159, 'U-14 YAŞ FUTBOL LİGİ'),
(160, 'OKULLARARASI YILDIZ KIZ - ERKEK FUTBOL'),
(161, 'OKULLARARASI A GENÇ KIZ - ERKEK FUTSAL ( 4 MAÇ )'),
(162, 'U-19 GELİŞİM LİGİ'),
(163, 'U14 YAŞ FUTBOL PLAYOFF'),
(164, 'U15 YAŞ FUTBOL LİGİ'),
(165, 'U16 YAŞ FUTBOL  PLAYOFF'),
(166, 'ÇİPP FUTBOL TURNUVASI'),
(167, 'ÇİPP FUTBOL TURNUVASI / YARI FİNAL - FİNAL'),
(168, 'GRASSROOTS ÇOCUK FUTBOL ETKİNLİĞİ'),
(169, 'U16 YAŞ FUTBOL PLAYOFF'),
(170, 'U-15 GELİŞİM'),
(171, 'U-14 GELİŞİM'),
(172, '2. KÜME AMATÖR LİG'),
(173, 'OKULLARARASI A GENÇ FUTBOL'),
(174, 'U14 YAŞ FUTBOL BARAJ MAÇI'),
(175, 'U18 YAŞ FUTBOL PLAYOFF'),
(176, 'OKULLARARASI GENÇ ERKEKLER'),
(177, 'OKULLARARASI KÜÇÜK ERKEKLER'),
(178, 'KADIN MİLİİ'),
(179, '1. KÜME BÜYÜKLER LİGİ PLAYOFF'),
(180, 'OKULLARARASI A GENÇ ERKEK FUTBOL'),
(181, 'OKULLARARASI YILDIZ ERKEK FUTSAL - 4 MAÇ'),
(182, 'OKULLARARASI YILDIZ ERKEK FUTSAL - 3 MAÇ'),
(183, 'KÜÇÜK ERKEKLER'),
(184, 'OKULLARARASI A GENÇ KIZ FUTBOL'),
(185, 'U14 YAŞ FUTBOL PLAYOFF BARAJ MAÇI'),
(186, 'OKULLARARASI A GENÇ ERKEK FUTBOL - YARI FİNAL'),
(187, 'OKULLAR ARASI A GENÇ ERKEKLER'),
(188, 'OKULLARARASI A GENÇ ERKEK FUTBOL - YARI  FİNAL'),
(189, 'U14 YAŞ FUTBOL - 3.\'LÜK MAÇI'),
(190, 'U14 YAŞ FUTBOL - FİNAL'),
(191, 'OKULLARARASI A GENÇ ERKEK FUTBOL - 3.\'LÜK MAÇI'),
(192, 'OKULLARARASI A GENÇ ERKEK FUTBOL - FİNAL'),
(193, 'U-16 BARAJ'),
(194, 'OKULARASI YILDIZ ERKEKLER (2 MAÇ)'),
(195, 'U-14 YARI FİNAL'),
(196, 'U16  GELİŞİM LİGİ'),
(197, 'OKULLARARASI KÜÇÜK ERKEK FUTBOL'),
(198, 'KÜÇÜK ERKEKLER FUTBOL'),
(199, 'OKULLARARASI A GENÇ ERKEK FUTBOL - 2 MAÇ'),
(200, 'U18 YAŞ FUTBOL PALYOFF'),
(201, 'OKULLARARASI YILDIZ ERKEK FUTSAL - 5 MAÇ'),
(202, 'U16 YAŞ FUTBOL YARI FİNAL'),
(203, '1. KÜME BÜYÜKLER PLAYOFF'),
(204, 'U14 YAŞ FUTBOL 3.\'LÜK MAÇI'),
(205, 'U14 YAŞ FUTBOL FİNAL'),
(206, 'OKULLARARASI YILDIZ KIZ FUTSAL - 4 MAÇ'),
(207, 'OKULLARARASI YILDIZ ERKEK FUTSAL - 2 MAÇ'),
(208, 'OKULLARARASI YILDIZ ERKEK FUTSAL - YARI FİNAL'),
(209, '18 YAŞ FUTBOL LİGİ PLAYOFF'),
(210, 'OKULLARARASI YILDIZ ERKEK FUTSAL - 3.\'LÜK'),
(211, 'OKULLARARASI YILDIZ ERKEK FUTSAL - FİNAL'),
(212, 'U16 YAŞ FUTBOL 3.\'LÜK MAÇI'),
(213, 'U16 YAŞ FUTBOL FİNAL'),
(214, 'OKULLARARASI KÜÇÜK KIZ FUTBOL'),
(215, 'ADANA MASTRELER LİGİ'),
(216, 'U14  GELİŞİM LİGİ'),
(217, 'OKULLARARASI KÜÇÜK ERKEK FUTBOL - FİNAL MAÇLARI'),
(218, 'OKULLARARASI KÜÇÜK ERKEK FUTSAL - 2 MAÇ'),
(219, 'U18 YAŞ FUTRBOL PLAYOFF'),
(220, 'OKULLARARASI YILDIZ ERKEK FUTSAL -  ÇEYREK FİNAL - 4 MAÇ'),
(221, 'OKULLARARASI YILDIZ ERKEK FUTSAL -  YARI FİNAL - 4 MAÇ'),
(222, 'U17 GELİŞM LİGİ'),
(223, 'U11 YAŞ FUTBOL LİGİ'),
(224, 'OKULLARARASI YILDIZ KIZ FUTSAL - 02 MAÇ'),
(225, 'OKULLARARASI YILDIZ ERKEK FUTSAL - 02 MAÇ'),
(226, 'U14 MİLLİ TAKIM SEÇMELERİ'),
(227, 'U18 YAŞ FUTBOL LİGİ - 3.\'LÜK MAÇI'),
(228, 'U18 YAŞ FUTBOL LİGİ - FİNAL'),
(229, 'U13 YAŞ FUTBOL LİGİ'),
(230, 'U17  GELİŞİM LİGİ'),
(231, 'U15 YAŞ FUTBOL BARAJ MAÇI'),
(232, 'U16 TÜRKİYE ŞAMP. - FİNAL'),
(233, 'KOÇ GRUBU FUTBOL TURNUVASI'),
(234, 'SÜPER AMATÖR LİG - PLAYOFF'),
(235, 'U15 YAŞ FUTBOL LİGİ - PLAYOFF'),
(236, '5X5 B GENÇLER'),
(237, '5X5 A GENÇLER'),
(238, '5X5 B GENÇLER KIZ'),
(239, '3.LÜK 4.LÜK MAÇI A GENÇLER'),
(240, '1.LİK 2.LİK MAÇI A GENÇLER'),
(241, '3.LÜK 4.LÜK MAÇI B GENÇLER'),
(242, '1.LİK 2.LİK MAÇI B GENÇLER'),
(243, '3.LÜK 4.LÜK MAÇI KIZLAR'),
(244, '1.LİK 2.LİK MAÇI KIZLAR'),
(245, 'U15 YAŞ FUTBOL LİGİ - YARI FİNAL'),
(246, 'U15 YAŞ FUTBOL LİGİ - 3 / 4 MAÇI'),
(247, 'U15 YAŞ FUTBOL LİGİ - FİNAL'),
(248, 'SÜPER AMATÖR LİG PLAYOFF'),
(249, 'U18 TÜRKİYE ŞAMPİYONASI'),
(250, 'OKULLARARASI KÜÇÜK KIZ/ERKEK FUTSAL'),
(251, 'KOÇ GRUBU FUTBOL TURNUVASI - YARI FİNAL'),
(252, 'OKULLARARASI KÜÇÜK KIZ/ERKEK FUTSAL - YARI FİNAL'),
(253, 'İMAM HATİP SPOR OYUNLARI / YILDIZLAR - FUTSAL'),
(254, 'İMAM HATİP SPOR OYUNLARI / KÜÇÜKLER - FUTSAL'),
(255, 'OKULLARARASI KÜÇÜK ERKEK FUTSAL - 3\'LÜK'),
(256, 'OKULLARARASI KÜÇÜK KIZ FUTSAL - FİNAL'),
(257, 'OKULLARARASI KÜÇÜK ERKEK FUTSAL FİNAL'),
(258, 'İMAM HATİP SPOR OYUNLARI / YILDIZLAR ÇEYREK FİNAL - FUTSAL'),
(259, 'İMAM HATİP SPOR OYUNLARI / GENÇLER ÇEYREK FİNAL - FUTSAL'),
(260, 'İMAM HATİP SPOR OYUNLARI / KÜÇÜKLER ÇEYREK FİNAL- FUTSAL'),
(261, 'İMAM HATİP SPOR OYUNLARI / GENÇLER YARI FİNAL - FUTSAL'),
(262, 'İMAM HATİP SPOR OYUNLARI / KÜÇÜKLER YARI FİNAL- FUTSAL'),
(263, 'İMAM HATİP SPOR OYUNLARI / YILDIZLAR YARI FİNAL- FUTSAL'),
(264, 'İMAM HATİP SPOR OYUNLARI / KÜÇÜKLER FİNAL - FUTSAL'),
(265, 'İMAM HATİP SPOR OYUNLARI / KÜÇÜKLER 3.\'LÜK - FUTSAL'),
(266, 'İMAM HATİP SPOR OYUNLARI / YILDIZLAR FİNAL- FUTSAL'),
(267, 'İMAM HATİP SPOR OYUNLARI / YILDIZLAR 3.\'LÜK - FUTSAL'),
(268, 'İMAM HATİP SPOR OYUNLARI / GENÇLER FİNAL- FUTSAL'),
(269, 'İMAM HATİP SPOR OYUNLARI / GENÇLER 3.\'LÜK - FUTSAL'),
(270, 'KOÇ GRUBU FUTBOL TURNUVASI - 3/4 LÜK MAÇI'),
(271, 'KOÇ GRUBU FUTBOL TURNUVASI - FİNAL'),
(272, 'ÜNİVERSİTE FUTSAL'),
(273, 'FUTSAL B ERKEKLER'),
(274, 'U18 TÜRKİYE ŞAMPİYONASI - YARI FİNAL'),
(275, 'OKULLARARASI KÜÇÜK ERKEK FUTSAL - 1. TUR ELEME MÜSABAKALARI'),
(276, 'U18 TÜRKİYE ŞAMPİYONASI - FİNAL'),
(277, 'ÜNİVERSİTE FUTSAL KADINLAR YARI FİNAL'),
(278, 'ÜNİVERSİTE FUTSALERKEKLER YARI FİNAL'),
(279, 'KADINLAR 3. LİG'),
(280, 'SAVAŞ BEDİR HALI SAHA TURNUVASI'),
(281, 'ÜNİVERSİTE FUTSAL KADINLAR FİNAL'),
(282, 'ÜNİVERSİTE FUTSAL KADINLAR 3.LÜK'),
(283, 'ÜNİVERSİTE FUTSAL ERKEKLER 3.LÜK'),
(284, 'ÜNİVERSİTE FUTSAL ERKEKLER FİNAL'),
(285, 'AOSB FUTBOL TURNUVASI - ÇEYREK FİNAL'),
(286, 'OKULLARARASI GENÇ B ERKEK FUTSAL'),
(287, 'OKULLARARASI KÜÇÜK KIZ FUTSAL'),
(288, 'OKULLARARASI KÜÇÜK ERKEK FUTSAL  ( 2. TUR )'),
(289, 'OKULLAR ARASI 2.KÜME FUTSAL'),
(290, 'OKULLARARASI B GENÇ KIZLAR FUTSAL'),
(291, 'OKULLARARASI GENÇ B ERKEK FUTSAL ( 1. TUR )'),
(292, 'OKULLARARASI GENÇ B ERKEK FUTSAL ( 2. TUR )'),
(293, 'U11 YAŞ FUTBOL LİGİ PLAYOFF'),
(294, 'OKULLARARASI KÜÇÜK ERKEK FUTSAL  ( YARI FİNAL )'),
(295, 'OKULLARARASI B GENÇ ERKEK FUTSAL ( 2. TUR )'),
(296, 'AOSB FUTBOL TURNUVASI - YARI FİNAL'),
(297, 'OKULLARARASI GENÇ B KIZ FUTSAL - ELEME MAÇLARI'),
(298, 'OKULLARARASI GENÇ B ERKEK FUTSAL - YARI FİNAL'),
(299, 'OKULLARARASI KÜÇÜK KIZ FUTSAL  - FİNAL MAÇLARI'),
(300, 'OKULLARARASI 2.KÜME FUTSAL - YARI FİNAL'),
(301, 'U11 YAŞ FUTBOL PLAYOFF'),
(302, '5X5 ERKEKLER FUTBOL GENÇLİK KUPASI - SON 16'),
(303, '5X5 ERKEKLER FUTBOL GENÇLİK KUPASI - ÇEYREK FİNAL 1'),
(304, '5X5 ERKEKLER FUTBOL GENÇLİK KUPASI - ÇEYREK FİNAL 3'),
(305, '5X5 ERKEKLER FUTBOL GENÇLİK KUPASI - ÇEYREK FİNAL 4'),
(306, '5X5 ERKEKLER FUTBOL GENÇLİK KUPASI - ÇEYREK FİNAL 2'),
(307, 'OKULLARARASI 2.KÜME FUTSAL - 3.\'LÜK'),
(308, 'OKULLARARASI 2.KÜME FUTSAL - FİNAL'),
(309, 'OKULLARARASI KÜÇÜK ERKEK FUTSAL  - FİNAL MAÇLARI'),
(310, 'OKULLARARASI B GENÇ KIZ FUTSAL - FİNAL MAÇLARI'),
(311, 'OKULLARARASI GENÇ B ERKEK FUTSAL - FİNAL MAÇLARI'),
(312, '5X5 KIZLAR FUTBOL GENÇLİK KUPASI - YARIN FİNAL 1'),
(313, '5X5 KIZLAR FUTBOL GENÇLİK KUPASI - YARIN FİNAL 2'),
(314, '5X5 ERKEKLER FUTBOL GENÇLİK KUPASI - YARIN FİNAL 2'),
(315, '5X5 KIZLAR FUTBOL GENÇLİK KUPASI - FİNAL'),
(316, '5X5 KIZLAR FUTBOL GENÇLİK KUPASI - 3.\'LÜK'),
(317, '5X5 ERKEKLER FUTBOL GENÇLİK KUPASI - FİNAL'),
(318, '5X5 ERKEKLER FUTBOL GENÇLİK KUPASI - 3.\'LÜK'),
(319, 'U13 YAŞ FUTBOL LİGİ - PLAYOFF'),
(320, 'ADANA MASTERLER LİGİ - PLAYOFF'),
(321, 'U11 YAŞ FUTBOL LİGİ - YARI FİNAL'),
(322, 'ADANA MASTERLER LİGİ -PLAYOFF'),
(323, 'SAVAŞ BEDİR HALI SAHA TURNUVASI - SON 16 TURU'),
(324, 'U13 YAŞ FUTBOL PLAYOFF'),
(325, 'AOSB FUTBOL TURNUVASI - FİNAL'),
(326, 'ADANA MASTERLER LİGİ - YARI FİNAL'),
(327, 'U13 YAŞ FUTBOL LİGİ - YARI FİNAL'),
(328, 'U-11 FUTBOL 3/4 MAÇI'),
(329, 'U-11 FİNAL'),
(330, 'ADANA MASTERLER LİGİ - FİNAL'),
(331, 'SAVAŞ BEDİR HALI SAHA TURNUVASI - ÇEYREK FİNAL'),
(332, 'U13 YAŞ FUTBOL 3/4 MAÇI'),
(333, 'U13 YAŞ FUTBOL -  FİNAL'),
(334, 'YUNUSOĞLU TURNUVASI'),
(335, 'SAVAŞ BEDİR HALI SAHA TURNUVASI - YARI FİNAL'),
(336, 'ALADAĞ TURNUVASI'),
(337, 'SAVAŞ BEDİR HALI SAHA TURNUVASI - 3.\'LÜK'),
(338, 'SAVAŞ BEDİR HALI SAHA TURNUVASI - FİNAL'),
(339, 'KIZILDAĞ TURNUVASI');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `mac_kronometre`
--

CREATE TABLE `mac_kronometre` (
  `id` int(11) NOT NULL,
  `musabaka_id` int(11) DEFAULT NULL,
  `ilk_yari_baslangic` datetime DEFAULT NULL,
  `ikinci_yari_baslangic` datetime DEFAULT NULL,
  `duraklatildi` tinyint(1) DEFAULT 0,
  `uzatma_dakika` int(11) DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `mac_notlar`
--

CREATE TABLE `mac_notlar` (
  `id` int(11) NOT NULL,
  `musabaka_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `dakika` varchar(10) DEFAULT NULL,
  `hakem_turu` enum('genel','hakem','yardimci_1','yardimci_2','dorduncu_hakem') DEFAULT 'genel',
  `not_metni` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `mac_notlari_canli`
--

CREATE TABLE `mac_notlari_canli` (
  `id` int(11) NOT NULL,
  `musabaka_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `dakika` varchar(10) DEFAULT NULL,
  `hakem_turu` enum('genel','hakem','yardimci_1','yardimci_2','dorduncu_hakem') DEFAULT 'genel',
  `takim` enum('ev_sahibi','misafir','her_iki') DEFAULT 'her_iki',
  `olay_turu` enum('gol','ihrac','ihtar','degisiklik','genel') DEFAULT 'genel',
  `forma_no` varchar(10) DEFAULT NULL,
  `not_metni` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `mazeretler`
--

CREATE TABLE `mazeretler` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `baslangic_tarihi` date NOT NULL,
  `bitis_tarihi` date NOT NULL,
  `aciklama` text NOT NULL,
  `durum` varchar(50) NOT NULL DEFAULT 'Beklemede',
  `red_gerekcesi` text DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musabakalar`
--

CREATE TABLE `musabakalar` (
  `id` int(11) NOT NULL,
  `hafta_no` int(11) NOT NULL,
  `mac_no` varchar(50) DEFAULT NULL,
  `tarih` date NOT NULL,
  `saat` time NOT NULL,
  `lig_id` int(11) NOT NULL,
  `stadyum_id` int(11) NOT NULL,
  `ev_sahibi_id` int(11) NOT NULL,
  `misafir_id` int(11) NOT NULL,
  `hakem_id` int(11) DEFAULT NULL,
  `yardimci_1_id` int(11) DEFAULT NULL,
  `yardimci_2_id` int(11) DEFAULT NULL,
  `dorduncu_hakem_id` int(11) DEFAULT NULL,
  `gozlemci_id` int(11) DEFAULT NULL,
  `durum` varchar(50) NOT NULL DEFAULT 'Atandı' COMMENT 'Atandı, Oynandı, İptal, Ertelendi',
  `yayin` tinyint(1) NOT NULL DEFAULT 1,
  `skor` varchar(10) DEFAULT NULL,
  `ihraclar` text DEFAULT NULL,
  `arsiv` tinyint(1) NOT NULL DEFAULT 0,
  `bildirim_gonderildi` tinyint(1) NOT NULL DEFAULT 0,
  `hakem_onay` tinyint(1) NOT NULL DEFAULT 0,
  `yardimci_1_onay` tinyint(1) NOT NULL DEFAULT 0,
  `yardimci_2_onay` tinyint(1) NOT NULL DEFAULT 0,
  `dorduncu_hakem_onay` tinyint(1) NOT NULL DEFAULT 0,
  `gozlemci_onay` tinyint(1) NOT NULL DEFAULT 0,
  `yayinda` tinyint(1) DEFAULT 0,
  `ev_skor` int(11) DEFAULT 0,
  `misafir_skor` int(11) DEFAULT 0,
  `hatirlatma_gonderildi` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musabaka_hatirlatmalar`
--

CREATE TABLE `musabaka_hatirlatmalar` (
  `id` int(11) NOT NULL,
  `musabaka_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `gonderim_tarihi` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musabaka_kronometre`
--

CREATE TABLE `musabaka_kronometre` (
  `musabaka_id` int(11) NOT NULL,
  `ilk_yari_baslangic` datetime DEFAULT NULL,
  `ikinci_yari_baslangic` datetime DEFAULT NULL,
  `ilk_yari_uzatma` int(11) DEFAULT 0,
  `ikinci_yari_uzatma` int(11) DEFAULT 0,
  `mac_bitti` tinyint(1) DEFAULT 0,
  `is_40_dakika` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musabaka_notlar`
--

CREATE TABLE `musabaka_notlar` (
  `id` int(11) NOT NULL,
  `musabaka_id` int(11) DEFAULT NULL,
  `dakika` varchar(10) DEFAULT NULL,
  `not_icerigi` text DEFAULT NULL,
  `kayit_tarihi` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musabaka_on_yukleme`
--

CREATE TABLE `musabaka_on_yukleme` (
  `id` int(11) NOT NULL,
  `mac_no` varchar(50) DEFAULT NULL,
  `hafta_no` int(11) NOT NULL,
  `tarih` date NOT NULL,
  `saat` time NOT NULL,
  `lig_id` int(11) NOT NULL,
  `stadyum_id` int(11) NOT NULL,
  `ev_sahibi_id` int(11) NOT NULL,
  `misafir_id` int(11) NOT NULL,
  `hakem_id` int(11) DEFAULT NULL,
  `yardimci_1_id` int(11) DEFAULT NULL,
  `yardimci_2_id` int(11) DEFAULT NULL,
  `dorduncu_hakem_id` int(11) DEFAULT NULL,
  `gozlemci_id` int(11) DEFAULT NULL,
  `durum` enum('Onay Bekliyor','Hata') NOT NULL DEFAULT 'Onay Bekliyor',
  `hata_mesaji` text DEFAULT NULL,
  `olusturulma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musaitlik`
--

CREATE TABLE `musaitlik` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gun` varchar(20) NOT NULL,
  `zaman_dilimi` varchar(20) NOT NULL,
  `musait` tinyint(1) NOT NULL DEFAULT 1,
  `sezon` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musaitlik_notlari`
--

CREATE TABLE `musaitlik_notlari` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sezon` varchar(10) NOT NULL,
  `gun` varchar(20) NOT NULL,
  `not` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musaitlik_talepleri`
--

CREATE TABLE `musaitlik_talepleri` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sezon` varchar(10) NOT NULL,
  `gerekce` text NOT NULL,
  `yeni_musaitlik_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`yeni_musaitlik_data`)),
  `durum` varchar(50) NOT NULL DEFAULT 'Beklemede' COMMENT 'Beklemede, Onaylandı, Reddedildi',
  `talep_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `yanit_tarihi` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `raporlar`
--

CREATE TABLE `raporlar` (
  `id` int(11) NOT NULL,
  `musabaka_id` int(11) NOT NULL,
  `gozlemci_id` int(11) NOT NULL,
  `rapor_dosya_yolu` varchar(255) DEFAULT NULL,
  `iade_edildi` tinyint(1) DEFAULT 0,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rapor_detaylari`
--

CREATE TABLE `rapor_detaylari` (
  `id` int(11) NOT NULL,
  `rapor_id` int(11) NOT NULL,
  `hakem_id` int(11) NOT NULL,
  `puan` decimal(3,1) NOT NULL,
  `not` text DEFAULT NULL,
  `listeden_kaldir` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sifre_sifirlama`
--

CREATE TABLE `sifre_sifirlama` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kod` varchar(6) NOT NULL,
  `olusturma_tarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `stadyumlar`
--

CREATE TABLE `stadyumlar` (
  `id` int(11) NOT NULL,
  `ad` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `stadyumlar`
--

INSERT INTO `stadyumlar` (`id`, `ad`) VALUES
(6, 'HADIRLI STADI'),
(7, 'MUHARREM GÜLERGİN STADI'),
(12, 'Tüpraş Stadyumu'),
(14, 'TEVRİZ DURA STADI'),
(15, '2 NOLU ALİ HOŞFİKİRER STADI'),
(16, 'İMAMOĞLU İLÇE STADI'),
(17, 'ADANA DEMİRSPOR TESİSLERİ'),
(18, 'PEYAMİ SAFA MARACI STADI'),
(19, 'HURŞİT KARSLI STADI'),
(20, 'PARK ARENA - 1'),
(21, 'PARK ARENA - 2'),
(22, 'CEYHAN SENTETİK STADI'),
(23, 'KARAİSALI İLÇE STADI'),
(25, 'SİMERANYA TESİSLERİ A SAHASI'),
(26, 'SİMERANYA TESİSLERİ B SAHASI'),
(27, 'GÜNDÜZ TEKİN ONAY TESİSLERİ'),
(28, 'TEVRİZ DURA STADI - 1'),
(29, 'ŞEHİT MEHMET YİBO SPOR SALONU'),
(30, 'NİHAT GEVEN SPOR SALONU'),
(31, 'KARATAŞ İLÇE STADI'),
(32, 'ALADAĞ İLÇE STADI'),
(33, 'Stadyum Adı'),
(34, 'K. KAYNAK KARDEŞLER STADI'),
(35, 'GÖL HALI SAHA - 1'),
(36, 'GÖL HALI SAHA - 2'),
(37, 'KARATAŞ PLAJ'),
(38, 'KOZAN İSMET ATLI STADI'),
(39, 'SEYHAN BELD. SÖĞÜTLÜ TESİSLERİ'),
(40, 'GÜNEY YILDIZI STADI'),
(41, 'TEVRİZ DURA STADI - 2'),
(42, 'CEYHAN SENTETİK İLÇE STADI'),
(43, 'AKDENİZ STADI'),
(44, 'GÜNEY PARK STADI'),
(45, 'A. B. ŞHR. BLD. ONUR MAH. TESİSLERİ'),
(46, 'POZANTI İLÇE STADI'),
(47, 'SEYHAN BELD.  SÖĞÜTLÜ TESİSLERİ'),
(48, 'KAYIŞLI STADI'),
(49, 'MÜRSELOĞLU STADI'),
(50, 'GÜLPINAR STADI'),
(51, 'YENİ ADANA STADYUMU'),
(52, 'PEYAMİ SAFAMARACI STADI'),
(53, 'GÜNEY RARK STADI'),
(54, 'A. B. ŞHR. BELD. ONUR MAH. TESİSLERİ'),
(55, 'SİMERANYA - 1'),
(56, 'SİMERANYA - 2'),
(57, 'KÜRKÇÜLER STADI'),
(58, 'SEYHAN BLD. SÖĞÜTLÜ TESİSLERİ'),
(59, 'ÇEAŞ STADI'),
(60, 'A. BÜYÜK ŞHR. BELD. ONUR MAH. TESİSLERİ'),
(63, 'VADİ ARENA HALI SAHA - 1'),
(64, 'VADİ ARENA HALI SAHA - 2'),
(65, 'MUHARREM GÜLERGİN'),
(66, '2 NOLU ALİ HOŞFİRKİRER STADI'),
(67, 'A.B. ŞHR. BELD. ONUR MAH. TESİSLERİ'),
(68, 'NİHAT GEVEN'),
(69, 'CEYHAN YENİ KAPALI SPOR SALONU'),
(70, 'NİHAT GEVEN (4 MAÇ)'),
(71, 'K.KAYNAK KARDEŞLERİ STADI'),
(72, 'SİMERANYA  HALI SAHA'),
(73, 'SEYHAN BELEDİYESİ SÖĞÜTLÜ TES.'),
(74, 'ADANA DEMİRSPOR TESİSLERİ - 2'),
(75, 'A. B. ŞHR. BLD. ONUR MH. TESİSLERİ'),
(76, 'YENİ CEYHAN STADYUMU'),
(77, 'CEYHAN STADYUMU'),
(78, 'GÜNEŞLİ STADI'),
(79, 'SEYHAN BELEDİYESİ STADI'),
(80, 'GÜNEYPARK STADI'),
(81, 'PEYAMİ SAFA MARACO'),
(82, 'SİMERANYA-1'),
(83, 'SİMERANYA-2'),
(84, 'KARAİSALI STADI'),
(85, 'GÜNDÜZ TEKİN ONAY'),
(86, 'ERTELENDİ'),
(87, 'A. B. ŞHR. BLD.ONUR MAH. TESİSLERİ'),
(88, 'CEYHAN ŞEHİR STADI'),
(89, 'A. B. ŞHRİ BLD. ONUR MAH. TESİSLERİ'),
(90, 'TFF GÜNDÜZ TEKİN ONAY TESİSLERİ'),
(91, 'İSMET ATLI ŞEHİR STADI'),
(92, 'ŞEHİT MEHMET OFLAZ  YİBO SPOR SALONU'),
(93, 'A. B. ŞHR. BLD. ONYUR MAH. TESİSLERİ'),
(94, 'ŞEHİT MEHMET OFLAZ YİBO SPOR SALONU'),
(95, 'CEYHAN KAPALI SPOR SALONU'),
(96, 'PARK ARENA HALI SAHASI -1'),
(97, 'PARK ARENA HALI SAHASI -2'),
(98, 'PARK ARENA HALI SAHASI - 1'),
(99, 'SİMERANYA HALI SAHASI - A'),
(100, 'SİMERANYA HALI SAHASI - B'),
(101, 'MUHARREM GÜLERGİN STADI-1'),
(102, 'MUHARREM GÜLERGİN STADI-2'),
(103, 'Ş.MEHMET OFLAZ YBO.'),
(104, 'TFF GÜNDÜZ TEKİ ONAY TESİSLERİ'),
(105, 'ASIM SAVAŞ SPOR SALONU'),
(106, 'K. KAYNAK VE KARDEŞLERİ STADI'),
(107, 'LÜTFULLAH AKSUNGUR SPOR SALONU-1'),
(108, 'LÜTFULLAH AKSUNGUR SPOR SALONU-2'),
(109, 'LÜTFULLAH AKSUNGUR SPOR SALONU-3'),
(110, 'SAKIP SABANCI 1'),
(111, 'SAKIP SABANCI 2'),
(112, 'LÜTFULLAH AKSUNGUR SPOR SALONU'),
(113, 'GÖL ARENA - 1'),
(114, 'GÖL ARENA - 2'),
(115, 'Ş.MEHMET OFLAZ YBO SPOR SALONU'),
(116, 'SİMERANYA HALI SAHA'),
(117, 'YUNUSOĞLU STADI'),
(118, 'ALADAĞ STADI'),
(119, 'KIZILDAĞ STADI');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `takimlar`
--

CREATE TABLE `takimlar` (
  `id` int(11) NOT NULL,
  `ad` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `takimlar`
--

INSERT INTO `takimlar` (`id`, `ad`) VALUES
(64, 'YÜKSEL GENÇLİK SPOR'),
(65, 'SEYHANGÜCÜ'),
(66, 'YENİBEYGÜCÜ'),
(67, 'BAKLALISPOR'),
(68, 'KOZANSPOR FK'),
(69, 'ANADOLU 19 MAYIS SPOR'),
(70, 'SARIÇAM KÜRKÇÜLER SPOR'),
(71, 'TOROSLAR 1922 SPOR'),
(72, 'SEYHAN BELEDİYE SPOR'),
(73, 'KARATAŞ BELEDİYE SPOR'),
(74, 'ALADAĞGÜCÜ'),
(75, 'KURTTEPE LİBERO SK'),
(76, 'ÇUKUROVA PRESS HAVUZLUBAHÇESPOR'),
(77, 'YAPI MESLEK SPOR'),
(78, 'ADANA YÜREĞİR GÜCÜ'),
(79, 'CEYHANGÜCÜ'),
(80, 'CEYHAN DOĞAN SPOR'),
(81, 'HEDEF 01 SPOR'),
(82, 'KİREMİTHANESPOR'),
(83, 'SEYHANSPOR'),
(84, 'SULUCA ORGANİZE SANAYİ'),
(85, 'KAZİME ÖZLER SPOR'),
(86, 'ÇUKUROVA DEMİRSPOR'),
(87, 'SARIÇAM KILIÇLI 1965 SPOR'),
(88, 'KOZAN ESNAF SPOR'),
(89, 'ŞAMPİYON ÇOCUK SPOR'),
(90, 'ÇUKUROVA GENÇLERBİRLİĞİ'),
(91, 'YÜZÜNCÜYILSPOR'),
(92, 'ÇUKUROVA BELEDİYE SPOR'),
(93, 'YEŞİLEVLERSPOR'),
(94, 'GAZİPAŞASPOR'),
(95, 'YÜREĞİR SPOR'),
(96, 'SARIÇAM BELEDİYE SPOR'),
(97, 'AKKAPISPOR'),
(98, 'KOZAN İMAR SPOR'),
(99, 'ÇUKUROVASPOR'),
(100, 'KANAL UFUK SPOR'),
(101, 'DEVECİ SPOR'),
(102, 'SİMERANYASPOR'),
(103, 'GÜNEŞLİSPOR'),
(104, 'YÜREĞİR DEMİR SPOR'),
(105, 'SEYHAN DEMİR SPOR'),
(106, 'SARIÇAMGÜCÜ'),
(107, 'HADIRLIGÜCÜ'),
(108, 'KÜÇÜK DİKİLİSPOR'),
(109, 'TOROS DEMİRSPOR'),
(110, 'KARAİSALI SPOR'),
(111, 'MERCİMEKSPOR'),
(112, 'KUNDURACI ESNAFSPOR'),
(113, 'ÇUKUROVA GENÇLİK SPOR'),
(114, 'İMAMOĞLUSPOR'),
(115, 'TUFANBEYLİ BELEDİYESPOR'),
(116, 'PTT EVLERİ SPOR'),
(125, 'SEYHAN BELEDİYESPOR'),
(126, 'KARATAŞ BELEDİYESPOR'),
(127, 'Beşiktaş'),
(128, 'Fenerbahçe'),
(129, 'BULUT YEŞİL İNŞ. ADANA 01 FK'),
(130, 'A. EPSAŞ K.MARAŞ İSTİKLALSPOR'),
(131, 'ÇUKUROVA PRES HAVUZLUBAHÇE'),
(132, 'DENİZLİ MİTHATPAŞASPOR'),
(133, 'SARIÇAM KILIÇLI 1965'),
(134, 'ADANA DOSTLAR SK'),
(135, 'ADANA SARIÇAM O1 SK'),
(136, 'ÇUKUROVA BELEDİYESPOR'),
(137, 'ŞAMPİYON ÇOCUKSPOR'),
(138, 'KÜÇÜK DİKİLİSPOR FK'),
(139, 'KOZAN 1266 BAYBARSSPOR'),
(140, 'KAYSERİ ŞEKER SK'),
(141, 'TAKIM 2'),
(142, 'MERSİN TERSPOR'),
(143, 'BÜYÜKŞEHİR BELEDİYESPOR'),
(144, 'ADANA GÜNEY 01 SK'),
(145, 'ADANA ANAFARTALAR 01 SK'),
(146, 'ADANA YILDIRIMSPOR'),
(147, 'ÇUKUROVA KOZASPOR'),
(148, 'KUZEY ADANASPOR'),
(149, 'ADANASPOR A.Ş.'),
(150, 'KIZILKAYA TARIM ŞANLIURFASPOR'),
(151, 'BAHÇEŞEHİR 2024 FK'),
(152, 'ADANA TAŞKÖPRÜSPOR'),
(153, 'ADANA YILDIZLAR 01 SK'),
(154, 'ADANA İMAMOĞLU 01 SK'),
(155, 'ÇUKUROVA GENÇLİKSPOR'),
(156, 'KAZİME ÖZLERSPOR'),
(157, 'YAZ SİGORTA ADALETGÜCÜ'),
(158, 'YÜREĞİR ATAKENTSPOR'),
(159, 'KOZAN ESNAFSPOR'),
(160, 'ASLANSPOR'),
(161, 'SEYHAN DEMİRSPOR'),
(162, 'KARAİSALISPOR'),
(163, 'ADANA DEMİRSPOR A.Ş.'),
(164, 'TRABZONSPOR A.Ş.'),
(165, 'TARSUS KAMPÜS FK'),
(166, 'OSMANİYE FT'),
(167, 'OSMANİYE BAROSU'),
(168, 'ADANA F TİPİ SK'),
(169, 'ADANA TARIM FK'),
(170, 'ADANA BAM FT'),
(171, 'ADONİS'),
(172, 'MERSİN ADLİYESİ FT'),
(173, 'ADANA BAROSU SK'),
(174, 'ADANA E TİPİ FT'),
(175, 'İSKENDERUN AÇIK CİK'),
(176, 'KOZAN CEZAEVİ FK'),
(177, 'ADANA ADLİYESİ ÖTÜKEN FK'),
(178, '2 NOLU YGC'),
(179, 'AYGAZ 1'),
(180, 'OPET'),
(181, 'ARÇELİK'),
(182, 'DÜZEY'),
(183, 'AYGAZ 2'),
(184, 'OTOKOÇ'),
(185, 'KARTALSPOR'),
(186, 'GENÇLERBİRLİĞİ'),
(187, 'TOROSSPOR'),
(188, 'İNCİRLİKSPOR'),
(189, 'AKINSPOR'),
(190, 'GÜNEY ADANASPOR'),
(191, 'ADANA YILDIRIM 01 SPOR'),
(192, 'MAVİ ŞİMŞEKLERSPOR'),
(193, 'GÜLBAHÇESİSPOR'),
(194, 'ADANA GÜNEŞİ FUTBOL SK'),
(195, 'BARIŞGÜCÜ FUTBOL SK'),
(196, 'GÜLPINARSPOR'),
(197, 'KUZEY ATLASSPOR'),
(198, 'TAKIM 1'),
(199, 'YÜREĞİR DEMİRSPOR'),
(200, 'ÇUKUROVA ÜNİVERSİTESİ'),
(201, 'ADANA AMATÖRCE SK'),
(202, 'CEYHAN TENİS TİFE SK'),
(203, 'BAYINDIRLIK YAPISPOR'),
(204, 'SARIÇAM BURUKSPOR'),
(205, 'REŞATBEYSPOR'),
(206, 'RAMAYSPOR'),
(207, 'ADANA MASSESPOR'),
(208, 'YÜKSEL GENÇLİKSPOR'),
(209, 'ÇUKUROVA AKDENİZSPOR'),
(210, 'KANARYASPOR'),
(211, 'EMEKSPOR'),
(212, 'OLİMPOS YURTSPOR'),
(213, 'ADANA EMEK 01 SPOR'),
(214, 'ADANA ŞİMŞEKLER 01 SK'),
(215, 'PTT EVLERİSPOR'),
(216, 'SULUCA 1 NOLU YGC'),
(217, 'MERSİN E TİPİ CİK'),
(218, 'ADANA 1 NOLU AÇIK CİK'),
(219, 'SULUCA L TİPİ FK'),
(220, 'ADANA DENETİMLİ SERBESTLİK'),
(221, 'ADANA 1 NOLU T TİPİ FT'),
(222, 'SULUCA AÇIK CİK FT'),
(223, 'KOZAN ADALET SPOR'),
(224, 'ADANA BAROSU FT'),
(225, 'ADANA 2 NOLU T TİPİ CİK'),
(226, 'ADANA YÜREĞİRGÜCÜ'),
(227, 'YAPI MESLEKSPOR'),
(228, 'YÜREĞİRSPOR'),
(229, 'SİNANPAŞASPOR'),
(230, 'BEŞOCAK DEMİRSPOR'),
(231, 'DEVECİSPOR'),
(232, 'ÇUKUROVA DEMİR ORDUSPOR'),
(233, 'İSMET İNÖNÜ ORTAOKULU'),
(234, 'BUCAK ORTAOKULU'),
(235, 'LÜTFİYE-ALİ ŞADİ ÇELİK ORTAOKULU'),
(236, 'AHMET CEVDET ÇAMURDAN ORTAOKULU'),
(237, 'ŞEHİT MEHMET OFLAZ YİBO'),
(238, 'ŞEHİT ÖĞRETMEN L. ÖLMEZ ORTAOKULU'),
(239, 'ŞEHİT MAHMUT YEŞİLÇAM ORTAOKULU'),
(240, 'ŞEHİT MUSTAFA TURANLI ORTAOKULU'),
(241, 'ŞEHİT FETTAH ÇEVİKOĞLU ORTAOKULU'),
(242, 'VAKIFBANK ORTAOKULU'),
(243, 'MEHMET TAL İMAMHATİP ORTAOKULU'),
(244, 'ÖZEL ADANA DOĞA ORTAOKULU'),
(245, 'TED  ADANA KOLEJİ ÖZEL ORTAOKULU'),
(246, 'ŞEHİT MEHMET FATİH ONGUN ORTAOKULU'),
(247, 'KENAN ÇETİNEL ORTAOKULU'),
(248, 'ORHANGAZİ ORTAOKULU'),
(249, 'AKKAPI ŞEHİT KEMAL YÜZGEÇ ORTAOKULU'),
(250, 'KURTTEPE ORTAOKULU'),
(251, 'ŞEHİT ZAFER OLUK ORTAOKULU'),
(252, 'ÖZEL ÇUKUROVA ALTINELLER ORTAOKULU'),
(253, 'YILDIRIM DEMİR ORDU 01 SPOR'),
(254, 'SARIÇAM DEMİRSPOR'),
(255, 'YÜREĞİRGÜCÜ'),
(256, '2 HAZİRAN ORTAOKULU'),
(257, 'BUHARA ORTAOKULU'),
(258, 'ALPARSLAN TÜRKEŞ ORTAOKULU'),
(259, 'ADASOKAĞI ORTAOKULU'),
(260, 'YAVUZ SELİM ORTAOKULU'),
(261, 'ÖZEL ADANA EKİM KOLEJİ ORTAOKULU'),
(262, 'ŞEHİT SERCAN YILMAZ ORTAOKULU'),
(263, 'BÜYÜKDİKİLİ ORTAOKULU'),
(264, 'ÖMER KANAATBİLEN ORTAOKULU'),
(265, 'MEHMET ADİL İKİZ ORTAOKULU'),
(266, 'ÖMER REFİKA HALICILAR ORTAOKULU'),
(267, 'TAŞKENT ORTAOKULU'),
(268, 'NİLÜFER HATUN ORTAOKULU'),
(269, 'ŞEHİT HACI AHMET ÖZTÜRK İ.O.O.'),
(270, 'ŞEHİT ENVER BUĞUR ORTAOKULU'),
(271, 'TEVRİZ DURA DEDESPOR'),
(272, 'VEFA 01 SPOR'),
(273, 'KANALUFUKSPOR'),
(274, '5 OCAK 1922 SPOR'),
(275, 'SARIÇAM BELEDİYESPOR'),
(276, 'TÜMOSAN KONYASPOR'),
(277, 'KURTTEPE LİBEROSPOR'),
(278, 'CEYHAN 1967 FK'),
(279, 'SAMANDAĞ ADLİYESİ'),
(280, 'ANAMUR CEZAEVİ'),
(281, 'HARİCEN TAHSİL FT'),
(282, 'PINAR MAH. CESURSPOR'),
(283, 'ŞAKİRPAŞASPOR'),
(284, 'ZİYAPAŞA ORTAOKULU'),
(285, 'ŞEHİT YUNUS UĞUR ORTAOKULU'),
(286, 'YILDIRIM BAYAZIT ORTAOKULU'),
(287, 'ÖZEL ERAL ORTAOKULU'),
(288, 'TOKİ ŞEHİT BAHATTİN KALAYCI O.OKULU'),
(289, '5 OCAK ORTAOKULU'),
(290, '1 NİSAN YATILI BÖLGE ORTAOKULU'),
(291, 'ŞEHİT METİN MALKAV ORTAOKULU'),
(292, 'ŞEHİT İSLAM AKYÜZ ORTAOKULU'),
(293, 'ÇARKIPARE ŞEHİT ERTAN TOKUŞ O.OKULU'),
(294, 'SABANCI AİLESİ İMAMHATİP ORTAOKULU'),
(295, 'ALİYA İZZET BEGOVİÇ ORTAOKULU'),
(296, 'ÖMER NASUHİ BİLMEN ANADOLU  İ.H.O.O.'),
(297, 'MALAZGİRT ORTAOKULU'),
(298, 'CEYHAN 1967 SK'),
(299, 'ÇATALAN BARAJ ORTAOKULU'),
(300, 'ÖĞRETMEN HIDIR ÜNVERDİ ORTAOKULU'),
(301, 'ATATÜRK ORTAOKULU'),
(302, 'KAŞGARLI MAHMUT ORTAOKULU'),
(303, 'ÖZEL DÖŞKAYA KOLEJİ ORTAOKULU'),
(304, 'İNKILAP İMAMHATİP ORTAOKULU'),
(305, 'MEHMET AKİF ORTAOKULU'),
(306, 'HACI ÖZCAN SİNAĞ ORTAOKULU'),
(307, 'ÖZEL ADANA FİNAL ORTAOKULU'),
(308, 'TUĞRULBEY ORTAOKULU'),
(309, 'TED ADANA KOLEJİ ÖZEL ORTAOKULU'),
(310, 'Ev Sahibi'),
(311, 'Misafir'),
(312, 'DAĞLIOĞLUSPOR'),
(313, 'KARŞIYAKASPOR'),
(314, 'ATAKAŞ HATAYSPOR'),
(315, 'ADANA 01 FUTBOL KULÜBÜ SK'),
(316, 'AMATÖR SPOR VEFA KULÜBÜ'),
(317, 'ADANA İCRAAT FC'),
(318, 'İNİTBAK FK'),
(319, 'CELSE ARASI'),
(320, 'LEX MAECHİNE'),
(321, 'HUKUK ADALET DAYANIŞMA'),
(322, 'HARİCEN TAHSİL'),
(323, 'ADALETGÜCÜ'),
(324, 'CEYHAN BARO'),
(325, 'BAROSSİA'),
(326, 'ÖTÜKEN FK'),
(327, 'GÖKBÖRÜ'),
(328, 'ANAVARZA'),
(329, 'PARS SPOR KULÜBÜ'),
(330, 'KOZANESNAFSPOR'),
(331, 'GALATA SPOR'),
(332, 'HADIRLIGÜCÜ 1978 SK'),
(333, 'ABFT'),
(334, 'BİM'),
(335, 'YER ÇEKİMLİ KARANFİL'),
(336, 'ABSK'),
(337, 'TRUVA SK'),
(338, 'YAMÇILI KARTALLAR'),
(339, 'A THE LİGHT'),
(340, 'BÖLGE ADLİYE MAHKEMESİ'),
(341, 'ALL VETERAN'),
(342, 'LAW AROG'),
(343, 'ADANA GENÇ BÖLGE ADLİYESİ'),
(344, 'ADANA ADLİYESPOR'),
(345, 'ÇİFTLİKKÖY ADONİSSPOR'),
(346, 'MALATYA YEŞİLYURT SPOR KULÜBÜ'),
(347, 'KARAİSALI ADLİYESİ'),
(348, 'LAW SAİNT GERMAN'),
(349, '1.TAKIM'),
(350, '2.TAKIM'),
(351, 'YERÇEKİMLİ KARANFİL'),
(352, 'İNTİBAK FK'),
(353, 'ADANA İCRAAT FK'),
(354, 'PARS FUTBOL KULÜBÜ'),
(355, 'ADANA GENÇ BAM'),
(356, 'AL VETERAN'),
(357, 'A THE LATE'),
(358, 'TRUVA'),
(359, 'BÖLGE MAHKEMESİ'),
(360, 'BAROSSİMA'),
(361, 'A. BÜYÜKŞEHİR BELEDİYESPOR'),
(362, 'BEYSPOR'),
(363, 'SULUCA ORG. SANAYİSPOR'),
(364, 'İMAMOĞLU BELEDİYESPOR'),
(365, 'CEYHAN DOĞANSPOR'),
(366, 'YENİBEYGÜCÜSPOR'),
(367, 'ÇUKUROVA BARAJSPOR'),
(368, 'ADANA YÜREĞİRGÜCÜSPOR'),
(369, 'GENÇLERBİRLİĞİSPOR'),
(370, 'SEYHANGÜCÜSPOR'),
(371, 'CEYHANGÜCÜSPOR'),
(372, 'KOZAN İMARSPOR'),
(373, 'ÇUKUROVA PRES HAVUZLUBAHÇESPOR'),
(374, 'KUM FIRTINASI'),
(375, 'KARATAŞ GENÇLER BİRLİĞİ'),
(376, 'YOUNG BOYS'),
(377, 'GAZİANTEP FUTBOL KULÜBÜ A.Ş.'),
(378, 'YAZ SİGORTA ADALETGÜCÜSPOR'),
(379, 'POZANTI BELEDİYESPOR'),
(380, 'BAHÇEŞEHİR 2024 F.K.'),
(381, 'KUZEY ATLASPOR'),
(382, 'KÜÇÜK DİKİLİSPOR F.K.'),
(383, 'CEYHAN 1967 F.K.'),
(384, 'YILDIRIM DEMİR 01 SPOR'),
(385, 'HADIRLIGÜCÜ 1978 S.K.'),
(386, 'ADANA BAM'),
(387, 'ADANA BİM'),
(388, 'SİLİFKE BELEDİYESPOR'),
(389, 'GALATASPOR'),
(390, 'ADANA BÜYÜKŞEHİR BELEDİYESPOR'),
(391, 'ÇUKUROVA ÜNİVERSİTESİSPOR'),
(392, 'İNTİBAK'),
(393, 'LEX MECHİNE'),
(394, 'KOZANSPOR F.K.'),
(395, 'ANADOLU 19 MAYISSPOR'),
(396, 'TALAS BELEDİYESPOR KULÜBÜ'),
(397, 'AKEDAŞ K.MARAŞ İSTİKLALSPOR'),
(398, 'ADANA 01 FUTBOL KULÜBÜ S.K.'),
(399, 'ADANA 5 OCAK 1922 SPOR'),
(400, 'MIDIK ADASPOR'),
(401, 'TARSUSGÜCÜSPOR'),
(402, 'HADIRLIGÜCÜSPOR'),
(403, 'BAM'),
(404, 'KARŞIYAKA GENÇLİK'),
(405, 'KAYIŞLISPOR'),
(406, 'AYDINLAR FK'),
(407, 'ANI86'),
(408, 'VEFA GENÇLİK'),
(409, 'KARAYUSUFLU FK'),
(410, 'FEVZİPAŞASPOR'),
(411, 'MÜRSELOĞLU GENÇLERGÜCÜ'),
(412, 'GÜVENSPOR'),
(413, 'KAPLANSPOR'),
(414, 'ENGELSPOR'),
(415, 'MAVİ ATLASSPOR'),
(416, 'YÜREĞİR ATAKETNSPOR'),
(417, 'ADANA 01 F.K.'),
(418, 'AYDINLAR GENÇLİK'),
(419, 'ANI 86'),
(420, 'ADANA İDMAN YURDUSPOR'),
(421, 'ŞIRNAK KADIN SPOR KULÜBÜ'),
(422, 'SARIÇAM BLEDİYESPOR'),
(423, 'MDGRUP OSMANİYESPOR'),
(424, 'HAVUZLUBAHÇE GENÇLİK'),
(425, 'MÜRSELOĞLU'),
(426, 'SUVERMEZ KAPADOKYASPOR'),
(427, 'ÜRGÜPSPOR'),
(428, 'DAĞLİOĞLUSPOR'),
(429, 'ASMALI MAHALLESİ'),
(430, 'DEMİRTAŞ MAHALLESİ'),
(431, 'SUGÖZÜ MAHALLESİ'),
(432, 'AYVALIK MAHALLESİ'),
(433, 'HAMZALI MAHALLESİ'),
(434, 'ZEYTİNBELLİ MAHALLESİ'),
(435, 'SULUCA ORG. SPOR'),
(436, 'KÜRKÇÜLER SPOR'),
(437, 'ÖREN MAHALLESİ'),
(438, 'GÖBEÖREN MAHALLESİ'),
(439, 'DERVİŞİYE MAHALLESİ'),
(440, 'K. YUMURTALIK MAHALLESİ'),
(441, 'YENİ KÖY MAHALLESİ'),
(442, 'AKDENİZ MAHALLESİ'),
(443, 'AYAS MAHALLESİ'),
(444, 'KEMALPAŞA MAHELLESİ'),
(445, 'YEŞİL KÖY MAHALLESİ'),
(446, 'AKYUVA MAHALLESİ'),
(447, 'NARLI ÖREN MAHALLESİ'),
(448, 'KALEMLİ MAHALLESİ'),
(449, 'ALADAĞGÜCÜSPOR'),
(450, 'SEYHANFÜCÜSPOR'),
(451, 'ADANA 01 FUBOL KULÜBÜ S.K.'),
(452, 'SAĞLIKSPOR'),
(453, 'DEMİR FK'),
(454, 'GENÇ SMMM'),
(455, 'YÜREĞİR SGK'),
(456, 'CEYHAN'),
(457, 'DOSTLUK'),
(458, 'ANAFARTALAR'),
(459, 'ULAŞTIRMA'),
(460, 'MUHTASAR UNİTED'),
(461, 'İSOTLAR'),
(462, 'R.K SPOR'),
(463, 'SEYHAN SSK'),
(464, 'ADANA 01 FUTBOL KULÜBÜ'),
(465, 'SARIÇAM KÜRKÇÜLERSPOR'),
(466, 'TOROS  DEMİRSPOR'),
(467, 'ADANA BÜYÜKŞEHİR BELDİYESPOR'),
(468, 'KOZAM İMARSPOR'),
(469, 'ADANA 5 OCAK 192 SPOR'),
(470, 'TARSUSGÜCÜ'),
(471, 'R.K. SPOR'),
(472, 'ANAKARTALAR'),
(473, 'SEYHAN SGK'),
(474, 'MUH.UNİTED'),
(475, 'YILDIZLAR'),
(476, 'SAĞLIK SPOR'),
(477, 'ADANA İDMANYURDUSPOR'),
(478, 'GENÇ ÜLKÜM SPOR KULÜBÜ'),
(479, 'SULUCA ORGANİZE SANAYİSPOR'),
(480, 'MÜRSELOĞLU GENÇLİKGÜCÜ'),
(481, 'KARTALSPPOR'),
(482, 'SIR HES'),
(483, 'İSKENDERUN İŞB'),
(484, 'SEYHAN HES'),
(485, 'RÖLE'),
(486, 'ÇFTLİKKÖY ADONİSSPOR'),
(487, 'DİRENÇSPOR'),
(488, 'TEDAŞ'),
(489, 'GÜVENLİK-İŞ SENDİKASI'),
(490, 'CANLI BAKIM-YTM'),
(491, 'MAVİ ŞİMŞEKLER'),
(492, 'AVANOĞLU SPOR KULÜBÜ'),
(493, 'KOZANSPOR'),
(494, 'GÜVENLİK - İŞ SENDİKASI'),
(495, 'ABFT'),
(496, 'ÖTÜKEN FK'),
(497, 'GAZİANTEP SAFİR SPOR KULÜBÜ'),
(498, 'TAZ SİGORTA ADALETGÜCÜSPOR'),
(499, 'RK SPOR'),
(500, 'MUH. UNİTED'),
(501, 'ANAVARZA FT'),
(502, 'REHBERLİK FK'),
(503, 'TRUVA FK'),
(504, 'KOZAN ADALETSPOR'),
(505, 'ADANA 2 NOLU T TİPİ'),
(506, 'AYTİGİNLER'),
(507, 'BAMCELONA'),
(508, 'NEKKETSU'),
(509, 'SULUCA YGC'),
(510, 'YENİ MERSİN İDMANYURDU FUTBOL A.Ş.'),
(511, 'PINAR MH. CESURSPOR'),
(512, 'KUZEY ADANSPOR'),
(513, 'ADANA TAŞKÖPÜSPOR'),
(514, 'KOZAN BARO'),
(515, 'YARGI SPOR'),
(516, 'ADANA F TİPİ'),
(517, 'İCRA GÜCÜ'),
(518, 'DENETİMLİ SERBESTLİK'),
(519, 'SAHA İÇİ ADALET'),
(520, 'MÜTAALA'),
(521, 'OHAL FK'),
(522, 'ADANA E TİPİ SK'),
(523, 'ADANA 1 NOLU T'),
(524, 'ZİNDAN FK'),
(525, 'KOZAN CEZAEVİ'),
(526, 'ADANA BÖLGE İDARE FK'),
(527, 'AKEDAŞ KAHRAMANMARAŞ İSTKLAL SPOR'),
(528, 'CANLI BAKIM - YTM'),
(529, 'KOCAYUSUFLU'),
(530, 'YILDIRIM DEMİR O1 SPOR'),
(531, 'İCRAGÜCÜ'),
(532, 'YARGISPOR'),
(533, 'GENLERBİRLİĞİSPOR'),
(534, 'ADANA DEMİRSPOR A.Ş'),
(535, 'HAIRLIGÜCÜ 1978 SK'),
(536, 'KÜRKÇÜLERSPOR'),
(537, 'NEKETSU'),
(538, 'TORORSLAR 1922 SPOR'),
(539, 'İSKENDERUN'),
(540, 'YTM'),
(541, 'DİRENÇ SIR'),
(542, 'HES'),
(543, 'YILMAZ GENÇLİK'),
(544, 'SEYHANDEMİRSPOR'),
(545, 'EGA SPOR'),
(546, 'İŞ\'TE GAZİANTEP'),
(547, 'ANTAKYA KARMA'),
(548, 'PERRE SPOR'),
(549, 'İŞ MERSİN'),
(550, 'ADANA BÜYÜK ŞEHİR BELEDİYESPOR'),
(551, 'KARAYUSUFLU'),
(552, 'ADANA 01 FK'),
(553, 'TAKIM1'),
(554, 'YÜZÜCÜYILSPOR'),
(555, 'TAKIM2'),
(556, 'ADANA TAŞKÖRÜSPOR'),
(557, 'HATAY DEFNE 1994 SPOR KULÜBÜ'),
(558, 'ADANA YÜREGİRGÜCÜSPOR'),
(559, 'A. BÜYÜK ŞEHİR BELEDİYESPOR'),
(560, 'AKKAPI SPOR'),
(561, 'ÇUKUROVA KYK'),
(562, 'CEYLAN KYK'),
(563, 'ADANA ERKEK YURDU'),
(564, 'KOZAN YURDU'),
(565, 'CEYHAN YURDU'),
(566, 'KUTÜL AMARE YURDU'),
(567, 'MERSİN TER SPOR'),
(568, 'ADANA YÜEĞİRGÜCÜSPOR'),
(569, 'SAIÇAM BURUKSPOR'),
(570, 'YÜREĞİR ATAKENSPOR'),
(571, 'AKEDAŞ KAHRAMANMARAŞ İSTİKLALSPOR'),
(572, 'YENİNEYGÜCÜSPOR'),
(573, 'ADANA DEMİRSPOR A.Ş. U14'),
(574, 'ADANA 01 FUTBOL KULÜBÜ SK U14'),
(575, 'ADANASPOR A.Ş. U14'),
(576, 'TURKISH OIL YENİ MERSİN İDMANYURDU U14'),
(577, 'ADANA KARMA'),
(578, 'MERSİN KARMA'),
(579, 'OSMANİYE KARMA'),
(580, 'DENİZLİ MİYHATPAŞASPOR'),
(581, 'ÇUKUROVA PRES  HAVUZLUBAHÇESPOR'),
(582, 'ANAFARTALAR FK'),
(583, 'TURKISH OIL YENİ MERSİN İDMANYURDU FUTBOL A.Ş.'),
(584, 'KAYIŞLI'),
(585, 'FEVZİPAŞA'),
(586, 'BUHARA O.O.'),
(587, 'ALİYA İZZET BEGOVİÇ O.O.'),
(588, 'BİLGE KAAN O.O.'),
(589, 'MERYEM-MEHMET KAYHAN O.O.'),
(590, 'YAVUZLAR O.O.'),
(591, 'Ş.EBUBEKİR DURMUŞ O.O.'),
(592, 'İSTİKLAL O.O.'),
(593, 'İSMAİL HAZAR O.O.'),
(594, 'RAMAZANOĞLU O.O.'),
(595, 'ÇARKIPARE Ş.ERTAN TOKUŞ O.O.'),
(596, 'VAKIFBANK O.O.'),
(597, 'Ş.METİN MALKAV O.O.'),
(598, 'NÜLİFER HATUN O.O.'),
(599, 'KARŞIYAKA O.O.'),
(600, 'Ş.ENVER BUĞUR O.O.'),
(601, 'ÖĞRETMEN ZEYNEP ERDOĞDU O.O.'),
(602, 'KAZİME ÖZLER O.O.'),
(603, 'YILDIRIM BEYAZIT O.O.'),
(604, 'KOZAN KOLEJİ O.O.'),
(605, 'REMZİ OĞUZ ARIK O.O.'),
(606, 'İSMET İNÖNÜ O.O'),
(607, '60.YIL ŞHT. MUSTAFA AKER O.O.'),
(608, 'KÖSRELİ O.O.'),
(609, 'HACI ÖZCAN SINAĞ O.O.'),
(610, 'OSMANGAZİ O.O.'),
(611, 'ÇUK.İMAMHATİP O.O.'),
(612, 'KAŞGARLI MAHMUT O.O.'),
(613, 'MEHMET AKİF O.O.'),
(614, 'LÜTFİYE ALİ ŞADİ ÇELİK O.O.'),
(615, 'GEYHANGÜCÜSPOR'),
(616, 'KİREMİTHANSPOR'),
(617, 'SARIÇA KILIÇLI 1965 SPOR'),
(618, 'MERYEM-MEHMET KAYHAN ORTAOKULU'),
(619, 'BİLGE KAĞAN ORTAOKULU'),
(620, 'ÇUKUROVA İMAM HATİP ORTAOKULU'),
(621, 'YAVUZLAR ORTAOKULU'),
(622, 'İSTİKLAL ORTAOKULU'),
(623, 'Ç. ŞEHİT ERTAN TOKUŞ ORTAOKULU'),
(624, 'İSMAİL HAZAR ORTAOKULU'),
(625, 'RAMAZANOĞLU ORTAOKULU'),
(626, 'Ö. ZEYNEP ERDOĞDU ORTAOKULU'),
(627, 'KARŞIYAKA ORTAOKULU'),
(628, 'ŞEHİT ENVER BUĞUR ORTAOKLU'),
(629, 'KAZİME ÖZLER ORTAOKULU'),
(630, 'REMZİ OĞUZ ARIK ORTAOKULU'),
(631, 'L. ALİ ŞADİ ÇELİK ORTAOKULU'),
(632, 'KOZAN KOLEJİ ORTAOKULU'),
(633, 'KÖZRELİ ORTAOKULU'),
(634, 'ADANA 01 FUTBOL KULÜBÜB SK'),
(635, '60.YILŞEHİT MUSTAFA AKER ORTAOKULU'),
(636, 'GENÇELRBİRLİĞİSPOR'),
(637, 'ADANA AMATÖRCESPOR'),
(638, 'ADANA MİLANOSPOR'),
(639, 'ADANA ÇUKUROVA 01 SPOR'),
(640, 'ADANA EMEK 01 SPOE'),
(641, 'ADANA SARIÇAM 01 SPOR'),
(642, 'ADANA BAROSU F.T.'),
(643, 'ADANA BAROSU HUKUK ADALET'),
(644, 'ADANA BAROSU S.K.'),
(645, 'GAZİANTEP BAROSU'),
(646, 'HATAY BAROSU'),
(647, 'İSTANBUL ANADOLU BAROSU'),
(648, 'ADANA BAROSU ANAVARZA F.T.'),
(649, 'GAZİANTEP ZEUGMA'),
(650, 'SAKARYA BAROSU'),
(651, 'KÜTAHYA BAROSU'),
(652, 'SİVAS BAROSU'),
(653, 'ADANA BAROSU ADONİS'),
(654, 'İZMİR KÖRFEZ'),
(655, 'İSTANBUL ANKA'),
(656, 'ANKARA GORDİON'),
(657, 'İZMİR ALSANCAK'),
(658, 'YÜREĞİRGÜCÜSPOR'),
(659, 'ADANA DOSTLARSPOR'),
(660, 'ADANA KÜÇÜK DİKİLİ 01 SPOR'),
(661, 'KÜÇÜK DİKİLSPOR FK'),
(662, 'ÇUKUROVA RES HAVUZLUBAHÇESPOR'),
(663, 'ADANAGÜCÜSPOR'),
(664, 'YAZ SİGORTA ADALETGÜCÜSPO'),
(665, 'ADANA CİTY 01 SPOR'),
(666, 'ADANA ANAFARTALAR 01 SPOR'),
(667, 'İAMAMOĞLU BELEDİYESPOR'),
(668, 'ADANA ŞİMŞEKLER 01 SPOR'),
(669, 'CEYHAN STAR SPOR'),
(670, 'ADANA BARIŞGÜCÜSPOR'),
(671, 'ADANA GÜNEY 01 SPOR'),
(672, 'ADANA ELİT 01 SPOR'),
(673, 'ADANA YILDIZLAR 01 SPOR'),
(674, 'T0ROSLAR 1922 SPOR'),
(675, 'ADANA İMAMOĞLU 01 SPOR'),
(676, 'ADANA BEŞOCAK 01 SPOR'),
(677, 'İZMİR BARO'),
(678, 'İSTANBUL BARO'),
(679, 'SARIÇAM KÜKÇÜLERSPOR'),
(680, 'İSKENDERUN 6 ŞUBAT SPOR KULÜBÜ'),
(681, 'KAYSERİ KADIN FUTBOL KULÜBÜ'),
(682, 'ADANA AMATÖRCE SPOR'),
(683, 'TURKISH OIL Y. MERSİN İDMANYURDU A.Ş.'),
(684, 'CEYHAN TENİS LİFE SPOR'),
(685, 'ANADOLU 19 MAYISPOR'),
(686, 'ADANA GÜNEŞİ FK'),
(687, 'ADANA MİLANO SPOR'),
(688, 'ADANA DOSTLAR 01 SPOR'),
(689, 'ÇUKUROV DEMİRSPOR'),
(690, 'ADANA DOSTLAR SPOR KULÜBÜ'),
(691, 'MERMERAY EFSANE SPOR KULÜBÜ'),
(692, 'BAKLLAISPOR'),
(693, 'ADANA YILDIZLAR 10 SPOR'),
(694, 'YAMANSPOR'),
(695, 'YAMAN SPOR'),
(696, 'ADANA DOSTLAR SPOR'),
(697, 'SEYHANGÜCÜSOOR'),
(698, 'ADANA BARIŞGÜCÜ SPOR'),
(699, 'ADANA DEMİR SPOR'),
(700, 'TOROS SPOR'),
(701, 'ÇUKUROVA KOZA SPOR'),
(702, 'ÇUKUROVA DEMİRORDU SPOR'),
(703, '01 ADANA FK'),
(704, 'RAMAY SPOR'),
(705, 'SİMERANYA SPOR'),
(706, 'GAZİKENTSPOR'),
(707, 'SAIRÇAM BELEDİYESPOR'),
(708, 'YUSUF BAYSAL ANADOLU LİSESİ'),
(709, 'BUCAK Ç.P.A.L.'),
(710, 'ŞEHİT ARDA CAM M.T.A.L.'),
(711, 'RAMAZANOĞLU M.T.A.L.'),
(712, '50. YIL İBRAHİM YÜCE ANADOLU L,İSESİ'),
(713, 'KOZAN ANADOLU LİSESİ'),
(714, 'KAZIM KARABEKİR ANADOLU LİSESİ'),
(715, 'MEHMET AKİF ERSOY ANADOLU LİSESİ'),
(716, 'OSMANGAZİ ANADOLU İMAM HATİP LİSESİ'),
(717, 'SANKO ANADOLU LİSESİ ALADAĞ'),
(718, 'FATİH ANADOLU LİSESİ'),
(719, 'SOLAR AKADEMİ M.T.A.L.'),
(720, 'TOROS TARIM A.L.'),
(721, 'İLBEYLİ BEYTEKS MTAL'),
(722, 'MOYAL'),
(723, 'CEYHAN FİNAL AKADEMİ'),
(724, 'FEN LİSESİ'),
(725, 'HALİL ÇİFTCİ A.L.'),
(726, 'ŞABAN ÜÇGÜL MTAL'),
(727, 'YALTIR KARDEŞLER O.O.'),
(728, 'NURHAN DEMİRTAŞ.O.O'),
(729, 'PAMUKELİ O.O.'),
(730, 'Ş.HÜSEYİN BİRİMEN O.O.'),
(731, 'SARIÇAM KILIÇLIU 1965 SPOR'),
(732, 'ŞEHİT MEHMET ATICI ANADOLU LİSESİ'),
(733, 'KOZAN İMARSPPOR'),
(734, 'CEYHAN MTAL'),
(735, 'KARMA 1'),
(736, 'KARMA 2'),
(737, 'ADANA ŞİMŞEKLERSPOR'),
(738, 'YEŞİLVELERSPOR'),
(739, 'ADANA GÜNEŞİ F.K.'),
(740, 'ŞANPİYON ÇOCUK SPOR'),
(741, 'ADANA EMEKSPOR 01 SPOR'),
(742, 'GENÇLERBİRLİĞİ SPOR'),
(743, 'ANAFARTALAR ANADOLU LİSESİ'),
(744, 'ŞEHİT AHMET - MEHMET ORUÇ SPOR LİSESİ'),
(745, 'MOBİL A.Ş. TİCARET M.T.A.L.'),
(746, 'ADANA SARIÇAM SPOR LİSESİ'),
(747, 'ADANA ANAFARTALAR SPOR'),
(748, 'TURGUT ÖZAL ANADOLU LİSESİ'),
(749, 'ÇOBANOĞLU TİCARET M.T.A.L.'),
(750, 'GAZİ MUSTAFA KEMAL ANADOLU LİSESİ'),
(751, 'LOKMAN HEKİM ANADOLU LİSESİ'),
(752, 'ŞABAN ÜÇGÜL M.T.A.L.'),
(753, 'CEYHAN M.T.A.L.'),
(754, 'Ö. CEYHAN FİNAL AKADEMİ'),
(755, 'İLBEYLİ BEYTEKS M.T.A.L.'),
(756, 'TOROS TARIM ANADOLU LİSESİ'),
(757, 'M.O.Y. A. L.'),
(758, 'OSMANGAZİ ANADOLU İMAM AHTİP LİSESİ'),
(759, 'ÇUKUROVA İ.H.O.O.'),
(760, 'DOSTELLER O.O.'),
(761, 'AHMET SAPMAZ O.O.'),
(762, 'ÖZEL SULAR O.O.'),
(763, 'Ş. EBUBEKİR DURMUŞ O.O.'),
(764, 'Ş. HALİT YAŞAR MİNE O.O.'),
(765, 'MERYEM MEHMET KAYHAN O.O.'),
(766, 'PROF.DR. FUAT SEZGİN O.O.'),
(767, 'ŞAKİRPAŞA ANADOLU LİSESİ'),
(768, 'HACI AHMET ATIL ANADOLU LİSESİ'),
(769, 'BARBAROS BORSA İSTANBUL A. L.'),
(770, 'YAVUZ SELİM O.O.'),
(771, 'BAYRAM KARADAĞ O.O.'),
(772, 'Ş.HÜSEYİN BİRMEN O.O.'),
(773, 'Ş. ENVER BUĞUR O.O.'),
(774, 'Ş.YILMAZ BOZKURT O.O.'),
(775, 'ŞAKİRPAŞA ÜMRAN O.O.'),
(776, 'ADANA TİÇARET ODASI A.L.'),
(777, 'ANAFARTALAR A.L.'),
(778, 'ÖZEL AOSB SARIÇAM TEKNOLOJİ KOLEJİ M.T.A.L.'),
(779, 'AKEDAŞ KAHRAMANMARAŞ İSTİKLAL SPOR'),
(780, 'İNCİRLİK ANADOLU LİSESİ'),
(781, 'Ş.HALİT YAŞAR MİNE O.O.'),
(782, 'Ş. YILMAZ BOZKURT O.O.'),
(783, 'ADANA TİCARET BOSASI ANADOLU LİSESİ'),
(784, 'Ş.MUHAMMET ALİ DEMİR O.O.'),
(785, 'KARAİSALI ATATÜRK O.O.'),
(786, 'ŞAKİRPAŞA ÜMRAN O.O.'),
(787, 'YAVUZ SELİM O.O.'),
(788, 'BEŞOACK DEMİRSPOR'),
(789, 'ADANA KÜÇÜKDİKİLİ 01 SPOR'),
(790, 'CEYAHNGÜCÜSPOR'),
(791, 'GAZİANTEP ASYASPOR'),
(792, 'ADANA 01 CİTY SPOR'),
(793, 'BARBAROS BORSA İSTANBUL ANADOLU LİSESİ'),
(794, 'ŞEHİT MUHAMMET ALİ DEMİR ORTAOKULU'),
(795, 'ÖĞRETMEN ZEYNEP ERDOĞDU ORTAOKULU'),
(796, 'KARAİSALI ATATÜRK ORTAOKULU'),
(797, 'ŞEHİT YILMAZ BOZKURT ORTAOKULU'),
(798, 'İSMET İNÖNÜ O.O.'),
(799, 'ÖZEL KOZAN KOLEJİ O.O.'),
(800, 'Ş. CENGİZ EROĞLU O.O.'),
(801, 'ŞEHİT EBUBEKİR DURMUŞ ORTAOKULU'),
(802, 'YALTIR KARDEŞLER ORTAOKULU'),
(803, 'PROF.DR. FUAT SEZGİN ORTAOKULU'),
(804, 'AHMET SAPMAZ ORTAOKULU'),
(805, 'DOSTELLER ORTAOKULU'),
(806, 'MERYEM - MEHMET KAYHAN ORTAOKULU'),
(807, 'ŞEHİT AHMET-MEHMET ORUÇ SPOR LİSESİ'),
(808, 'ADANASPOR A.Ş'),
(809, 'MALATYA YEŞİLYURTSPOR KULÜBÜ'),
(810, 'ÖZEL SULAR ORTAOKULU'),
(811, 'ŞEHİT HALİT YAŞAR MİNE ORTAOKULU'),
(812, 'ÇARKIPARE Ş. ERTAN TOKUŞ ORTAOKULU'),
(813, 'BAYRAM KARADAĞ ORTAOKULU'),
(814, 'ŞEHİT HÜSEYİN BİRİMEN ORTAOKULU'),
(815, 'ŞAKİRPAŞA ÜMRAN ORTAOKULU'),
(816, 'SARIÇAM DMİRSPOR'),
(817, 'ÇUKURIVA DEMİRSPOR'),
(818, 'TUFAN BEYLİ BELEDİYESPOR'),
(819, 'YAPI MESLEKPOR'),
(820, 'SİSLFKE BELEDİYESPOR'),
(821, 'TALAS BELEDİYESPOR'),
(822, 'ÜRGÜP SPOR'),
(823, 'SARIÇAMGÜCÜSPOR'),
(824, 'SARIÇAN BURUKSPOR'),
(825, 'ADANA BESOCAK 01 SPOR'),
(826, 'ADANA GARIŞGÜCÜ SPOR'),
(827, 'SARIÇAM KILIÇLI 1965 2SPOR'),
(828, 'EMEK SPOR'),
(829, 'ADANA ŞİMŞEKLER SPOR'),
(830, 'ADANA 01 FUTBOL KUÜBÜ S.K.'),
(831, 'K.MARAŞ TOSYALI SPOR'),
(832, 'KARAMAN BİLGE GENÇLİK'),
(833, 'MERSİN KUVAYİ MİLLİYE'),
(834, 'HATAY KIVILCIM SPOR'),
(835, 'MERSİN YENİŞEHİR'),
(836, 'İSKENDERUN POYRAZ GENÇLİK'),
(837, '7 MART KADİRLİ DEMİR SPOR'),
(838, 'KİLİS 01 SPOR'),
(839, 'ADANA DEMİSPOR A.Ş.'),
(840, 'BEKO'),
(841, 'KOLAY GELSİN'),
(842, 'YAPIKREDİ'),
(843, 'KÜÇÜK DİKİLİ 01 SPOR'),
(844, 'AOSB'),
(845, 'DKC AV SANAYİ'),
(846, 'GETA TARIM'),
(847, 'ADANA MENSUCAT'),
(848, 'OĞUZ TEKSTİL'),
(849, 'JANDARMA'),
(850, 'TAT NİŞAŞTA'),
(851, 'ANKUTSAN'),
(852, 'CEYTECH'),
(853, 'BOSSA'),
(854, 'ZAHİT ALÜMİNYUM'),
(855, 'TOYAS TEKNİK'),
(856, 'BETA ENERJİ'),
(857, 'GENER TARIM'),
(858, 'GÜLEZLER METAL'),
(859, 'DOĞANAY GIDA'),
(860, 'OMNİA NİŞAŞTA'),
(861, 'ATEKS MÜHENDİSLİK'),
(862, 'SİMGETEK'),
(863, 'PALMİYE TEKSTİL'),
(864, 'ADM BESİN'),
(865, 'SP ENERJİ'),
(866, 'ARSETEKS'),
(867, 'ÇUKUROVA SİLO'),
(868, 'PİDOK PLASTİK'),
(869, 'ATLANTİK MOBİLYA'),
(870, 'SAFAŞ PLASTİK'),
(871, 'AFAD'),
(872, 'DEM ÇATI'),
(873, 'ŞARMAK MAKİNA'),
(874, 'ASLANBAŞ ÇİVİ'),
(875, 'BETA GIDA'),
(876, 'CEYHAN STARSPOR'),
(877, 'SETHAN BELEDİYESPOR'),
(878, 'TOSYALI SPOR KULÜBÜ'),
(879, 'HATAY KIVILCIM SPOR'),
(880, 'MERSİN YENİŞEHİR SPOR KULÜBÜ'),
(881, 'İSKENDERUN POYRAZ GENÇLİK VE SPOR'),
(882, 'TOSYALI K.MARAŞ S.K.'),
(883, 'HADIRLIGÜCÜ.'),
(884, 'ÇUKUROVA DEMİR SPOR'),
(885, 'ADANA YILDIRIM SPOR'),
(886, 'YAPI KREDİ'),
(887, 'YILDRIM DEMİR 01 SPOR'),
(888, 'KOZANSOR F.K.'),
(889, 'OĞUZ GIDA'),
(890, 'REALTEKS'),
(891, 'SARMAK MAKİNA'),
(892, 'PALMİYE TESKTİL'),
(893, 'OMNIA NİŞASTA'),
(894, 'TEMİZ - İŞ TENEKE'),
(895, 'LİMKON GIDA'),
(896, 'TEZCANLAR YATIRIM'),
(897, 'STD TRANSFORMATÖR'),
(898, 'RT MAKİNA'),
(899, 'TAT NİŞASTA'),
(900, 'MAKSER PVC'),
(901, 'LHAL ŞAMPİYON'),
(902, 'Ş.ABDULLAH YILDIRIM A.L.'),
(903, 'KAZIM KARABEKİR A.L.'),
(904, 'SİMERANYA T.SPOR'),
(905, 'SUNAR NURİ ÇOMU A.L.'),
(906, 'Ş.ABDULLAH AYDIN EMER A.L.'),
(907, 'LHAL ANAKONDA'),
(908, 'ÇİN SEDDİ'),
(909, '5 OCAK DEMİR SPOR'),
(910, 'KIRMIZI ŞİMŞEKLER'),
(911, 'GÜNEŞ IŞIKLARI'),
(912, 'TOKİ KÖPRÜLÜ A.L.'),
(913, 'SARI PAPATYALAR'),
(914, 'AKDENİZ CİTY'),
(915, 'LOKMAN HEKİM KIZ TAKIMI'),
(916, 'TROS DEMİRSPOR'),
(917, 'ÇUKUROVA AKDENİZPSPOR'),
(918, 'SEYHAM DEMİRSPOR'),
(919, 'SAFAS PLASTİK'),
(920, 'PIDOK PLASTİK'),
(921, 'OMNİA NİŞASTA'),
(922, 'ADANA ANAFARTALAR 10 SPOR'),
(923, 'ÇUKUROAV DEMİRSPOR'),
(924, 'ŞEHİT HACI AHMET ÖZTÜRK İ.H. ORTAOKULU'),
(925, 'HADIRLI ORTAOKULU'),
(926, 'MEHMET ZAHİD KOTKU İ.H. ORTAOKULU'),
(927, '125. YIL ORTAOKULU'),
(928, 'MEHMET TAL İ.H. ORTAOKULU'),
(929, 'ÖMER NASUHİ BİLMEN İ.H. ORTAOKULU'),
(930, 'SADIKA SABANCI ORTAOKULU'),
(931, 'NİZAMÜLMÜLK İ.H. ORTAOKULU'),
(932, 'ÖZEL ADANA İSABET ORTAOKULU'),
(933, 'İNKILAP İ.H. ORTAOKULU'),
(934, 'DUMLUPINAR ORTAOKULU'),
(935, 'MERYEM - ABDURRAHİM GİZER ORTAOKULU'),
(936, 'ÖZEL MİNECAN ORTAOKULU'),
(937, 'SABANCI AİLESİ İ. H. ORTAOKULU'),
(938, 'ÖZEL SEYHAN ALTINELLER ORTAOKULU'),
(939, 'SARIÇAM İSTİKLAL ORTAOKULU'),
(940, 'EFENDİ HALİL ORTAOKULU'),
(941, 'ŞEHİT NİHAT ŞENER ORTAOKULU'),
(942, 'TOROS ORTAOKULU'),
(943, 'CUMHURİYET ORTAOKULU'),
(944, 'ÖZEL ADANA İSTANBUL LİDER KOLEJİ ORTAOKULU'),
(945, 'NİZAMÜLMÜLK İ. H. ORTAOKULU'),
(946, 'ŞEHİT HACI AHMET ÖZTÜRK İ. H. ORTAOKULU'),
(947, 'MEHMET TAL İ. H. ORTAOKULU'),
(948, 'ÖMER NASUHİ BİLMEN İ. H. ORTAOKULU'),
(949, 'MEHMET ZAHİD KOTKU İ. H. ORTAOKULU'),
(950, 'NECİP FAZIL KISAKÜREK İHO'),
(951, 'Ş.ADEM OĞUZ O.O'),
(952, 'BUCAK O.O.'),
(953, 'Ş.MEHMET OFLAZ YBO'),
(954, '2 HAZİRAN O.O.'),
(955, '60.YIL O.O.'),
(956, 'YUNUS EMRE O.O.'),
(957, 'BÜYÜKSOFULU O.O.'),
(958, 'Ş.ZİYA ÖZKOZANOĞLU O.O.'),
(959, 'ALADAĞ İHOO'),
(960, 'Ş.ÖĞRETMEN LÜTFİ OFLAZ O.O.'),
(961, 'A GRUBU BİRİNCİSİ'),
(962, 'C GRUBU İKİNCİSİ'),
(963, 'B GRUBU BİRİNCİSİ'),
(964, 'D GRUBU İKİNCİSİ'),
(965, 'C GRUBU BİRİNCİSİ'),
(966, 'A GRUBU İKİNCİSİ'),
(967, 'D GRUBU BİRİNCİSİ'),
(968, 'B GRUBU İKİNCİSİ'),
(969, 'ADANA MENCUSAT'),
(970, 'PALMİYA TEKSTİL'),
(971, 'İNKILAP İMAM HATİP ORTAOKULU'),
(972, 'SABANCI AİLESİ İMAM HATİP ORTAOKULU'),
(973, '23 NİSAN İMAM'),
(974, 'SARIÇAM ORHANGAZİ'),
(975, 'NECDET KAHRAMAN'),
(976, 'BEDİÜZZAMAN'),
(977, 'ÇUKUROVA İHO'),
(978, 'YAŞAR RUKİYE'),
(979, 'MEHMET ZAHİD'),
(980, 'SABANCI AİLESİ'),
(981, 'NURETTİN TOPÇU'),
(982, 'MEHMET TAL'),
(983, '15 TEMMUZ'),
(984, 'İNKILAP İHO'),
(985, 'TOKİ ERTUĞRUL GAZİ'),
(986, 'ŞEHİT MURAT DEMİRCİ'),
(987, 'EMİNE ÖZGÜLER'),
(988, 'HACI  BAYRAM VELİ'),
(989, 'CEYHAN 1967 S.K.'),
(990, 'ÖZEL ADANA BİL KOLEJİ ORTAOKULU'),
(991, 'ŞEHİT ADEM OĞUZ YİBO'),
(992, 'ŞEYH ŞAMİL'),
(993, 'HAYRET EFENDİ'),
(994, 'MEHMET ADİL İKİZ'),
(995, 'İNKILAP İMAM'),
(996, 'NİZAMÜLMÜLK'),
(997, 'NİZAMÜLMÜLK İMAM HATİP ORTAOKULU'),
(998, 'MEHMET TAL İMAM HATİP ORTAOKULU'),
(999, 'ADANA 01 FUTBOL KULUBÜ S.K.'),
(1000, 'ADANA BÜYÜKŞEHİR BELEDİYESİSPOR'),
(1001, 'KÜÇÜK DİKİLİ O1 SPOR'),
(1002, 'KOZAN İMARSSPOR'),
(1003, 'ÖZEL ADANA BİL. KOLEJİ ORTAOKULU'),
(1004, 'CEYHA STAR SPOR'),
(1005, 'GZP.SANAYİ ESNAF SPOR'),
(1006, 'BİNEVLER SPOR'),
(1007, 'MERSİN KUVAYİ MİLLİYE'),
(1008, 'HATAY YENİÇAĞ FK'),
(1009, 'G.ANTEP ŞEHİT KAMİL'),
(1010, 'Ş.URFA DSİ'),
(1011, 'Ş.URFA BLD.'),
(1012, 'HATAY ALTINGENÇLİK'),
(1013, 'YENİÇAĞ FUTBOL SPOR KULÜBÜ'),
(1014, 'GAZİANTEP ŞEHİT KAMİL BELD. SPOR KULÜBÜ'),
(1015, 'ARSUZ ALTINGENÇLİK VE SPOR'),
(1016, 'BİNEVLER SPOR'),
(1017, 'RELATEKS'),
(1018, 'ADANA EMEKSPOR'),
(1019, 'ADANA GARIŞGÜCÜSPOR'),
(1020, 'TOKİ ŞEHİTOZAN ONUR İLGEN ANADOLU LİSESİ'),
(1021, 'BAHTİYAR VAHABZADE SOSYAL BİLİMLER LİSESİ'),
(1022, 'PİRİ REİS ANADOLU LİSESİ'),
(1023, 'YEŞİLEVLER MTAL'),
(1024, 'FATİH TERİM ANADOLU LİSESİ'),
(1025, 'SEYHAN BORSA İSTANBUL FEN LİSESİ'),
(1026, 'SANKO ALADAĞ ANADOLU LİSESİ'),
(1027, 'ŞEHİT İBRAHİM DERİNDERE ANADOLU LİSESİ'),
(1028, 'ÖMER NASUHİ BİLMEN AİHL'),
(1029, 'ÖA MODERN BİLİMLER AKADEMİSİ ( MBA ) AL'),
(1030, 'FARABİ ANADOLU LİSESİ'),
(1031, 'AHMET KURTTEPELİ ANADOLU LİSESİ'),
(1032, 'KARAİSALI ANADOLU LİSESİ'),
(1033, 'ŞEHİT EREN YÜCEL ANADOLU LİSESİ'),
(1034, 'SEYHAN DANİŞMENT GAZİ ANADOLU LİSESİ'),
(1035, 'YÜREĞİR HALICILAR ANADOLU LİSESİ'),
(1036, 'ÇARKIPARE ŞEHİT ERTAN TOKUŞ ORTAOKULU'),
(1037, 'CAMİLİ AKARCA ORTAOKULU'),
(1038, 'OSMANGAZİ ORTAOKULU'),
(1039, 'MEHMET AKİF ANADOLU LİSESİ'),
(1040, 'RAMAZANOĞLU MTAL'),
(1041, 'SOLAR AKADEMİ'),
(1042, 'ALADAĞ SANKO ANADOLU LİSESİ'),
(1043, 'SİS MTAL'),
(1044, '50.YIL İBRAHİM YÜCE ANADOLU LİSESİ'),
(1045, 'CAMİLİ KARACA ORTAOKULU'),
(1046, 'ŞEHİT KADİR KIRBAÇ KIZ ANADOLU LİSESİ'),
(1047, 'ÖZEL UZAY MESLEKİ VE TEKNİK ANADOLU LİSESİ'),
(1048, 'ŞEHİT ALİ BEZİK ANADOLU LİSESİ'),
(1049, 'HAVUZLUBAHÇE KIZ MESLEKİ VE TEKNİK A.L.'),
(1050, 'SARIHAMZALI ANADOLU LİSESİ'),
(1051, 'ABDULKADİR PAKSOY ANADOLU LİSESİ'),
(1052, 'SARIÇAM AİHL'),
(1053, 'BORSA İSTANBUL ŞEHİT ERHAN KONUK AİHL'),
(1054, 'ŞEHİR ALİ BEZİK ANADOLU LİSESİ'),
(1055, 'ABFT 1'),
(1056, 'ABFT 2'),
(1057, 'Av. MİLAN'),
(1058, 'GENÇLERBİRLİĞİ'),
(1059, 'ADALET GÜCÜ'),
(1060, 'SÜP-ERSİN FK'),
(1061, 'ADONİS VETO'),
(1062, 'MİZANİ ADALET'),
(1063, 'BODO GLINT'),
(1064, 'BERAAT FC'),
(1065, 'BAROSSİA'),
(1066, 'KARATAŞ ADLİYESİ'),
(1067, 'NANKATSU'),
(1068, 'AL VETERAN'),
(1069, 'ADANA 01 FUTBOL KULÜBÜ F.K.'),
(1070, 'ÇUKUROVA  DEMİR ORDUSPOR'),
(1071, 'CEYHAN TENİS LİFESPOR'),
(1072, 'TARSUS GENÇLERBİRLİĞİSPOR'),
(1073, 'GÜNET ADANASPOR'),
(1074, 'SÜP - ERSİN FK'),
(1075, 'LAW SAINT GERMAIN'),
(1076, 'ABFT2'),
(1077, 'GENÇLER BİRLİĞİ'),
(1078, 'ABFT1'),
(1079, 'AV. MİLAN'),
(1080, 'STD'),
(1081, 'KABE\'DE HACILAR'),
(1082, 'SANCAR REİS 01'),
(1083, 'ÇOBANOÜLU TİCARET M.T.A.L.'),
(1084, 'AZİZ SANCAR M.T.A.L.'),
(1085, 'TOROSLAR 1922 S.K.'),
(1086, 'ÇEP M.T.A.L.'),
(1087, 'HALL NASR'),
(1088, 'SİMERANYA T. S.K.'),
(1089, 'DADAL AND.'),
(1090, 'TOROS KAPLANLARI S.K.'),
(1091, 'LAW SAINT GERMAİN'),
(1092, 'ADANAGÜCÜ'),
(1093, 'TAKM 1'),
(1094, 'CEYHAN TENİS LİFE'),
(1095, 'AMATÖRCE SK'),
(1096, 'EMEK 01 SK'),
(1097, 'GÜNEY 01 SK'),
(1098, 'ŞİMŞEKLER 01 SK'),
(1099, 'DOSTLAR SK'),
(1100, 'ADANA GÜNEŞİ SK'),
(1101, 'BARIŞGÜCÜ SK'),
(1102, 'DEM ÇATI İNŞAAT'),
(1103, 'TARSUS GENÇLERBİRLİĞİ'),
(1104, 'BAROSSIA DORTMUND'),
(1105, 'PANZERLER'),
(1106, 'ÇAĞIRKANLISPOR'),
(1107, 'KARDEŞLERGÜCÜ'),
(1108, 'BAY'),
(1109, '1980\'LER'),
(1110, 'YUNUSOĞLU FK'),
(1111, 'BEY BETON SPOR'),
(1112, 'YILDIZLAR FK'),
(1113, 'YUNUSOĞLU SOCİEDAD'),
(1114, 'YUNUSOĞLUSPOR'),
(1115, 'GENÇLERGÜCÜ'),
(1116, 'POSYAĞBASAN SPOR'),
(1117, 'GERDİBİ SPOR'),
(1118, 'BÜYÜK SOFULU SPOR'),
(1119, 'LF AHŞAP AKÖREN'),
(1120, 'BEŞ OCAK DEMİRSPOR'),
(1121, 'BAŞPINAR SPOR'),
(1122, 'TAHTALI KEFEN SPOR'),
(1123, 'ÖZCANLAR MOBİLYA KÖKEZ'),
(1124, 'ALADAĞ SPOR'),
(1125, 'KARATAŞ'),
(1126, 'DERİNDERE ORM. MARVİYAN'),
(1127, 'AKÖREN GENÇLİK'),
(1128, 'LF AHŞAP GERDAĞ'),
(1129, 'KRİTER İNŞ. EBRİŞİM'),
(1130, 'GİLDİRLİSPOR'),
(1131, 'BOZTAHTA ORMAN SPOR'),
(1132, 'KUŞCUSOFULU 1650'),
(1133, 'ÇATALANSPOR'),
(1134, 'SOFULU CİTY'),
(1135, 'SİVİŞLİ GENÇLİK'),
(1136, 'SOĞUKOLUK SPOR'),
(1137, 'SİNAPAŞA FK'),
(1138, 'KIRIKLISPOR'),
(1139, 'KUŞCUSOFULU FK'),
(1140, 'MURT ÇUKURUSPOR'),
(1141, 'ÇORLUSPOR'),
(1142, 'KUZGUN FK'),
(1143, 'ÇECELİSPOR'),
(1144, 'KAŞOBASPOR'),
(1145, 'ÇEVLİKSPOR'),
(1146, 'BÜYÜKSOFULU SPOR'),
(1147, 'DÖRTLERSPOR'),
(1148, 'SADIKALİSPOR'),
(1149, 'EMELCİK 1977'),
(1150, 'KALEDAĞISPOR'),
(1151, 'BAY BETON SPOR'),
(1152, 'SİNANPAŞA FK'),
(1153, 'MURTÇUKURUSPOR'),
(1154, 'BOZTAHTA ORMANSPOR'),
(1155, 'DERİNDERE ORM. MAVRİYAN'),
(1156, 'KUŞÇUSOFULU 1650'),
(1157, 'KUŞÇUSOFULU FK'),
(1158, 'ALADAĞSPOR'),
(1159, 'ÖZCANLAR MOB. KÖKEZ');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `ad` varchar(100) NOT NULL,
  `soyad` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `rol` int(11) NOT NULL DEFAULT 2 COMMENT '1: Yönetici, 2: Hakem, 3: Gözlemci',
  `klasman` varchar(100) DEFAULT NULL,
  `telefon` varchar(11) DEFAULT NULL,
  `lisans_no` varchar(50) DEFAULT NULL,
  `dogum_tarihi` date DEFAULT NULL,
  `last_birthday_notification_year` int(4) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `egitim_durum` tinyint(1) DEFAULT 0,
  `antreman_durum` tinyint(1) DEFAULT 0,
  `ceza_durum` tinyint(1) DEFAULT 0,
  `uyari_kaldirildi` tinyint(1) DEFAULT 0,
  `tc_no` varchar(11) DEFAULT NULL,
  `banka_bilgisi` varchar(255) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `dogum_yeri` varchar(100) DEFAULT NULL,
  `kan_grubu` varchar(5) DEFAULT NULL,
  `il` varchar(50) DEFAULT NULL,
  `ilce` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `ad`, `soyad`, `email`, `sifre`, `rol`, `klasman`, `telefon`, `lisans_no`, `dogum_tarihi`, `last_birthday_notification_year`, `aktif`, `olusturma_tarihi`, `egitim_durum`, `antreman_durum`, `ceza_durum`, `uyari_kaldirildi`, `tc_no`, `banka_bilgisi`, `iban`, `dogum_yeri`, `kan_grubu`, `il`, `ilce`) VALUES
(1047, 'Admin', '0', 'ihk@mbsadana.com', '$2y$10$dpfLFK/qMf9p7dUF3oyK3ea7x9ygAx//sZ.5lr2lUb.S2QBg5aw7e', 1, '', '', '', NULL, NULL, 1, '2025-08-27 14:58:55', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2259, 'ZORBAY', 'KÜÇÜK', '28522@mbs.com', '$2y$10$mV3khYDrgYw0ccC7Vq3vYu55bNIuCXc8NgR5/tiuXxiiaK4gHQosa', 2, 'Üst Klasman Hakemi', '', '28522', '1992-09-14', NULL, 0, '2025-08-27 19:13:31', 0, 0, 0, 1, '', '', '', '', '', '', ''),
(2260, 'FERHAT', 'ÇALAR', '40362@mbs.com', '$2y$10$A5yjPWj6eIG1cGz0ts.Y9OHgBE6AQ7gbjVNw4hlNTYOSm6D0wEE1G', 2, 'Üst Klasman Yardımcı Hakemi', '0?', '40362', '1997-09-20', NULL, 1, '2025-08-27 19:13:31', 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2261, 'FURKAN', 'ÜRÜN', '27235@mbs.com', '$2y$10$rvxZFE5mFS2uOPFqHM4vo.SiC7xyDROwiTwrPrEErfKTKRS.S1ZfS', 2, 'Üst Klasman Yardımcı Hakemi', '', '27235', '1991-10-06', NULL, 1, '2025-08-27 19:13:32', 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2262, 'MERT', 'BULUT', '28527@mbs.com', '$2y$10$E/m73oFOSde9A3sQpFPk3OPFHbFRpCZv05yRFvSz3iVSB6QNqQhDO', 2, 'Üst Klasman Yardımcı Hakemi', '05345580788', '28527', '1992-09-09', NULL, 1, '2025-08-27 19:13:32', 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2263, 'MURAT', 'TEMEL', '21052@mbs.com', '$2y$10$0iNchx6jjeKVqSOwlGrqweUtWWV5O6I2felISwOZ/vQpn6dt26dvW', 2, 'Üst Klasman Yardımcı Hakemi', '05056656343', '21052', '1984-11-20', NULL, 1, '2025-08-27 19:13:32', 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2264, 'DOĞUKAN', 'YILDIRIM', 'dogukan_01_38@hotmail.com', '$2y$10$EsjnRo.fEHdOyqZwEik.Eu1Fwc8gzew0CsK.969eW42oKPQHHftk6', 2, 'Klasman Hakemi', '05447221020', '41113', '1992-02-26', NULL, 1, '2025-08-27 19:13:32', 1, 1, 0, 1, '32881602370', 'Denizbank', 'TR510013400000741241100002', 'Adana', '0 RH(', 'ADANA', 'SEYHAN'),
(2265, 'FATİH', 'YÜZBAŞI', 'yuzbasi70@gmail.com', '$2y$10$YeC6HevcBC/95k7Vm17ShO53yuj/fahHdbA5qEs4dBCaME8CdB6SW', 2, 'Klasman Yardımcı Hakemi', '05422246829', '42974', '1997-01-20', NULL, 0, '2025-08-27 19:13:32', 0, 0, 0, 1, '', '', '', '', '', '', ''),
(2266, 'ALİCAN', 'MÜMTAZ', '44350@mbs.com', '$2y$10$Xyzfo.zOiQngTXIJtfzai.NsGgwstSnqUrTzlR92t8mo7OeKTKDOi', 2, 'Bölgesel Hakem', '05427126670', '44350', '2000-10-02', NULL, 0, '2025-08-27 19:13:32', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2267, 'EMİR', 'TOPRAK', 'emirtoprak@hotmail.com', '$2y$10$xRYo7uk80cJt9Lez0UAzUeS09kNKJbkPFEJZ787wm3V3zqUY4KXCK', 2, 'Bölgesel Hakem', '05547183209', '29769', '1991-06-27', NULL, 1, '2025-08-27 19:13:32', 1, 1, 0, 1, '17206180016', 'Denizbank', 'TR44 0013 4000 0174 8456 8000 01', 'Adana', 'A RH ', 'Adana', 'Çukurova'),
(2268, 'GÜNEY', 'GÜNEYLİOĞLU', '31561@mbs.com', '$2y$10$WIs9jUsbDV4rPpLROrC0E.m0vBArIlA/KPLsig9p6a0sLwdCxiPje', 2, 'Bölgesel Hakem', '05522403290', '31561', '1995-04-23', NULL, 0, '2025-08-27 19:13:32', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2269, 'HACI HANİFİ', 'EVGİ', 'hanifievgi@gmail.com', '$2y$10$0gfs2810nPI3X3VlXoUA/unEduYUMuK0LqwD.LrN4pinnUfoGvGji', 2, 'Bölgesel Hakem', '05468852030', '46269', '2001-04-15', NULL, 0, '2025-08-27 19:13:32', 0, 0, 0, 1, '18692546966', 'DenizBank', 'TR07 0013 4000 0174 9619 6000 01', 'Osmaniye', 'A-rh(', 'Adana', 'Seyhan'),
(2270, 'MEHMET BATUHAN', 'BİLGİÇ', 'batuhanbilgic0@gmail.com', '$2y$10$MDEgoGc7ZIsELQRF08IAN.Aq6igXrnlFynbwNGUUL9z1JoOABEfTW', 2, 'Bölgesel Hakem', '05077788977', '49970', '1999-11-26', NULL, 1, '2025-08-27 19:13:32', 1, 1, 0, 1, '44968700480', 'Denizbank', 'TR330013400002004424500001', 'Yüreğir/Adana', '0 RH+', 'Adana', 'Seyhan '),
(2271, 'MUSTAFA BİLGİN', 'DELİBAŞ', 'av.mustafabilgindelibas@gmail.com', '$2y$10$TMNMGs.0esUouoVXYR4ZVu0k03fCFt.eNckGRfPPmsCucBhoAZwZy', 2, 'Bölgesel Hakem', '05358236667', '41996', '1997-07-13', NULL, 0, '2025-08-27 19:13:32', 0, 0, 0, 1, '10429338448', 'Denizbank', 'TR75 0013 4000 0165 5157 7000 01', 'Aladağ', '0+', 'Adana', 'Seyhan'),
(2272, 'TUFAN', 'GÖNOĞLU', 'tufanngonoglu@gmail.com', '$2y$10$USV4Y1aD8JqfgSPDPNR1Yu0MfdeJ7ylea.Weg54t4u06Kunw5pKG.', 2, 'Bölgesel Hakem', '05079127171', '43393', '1997-01-01', NULL, 1, '2025-08-27 19:13:32', 1, 1, 0, 1, '11023354278', 'Denizbank', 'TR77 0013 4000 0174 7736 4000 01', 'Adana', '0Rh+', 'Adana', 'Çukurova'),
(2273, 'HALİL', 'İLENGİZ', '31564@mbs.com', '$2y$10$yxM5YYdBhlQPzzpVKpFcsOuv/PHph7spWU6uHZ1eEeb2r9xHDTbce', 2, 'İl Hakemi', '05443731631', '31564', '1992-09-01', NULL, 0, '2025-08-27 19:13:32', 0, 0, 0, 1, '', '', '', '', '', '', ''),
(2274, 'ALİ KEMAL', 'SARIKÇIOĞLU', 'kemalsarikcioglu@gmail.com', '$2y$10$ODDDpBUZICKljbHeqPc8JOnc.znuRKjKlqZh0beUCFEXhi.4bXMmK', 2, 'Bölgesel Yardımcı Hakem', '05422143080', '44846', '1998-06-16', NULL, 0, '2025-08-27 19:13:32', 0, 0, 0, 1, '20542108170', 'Denizbank', 'TR13 0013 4000 0174 7976 3000 01', 'Yüreğir/ADANA', 'B rh+', 'Adana', 'Yüreğir'),
(2275, 'AYDIN', 'KUBAY', 'ankubay43@gmail.com', '$2y$10$SBo/9zOhAyHo9jSzz/YWAO12/OVrGIf5Fhg4GK4SdebzJt76TugJ.', 2, 'Bölgesel Yardımcı Hakem', '05346165631', '48138', '1997-07-27', NULL, 0, '2025-08-27 19:13:32', 0, 0, 0, 1, '37837532862', 'DENİZBANK', 'TR90 0013 4000 0174 7763 3000 01', 'SEYHAN', '0RH+', 'ADANA ', 'ÇUKUROVA'),
(2276, 'BURAK', 'DELİKANLI', 'burakdelikanli9@gmail.com', '$2y$10$8lpSsc5bvvpEig9shX9LZ.tIoeJ/mFy7Nb1BihG5t59XsjRKBgnSa', 2, 'Bölgesel Yardımcı Hakem', '05469132000', '48142', '2000-01-15', NULL, 0, '2025-08-27 19:13:32', 0, 0, 0, 1, '37159222304', 'Denizbank ', ' TR11 0013 4000 0174 9594 3000 01', 'Adana', 'A+', 'Adana', 'Çukurova'),
(2277, 'BURAK', 'SAVAŞ', 'buraksavas01@gmail.com', '$2y$10$WMTJgtzBNEHVczvWONSWw.18NWkWdZxcg5UgP0kA04CzbFxUNEccu', 2, 'Bölgesel Yardımcı Hakem', '05454491188', '41981', '1993-07-19', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '15289286618', 'DENIZBANK', 'TR490013400001748912600001', 'ADANA', 'A RH-', 'ADANA', 'CUKUROVA'),
(2278, 'DOĞUKAN', 'EKİNCİ', 'dogukanekkinci@gmail.com', '$2y$10$ZaLolp.CZqeajdcgBqvEAu10OehuzRhr1LMG4UMlA5Cq/2DWdvFMu', 2, 'Bölgesel Yardımcı Hakem', '05011032319', '49996', '2001-07-18', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '16811870350', 'DenizBank ', 'TR28 0013 4000 0199 3114 4000 01', 'Adana ', 'AB+', 'Adana', 'Seyhan'),
(2279, 'ERSİN', 'AYŞİN', 'ersinaysin01@gmail.com', '$2y$10$XPeRqgnZq76USsuBUikdb.JoW6DLO8e.jHA6ljqObFVfagx8xM9hm', 2, 'Bölgesel Yardımcı Hakem', '05465340884', '49995', '2003-10-07', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '16585212820', 'Deniz Bank', 'TR63 0013 4000 0199 4995 5000 01', 'Adana ', '0 RH+', 'Adana', 'Seyhan'),
(2280, 'HÜSEYİN BATUHAN', 'ŞAHİN', 'batuhan0106@outlook.com', '$2y$10$EtN9FgGzkMlKEs8aNk/1COjZBFMeegIM7U6abaSJLP62kcXWt/ICK', 2, 'Bölgesel Yardımcı Hakem', '05558488570', '49989', '1996-10-07', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2281, 'KERİM KAAN', 'MATYAR', 'k.matyar@gmail.com', '$2y$10$C7nZ61/ar7y7b9HZTD29J.b/ywv8XwOdfs1cO.SiRzZYFsVywCeVi', 2, 'Bölgesel Yardımcı Hakem', '05313638613', '43924', '1997-01-29', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '16720233420', 'Deniz Bank ', 'TR26 0013 4000 0174 9235 1000 01', 'Adana ', '0 RH(', 'Adana ', 'Seyhan '),
(2282, 'MUHAMMET EREN', 'GÖKTAŞ', 'erenngoktas@gmail.com', '$2y$10$KqfwLj4Hx5TjHC5X8F65LehvU44RZ81IMrYBf7FTqr7QBVDUVMEwO', 2, 'Bölgesel Yardımcı Hakem', '05453221940', '48147', '2001-10-08', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '64543376626', '17492669-351 / Kuzey Adana ', 'TR85 0013 4000 0174 9266 9000 01', 'Seyhan/Adana', 'A RH+', 'Adana', 'Çukurova'),
(2283, 'MURAT', 'ORUÇ', 'muroruc@outlook.com', '$2y$10$4fb5mTmCNX0Y2IOMLhlJ1epqNxMRHzFdw.f5OUO5p5Xe2780auV0S', 2, 'Bölgesel Yardımcı Hakem', '05070030417', '41993', '1994-11-17', NULL, 0, '2025-08-27 19:13:33', 0, 0, 0, 1, '31352092320', 'Denizbank', '770013400001790746200001', 'Alaşehir', 'A Rh+', 'Adana', 'Seyhan'),
(2284, 'YILMAZ', 'DOĞAN', 'dyilmaz213@gmail.com', '$2y$10$E8iNtGk7IOO76.haer5OD.BtHafeywBv7cP1bXoqpLggCKqAv8.OO', 2, 'Bölgesel Yardımcı Hakem', '05518311101', '50489', '1998-11-13', NULL, 0, '2025-08-27 19:13:33', 0, 0, 0, 1, '32878360962', 'Denizbank', 'TR150013400002398566900001', 'Çermik', 'B RH ', 'Adana', 'Seyhan'),
(2285, 'SEMRA', 'DOĞAN', 'semrasumeyradogan@gmail.com', '$2y$10$PnHzHq//DZpjyjBFWg5.1euutzHVvsDviVEDFrZCFLDAORwJEAIye', 2, 'Bölgesel Yardımcı Hakem', '05362319508', '48151', '2001-04-20', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '16555292814', 'Deniz bank ', 'TR12 0013 4000 0174 9415 8000 01', 'Adıyaman ', '0Rh-', 'Adana ', 'Çukurova '),
(2286, 'ZEYNEP', 'ÇELİK', '48154@mbs.com', '$2y$10$G8U6udNBEH3Nbe7jO08wJeP8whkO4DlRl5KjXdfIIOiCeFRwdSCnm', 2, 'Bölgesel Yardımcı Hakem', '05516597661', '48154', '2000-01-15', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '10144357724', 'Denizbank ', 'TR22 0013 4000 0174 8193 4000 01', 'Adana / Seyhan ', 'Brh+', 'Adana ', 'Sarıçam '),
(2287, 'ABDULLAH', 'GÖKTAŞ', 'abdullah.adana@hotmail.com', '$2y$10$jsYn3lWFg.RO68kdLc8eDeWCgFqcEgd/RTYl8YwhdRFby37TAUFyq', 2, 'İl Hakemi', '05384169659', '41976', '1996-03-21', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '56101657988', 'DENİZBANK', 'TR060013400001353679200001', 'Seyhan/ADANA', 'Arh+', 'ADANA', 'Seyhan'),
(2288, 'ABDULMECİT', 'ERTEM', 'mecitertem@gmail.com', '$2y$10$xRav0JzDKfPEmvl6pvJA2.aCDfCsOTQeELxhIy6hX6eE7227J7DHK', 2, 'İl Hakemi', '05317312567', '29801', '1991-01-02', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '66619003078', 'GARANTİ BANKASI ', 'TR790006200000100006984846', 'ADANA', 'A Rh ', 'ADANA', 'SEYHAN'),
(2289, 'ABDULSAMET', 'DEMİR', 'smtdmrr20@gmail.com', '$2y$10$F8KFkyWVnZ4kD93nV2HvTexk7YQ9w.McxbVqE7JIWsmS/QxyaV/BW', 2, 'İl Hakemi', '05411080118', '41977', '1991-07-29', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '50050822596', 'Ziraat bankası ', 'TR69 0001 0023 4365 6154 7450 05', 'Yüreğir ', '0 rh+', 'Adana ', 'Yüreğir '),
(2290, 'ADİL', 'ŞANLI', '31569@mbs.com', '$2y$10$bmj7sPvpZsSFw5geVsjXFO3bIGBdxiVcaIQghPELw1eKZyFe3/5fi', 2, 'İl Hakemi', '05392193177', '31569', '1992-11-01', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '44974699754', 'Denizbank', 'TR660013400001749195500001', 'Andırın', '0(+) ', 'Kahramanmaraş', 'Andırın'),
(2291, 'AHMET AŞKIN', 'ERİMTEK', 'ahmetaskinerimtek@gmail.com', '$2y$10$c7vySgexWZY9Xp4/eXkR8uTGmAv0db2InVF5MebOYYOmbH8miY.c2', 2, 'İl Hakemi', '05457410001', '52057', '2002-12-25', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '12688374144', 'Denizbank ', 'TR32 0013 4000 0229 9725 5000 01', 'Adana ', 'B+', 'Adana ', 'Yüreğir '),
(2292, 'AHMET SELİM', 'DURNA', 'durna-selim@yandex.com', '$2y$10$/ahI0sTc78TnPj95IOng3eE5aSWjJvxh8iNEX1JUIdcySZXVa8PLS', 2, 'İl Hakemi', '05529501664', '48736', '1996-03-26', NULL, 0, '2025-08-27 19:13:33', 0, 0, 0, 1, '59320551426', 'Vakıfbank', 'TR09 0001 5001 5800 7305 6691 43', 'Adana ', '0(rh)', 'Adana', 'Sarıçam'),
(2293, 'ALİ', 'DİLEK', 'alidilek9494@gmail.com', '$2y$10$8wCBX65ptOCpe9QZABbnSuArERL.jRhfuc9vOZSz1ndeYk2d83XX6', 2, 'İl Hakemi', '05074818727', '48137', '1994-06-18', NULL, 1, '2025-08-27 19:13:33', 1, 1, 0, 1, '16796828186', 'DENİZBANK ', '75 0013 4000 0174 9305 9000 01', 'TRABZON ', 'AB+', 'ADANA', 'ÇUKUROVA '),
(2294, 'ALİ SAMİ', 'KALAY', 'alskly09@gmail.com', '$2y$10$c.KirqNEmFGe.2eK1k0EU.BjrbnK8XU05lF7oN8R7K2LponcfUgXy', 2, 'İl Hakemi', '05443148570', '49320', '2004-08-30', NULL, 1, '2025-08-27 19:13:34', 1, 1, 0, 1, '10090467164', 'DENİZBANK', 'TR14 0013 4000 0201 6914 6000 01', 'SEYHAN / ADANA', 'ARH+ ', 'ADANA', 'SARIÇAM'),
(2295, 'ALPEREN', 'SEKİLİ', 'sekilialperenn@gmail.com', '$2y$10$4zyZcfAKx7v7g.rdtW/R6u7H2aaiJYqDcboCrVy1Gfj.lemYN9jgm', 2, 'İl Hakemi', '05335549339', '51426', '2006-04-28', NULL, 0, '2025-08-27 19:13:34', 0, 0, 0, 1, '10168400246', 'DenizBank', 'TR22 0013 4000 0230 1510 5000 01', 'Adana', '0+', 'Adana', 'Yureğir/Yavuzlar'),
(2296, 'ARDA ARGUN', 'ZENGİNYER', 'zenginyer@gmail.com', '$2y$10$VZto7q3QjQB9KpXvX4.S9up0/njPv908KyxiPrdONrzibIb5tQBIy', 2, 'İl Hakemi', '05464308262', '28524', '1992-08-06', NULL, 1, '2025-08-27 19:13:34', 1, 0, 0, 1, '14815261716', 'Denizbank', 'TR17 0013 4000 0189 5982 7000 01', 'Seyhan / Adana', 'A rH ', 'Adana', 'Çukurova '),
(2297, 'AYTUĞ', 'DOĞAN', 'aytugdogan81@gmail.com', '$2y$10$c1.RKhadjqAjzbTkMt4SM.Sx4sZltrfRiLVwizmAAaQKIdxDFBK1q', 2, 'İl Hakemi', '05333748746', '21063', '1981-10-18', NULL, 1, '2025-08-27 19:13:34', 1, 0, 0, 1, '16552203868', 'Deniz Bank ', 'TR570013400001751432500001', 'ADANA', 'AB Rh', 'ADANA ', 'Seyhan '),
(2298, 'BARAN', 'BÜYÜKŞANALAN', '49973@mbs.com', '$2y$10$gWeCBzejan7AqrYOr41nzeULP5zqXu7mBClvM/trSrz2fWRr3/RZm', 2, 'İl Hakemi', '05451257636', '49973', '2004-07-11', NULL, 0, '2025-08-27 19:13:34', 0, 0, 0, 1, '10714408870', 'Denizbank', 'TR34 0013 4000 0201 2869 3000 01', 'Adana', 'AB Rh', 'Adana', 'Seyhan'),
(2299, 'BATUHAN', 'SEMERKANT', 'bsemerkant999@gmail.com', '$2y$10$s8MvF6RAWjCxHSDQyHes4u6D/xM3ESWVxRQyMcRV2y3PEaEmNdjKy', 2, 'İl Hakemi', '05071934000', '50356', '2001-10-03', NULL, 0, '2025-08-27 19:13:34', 0, 0, 0, 1, '42916412010', 'DENİZ BANK', 'TR15 0013 4000 0216 7415 9000 01', 'ANAMUR', '0 RH+', 'Adana ', 'Sarıçam '),
(2300, 'BERKANT', 'KURT', '49984@mbs.com', '$2y$10$QdUT8yIGH420W7LkFETiZe4HXDDJoyhm1jVPkj4TkOBlU/EVkPp1q', 2, 'İl Hakemi', '05459545388', '49984', '1999-07-25', NULL, 0, '2025-08-27 19:13:34', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2301, 'BERKAY', 'ASLAN', 'aslann.ber@gmail.com', '$2y$10$GLmLnZ5c81SKHxrNl2Az3OPKvrZ0eqKDgzRa4UcPFzd98sMIdpsqu', 2, 'İl Hakemi', '05310307780', '48263', '2000-08-18', NULL, 1, '2025-08-27 19:13:34', 1, 1, 0, 1, '18745140066', 'DENİZBANK ', 'TR35 0013 4000 0174 7677 1000 01', 'Seyhan/ADANA', 'BrH+', 'ADANA', 'Seyhan '),
(2302, 'BERKAY', 'UYGUNGÜL', 'berkayuygungul@gmail.com', '$2y$10$oxSdOARjfe7bcFbQ0dNYJ.lqTTRDnF3XdbGcxzoopKjOOK0vqoxK.', 2, 'İl Hakemi', '05461401811', '51430', '2000-11-18', NULL, 1, '2025-08-27 19:13:34', 1, 1, 0, 1, '11779372120', 'Denizbank', 'TR81 0013 4000 0244 5461 2000 01', 'Adana', 'BRh+', 'Adana', 'Seyhan'),
(2303, 'BUĞRA UMUT', 'ÇEVİK', 'bugraumut0101@gmail.com', '$2y$10$iDtJcOoW..U9DfcyT115tuwno246Af2zIgvbbSmC2iTHiBLcYTSxG', 2, 'İl Hakemi', '05451992709', '48737', '1999-09-27', NULL, 0, '2025-08-27 19:13:34', 0, 0, 0, 1, '10030426084', 'Denizbank', 'TR37 0013 4000 0174 8784 8000 01', 'Adana', 'AB rh', 'Adana', 'Çukurova '),
(2304, 'BURAK', 'DANIŞ', 'burakdanis904@gmail.com', '$2y$10$5LPdLpRb0mLr673AHN2WbuT4vqEWIZe6RzMSDN4tUCgvmirp3A0yK', 2, 'İl Hakemi', '05337347601', '51992', '2006-01-23', NULL, 1, '2025-08-27 19:13:34', 1, 1, 0, 1, '10588424754', 'Yapı Kredi ', 'TR 51 0006 7010 0000 0036 6006 75', 'Adana/Seyhan ', '0-', 'ADANA', 'ÇUKUROVA'),
(2305, 'BURAK', 'İNAN', 'Burakinan385@gmail.com', '$2y$10$Vh.51oowbNYs0NhmqXqroOTh4o34F98gOzoPliwkpJ6Ly4rTyHHuK', 2, 'İl Hakemi', '05392729401', '48472', '2001-01-26', NULL, 1, '2025-08-27 19:13:34', 1, 1, 0, 1, '10133441502', 'TC. Ziraat Bankası', 'TR33 0001 0026 0090 6499 4850 01', 'Adana', 'A rh ', 'Adana', 'Seyhan'),
(2306, 'BURAK CAN', 'ÇINGIL', 'burakcngl17@gmail.com', '$2y$10$ukQJIPO/nL.k3ROy0zTcreDHd9t9XyReyyFVS.lvY/5Yoi4TPqwnC', 2, 'İl Hakemi', '05469373604', '51424', '2005-01-17', NULL, 1, '2025-08-27 19:13:34', 1, 1, 0, 1, '10129439032', 'Denizbank', 'TR57 0013 4000 0230 7543 2000 01', 'Seyhan', 'A +', 'Adana', 'Çukurova'),
(2307, 'CEMRE TUANA', 'GÜVEN', 'guvencemretuana@gmail.com', '$2y$10$4JZ50WDesugmxO8GlWxKtOtsNLK5XI61tMU88AIsBSAofFPyM6Jwa', 2, 'İl Hakemi', '05519607191', '51176', '2005-09-02', NULL, 0, '2025-08-27 19:13:34', 0, 0, 0, 1, '10643349954', 'Denizbank', 'TR73 0013 4000 0213 9030 6000 01', 'Hatay / İskenderun', 'A RH ', 'Adana ', 'Sarıçam'),
(2308, 'ÇAĞATAY', 'BAKADUR', 'cagataybkdr@gmail.com', '$2y$10$0wPjrEtRI69fn87p7t/3nOUq7HtAi8k/mFu/DhhN8JY7Kz/iNmmt6', 2, 'İl Hakemi', '05425944267', '51422', '2002-05-20', NULL, 1, '2025-08-27 19:13:34', 1, 1, 0, 1, '18391171674', 'Garanti bankasi ', 'TR48 0006 2001 2050 0006 6411 45', 'Yüreğir ', '', 'Adana', 'Yüreğir '),
(2309, 'ÇAĞATAY', 'YILDIZ', 'cagatay.yildz@gmail.com', '$2y$10$voE1V3zXNHjm4iTUf.IFoumgk2nVwfNSzevYBkvx0FyEc/WT6/EQ2', 2, 'İl Hakemi', '05348986510', '45399', '1999-05-06', NULL, 0, '2025-08-27 19:13:34', 0, 0, 0, 1, '21943889958', 'DENİZBANK', 'TR20 0013 4000 0174 7396 1000 01', 'ADANA', 'BRH+', 'ADANA ', 'ÇUKUROVA'),
(2310, 'DAVUT', 'ULUÇAY', 'davutulucaytff@gmail.com', '$2y$10$nL6XUVf00As.D3/C.IW3VuCSRLrO7DtaAceAFx3xtHjjoWFQcb1GO', 2, 'İl Hakemi', '05443308747', '26748', '1983-06-01', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '14059269332', 'Deniz Bank', 'TR25 0013 4000 0018 0875 1000 01', 'Feke', '0rh-', 'Adana', 'Kozan'),
(2311, 'DURSUN', 'KAZAN', 'dursunkazan_05@hotmail.com', '$2y$10$nUZQQeu1z9Cb22WmnVPjIeGPG8rEleqhJZWYdXzC7vqmQD7ovPS8a', 2, 'İl Hakemi', '05448445893', '28508', '1981-08-02', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '21496259468', 'Denizbank ', 'TR68 0013 4000 0085 8497 7000 03', 'Amasya', 'A( rh', 'Adana ', 'Seyhan '),
(2312, 'EMRE ERDAL', 'ÖNDERLİ', 'emreonderli01@gmail.com', '$2y$10$EPeg0AOfCKCWvkYsFoHrU.qQF/FRMnWlJ1WVQSA94vqD46VYvUfJy', 2, 'İl Hakemi', '05550353091', '51994', '2005-06-08', NULL, 1, '2025-08-27 19:13:35', 1, 0, 0, 1, '10255432372', 'DENİZ BANK', 'TR30 0013 4000 0225 3842 6000 01', 'Adana', 'B+', 'Adana ', 'Yüreğir '),
(2313, 'ERTUĞRUL', 'ESKİ EVCİK', 'ertugruleskievcik4@gmail.com', '$2y$10$BVCGNdvusyAH8zzPT.c2wOFy9/I36CkgnGxio3zQRBeBbL/iUpFdK', 2, 'İl Hakemi', '05444108846', '49784', '2001-01-30', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '19315121058', 'Ziraat Bankası ', 'TR 3000 0100 1783 907014845001', 'Adana', 'A+Rh', 'Adana', 'Sarıçam'),
(2314, 'FURKAN', 'UÇAR', '44009@mbs.com', '$2y$10$Ac.PPvFYrs4VD5JQCZg03ub.POjdmgdJnVXjXBssvLhq2cRmUd.RG', 2, 'İl Hakemi', '05464833640', '44009', '1999-07-07', NULL, 0, '2025-08-27 19:13:35', 0, 0, 0, 1, '10850111030', 'Garanti Bankası ', 'TR850006200050000006856240', '07.07.1999', 'A-', 'Adana', ''),
(2315, 'GIYASETTİN', 'TAŞKIN', 'giyastaskin2019@gmail.com', '$2y$10$U4EDXRl5TOMdX65irGh7Ye5NnypBVtbF390IxRBqBCk/Z0noIVwJy', 2, 'İl Hakemi', '05056874258', '21057', '1981-01-01', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '17480751384', 'Ziraat Bankası', 'TR41 0001 0004 5925 5942 3650 12', 'Baykan', 'Arh +', 'Adana', 'Çukurova '),
(2316, 'GİZEM', 'ÇAKIR', 'gizemcakirr90@gmail.com', '$2y$10$cZoFPgB19pPTbBKXii/lw.ew/gHnqMsxC5uZFYOV5Aj.AL4btB72m', 2, 'İl Hakemi', '05558706293', '44287', '1990-10-05', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '20092088830', 'Denizbank ', 'TR83 0013 4000 0174 7616 0000 01', 'Adana / Seyhan', 'ARH+', 'Adana ', 'Çukurova '),
(2317, 'HAKAN', 'AKKÖL', 'hakan_akkol_01@hotmail.com', '$2y$10$NSFJpbFh4XZnNb8tzOVtvumd20D8Rt4Bf7NQYS9ARYJzWSYHfjz5y', 2, 'İl Hakemi', '05374767504', '24809', '1986-11-11', NULL, 1, '2025-08-27 19:13:35', 1, 0, 0, 1, '12541309498', 'Denizbank ', 'TR39 0013 4000 0174 8660 6000 01', 'Adana ', 'O Rh+', 'Adana', 'Karataş '),
(2318, 'HALİL İBRAHİM', 'KILIÇ', 'pdribrahimkilic@gmail.com', '$2y$10$tfye.nBVqvoth3QGNZacUuLCOZPwYoQjk2uI5FCRjNmu3puyr0WZS', 2, 'İl Hakemi', '05546812828', '50487', '1994-01-20', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '17914159670', 'VAKIFBANK', 'TR84 0001 5001 5800 7305 7464 60', 'SEYHAN', 'B Rh+', 'ADANA', 'SEYHAN'),
(2319, 'HANİFİ', 'VURUCU', 'hvurucu_81@hotmail.com', '$2y$10$u4t/Kx0ILy128NlKTmpB9elre6PccCXo1J/ENb/u2wsfvQm5pMRay', 2, 'İl Hakemi', '05058941835', '21531', '1981-06-01', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '37024477778', 'Deniz Bank', 'TR53 0013 4000 0174 9401 4000 01', 'Antakya', 'A rh ', 'Adana', 'Yüreğir'),
(2320, 'HASAN', 'CAN', 'canhasan_1989@hotmail.com', '$2y$10$1CnRFaNaNvCnUh1dNYGJyuvJecXT5II2vbQ87DC96Hffs1EUzJyma', 2, 'İl Hakemi', '05462210147', '26752', '1989-09-01', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '66583005610', 'Deniz bank ', 'TR760013400000844949100020', 'Seyhan', '0 rh ', 'Adana ', 'Seyhan'),
(2321, 'HASAN FEHMİ', 'VURANAY', 'abc@gmail.com', '$2y$10$Ho/H5TT4N1TOxLAKSYVim.d8VgDRwawJMPuHmm1bRrp/dtxy8IiQa', 2, 'İl Hakemi', '05442761076', '29776', '1988-06-13', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '15181252926', '', '', '', '', '', ''),
(2322, 'HÜSEYİN', 'ATEŞ', 'huseyinates099@gmail.com', '$2y$10$dfYKz2tJ5HqUDJPYTVC7QegPkMsD9mPw0CCx/n3YQHKqr77kXldA2', 2, 'İl Hakemi', '05319473466', '48143', '1999-01-01', NULL, 0, '2025-08-27 19:13:35', 0, 0, 0, 1, '19369116208', 'Denizbank', 'TR720013400001747416400001', 'ADANA', 'AB RH', 'ADANA', 'SEYHAN'),
(2323, 'HÜSEYİN SEZER', 'ÖKTEN', '50488@mbs.com', '$2y$10$13igFyIg4Dn4c7SBu/XUI.1u9w1KAvX9rC0sAwAW9Z5NmyY3IdB2u', 2, 'İl Hakemi', '05064399325', '50488', '1994-12-19', NULL, 1, '2025-08-27 19:13:35', 1, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2324, 'İDRİS', 'AKTÜRK', 'Aktrk.idris@gmail.com', '$2y$10$Hr4DLTcp7NwCbumZwiMavu6S73O40JgBz.s7rD/0dAthsNSAsVwH2', 2, 'İl Hakemi', '05312826405', '49966', '2002-11-26', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '11314388098', 'DENİZ BANK', 'TR37 0013 4000 0200 9617 8000 01', 'ADANA', '0Rh-', 'ADANA', 'SEYHAN'),
(2325, 'LEZGİN', 'AYGÜN', 'aygunsezgin34@gmail.com', '$2y$10$mgmu5Vgn/gDxy5NBsdvKFejgFLkT0M3Ee.wgEG.1D3MbXzm5qKQ7.', 2, 'İl Hakemi', '05522459973', '51989', '2002-07-25', NULL, 1, '2025-08-27 19:13:35', 1, 1, 0, 1, '51211087106', 'Ziraat bankası ', 'TR940001002409954290105002', 'Siirt', 'A rh ', 'Adana', 'Sarıçam'),
(2326, 'MEHMET', 'AKTAŞ', 'aktas1213@gmail.com', '$2y$10$4TQEcbTdpUxKbnRAOOsWSORVXp0AcxRzO/3bGebmHuztKTZJcwRjC', 2, 'İl Hakemi', '05458410155', '26747', '1988-02-07', NULL, 1, '2025-08-27 19:13:36', 1, 1, 0, 1, '12457280638', 'Deniz Bank', 'TR210013400001748701700001', 'Ceyhan', 'Arh+', 'Adana', 'Ceyhan'),
(2327, 'MEHMET', 'COŞKUN', 'mcskn763@gmail.com', '$2y$10$I7/Ys4zZguotIX3nLoVesuqONCGn7QfEWNaJT2mt23zd.Pg.25XLy', 2, 'İl Hakemi', '05418924627', '48144', '1997-10-10', NULL, 0, '2025-08-27 19:13:36', 0, 0, 0, 1, '21421082550', 'GARANTİ BANKASI', 'TR24 0006 2001 1580 0006 6755 68', 'ADANA SARIÇAM KÜRKÇÜLER YÜREKLİ', '0RH +', 'ADANA ', 'SARIÇAM '),
(2328, 'MEHMET', 'ÖZYAZGAN', 'ozyazganmemet01@gmail.com', '$2y$10$Pql97Fy3HI0oWuvQdf5Yn.rmgyQH6po0AhFOqpA8LAbDvL63C59ie', 2, 'İl Hakemi', '05536639183', '47763', '1998-09-01', NULL, 0, '2025-08-27 19:13:36', 0, 0, 0, 1, '16171217376', 'Enpara Bank', ' TR44 0015 7000 0000 0082 4165 40 ', 'Kuveyt', '0 RH+', 'Adana', 'Sarıçam'),
(2329, 'MEHMET CENKER', 'ATAKAY', 'cenker01cenker@gmail.com', '$2y$10$DB.oS8PwohNQpFxwLKqzPOPX6NkYJ1TX2PGQ8sOtOYB92nl1Yl0Ri', 2, 'İl Hakemi', '05442615953', '51420', '2005-10-31', NULL, 1, '2025-08-27 19:13:36', 1, 1, 0, 1, '10489425294', 'İş bankası', 'TR470006400000160160744699 ', 'Adana', 'A Rh+', 'Adana', 'Çukurova'),
(2330, 'MEHMET FATİH', 'DELİÇAY', 'delicay_0175@hotmail.com', '$2y$10$ofSqWNZMvH5gfOVrzBaOROWjnXcePtSG1uCnItKvch6FrpWOXcU.6', 2, 'İl Hakemi', '05072408163', '21039', '1984-05-26', NULL, 1, '2025-08-27 19:13:36', 1, 1, 0, 1, '', '', '', '', '', '', ''),
(2331, 'MELİH', 'ÇAVUŞ', 'melihcavus@gmail.com', '$2y$10$ZVC53XjfStmzPXWD10Iq0unx1VWVWmnftnIqcu6f1SWJzAAKodORu', 2, 'İl Hakemi', '05355647577', '23353', '1981-11-25', NULL, 1, '2025-08-27 19:13:36', 1, 1, 0, 1, '20245118520', 'Denizbank', 'TR44 0013 4000 0174 9252 2000 01', 'Adana', 'A Rh ', 'Adana', 'Çukurova'),
(2332, 'MUHAMMET ÖMER', 'YAŞAR', 'muhammedomeryasar33@gmail.com', '$2y$10$gNjEqNCZzffONgQzh.0Y0O1pCF20ECoCur8CGYOWk8KPhspyVNLJS', 2, 'İl Hakemi', '05551603175', '48740', '2003-12-03', NULL, 1, '2025-08-27 19:13:36', 1, 1, 0, 1, '10150464720', 'Deniz Bank', 'TR80 0013 4000 0174 8132 1000 01', 'Adana', '0 RH ', 'Adana ', 'Yüreğir '),
(2333, 'MURAT', 'ŞİMŞEK', 'muratsimsek154@gmail.com', '$2y$10$/XGEGsHDnPpK/XaO0.dec.n0RXMiF/aAShtLF3rGyRoGgpQuPCV8y', 2, 'İl Hakemi', '05355647475', '30521', '1994-10-29', NULL, 1, '2025-08-27 19:13:36', 1, 1, 0, 1, '21889033330', 'Denizbank', 'TR820013400001517924000008', 'Seyhan', 'A(+)', 'Adana ', 'Seyhan'),
(2334, 'MURAT', 'YILMAZ', 'muratyilmaz1332@gmail.com', '$2y$10$EdKpFCaJqutNgtvmqgB9xOH3Z9ZH1sxB.I0oVo7E0jukWQBx1LOeW', 2, 'İl Hakemi', '05537218402', '45404', '2001-02-22', NULL, 1, '2025-08-27 19:13:36', 1, 1, 0, 1, '19621178746', '17488038-351', 'TR57 0013 4000 0174 8803 8000 01', 'Seyhan', 'A rh+', 'Adana', 'Seyhan'),
(2335, 'MUSA ERKAN', 'ÇÖMEZ', '49974@mbs.com', '$2y$10$nQxvQyucOGwBwfKWGmIBoO9yStnM1xdnYUgmtO9h7pRPlaO4LL7o6', 2, 'İl Hakemi', '05337658037', '49974', '1996-09-19', NULL, 0, '2025-08-27 19:13:36', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2336, 'MUSACAN', 'AKGÜL', 'musacan200301@gmail.com', '$2y$10$fLU.xGSh1YWogqz.4LnyeuOYXLH9ngbigZ4nAt8p1wVxJwyM1vPWK', 2, 'İl Hakemi', '05455660125', '49304', '2003-05-04', NULL, 1, '2025-08-27 19:13:36', 1, 1, 0, 1, '10867403166', 'Denizbank ', 'TR77 0013 4000 0210 5404 5000 01', 'Adana', 'Arh+', 'Adana', 'Seyhan'),
(2337, 'NAZİRE', 'KUZU', '51178@mbs.com', '$2y$10$zgt7u4bfgWy3.qel5TA5IOy9xZ3hSvwY3f0c4Wxgj2texKLomThI2', 2, 'İl Hakemi', '05449247544', '51178', '2005-06-10', NULL, 0, '2025-08-27 19:13:36', 0, 0, 0, 1, '10457371660', 'Denizabank', 'TR30 0013 4000 0214 4398 9000 02', 'Samandağ', 'AB RH', 'Adana', 'Çukurova'),
(2338, 'NECDET', 'BÜYÜKŞANALAN', '47764@mbs.com', '$2y$10$Nv.3HShaFl7gVdlyEO9Og.v7zX5vIHnV4RQbO2yJnFeSKGtb/FGGq', 2, 'İl Hakemi', '05535887127', '47764', '1999-12-11', NULL, 0, '2025-08-27 19:13:36', 0, 0, 0, 1, '22561005416', 'Denizbank', 'TR94 0013 4000 0174 9581 0000 01', 'Seyhan/ADANA', 'A rh(', 'Adana', 'Sarıçam'),
(2339, 'OKAN', 'GÜLDAL', 'okanguldal@gmail.com', '$2y$10$1JedDoanb.PTlFLsClVl6OHvemIx0S2EOonnTwFV346Dx1lByRZI6', 2, 'İl Hakemi', '05074805060', '28516', '1985-07-31', NULL, 1, '2025-08-27 19:13:36', 1, 0, 0, 1, '10645364122', 'DENİZBANK', 'TR76 0013 4000 0174 8244 7000 01', 'ADANA', '0 Rh ', 'ADANA ', 'SEYHAN '),
(2340, 'OKAN', 'ŞENSOY', 'oknsnsy@hotmail.com', '$2y$10$i0nCB4x4XLupjPKop4iuYeNfXNTQ//6frDXrHxUz29JUz2rU//nhy', 2, 'İl Hakemi', '05073300111', '26758', '1988-07-30', NULL, 1, '2025-08-27 19:13:36', 1, 0, 0, 1, '13939296192', 'Deniz bank', 'TR470013400001747620600001', 'ADANA', 'ABRH+', 'ADANA', 'SEYHAN'),
(2341, 'ONUR', 'SİSLİOĞLU', '49988@mbs.com', '$2y$10$fOJ/8yKvgnmHtK4oyQ8Q7OKXzAGWnIR3JxB7uPa1K6tk7UPzecGVa', 2, 'İl Hakemi', '05418727893', '49988', '2004-06-19', NULL, 0, '2025-08-27 19:13:36', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2342, 'ÖMER', 'AŞKAR', 'omerraskarr@gmail.com', '$2y$10$uJ4zjZyr585ZqpGyBJX/jejdHYXfrYvCGIvhaYrQxj.32oJela0.m', 2, 'İl Hakemi', '05523552606', '46286', '1998-06-26', NULL, 1, '2025-08-27 19:13:37', 1, 0, 0, 1, '16280174560', 'DENİZBANK', 'TR11 0013 4000 0174 9089 9000 01', 'REYHANLI', 'A Rh+', 'Adana', 'Sarıçam'),
(2343, 'ROJİN', 'YAVAŞ', 'rojinyavas01@gmail.com', '$2y$10$E9Cl2m.6AKHkrzl3j2kri.9Fnf9iRkzIri0bKSJyJHAINGTRVeujG', 2, 'İl Hakemi', '05413886749', '51998', '2000-11-29', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '10045445452', 'Denizbank', 'TR17 0013 4000 0234 3870 5000 01', 'Adana', 'Arh-', 'Adana ', 'Seyhan'),
(2344, 'SALİH', 'DÜNDAR', '51068@mbs.com', '$2y$10$9Icm9BtXVQTy7yskXI2zN.XnngCpvgqWD2KwNfuYJF0rnV8DAfdJa', 2, 'İl Hakemi', '05530895980', '51068', '2002-01-21', NULL, 0, '2025-08-27 19:13:37', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2345, 'SAMET', 'BOYAR', 'pdsametboyar@gmail.com', '$2y$10$9mC9ebwY/6SXUCQwqPYXUOF1YoB0xCTM3Lb0WBAVyfwQkEzRJF9FW', 2, 'İl Hakemi', '05075986762', '46554', '1998-09-17', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '16178209756', 'Denizbank', 'TR88 0013 4000 0174 9497 7000 01', 'Seyhan', 'B Rh(', 'Adana', 'Yüreğir'),
(2346, 'SEFA EREN', 'HATIRA', 'sefahatira645@gmail.com', '$2y$10$VwyytcFgkO9DxfwHUkwN1e3TYHpNBxj77lUsrAc2M/RtZj9GVuPQK', 2, 'İl Hakemi', '05510304851', '51418', '2006-06-21', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '11387562890', 'Ziraat Bankası ', 'TR73 0001 0090 1024 3817 3050 01', 'İzmir / Konak', 'B+', 'Adana ', 'Seyhan'),
(2347, 'SEMİH', 'TOLONGÜÇ', '31352@mbs.com', '$2y$10$6jf2f0IXEqALyYYhtTA7veArepI129yyKKKRInNoldRRnU4m8th7O', 2, 'İl Hakemi', '05455685904', '31352', '1989-11-22', NULL, 0, '2025-08-27 19:13:37', 0, 0, 0, 1, '', '', '', '', '', '', ''),
(2348, 'SEMİH', 'ULUSAL', '49485@mbs.com', '$2y$10$kZMqDL5mDnajdehCP8TE5OYXBXTDRmpVjRX23kIMsTpD8.7BTk1S2', 2, 'İl Hakemi', '05350250156', '49485', '2002-08-15', NULL, 0, '2025-08-27 19:13:37', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2349, 'SERHAT', 'AKGÜL', 'sirhot_1010@hotmail.com', '$2y$10$pO/zhmsoasx2hoYoKHBpiueSp4WM96JqDRGza6u7RtcQioptmo5Pm', 2, 'İl Hakemi', '05061472467', '26792', '1990-09-13', NULL, 1, '2025-08-27 19:13:37', 1, 0, 0, 1, '18694172446', 'Denizbank ', 'TR18 0013 4000 0162 1924 7000 01', 'Adana ', '0Rh(+', 'Adana ', 'Sarıçam '),
(2350, 'ŞENOL', 'YILMAZ', '25581@mbs.com', '$2y$10$/8PwZgvzSOpmSBINgAvt/uwSqm6uMSjNBlDpAYNV87u1Fwz1Z2OjK', 2, 'İl Hakemi', '05059072904', '25581', '1986-05-12', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '14575208518', 'AKBANK ', 'TR41 0004 6007 0988 8000 1687 19', 'Ceyhan ', '0 RH ', 'Adana ', 'Seyhan'),
(2351, 'TUGAY', 'GÖNENCİ', 'tugaygonenci@gmail.com', '$2y$10$qYnrsOdjbwK9oky0u0Ele.ZhAYDe1jCemDg2pEK3fNlOZk.RmVbuG', 2, 'İl Hakemi', '05448214757', '29782', '1993-11-11', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '12175354222', 'Denizbank', 'TR09 0013 4000 0174 8350 8000 01', 'Seyhan', 'A(-)', 'Adana', 'Seyhan'),
(2352, 'TUĞBA', 'AYYILDIZ', 'tuba__ayyildiz@hotmail.com', '$2y$10$aedVTqycEwtq9/pqbQ6/dOM0ExieHdVPzm.k95/LPy9FtCNBf3biK', 2, 'İl Hakemi', '05447333801', '42728', '1989-09-11', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '29879025068', 'Denizbank ', 'TR390013400000475032600002', 'Seyhan', 'A rh ', 'Adana ', 'Seyhan '),
(2353, 'YASEMİN', 'ULUER', 'yaseminuluer33@gmail.com', '$2y$10$aRQS2I1FbSqSY2dIfKN1K.PeS/x0QoO3l1zBaqI2z99DKirVR1lBi', 2, 'İl Hakemi', '05373083303', '49488', '2001-01-25', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '11215341840', 'Ziraat bankası', 'TR32 0001 0021 8191 0156 2950 01', 'Adana ', 'B rh ', 'Adana', 'İmamoğlu '),
(2354, 'YUSUF', 'BOĞA', '49972@mbs.com', '$2y$10$k/QZEdWb6CWlUZFo9boDD.o5ikpq7USTp7EGiM0bi57aWywA.hoIq', 2, 'İl Hakemi', '05443148513', '49972', '2002-10-17', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '10195464358', 'Denizbank', 'TR940013400002001674300001', 'Yüreğir ', 'ARh+', 'Adana', 'Yüreğir'),
(2355, 'YUSUF', 'GÖK', 'gkysf01@gmail.com', '$2y$10$kYFR2orGxJItDP6fNY7M.e57DEYd2sVfQSAFKJzcYM9wgnFHC0JBi', 2, 'İl Hakemi', '05366767064', '49978', '2000-08-25', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '14239211496', 'Ziraat Bankası', 'TR60 0001 0010 6290 1258 3350 01', 'Adana', 'A rh+', 'Adana', 'Yüreğir'),
(2356, 'ZAFER', 'YILMAZCAN', 'z.yilmazcan11@gmail.com', '$2y$10$g.t2U/gaAeHauAYmafJQvu1E0HXdu/qNDZsVJv0mTvk5NrM75YJKK', 2, 'İl Hakemi', '05469441812', '48153', '1996-08-08', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '22978018276', 'Denizbank', 'TR90 0013 4000 0174 9548 1000 01', 'Tufanbeyli', 'A+', 'Adana', 'Tufanbeyli'),
(2357, 'ZİYA', 'GÜLDAĞ', 'zygldg@hotmail.com', '$2y$10$T73GXuCq/g7uNUKirOnKfuDYBRrjT1taxJ75IT3Chia33eNzp.dAK', 2, 'İl Hakemi', '05376162631', '29797', '1987-11-13', NULL, 1, '2025-08-27 19:13:37', 1, 1, 0, 1, '22261017696', 'Denizbank ', 'TR67 0013 4000 0174 7891 8000 01', 'Seyhan ', '0 +', 'Adana', 'Seyhan '),
(2358, 'ABDULAH', 'DÖĞER', '1@mbs.com', '$2y$10$9VbKPCai7gX43CdladTvaeiSZySPbhzuWmLNK74ifc9UfJ6mH8WLO', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:52', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2359, 'ADEM ETHEM', 'ÜLGÜ', '2@mbs.com', '$2y$10$1aLnIdjOVHscbg1TWCTU8uDVXztmfGzXqE/vg4I8TQiYjci/c4YES', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:52', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2360, 'ADEM', 'GÜDÜK', '3@mbs.com', '$2y$10$ou5tMPZ.2VWEVB4lOELLLuyzDl0vIYO.J2bjlDCyIVuI34pqOD2RG', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:52', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2365, 'ALİ', 'KIZILCA', 'alikizilca5501@gmail.com', '$2y$10$47mgGvs.veqUkEf3N3Wewu5tx/2NLFixbyxGmBdaovJgFZtZ6tz8O', 2, 'İl Hakemi', '05436849887', '53209', '2007-11-08', NULL, 1, '2025-08-27 19:16:52', 1, 1, 0, 1, '11599415342', 'Ziraat Bankası', 'TR 5100 0100 9010 7843 2890 5001 ', 'Adana/Seyhan', 'A RH+', 'Adana', 'Sarıçam'),
(2367, 'ARDA DENİZ', 'ÇAKIR', 'ardadenizz0144@gmail.com', '$2y$10$jyfbt9YW4sS6nqQuiO8kx.2Gjp0vOGQywb9R9pxC.fy4JnNBDigJG', 2, 'İl Hakemi', '05439753973', '53208', '2005-09-10', NULL, 1, '2025-08-27 19:16:52', 1, 1, 0, 1, '10369432862', 'Denizbank', 'TR060013400002462845100001', 'Adana', 'B rh+', 'Adana', 'Seyhan'),
(2368, 'ASIM', 'KUBAY', 'asimkubay03@gmail.com', '$2y$10$D5VrcuuKRmeKMlSHmPxZP.h6PrETVaObDMzNSGmxHFgCLSFaXdJ5O', 2, 'İl Hakemi', '05451560145', '53207', '2007-12-10', NULL, 1, '2025-08-27 19:16:52', 1, 0, 0, 1, '11164402470', 'ZİRAAT BANKASI ', 'TR22 0001 0090 1053 4841 6050 01', 'Adana ', '0 Rh+', 'ADANA ', 'ÇUKUROVA'),
(2373, 'BARIŞ', 'CEYLAN', '16@mbs.com', '$2y$10$9g0el7DZ4E4fQcxS7AxBNeoEBqaX/WmpG9bEm1ALz9JoLZL8c.b.i', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:53', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2374, 'BARIŞ', 'GEZER', '17@mbs.com', '$2y$10$NuWUEwZPoeYWcves7EOCNupgQh99UqwmJ0FvJxrtr5rm5716h1DJy', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:53', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2377, 'BATUHAN', 'AKKUŞ', 'baturhan33ba@gmail.com', '$2y$10$Fz3Cgc45A9LTFTX3HHOUhOgmeKAJ23jHhuk7eLGdV874CBkZiuuGa', 2, 'Aday Hakem', '05319029985', '', '2001-03-12', NULL, 0, '2025-08-27 19:16:53', 0, 0, 0, 1, '50740164692', '', '', '', '', '', ''),
(2379, 'BEKİR KAAN', 'ERDEN', '22@mbs.com', '$2y$10$hG3DcJbMFZ0YqFWf8HokweNaw56OLEKDXw0kNFptYb5tk5IJCD0vO', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:53', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2380, 'BERAT', 'YILDIZ', '23@mbs.com', '$2y$10$FFnIqEMw/wwRPpnCiYI7nOPKn7Z1bGhZnENbaw.E5LKG.wDyy9uka', 2, 'Aday Hakem', '05332765381', '', '2008-02-03', NULL, 1, '2025-08-27 19:16:53', 1, 1, 0, 1, '14865037880', 'Ziraat Bankası', 'TR 480001009010814478505001', 'Osmaniye', 'B rh+', 'Adana', 'Yüreğir'),
(2384, 'BİRKAN', 'ÜRGENÇ', '27@mbs.com', '$2y$10$dUBa/ra1TvHo9mLY06JMyukAeRpr6PUiMyDk6rYu.Bsr0mRWHjRqq', 2, 'Aday Hakem', '05417752866', '', '2008-09-11', NULL, 1, '2025-08-27 19:16:53', 1, 1, 0, 1, '11963380628', 'Ziraat bankası', 'TR070001009010817897605001 ', 'Adana Seyhan', 'A Rh+', 'Adana', 'Yeşiloba'),
(2385, 'BUĞRA FATİH', 'DELİKANLI', 'delikanlifatih06@gmail.com', '$2y$10$lY/WFaskIJdK7/Hrp2FDf.K2AFuK9gHw/FxWNGzL1QGTh3TQQKHOW', 2, 'İl Hakemi', '05527043612', '53206', '2006-09-22', NULL, 1, '2025-08-27 19:16:53', 1, 1, 0, 1, '10720423324', 'Ziraat Bankası ', 'TR69 0001 0090 1075 4411 8050 01', 'Adana ', '0Rh+', 'Adana', 'Çukurova'),
(2386, 'BUĞRA', 'SELVİ', '29@mbs.com', '$2y$10$cWFIJJs36JW1RB.tE/9GNeJ1ksf3GOkx5YyIXsPJFlMyJlH8VXVtO', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:53', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2387, 'BUĞRAHAN', 'TEKİN', '30@mbs.com', '$2y$10$6IqIddwVR97o3H.KjDwVUetwiFssExDbaQsbaSMDKHjo/vB75iLWq', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:53', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2389, 'BÜŞRA', 'ÇETİNER', '32@mbs.com', '$2y$10$8wI47YhdOzH2MqiBQcFEFOOIhJif5LRAcn9m8V0Qol6Jmanb/PTv.', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:54', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2390, 'CEMRE', 'TAŞ', 'ctas40628@gmail.com', '$2y$10$YKM7G82IVnNkhcBUMy6WKeMW5pO4/oCd04JlR2elOM7qx3AsiPb4.', 2, 'Aday Hakem', '05464682926', '', '2008-09-07', NULL, 1, '2025-08-27 19:16:54', 1, 1, 0, 1, '21982073158', 'Halkbank ', 'TR600001200916200001139422', 'Adana', 'B Rh ', 'Adana ', 'Seyhan '),
(2392, 'CİHAN', 'AYATA', '35@mbs.com', '$2y$10$EXlJIUeOYpC2gowud.Nfs.h1Xi9y3qDMv7xeBvDDoiVSEqCiCOET.', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:54', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2395, 'ÇAĞLAR SAMET', 'GÜRLER', '38@mbs.com', '$2y$10$K1USUhxcHZdFUeUJRAExyOZI2lQEQTdwjpyQpLtcGgD.wfdJVPTY2', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:54', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2396, 'DİLARA', 'BEYOĞLU', '39@mbs.com', '$2y$10$iinpfNytow/PuL4RJvXil.jIIQmSQHDwnsrd04jy.HLZYgB/3dghO', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:54', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2398, 'DORUK', 'KARAKÖSE', 'dorukkarakosee@gmail.com', '$2y$10$eYT.PVDi1BNiOwzORQcw8ec63ndrzYP.FphuUxY.foBHk9uzqtg62', 2, 'Aday Hakem', '05389544180', '', '2009-05-22', NULL, 1, '2025-08-27 19:16:54', 1, 1, 0, 1, '21052066080', 'Ziraat Bankası', 'TR 5900 0100 9010 7914 3690 5001 ', 'Seyhan', 'B Rh+', 'Adana', 'Seyhan'),
(2401, 'EDANUR', 'KURKUT', '44@mbs.com', '$2y$10$cvywTBeaDuELsOUSDFMx4O9GKSkVcxO6sUkeTO3MCJX9Wb51LGhKi', 2, 'Aday Hakem', '05518866359', '', '2008-08-25', NULL, 1, '2025-08-27 19:16:54', 1, 1, 0, 1, '14641316376', 'ziraat bankası', 'TR48 0001 0090 1081 5293 3050 01', 'yüreğir', '0 rh+', 'adana', 'yüreğir'),
(2403, 'EFE EREN', 'YILDIZ', 'efeyildiz0801@gmail.com', '$2y$10$F18d12tNwSVk/vIOxmZkAu.VtqBfRge4jXmvORa2iiFF9392aGGCq', 2, 'Aday Hakem', '05448722339', '', '2008-08-08', NULL, 0, '2025-08-27 19:16:54', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2405, 'ELANUR', 'BOZKURT', 'belanur2301@gmail.com', '$2y$10$VgTK2cUAypY2uVDM1Ti2ouQvL3J2PsUwqaYUIBoF7TCKDqJGHoGqm', 2, 'İl Hakemi', '05394959189', '53205', '2000-10-08', NULL, 1, '2025-08-27 19:16:54', 1, 1, 0, 1, '17746144622', 'Ziraat Bankası ', 'TR60 0001 0020 9183 3810 0950 01', 'ALTINDAĞ / ANKARA', 'A Rh-', 'Adana ', 'Çukurova '),
(2406, 'EMİN ANIL', 'GÖKDEMİR', '49@mbs.com', '$2y$10$bDMANbqriEEEZnCwAz4dh.Ry2Qr5Y.RFL2H/6K8iVf6g4ubkZhSPK', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:55', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2407, 'EMİN BURAK', 'ÇANKA', 'burakcanka10@gmail.com', '$2y$10$1gWf4GwvQhZ4AkGhhb.vE.H/zyC4VX.8Hcwn0WYsQlw3OdIRwOOCa', 2, 'İl Hakemi', '05538546490', '53204', '2002-07-02', NULL, 0, '2025-08-27 19:16:55', 0, 0, 0, 1, '40033294724', 'İş Bankası', 'TR280006400000160100982759', 'Fatih', 'A+', 'Adana', 'Sarıçam'),
(2409, 'EMİR BATU', 'GÜNDOĞAN', '52@mbs.com', '$2y$10$M73tKXCbkDms9/06nG/eyufKdUfhJOkPdqtw5ETcty8RDTYHuQ6aO', 2, 'Aday Hakem', '', '', '2006-06-30', NULL, 0, '2025-08-27 19:16:55', 0, 0, 0, 1, '10624418242', 'Garanti Bankası', 'TR55 0006 2000 4660 0006 6613 98', 'Adana', '0 Rh-', 'Adana', 'Seyhan'),
(2412, 'EMRE', 'MARANGOZ', '55@mbs.com', '$2y$10$9T0LmmAzrsc6OubQQuA7pe0VkOoMNd1hIU9dawB4VH3BcB5VtKYJ2', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:55', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2413, 'EMRE', 'OKUYUCU', '56@mbs.com', '$2y$10$Bx7i.IKQUSnbgzHhmiUj/.lwvPiiBRjW2xu1QlwgMTRe1fy8ptW7e', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:55', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2415, 'ENES', 'ALAGÖZ', 'enesimalagoz@gmail.com', '$2y$10$oO/3sFwneY28/G4PAIlO6e7JMFNr7xtp1xJU76v8YVwYJ3BPfjfJG', 2, 'Aday Hakem', '05510093189', '', '2009-06-27', NULL, 1, '2025-08-27 19:16:55', 1, 1, 0, 1, '13651343792', 'Ziraat Bankası', 'TR40 0001 0090 1081 3908 3050 01', 'Adana/Seyhan', 'A rh+', 'Adana', 'Yüreğir'),
(2417, 'ERAY', 'ÖZENSOY', 'erayxozensoy@gmail.com', '$2y$10$lY1idfyyogy6SahFwgjVjeurNCRqUafau3wLJuAlOoxqf3PiQH7gi', 2, 'İl Hakemi', '05459002592', '53203', '2002-02-15', NULL, 1, '2025-08-27 19:16:55', 1, 1, 0, 1, '30425185334', 'Garanti Bankası', 'TR30 0006 2000 3140 0006 9393 49', 'Seyhan', '0 RH ', 'Adana', 'Seyhan'),
(2418, 'ERBİL', 'BİRİCİK', 'erbilbiricik@gmail.com', '$2y$10$nk1mR92cl4J4dGd3Ca/G0OHtUPMwm9gJUslW7lq5ajYFy3b9ib0je', 2, 'Aday Hakem', '05449248419', '', '2007-08-21', NULL, 0, '2025-08-27 19:16:55', 0, 0, 0, 1, '11479417756', 'Ziraat bankası ', 'TR 7700 0100 9010 8119 3070 5001 ', 'Seyhan', 'ARH (', 'Adana', 'SARIÇAM '),
(2419, 'EREN DENİZ', 'KIYDAL', 'rnkydl@gmail.com', '$2y$10$RQTswN3RFN9ZC1Ief8xnX.2AnUDTx9iRZvf5cwgMIiEcuDS44wH9y', 2, 'İl Hakemi', '05413689312', '53202', '2004-08-07', NULL, 1, '2025-08-27 19:16:55', 1, 0, 0, 1, '10768441236', '', 'TR58 0001 5001 5800 7320 3241 85 ', 'Adana/Seyhan', 'ARH+', 'Adana', 'Yüreğir '),
(2424, 'FIRAT', 'SARIKAYA', '67@mbs.com', '$2y$10$zFQZDvHm3XSlrOSuDPIoDOG3bsadPW.ATc0KazvKmrrHy0wC.uSI2', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:56', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2426, 'FURKAN', 'AKYÜZ', 'fakyuz580@gmail.com', '$2y$10$IxX0cBVCQPSGR0PazrpJFeEjYRl2.8yPD37dDysk2Co9dGGAcJJbK', 2, 'Aday Hakem', '05525510320', '', '2007-03-20', NULL, 0, '2025-08-27 19:16:56', 0, 0, 0, 1, '10897409862', 'Denizbank ', 'TR440013400002345026200001', 'Adana ', 'Arh+', 'Adana', 'Seyhan '),
(2429, 'GÜLCAN', 'KOCATAŞLAR', '72@mbs.com', '$2y$10$uuITy5PIN4.atctlWfJO4.UaQw4D3QOnmJlj7N2W34rhXvKj9ufdm', 2, 'İl Hakemi', '05439257146', '53201', '2005-08-04', NULL, 1, '2025-08-27 19:16:56', 1, 1, 0, 1, '10306428576', 'Deniz Bank ', 'TR570013400002448823700001', 'Adana', 'ARH+', 'Adana ', 'Seyhan '),
(2430, 'HALİL', 'AKKUŞ', 'akkushalil946@gmail.com', '$2y$10$4Nx5wxalbVEqMoX7LouFWeX0TsY7Rp8/5qbMsrFEjYkusjGUPI17a', 2, 'Aday Hakem', '05359284792', '', '2009-03-12', NULL, 0, '2025-08-27 19:16:56', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2431, 'HIDIR', 'DEMİR', '74@mbs.com', '$2y$10$8tML7bgVti6v65kh1u6RNOtXjnQGDFbax.cc8eB4XwkMASbMDJQka', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:56', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2433, 'HÜSEYİN EMRE', 'ÖZTÜRK', '76@mbs.com', '$2y$10$Feh4yN6L7ZNNOeF4ZoJYxu49hfFoYnX1wUtn50PKiDkBrjiOqhPJu', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:56', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2439, 'İPEK', 'DEMİR', '82@mbs.com', '$2y$10$V7jidGVZUn/KwyL4zcIglOSs8tgDGivWvNQD6RRv0UnTLnzk/tXPS', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:57', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2440, 'İREM', 'FIRAT', 'emotionleesirem180@gmail.com', '$2y$10$I1HcxAsjwd9YiwSAF4uZVuZ1DlbEZoBJ1p/tLNwq8GOiq9h1mHsNG', 2, 'Aday Hakem', '05374374110', '', '2007-07-28', NULL, 0, '2025-08-27 19:16:57', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2441, 'İSA', 'TAŞDEMİR', 'isatasdemir2001@gmail.com', '$2y$10$lzjzLdh9tAJu7o.1LOKUWOllbfPOQy/.CG.LUsxtyYwk8Wc2dXg/O', 2, 'Aday Hakem', '05510847093', '', '2001-10-21', NULL, 1, '2025-08-27 19:16:57', 1, 1, 0, 1, '10531451134', '', 'TR72 0001 0090 1044 0100 7050 01', 'Adana', '', '1', '? number:2032 ?'),
(2443, 'İSMAİL SAMET', 'FETTAHOĞLU', '86@mbs.com', '$2y$10$2KIKSTT/d6vgI.i9lEfbh.pHkfIY9vkWFWq58o59HiZamiEewwUL6', 2, 'Aday Hakem', '05076158221', '', '1998-10-10', NULL, 1, '2025-08-27 19:16:57', 1, 0, 0, 1, '66070326112', 'TÜRK EKONOMİ BANKASI', 'TR590003200000000123578702', 'DÜZİÇİ', 'ORH+', 'ADANA', 'CUKUROVA'),
(2444, 'KAAN', 'ŞEN', '87@mbs.com', '$2y$10$VED6POSp0UGVeknbo8K4C.tsAXzwikWvJTFEm4DgKWz5Ui5fiO84G', 2, 'Aday Hakem', '', '', NULL, NULL, 1, '2025-08-27 19:16:57', 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2445, 'KADER', 'ŞEN', '88@mbs.com', '$2y$10$coUz9jj/cSoPWn/6B1/lAOCQNxRH70Y2BK/tKmM50d66PJabc4Eii', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:57', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2449, 'MAHMUT', 'ABALI', '92@mbs.com', '$2y$10$hN3pRSfaA.IxsCVYCD0uDO2.TZkUCRGlmHHh3OeCCNk69TsDEl7wK', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:57', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2450, 'MECNUN', 'EMEN', 'aynuremen01@hotmail.com', '$2y$10$O3ohXU0E0yt0HF9x1e9M7On1vtKjG6ZTz5cCYiF7BGq1OeKVbswS2', 2, 'Aday Hakem', '05418122310', '', '1999-02-05', NULL, 0, '2025-08-27 19:16:57', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2451, 'MEHMET ARDA', 'ÇEKER', 'mardaceker17@gmail.com', '$2y$10$6Serh47q6eDqTBvtoIOsVOf3w5snz6SWl.Od9fKarpwLZtTMZpuwW', 2, 'İl Hakemi', '05527393192', '53200', '2006-04-30', NULL, 0, '2025-08-27 19:16:57', 0, 0, 0, 1, '10807439248', 'Denizbank ', 'TR77 0013 4000 0244 1334 9000 01', 'Adana/Yüreğir ', 'A Rh ', 'Adana ', 'Seyhan'),
(2454, 'MEHMETCAN', 'DEMİR', 'memetcandemir471@gmail.com', '$2y$10$SIJs3lNyZdTEd7wXxw.kjetEEDdy9YZqpHPC5zcZweCvlEhtInbc6', 2, 'Aday Hakem', '05375042101', '', '2009-02-27', NULL, 1, '2025-08-27 19:16:57', 1, 1, 0, 1, '22420034816', 'Ziraat Bankası ', 'TR1400 0100 9010 78854720 5001', 'Seyhan/Adana', 'A Rh+', 'Adana ', 'Seyhan '),
(2455, 'MERT', 'TABAK', 'merttabak08@gmail.com', '$2y$10$09m0Ko7O3e3rzdfLJ7Thwu8OFTbEl8zTVHPbvZkf30bFyidLCOxIS', 2, 'Aday Hakem', '05323230608', '', '2008-05-21', NULL, 0, '2025-08-27 19:16:58', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2457, 'MUHAMMED ENES', 'BAYAR', 'muhammedenes0113@gmail.com', '$2y$10$USpetgZVjka2.Y.bHT5e0uVdiegUAHLTt9ldhT2JiMr/1lYFJgDqa', 2, 'İl Hakemi', '05347720113', '53199', '2006-06-23', NULL, 1, '2025-08-27 19:16:58', 1, 1, 0, 1, '10942439530', 'DENİZBANK', 'TR69 0013 4000 0241 5942 4000 01', 'SEYHAN/ADANA', 'BRH+', 'ADANA ', 'YÜREĞİR '),
(2458, 'MUHAMMED HAYDAR', 'BULUT', 'muhammedhaydarbulut01@gmail.com', '$2y$10$2rWSFHWH9ndhwWKDSJJ0fekVBJJM/wYK1mbv6O5i9vl/2Yi0rLHwq', 2, 'Aday Hakem', '05428344895', '', '2003-04-28', NULL, 0, '2025-08-27 19:16:58', 0, 0, 0, 1, '17845170812', 'Ziraat Bankası ', 'TR 3400 0100 1783 8524 2553 5002 ', 'Adana ', 'B rh ', 'Adana ', 'Seyhan '),
(2459, 'MUHAMMET', 'AKYAR', '102@mbs.com', '$2y$10$4QK92ybpHTW2.JaNPrFXbe25zOMEl1Zz3fq/FHEmum1mnvzwGgIZG', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:58', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2460, 'MUHAMMET ALİ', 'ALTAŞ', 'muhammetalialtas01@gmail.com', '$2y$10$orLUMcZoN9p1pElcjrnaa.BbrsS4TymFtd56RC7LZXJZly1X/q/Jq', 2, 'Aday Hakem', '05419261304', '', '2007-03-13', NULL, 1, '2025-08-27 19:16:58', 1, 1, 0, 1, '11263430640', 'Ziraat', 'TR88 0001 0090 1081 7426 5050 01', 'Seyhan', 'Arh+', 'Adana', 'Sarıçam'),
(2467, 'MURAT CAN', 'KAYA', '110@mbs.com', '$2y$10$f9pi9mrcVVEWdgGOc7n/E.XHIEr6B8HLtDelGF5iH.8ADA96uDTwC', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:58', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2469, 'MURAT', 'YILDIRIM', '112@mbs.com', '$2y$10$/CmNJ1KoswJCvEZ5R3QfueHbWoO3K2NIdn0VapL2Oc3gdW1cEr0Si', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:58', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2470, 'MUSTAFA ÇINAR', 'ÇAĞLAR', '113@mbs.com', '$2y$10$81MrFzhC3Xea2qFyuGTgqOAQXpiHtl4AX2NKyw3IgF1z3aqywuxJi', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:58', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2471, 'MUSTAFA KAĞAN', 'DEMİRCİOĞLU', 'demircioglum123@gmail.com', '$2y$10$sqmMyAOq22Nho4ztdMT1xuhfm/Yvjazi.hXUTi6npkOaSbE/yNfYK', 2, 'Aday Hakem', '05419670768', '', '2006-05-26', NULL, 0, '2025-08-27 19:16:59', 0, 0, 0, 1, '10837441402', '', '', 'Adana Yüreğir ', '', 'Adana', 'Adana / Sarıçam'),
(2472, 'MUSTAFA KEMAL', 'BURUNSUZ', '115@mbs.com', '$2y$10$uHRFzX8Ni2gIvt5YMnugmudpqXfnb2P7DgjYozSZD8zejlDsvNrza', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:59', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2475, 'MUSTAFA', 'TUTAR', '118@mbs.com', '$2y$10$IPPRGpi9vYvjdw7GfF5BReinUSpqQ8L37bs5L323V.YModRDxy6MG', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:59', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2477, 'MÜZURCAN', 'YÜKSEKYAYLA', '120@mbs.com', '$2y$10$9ouO1ukyJ5.W8VcNyhRKZ.VHhfU3EWG1DF/ttJoCSzKTcDFRWOJgq', 2, 'Aday Hakem', '05422063959', '', '2008-04-24', NULL, 1, '2025-08-27 19:16:59', 1, 1, 0, 1, '14143328310', 'Ziraat bankası', 'TR69 0001 0090 1078 7799 2050 01', 'Adana', 'Orh(+', 'Adana', 'Yüreğir'),
(2478, 'MÜSLÜM', 'POLAT', '121@mbs.com', '$2y$10$ccjeZh/YQOv0C.Fug6mLDeRJZaqadSTqthLSIK6GnDTOgsogesotm', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:16:59', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2479, 'NEHİR GÜLEN', 'GÖKTEPE', 'goktepenehir03@gmail.com', '$2y$10$kbhWoxBum4/e98fGTDeL4.lq0XhLo88YHjnHz528LuRp9jNif0d1C', 2, 'Aday Hakem', '05467728974', '', '2007-03-08', NULL, 0, '2025-08-27 19:16:59', 0, 0, 0, 1, '10888405644', 'Akbank', 'TR64 0004 6002 6088 8000 2100 58', 'Adana', '', 'Adana', 'Çukurova '),
(2480, 'NURSİMA', 'ÖZDEMİR', '123@mbs.com', '$2y$10$e7Gli7zPFz3SrBRo1m1Pje13t.I35uYxSsvPFEnm6uuo3Df1.CpX2', 2, 'İl Hakemi', '05332542269', '53198', '2007-08-08', NULL, 1, '2025-08-27 19:16:59', 1, 1, 0, 1, '11476420780', 'Ziraat bankası', 'TR29 0001 0090 1079 3523 0050 01', 'Seyhan', 'AR+', 'Adana', 'Sarıçam'),
(2487, 'ÖMER FARUK', 'BÜTÜNER', 'btnr_omerfaruk@hotmail.com', '$2y$10$zZ4fEpQ5B9iVhvs.s2qKq.JB9gfFgrKXbo//VyuwzhJOi8ixd6/XG', 2, 'İl Hakemi', '05511230271', '53197', '2005-09-09', NULL, 1, '2025-08-27 19:17:00', 1, 1, 0, 1, '11036721708', 'Vakıfbank', ' TR08 0001 5001 5800 7322 3555 86', 'Meram', '0RH+', 'Adana', 'Seyhan'),
(2488, 'ÖMER FARUK', 'SERİN', 'omersern12@gmail.com', '$2y$10$yld0xL9aWihYL3.1WLBRTeNWqYaa0wMtvnejf.GGiBLWGoVwVE9dy', 2, 'İl Hakemi', '05522581949', '53196', '2004-09-06', NULL, 1, '2025-08-27 19:17:00', 1, 1, 0, 1, '29686907434', 'Ziraat Bankası ', 'TR75 0001 0090 1029 4938 2050 01 ', 'Gaziosmanpaşa ', '0rh+ ', 'ADANA', 'SARIÇAM '),
(2489, 'ÖMER', 'ÖZBEK', '132@mbs.com', '$2y$10$plLxbRbE56Vnc8TuZWoQcOiGXUqgvIEAzeSi32tD82mwOSeQkyQ0u', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:00', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2490, 'ÖZGE', 'TATLIDEDE', 'ozgetatlidede01@gmail.com', '$2y$10$oGtOjB9fuXawaSwWhM9QXeKp6Q84qK9/fXp87KMlnxTlz4GQh5cAm', 2, 'İl Hakemi', '05451284856', '53195', '2005-04-14', NULL, 1, '2025-08-27 19:17:00', 1, 1, 0, 1, '10487866594', 'DENİZBANK', 'TR28 0013 4000 0228 3474 2000 01', 'ARTUKLU / MARDİN', 'ARH+ ', 'ADANA', 'YÜREĞİR'),
(2491, 'PINAR', 'SUDAN', '134@mbs.com', '$2y$10$5ZloQq1FRh7pRP49liii..L.CASpsI8v.VsAGnsWn7TWBzx29WhX6', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:00', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2492, 'RABİA', 'KARAKOYUN', '135@mbs.com', '$2y$10$uKUD/4b9cyIuwqZAH2IsRunqZ4/lQ0BTJ7eUELnT7DqDvm.k3fjwm', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:00', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `ad`, `soyad`, `email`, `sifre`, `rol`, `klasman`, `telefon`, `lisans_no`, `dogum_tarihi`, `last_birthday_notification_year`, `aktif`, `olusturma_tarihi`, `egitim_durum`, `antreman_durum`, `ceza_durum`, `uyari_kaldirildi`, `tc_no`, `banka_bilgisi`, `iban`, `dogum_yeri`, `kan_grubu`, `il`, `ilce`) VALUES
(2498, 'RÜMEYSANUR', 'ÖZDEMİR', '141@mbs.com', '$2y$10$u/4SvPBM6ykn9L4iCwMK5.jIsQudVG.87ID4tansPJu1aWIaFoa5y', 2, 'Aday Hakem', '05317093915', '', '2006-07-12', NULL, 0, '2025-08-27 19:17:00', 0, 0, 0, 1, '10924440790', 'Ziraat katılım ', 'TR51 0020 9000 0201 1264 0000 01', 'Adana ', '', '', ''),
(2499, 'SAKİNE', 'KOCADAYI', '142@mbs.com', '$2y$10$ApQe8MfygIRxk7O5AhdUAe0JiLkWApgrJJMqDg.67chd/y31gGYQS', 2, 'İl Hakemi', '05510626344', '53194', '2006-07-19', NULL, 1, '2025-08-27 19:17:00', 1, 1, 0, 1, '10651424834', 'Yapikredi ', 'TR160006701000000029725848', 'Seyhan /adana ', '0rh(+', 'Adana', 'Seyhan '),
(2501, 'SELAHATTİN', 'GUNDUZ', '144@mbs.com', '$2y$10$.nB0Fs0JDlGc9ZohJqfnX.nZ0uWzWZXxVApwrw33tnvCBePt3NMua', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:00', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2506, 'SERHAN', 'SARI', '149@mbs.com', '$2y$10$kIA6l9bXB1Kt6d1GOHSCWO4W/Yr4B4/gWI4wEC7Dn9KIc0u3l7OcG', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:01', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2509, 'SİBEL', 'ÇELGAN', 'sbl017309@gmail.com', '$2y$10$zpwNDvVDpfFPmhepdY2k7ups1FC.Hg6Lo3GStVPQZmPwlb/0vVIDK', 2, 'Aday Hakem', '05015727309', '', '2004-09-02', NULL, 0, '2025-08-27 19:17:01', 0, 0, 0, 1, '15641975952', 'Vakıf katılım bankası', 'TR38 0021 0000 0007 9206 9000 02', 'Cizre', 'B+', 'Adana', 'Sarıçam'),
(2511, 'SUAT', 'KARSLI', 'berkkarsli1999@gmail.com', '$2y$10$vouTP0XpaCZ.gyEYcmGnqu/3SeKOMBD7DQS58kf8ALXq.R8v0qs5O', 2, 'Aday Hakem', '05541778276', '', '2008-10-30', NULL, 1, '2025-08-27 19:17:01', 1, 1, 0, 1, '19126129854', 'Ziraat bankası ', 'TR41 0001 0090 1078 5667 7050 01', 'Adana', 'A RH+', 'Adana ', 'Seyhan'),
(2513, 'SUDENAZ', 'ÖZASLAN', 'Sudenazozaslan8@gmail.com', '$2y$10$TbajxByxTo4c298Lxll5JullkqxXCqIRN/8LehKXpIcayTjbCdI/K', 2, 'İl Hakemi', '05466436849', '53193', '2007-05-10', NULL, 1, '2025-08-27 19:17:01', 1, 1, 0, 1, '11713661860', 'ziraat bankası', '460001009010785269905001', 'antalya', '0Rh-', 'adana', 'saricam'),
(2515, 'SUDENAZ', 'TÜRKMEN', '158@mbs.com', '$2y$10$KvFCM3qM8TDzYqGVaJn63OY7ZueEKmtpfw/H740IMMsZNcFuu/f0u', 2, 'Aday Hakem', '05332241419', '', '2008-01-01', NULL, 1, '2025-08-27 19:17:01', 1, 1, 0, 1, '11629413164', 'Ziraat bankası ', 'TR87 0001 0090 1081 6809 6050 01', 'Adana seyhan', '0+ ', 'Adana', 'Sarıçam '),
(2519, 'ŞÜLAY', 'PEHLİVAN', 'pehlivansulay2002@gmail.com', '$2y$10$idahsGka00Ze2LtBbv5L6OOiq6bkpX8bNcI0CiMh21BMrVY1N4aH6', 2, 'İl Hakemi', '05398931807', '53192', '2002-07-18', NULL, 1, '2025-08-27 19:17:01', 1, 0, 0, 1, '50146040774', 'ZİRAAT BANKASI', 'TR36 0001 0003 0695 1599 1950 01', 'HATAY', '0 +', 'ADANA', 'YÜREĞİR'),
(2520, 'TAYFUN', 'KESKİN', '163@mbs.com', '$2y$10$oJG./te3I7h3EBKRjDQ3DeUMF7mZf7q9vagvM9xImvrABg.6JwI8e', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:01', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2521, 'TUANA', 'TAPAN', 'tuanatapan@icloud.com', '$2y$10$WvAEiJ8ycVp9GlBPM6W53e/916gszofWyTYZed4RHj17ttbIxEF6u', 2, 'İl Hakemi', '05415072779', '53191', '2007-02-23', NULL, 1, '2025-08-27 19:17:02', 1, 1, 0, 1, '10873410240', 'DENİZBANK', 'TR95 0013 4000 0229 8674 7000 01', 'SEYHAN / ADANA', 'ARH+ ', 'ADANA', 'SEYHAN'),
(2522, 'TÜRKER', 'KAPLAN', '165@mbs.com', '$2y$10$tEnQ2KEXANFBIC0Y4kKIIeY55kMDveTuDA8LzZV5xS688YnemfqBK', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:02', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2524, 'VAHİDE NUR', 'UZUNOĞLU', 'vahideu01@icloud.com', '$2y$10$dH6fRr6/XOE7iis.becNfuOMq9FQ1cfDqmgRXYGafLFhVapIg2Dzi', 2, 'İl Hakemi', '05538105499', '53190', '2007-05-03', NULL, 1, '2025-08-27 19:17:02', 1, 1, 0, 1, '11320427538', 'Vakıfbank', ' TR11 0001 5001 5800 7350 4972 69', 'Seyhan/Adana', 'AB +', 'Adana ', 'Seyhan'),
(2525, 'VEYSİ', 'KANAT', '168@mbs.com', '$2y$10$GYotHpDYNqYRzTODV3kIReO4z5ThixUaDDZszwi1ysMDptH06AWWe', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:02', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2528, 'YİĞİT ÖNCEL', 'UĞURBAŞ', '171@mbs.com', '$2y$10$u71xOpNe7njYYEGdzo2grOTVfQNv7v.KjPn1ibd298p63f42.xevW', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:02', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2529, 'YİĞİT', 'ÖZSOY', '172@mbs.com', '$2y$10$vIMeEVwTG3zbIxhBmyYAy.WO19cUvj/8EIbaKIWq/Kil/4H4RV1ky', 2, 'Aday Hakem', '', '', NULL, NULL, 0, '2025-08-27 19:17:02', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2532, 'YUSUF İSLAM', 'EKİNCİ', 'yusufekinccii@icloud.com', '$2y$10$/9oAU5tLNq41yu.kjbnw7.9yBLp0fzHHBvrGH79h5Vj6A1RXM9Mxa', 2, 'Aday Hakem', '05395522930', '', '2005-05-14', NULL, 0, '2025-08-27 19:17:02', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2536, 'ZELİHA', 'ÇAVLAN', '179@mbs.com', '$2y$10$XHzEka3OjPScKLJp8nAkru3aBOqM7GwD4U/PZERa.MvaE6nJaebhK', 2, 'Aday Hakem', '05510933688', '', '2006-02-22', NULL, 1, '2025-08-27 19:17:02', 1, 1, 0, 1, '10096395744', 'Yapıkredi', 'TR520006701000000028233146', 'Karataş', '', 'Adana ', 'Sarıçam'),
(2537, 'ZEYNEP KÜBRA', 'İRTEGÜN', '1905@mbs.com', '$2y$10$gua.pa1HcBIWFiuIi0gU0.Ue0RQkF0Jmzy0Op2vAC/zUGNuDYokku', 2, 'Aday Hakem', '05309624107', '', '2008-08-20', NULL, 1, '2025-08-27 19:17:03', 1, 1, 0, 1, '16447219318', 'ziraat bankası', 'TR 6700 0100 9010 79383070 5001', 'adana', '0rh+', 'adana', 'seyhan'),
(2538, 'METEHAN', 'ALPAT', 'metehan1988@outlook.com', '$2y$10$HS/nZjZlD2lWbG2V6duMjuPo3sISJJQeVnvpjU9KrWhSKsMNhZi1O', 3, 'KLASMAN GÖZLEMCİSİ', '05362817611', '8784', '1988-08-11', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '12187351424', 'Denizbank', 'TR04 0013 4000 0174 8195 7000 01', 'SEYHAN', 'ORH-', 'Adana', 'Seyhan'),
(2539, 'MUSTAFA KÜRŞAD', 'UÇAR', 'm.k.ucar@hotmail.com', '$2y$10$C3XzzAjymywDIVQQkUexHOtqKbeKEbsUsRV4HRO0HIxpoWmUkOlKi', 3, 'KLASMAN GÖZLEMCİSİ', '05368543018', '3969', '1978-07-25', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '38884332740', 'İş Bankası', 'TR290006400000160430005577', 'Adana', 'O+', 'Adana', 'Çukurova'),
(2540, 'AYDIN', 'BİLİR', 'aydinbilir1977@gmail.com', '$2y$10$SaRPlrqZMI5EyHMQV0DOvevVQLWozBgL2tekHROyjSB3H.cReLpX6', 3, 'BÖLGESEL GÖZLEMCİ', '05469757073', '6469', '1977-10-20', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '56929634598', 'DENİZBANK A.Ş.', 'TR84 0013 4000 0174 7815 8000 01', 'OSMANİYE', 'Arh+', 'ADANA', 'ÇUKUROVA'),
(2541, 'BURAK CEM', 'TAHİROĞLU', 'burakcemt@hotmail.com', '$2y$10$oSzruL8JEdUh/Hl./Q.Em.TeCQVOaIAKe5jHq8gatuVCy.W9kpeQK', 3, 'BÖLGESEL GÖZLEMCİ', '05325646055', '6883', '1979-02-11', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2542, 'EŞREF', 'GÖKALP', 'esref_gokalp@hotmail.com', '$2y$10$3nIz0hyUBHCZ8c7Lyj.Uju6jsqyhO2hN0Sn9itLA0InyC0APE.p.y', 3, 'BÖLGESEL GÖZLEMCİ', '05053223677', '6467', '1981-11-19', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '15925163990', 'ZİRAAT BANKASI', 'TR19 0001 0015 4439 1339 1550 05', 'CEYHAN', '0(Rh+', 'Adana', 'Ceyhan'),
(2543, 'FERHAT', 'INIG', 'ferhatinig@hotmail.com', '$2y$10$Qy82E721kPL4QMcDnEsjI.QyftN4OQs/GL69zN2UETN59kqmL/aBe', 3, 'BÖLGESEL GÖZLEMCİ', '05412196801', '5602', '1968-06-10', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '19054120282', 'DENİZBANK', '', 'ADANA', 'AB-', 'ADANA', 'ÇUKUROVA'),
(2544, 'OLGUN', 'BAŞDAN', '6099@mbs.com', '$2y$10$BQGEGD6Ltohw4v788mgn6Oq8xLn5YQ9cmrRQuAwyL41O/UVnb2Ti6', 3, 'BÖLGESEL GÖZLEMCİ', '05327488412', '6099', '1975-04-01', NULL, 0, '2025-08-27 19:24:05', 1, 0, 0, 1, '21196089860', 'Denizbank', 'TR64 0013 4000 0174 8931 7000 01', 'Adana', '', 'ADANA', 'SEYHAN'),
(2545, 'ÖZGEN FATİH', 'KALAY', 'ozgenkalay@gmail.com', '$2y$10$kCXSntT65tFDA7wb.fq0L.vXjNwO3.PboMNK.UVehUeqLiYN5lGlK', 3, 'BÖLGESEL GÖZLEMCİ', '05326238920', '6223', '1977-07-21', NULL, 0, '2025-08-27 19:24:05', 1, 0, 0, 1, '', '', '', '', '', '', ''),
(2546, 'ÖZGÜR', 'CEYLAN', 'ozgur.hakem@hotmail.com', '$2y$10$VgBRnOwDLioLsQ/HvsWlleSLc2GDspQ1U1JW7PRvPSzXCA72nK3.W', 3, 'BÖLGESEL GÖZLEMCİ', '05415647108', '5605', '1977-08-13', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '45256198156', 'Denizbank', 'TR17 0013 4000 0023 9130 0000 11', 'Hatay kırıkhan', 'Abrh+', 'Adana', 'Çukurova'),
(2547, 'TURGUT ŞAFAK', 'URSAVAŞ', 'sursavas1978@gmail.com', '$2y$10$VY2VNcMtzSpByH5Lzeqk1OqzTW79ugszZg6uJ9kTnmGtNDj050Dau', 3, 'BÖLGESEL GÖZLEMCİ', '05069087060', '6305', '1978-05-25', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '37081396906', '', '', 'Gaziantep ', 'A Rh ', 'Adana', 'Kozan'),
(2548, 'TÜLAY', 'TAPCI', '5374@mbs.com', '$2y$10$K2jCRuEp5X/iR4AO.TVTUuyamTNDbwcbRBGq.lONbdn68lKBbLiq6', 3, 'BÖLGESEL GÖZLEMCİ', '05325745754', '5374', '1974-07-25', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '17012163876', 'Deniz Bank', 'TR11 0013 4000 0174 9128 7000 01', 'Mersin ', 'A RH+', 'Adana', 'Çukurova'),
(2549, 'ABDULHAMİT', 'TAŞKIN', 'hamit.taskin.01@hotmail.com', '$2y$10$SijTfaPzdjqq0aXiqizXKu518aZmSGMEZC0pvl3UQ6QILsAkwq4d2', 3, 'İL GÖZLEMCİSİ', '05428443094', '8639', '1994-08-30', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '20323112914', 'Deniz Bank', 'TR79 0013 4000 0179 8595 4000 01', 'Yüreğir / ADANA', 'Arh +', 'Adana', 'Sarıçam'),
(2550, 'ABDULKADİR', 'BULDU', 'a.kadirbuldu@hotmail.com', '$2y$10$2hh9v0ETzAj0JQWepj2XX.lDhjhWUwKTocG.wU6xlIQdH8gxiOiUG', 3, 'İL GÖZLEMCİSİ', '05336612643', '1818', '1961-05-03', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '10933392752', 'Denizbank Kuzey Adana şub. Hes.no; 3230-17484355-351', 'TR42 0013 4000 0174 8435 5000 01', 'Ceyhan/ Adana ', 'ARH+', 'Adana ', 'Çukurova '),
(2551, 'ABDULLAH', 'ÇAM', 'abdullahcam120@gmail.com', '$2y$10$Bix5Ft.L9QOhKUPO6YKjKeFhJ26/iABc2hKOkh3.zf2sYDxmddWeO', 3, 'İL GÖZLEMCİSİ', '05529365301', '8640', '1988-05-25', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '12565300518', 'Denizbank', 'TR09 0013 4000 0175 1222 0000 01', 'Adana/Karaisali', 'Arh+', 'Adana ', 'Karaisalı '),
(2552, 'AYDIN', 'KIVANÇ', 'kvnc3301@gmail.com', '$2y$10$DXbgSCMulTxlVRyljl6UVuhwx1NKF6RDO.kcpn8BNWGlAQk1uyqIO', 3, 'İL GÖZLEMCİSİ', '05405248793', '3971', '1974-08-18', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '15682231840', 'Denizbank', 'TR61 0013 4000 0157 0211 2000 01', 'Adana', 'A+', 'ADANA', 'ÇUKUROVA'),
(2553, 'BATUHAN', 'KOÇ', 'batuhan29766@gmail.com', '$2y$10$yc46zAn/tHmjOqgsdPlbL.4dZugFLnZhhZ0a9AFMdTz1s2dqAdEPO', 3, 'İL GÖZLEMCİSİ', '05374515939', '8737', '1995-04-14', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, '18112157512', 'Denizbank', 'TR10 0013 4000 0174 9307 2000 01', 'Seyhan', 'Arh+', 'Adana', 'Yüreğir'),
(2554, 'BİLAL', 'ÖZDEMİR', '6870@mbs.com', '$2y$10$zcqojs4knWPP/eBT4SqlKOk2qFxfZP.LemgyFxeH/XpUCaL0C82nO', 3, 'İL GÖZLEMCİSİ', '0', '6870', '1987-05-07', NULL, 1, '2025-08-27 19:24:05', 1, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2555, 'DENİZ', 'ARSLAN', 'hakemdeniz01@gmail.com', '$2y$10$8NiNAJ0HZ/VNSvgvZTV.s.AGrP2laAdo3rBpZJnoSsytTFCKaoQom', 3, 'İL GÖZLEMCİSİ', '05422625456', '8736', '1988-07-18', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '17845133114', 'Garanti Bankası ', 'TR44 0006 2000 6600 0006 6932 18', 'Karataş', 'Brh +', 'Adana', 'Çukurova '),
(2557, 'ERDAL', 'SAYILIKAN', 'erdalsayilkan@hotmail.com', '$2y$10$sX/GYuW0U54LOLr5z4ok.eAyoZMzn99qD.Pj2LSXCq8.YS6Isntnu', 3, 'İL GÖZLEMCİSİ', '05326471647', '5603', '1978-09-24', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '22171053630', 'AKBANK', 'TR71 0004 6008 8688 8000 1603 96', 'ADANA', 'A+', 'ADANA', 'SEYHAN'),
(2558, 'ERHAN', 'KALAY', '1831@mbs.com', '$2y$10$BRsjgcDQKKKth3VFtBC4fedb1Do259ammxrg/t3eei7syL/nwkKMO', 3, 'İL GÖZLEMCİSİ', '05356451929', '1831', '1960-01-01', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '12022395880', '', '', 'KADİRLİ', '0RH+', 'ADANA', 'SEYHAN'),
(2559, 'ERTAN', 'İPLİK', 'e.iplik@hotmail.com', '$2y$10$FNrRCg.JwH8sL/tCrcUoBOVYYXL4cQe8FhdEeu4mEFcd6hNactFLG', 3, 'İL GÖZLEMCİSİ', '05323048829', '7775', '1977-05-31', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '18877127416', 'DENİZBANK', 'TR78 0013 4000 0116 0387 8000 01', 'ADANA', 'BRH+', 'ADANA', 'Çukurova'),
(2560, 'FURKAN', 'ÇELİKEL', 'furkancelikel7@gmail.com', '$2y$10$Rsp7vaef54Ba2i.etdlxJOUqiRhABvCfn5nUbSxWSPdljZ0Nt7hsu', 3, 'İL GÖZLEMCİSİ', '05423649620', '8642', '1993-08-25', NULL, 0, '2025-08-27 19:24:06', 0, 0, 0, 1, '44365043470', 'TEB', 'TR110003200000000120028978', 'ELAZIĞ', '0RH+', 'ADANA', 'SARIÇAM'),
(2561, 'HAKAN', 'ALAY', 'hknalay1926@gmail.com', '$2y$10$uOVR8ufXZCGqIVUdaVPLp.SEUUfe4z/SXzgI2dx/Vtb2Drn7RnPPK', 3, 'İL GÖZLEMCİSİ', '05383327809', '6549', '1979-12-05', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '21997777706', 'Denizbank ', 'TR96 0013 4000 0174 8118 2000 01', 'Adana ', 'O Rh+', 'Adana', 'Seyhan '),
(2562, 'HALİL', 'DAĞDEVİREN', '27halildagdeviren@gmail.com', '$2y$10$Cu/XOYElqjn4WEewQ39dHup/UI1hIWdCyF4.lJKA3NfhxUNIQRa56', 3, 'İL GÖZLEMCİSİ', '05056216140', '5855', '1973-02-20', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '41758233104', 'Ziraat bankası ', 'Tr260001002091443476305004', 'Nizip/Gaziantep', 'O Rh+', 'Gaziantep', 'Nizip'),
(2563, 'HALİT', 'ALADAĞ', 'academybesyo@gmail.com', '$2y$10$laWBnB40zlRsnLCrL.Ts.uDddPcgqTyCf0xKmlhxjC3NFkG5graBK', 3, 'İL GÖZLEMCİSİ', '05058560694', '4198', '1976-02-01', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '20188047864', 'Yapı Kredi Bankası', 'TR08 0006 7010 0000 0056 1323 99', 'Karaisalı', '0 Rh ', 'Adana', 'Karaisalı'),
(2564, 'HASAN', 'ALTINBAŞAK', 'hasan.altinbasak@yandex.com', '$2y$10$7l6giUptdvsfUNZ0r0.C4OW7TgQ3muSRzRKi.zsy19edRm8edH5iG', 3, 'İL GÖZLEMCİSİ', '05327819678', '6872', '1988-09-01', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '46018195116', 'Denizbank', 'TR41 0013 4000 0174 8672 2000 01', 'Mersin', 'Arh+', 'Adana', 'Yumurtalık'),
(2565, 'İRFAN', 'UĞURLUAY', 'irfanugurluay@hotmail.com', '$2y$10$kwasW2AQ2ntdQqa0zHXEn.sTAAeuo0B9XaLGWIi8Pt3HxDejcJKOe', 3, 'İL GÖZLEMCİSİ', '05326150100', '1832', '1964-08-16', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '', 'Denizbank', 'TR270013400001162877300002', 'DARENDE/ MALATYA', 'A.RH+', 'Adana', 'Çukurova'),
(2566, 'MEHMET', 'ARISOY', 'arisoymehmet01@gmail.com', '$2y$10$gQCtijYySv6No1/om6UlRe6HC0RGvwyX6yyp9L8mEXLkm88odq.Yu', 3, 'İL GÖZLEMCİSİ', '05063021380', '5939', '1981-04-01', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '18568101404', 'Denizbank Adıyaman Şubesi', 'TR36 0013 4000 0174 7644 1000 01', 'Karaisalı', 'A Rh ', 'Adana', 'Seyhan'),
(2567, 'MEHMET', 'BAŞKAN', 'baskandanismanlik@hotmail.com', '$2y$10$z0PJ4W1zcEJt4tZT0SW/u.jzLSGaQeJY1lx0en/PtjHT55l0c04N2', 3, 'İL GÖZLEMCİSİ', '05462860101', '5935', '1969-01-05', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '14629308430', 'DENİZBANK  YÜREĞİR / ADANA', 'TR15 0013 4000 0167 2560 7000 01', 'ADANA', '0 (-)', 'ADANA', 'SARIÇAM'),
(2568, 'MEHMET', 'GÖÇER', 'mehmetgocer51@hotmail.com', '$2y$10$MdqIker7IC8JApHfghH.6.g020h.nO3LHEJqdIVsgDE6Biq0qODPO', 3, 'İL GÖZLEMCİSİ', '05326637397', '8636', '1977-10-03', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '21139013976', 'Yapı Kredi Bankası', 'TR600006701000000056129911', 'Karaisalı', '0 rh ', 'Adana', 'Seyhan'),
(2569, 'MEHMET SONER', 'ODUNCU', 'msoduncu@hotmail.com', '$2y$10$kpukNDfQgmIQxbEPvNWjWueFI6QZGef9KTt.WjfwLku9zt2O.nRBa', 3, 'İL GÖZLEMCİSİ', '05052582032', '5373', '1983-01-09', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '10927394274', 'Akbank', 'TR86 0004 6003 3488 8000 1917 03', 'Adana', 'A +', 'Adana', 'Çukurova'),
(2570, 'METİN', 'ORAL', 'metoral83@gmail.com', '$2y$10$kA68e83eOPzIlxzP9XCFBe8UAT/EmaZ2Kcr1c9bhiHExRfHhxgowS', 3, 'İL GÖZLEMCİSİ', '05368196296', '8732', '1983-08-20', NULL, 1, '2025-08-27 19:24:06', 1, 0, 0, 1, '23404931534', 'Ziraat bankası', 'TR74 0001 0004 5959 5372 0850 08', 'Bingazi libya', '0rh+', 'Adana', 'Çukurova'),
(2571, 'MURAT', 'BAYSAL', 'muratbaysal@hotmail.com', '$2y$10$YO3FmAii9xNVlU1ozHpVcO7pST/vjpW3yiVocZbU1SLPqbM.JkUwW', 3, 'İL GÖZLEMCİSİ', '05055844499', '6222', '1971-08-01', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '39670373626', 'VAKIFBANK', 'TR570001500158007303819847', 'DÖRTYOL', 'ARH+', 'HATAY', 'PAYAS'),
(2572, 'MURAT', 'KIRGIL', '8735@mbs.com', '$2y$10$TdX3Mm9tF7SNys2OLxIkt.PG34KIPG2LKodTfzEOa4wr4ONIC1iv2', 3, 'İL GÖZLEMCİSİ', '', '8735', '1989-03-15', NULL, 0, '2025-08-27 19:24:07', 1, 0, 0, 1, '', '', '', '', '', '', ''),
(2573, 'NİHAT', 'PEKSOY', 'nihatpeksoy@hotmail.com', '$2y$10$nLM3RDGfXjEO2986ewpfWeAdNLh4hz9HVvBQErp2jfl/yCPJA1Vhu', 3, 'İL GÖZLEMCİSİ', '05354792188', '4591', '1965-03-10', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '53536414988', 'Denizbank', 'TR24 0013 4000 0180 7452 6000 01', 'Andirin ', '0 RH ', 'Adana', 'Seyhan'),
(2574, 'ÖMER', 'TÜMBAŞER', 'birsen_sahancan@hotmail.com', '$2y$10$FEk/9uhzC80n7QZTx2S7VeEWKvlx33IxghUZC1hvxBQDjcyYIIxhG', 3, 'İL GÖZLEMCİSİ', '05536357043', '8637', '1979-03-02', NULL, 0, '2025-08-27 19:24:07', 0, 0, 0, 1, '11488370912', 'denızbank', '330013400001209490100001', 'ADANA', 'AB RH', 'ADANA', 'SEYHAN'),
(2575, 'SADIK', 'BAHÇİVAN', 'sadikbahcivan@hotmail.com', '$2y$10$5d/ex7JwtU4Q468KRFE1n.6xoYE3mYBZAhtNrAxm/48yLBU6nfU9S', 3, 'İL GÖZLEMCİSİ', '05364341453', '7779', '1975-07-13', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '51547707624', 'GARANTİ BANKASI', 'TR52 0006 2001 0430 0006 6953 30', 'ADANA', 'B Rh ', 'ADANA', 'ÇUKUROVA'),
(2576, 'SAVAŞ', 'İLHAN', '6468@mbs.com', '$2y$10$azIhaatZQGKXe0DyE51hwOMllf9C05eRH2c7TvdmTEKnMJW3VwsFa', 3, 'İL GÖZLEMCİSİ', '05056430332', '6468', '1974-08-01', NULL, 0, '2025-08-27 19:24:07', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2577, 'SELİM', 'BAYKUT', 'selimbaykut@hotmail.com', '$2y$10$s460LoAtoqUNMvHwvvBFd.g2JuPli70jEQUJQMkYSPLMh3P8JPf/m', 3, 'İL GÖZLEMCİSİ', '05327420924', '3968', '1967-12-24', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '11551410200', 'DENİZBANK', 'TR66 0013 4000 0025 4483 7000 01', 'ADANA', '0 +', 'Adana', 'ÇUKUROVA'),
(2578, 'SEYFETTİN', 'AYKAN', 'seyfettinaykan@hotmail.com', '$2y$10$KVKgV0pA6d4TgdUy/uBPnORMFbLVrrjb/0Sjj7FbGFa286iSewo7a', 3, 'İL GÖZLEMCİSİ', '05386664301', '1819', '1962-04-06', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '11074366480', 'Deniz bank', 'TR720013400001747591000001', 'KOZAN', 'BRH+', 'ADANA', 'SEYHAN'),
(2579, 'SEZGİN', 'FINDIKLI', '8131@mbs.com', '$2y$10$eaQi.6dWb6GRtCfVgQB0L.9Dlkb8tbBXGBCFmThclYCdvRVV6szlG', 3, 'İL GÖZLEMCİSİ', '05393194774', '8131', '1984-02-02', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2580, 'SONER', 'AKSOĞAN', 'soner.aksogan@hotmail.com', '$2y$10$f1qBsxC8/lWy67n5SCmdsegEWPc.99t5HQnq1d.sbxgaHvpuFLG7y', 3, 'İL GÖZLEMCİSİ', '05063505556', '6828', '1981-03-09', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '48643490222', 'Deniz bank', 'TR77 0013 4000 0083 2803 3000 01', 'Malatya', '0 Rh ', 'Adana', 'Seyhan'),
(2581, 'UĞUR', 'YANARATEŞ', 'uguryanarates01@hotmail.com', '$2y$10$HYw.MdlAJ7Q6U7uBCptGj.NRSome7pJiR8dj9AScrGE6l8K5j50MO', 3, 'İL GÖZLEMCİSİ', '05322456200', '8127', '1977-06-05', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '14062295694', 'Denizbank ', 'TR88 0013 4000 0193 5892 9000 01', 'KADİRLİ', '0 rh ', 'Adana', 'Çukurova '),
(2582, 'UMUT', 'GÖK', 'umut_gok_01@hotmail.com', '$2y$10$ScM5Y85TfuV1BwPGBVKIj.OYZ60Jpc/tUEHzkkpVRbxHdjzjKbRAi', 3, 'İL GÖZLEMCİSİ', '05072068909', '8731', '1979-10-29', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '22408016452', 'Akbank', 'TR73 0004 6003 5188 8000 1818 72', 'Adana', 'B Rh+', 'Adana', 'Seyhan '),
(2583, 'VOLKAN', 'AKGÖZ', 'volkanakgoz01@gmail.com', '$2y$10$wEdt99cptRbR4vTTBq1fweDnBUBpTZWlnpkyWO54/2mRv517j8xaC', 3, 'İL GÖZLEMCİSİ', '05448801653', '8733', '1981-06-07', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '15763264644', 'ZİRAAT BANKASI ( KANALKÖPRÜ ŞUBESİ )', 'TR53 0001 0011 1946 0657 5950 03', 'adana', 'A rh+', 'ADANA', 'YUREGIR'),
(2584, 'VOLKAN', 'TÜREDİ', 'volkanturedi@hotmail.com', '$2y$10$4RHCH.qgMLhxHbDq33eVK.HGMektVLAAIGjVAP4qF//RHMv.0a/TK', 3, 'İL GÖZLEMCİSİ', '05315535633', '8734', '1984-09-03', NULL, 1, '2025-08-27 19:24:07', 1, 0, 0, 1, '22678004852', 'AKBANK', 'TR510004600313888000233541', 'ADANA', 'B RH ', 'ADANA', 'SEYHAN'),
(2587, 'EMRE', 'KOÇ', 'emre4koc@gmail.com', '$2y$10$cyUrdTK2i/lwKIjeQiyyBOr65yOXZh43obqfX6Tf2HI2iWS7R698q', 3, 'İL GÖZLEMCİSİ', '05333107449', '8638', '1994-07-29', NULL, 1, '2025-08-28 11:55:18', 1, 0, 0, 1, '11365356404', 'Garanti Bankası', 'TR53 0006 2000 3140 0006 6436 67', 'ADANA', '0Rh+', 'ADANA', 'SARIÇAM'),
(2588, 'Admin', '1', 'admin1@mbsadana.com', '$2y$10$PtdXMMkBsIA29i/CN5ZLge7uWYtvr8oftm9lfJPRF.J4f/BfjN3qe', 1, '', '', '', '2025-09-05', NULL, 1, '2025-09-05 18:31:10', 0, 0, 0, 0, '', '', '', '', '', '', ''),
(2589, 'Admin', '2', 'admin2@mbsadana.com', '$2y$10$c39NUkKFuhyBQtW5eE4FkecEeTFlwO7NjWiQVufrw8NZ8L4LClh8W', 1, '', '', '', '2025-09-05', NULL, 0, '2025-09-05 18:31:40', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2590, 'Admin', '3', 'admin3@mbsadana.com', '$2y$10$hBsY6nZmlgMesZX35xgVVOXXWw2bgFVqhf9uQ.2tPt2ciyziab/ge', 1, '', '', '', '2025-09-05', NULL, 0, '2025-09-05 18:32:41', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2591, 'Admin', '4', 'admin4@mbsadana.com', '$2y$10$JDCE.ngxzeFBLY/Tbx3/E..z11w3GLr81s/zhbzZvR3xlD/0LKKvG', 1, '', '', '', NULL, NULL, 1, '2025-09-09 09:16:29', 0, 0, 0, 0, '', '', '', '', '', '', ''),
(2592, 'YUSUF', 'KAYA', 'tff_kaya@hotmail.com', '$2y$10$CFTH/6K0yhyDXCj7x0MoCuH6i14SlY3h1FwLpDxYApcz/ODDwpjaK', 2, 'İl Hakemi', '05512480939', '43313', '1992-10-14', NULL, 1, '2025-09-16 09:57:02', 1, 1, 0, 1, '39187314624', 'Deniz Bank', 'TR98 0013 4000 0174 8973 7000 01', 'İslahiye / Gaziantep', 'A Rh ', 'Adana', 'Ceyhan'),
(2593, 'CEREN', 'SABUNCU', 'cerensabuncu456@gmail.com', '$2y$10$1yYLA6Fc70.fAjY1AII3bOz9imBAo4BDbsJKIrsdP0SCi7ji9Bu.6', 2, 'İl hakemi', '05050519261', '50982', '2003-09-25', NULL, 1, '2025-09-16 09:58:17', 1, 1, 0, 1, '15682206640', 'Denizbank', 'TR30 0013 4000 0197 8013 4000 01', 'Karataş', '0Rh+', 'Adana', 'Karataş'),
(2594, 'ABDULSAMET', 'EKER', 'ekers0801@gmail.com', '$2y$10$lrm33XKMHlSLHJ20hMfLweRg6SVDlKfoivffVt/Ak.UkZU1SXu1cK', 2, 'İl hakemi', '05517990801', '45858', '2000-09-28', NULL, 0, '2025-09-16 10:01:41', 0, 0, 0, 1, '13321248846', 'Deniz bank ', 'TR81 0013 4000 0174 7934 2000 01', 'Adana ', 'B+', 'Adana ', 'Çukurova '),
(2595, 'ALİ İHSAN ', 'KALALI', 'aliihsan438@gmail.com', '$2y$10$Absm1h5Ujb.tVha1G9DvOuO4pGCgtVnYEPeACbttwUVmAl7Kdx6u.', 2, 'İl Hakemi', '05541919550', '47040', '1998-09-22', NULL, 0, '2025-09-16 18:30:25', 0, 0, 0, 1, '16880849318', 'Denizbank', 'TR720013400001747891700001', 'Aşkabat/Türkmenistan', 'A rh(', 'Adana', 'Merkez'),
(2596, 'MUHAMMET', 'KILIÇ', 'muhammetkilicc01@gmail.com', '$2y$10$Xdu/EA.kJzHBU/qbgBzhKex/ojQqZm2wh0DlPZcRi1t/SCp3QcsdG', 2, 'il hakemi', '05345605030', '40374', '1991-05-02', NULL, 1, '2025-09-17 06:42:03', 1, 1, 0, 1, '15976231914', 'GARANTİ', 'TR76 0006 2001 1580 0006 6878 24', 'ADANA', 'B RH ', 'ADANA', 'SEYHAN'),
(2597, 'SERKAN', 'KARAYİĞİT', 'serkankgs@hotmail.com', '$2y$10$VFfGiAB8H5ai.BvDMexraOfN91XomkBCmymsR88ded.SbsLY24nCa', 3, 'İL GÖZLEMCİSİ', '05444872308', '6489', '1982-06-11', NULL, 1, '2025-09-20 15:03:41', 1, 0, 0, 1, '47914182040', 'Halkbank', 'TR620001200916600001034606', 'Osmaniye ', '0 rh ', 'Adana ', 'Çukurova '),
(2598, 'ALKAN', 'PARSAK', 'alkan_01@hotmail.com', '$2y$10$hm6UoGQvJfhm6OwBl4vWz.82Uwe5TOmQu6wyprVGJ/X58gRE5fwXq', 2, 'İl Hakemi', '05067547510', '28523', '1992-02-14', NULL, 1, '2025-09-23 13:52:52', 1, 1, 0, 1, '18148158354', 'Denizbank', 'TR690013400001747874300001', 'Seyhan', 'A rH+', 'Adana', 'Çukurova'),
(2600, '', '', '', '$2y$10$msHZDQfo3OAAj3ltt2aaLeBR1gvma6EqdZQuWx266s056ibl3OKWG', 0, '', '', '', NULL, NULL, 1, '2025-09-23 13:57:02', 0, 0, 0, 0, '', '', '', '', '', '', ''),
(2601, 'Admin', '5', 'admin5@mbsadana.com', '$2y$10$PNfcgVKQv9ZljrqetkfKT.ZJ1.tqBWUIKhRiwtzoWwvc2U/Qn26ay', 1, '', '', '', NULL, NULL, 1, '2025-09-24 08:01:22', 0, 0, 0, 0, '', '', '', '', '', '', ''),
(2602, 'EMRE', 'KERPİÇ', 'emrekerpic01@gmail.com', '$2y$10$2CNU4BL1pq9J180K0ut2s.w2lsXDWlspfw87kmDJo1mcQwu50Ch6u', 2, 'İl Hakemi', '05010800308', '51569', '2003-08-03', NULL, 0, '2025-09-24 12:57:03', 0, 0, 0, 1, '20167093356', 'Akbank', 'TR950004600104888000304503', 'Adana', 'A +', 'Adana', 'Çukurova'),
(2604, 'Admin', '6', 'admin6@mbsadana.com', '$2y$10$hS1Um5CeeBj1WHCYoCeK7OTypy24wOgo7yiHnFBegxEW4BOPyB/Lu', 1, '', '', '', NULL, NULL, 1, '2025-09-25 18:11:31', 0, 0, 0, 0, '', '', '', '', '', '', ''),
(2605, 'ZEKİ BATIN', 'AKGÜL', 'zekibatinakgul@gmail.com', '$2y$10$MnY4SH.7TVt01.2d5JE7A.XscdcTH5o9wP94qM3yHp/u1TN6dDE3m', 2, 'Aday Hakem', '05317761092', '', NULL, NULL, 0, '2025-10-10 10:53:54', 0, 0, 0, 1, '11134434630', '', 'TR 7600 0100 9010 50676290 5001', '', '0 +', '', ''),
(2606, 'ABBAS', 'ÖZER', 'ozerabbas44@hotmail.com', '$2y$10$bpO6Tmp2IniU2df1yAriteP1CB0sPq06835aMnKtOrcaXPJ2iw986', 2, 'İl Hakemi', '05368929279', '29195', '1987-05-02', NULL, 1, '2025-10-29 07:46:35', 1, 1, 0, 1, '60037122304', 'DENİZBANK', 'TR080013400000706186800008', 'MALATYA', 'A RH ', 'ADANA', 'ÇUKUROVA'),
(2607, 'TEST', 'GÖZLEMCİ', 'testgozlemci@mbsadana.com', '$2y$10$JY0E0NkATIZn/diIggt9I.w.PMPC/ARj84ia5FE9i7MKiKfp4Aw8m', 3, '', '', '', '1993-01-01', NULL, 1, '2025-11-04 12:13:03', 0, 0, 0, 1, '', '', '', '', '', '', ''),
(2608, 'TEST', 'HAKEM', 'testhakem@mbsadana.com', '$2y$10$N64ekuXyw/wZlHTPvXCI7u/lVReEx1whVLyG39Umfg2C6h6cQ6RH.', 2, '', '', '', '1993-01-01', NULL, 1, '2025-11-04 12:14:02', 0, 0, 0, 1, '', '', '', '', '', '', ''),
(2609, 'TEST', 'HAKEM 1', 'testhakem1@mbsadana.com', '$2y$10$AIlGN3p2bf8fzERgkfEmTeE49IKn3TzlUiBUcnkDlQee6EVXIx1N2', 2, '', '', '', NULL, NULL, 1, '2025-11-04 12:14:15', 0, 0, 0, 1, '', '', '', '', '', '', ''),
(2610, 'TEST', 'HAKEM 2', 'testhakem2@mbsadana.com', '$2y$10$t2DxbrG990EG7MazJT86I.LuOah6EqKXfX8ti.EpCR5fYkapAlVmS', 2, '', '', '', '1993-01-01', NULL, 1, '2025-11-04 12:14:35', 0, 0, 0, 1, '', '', '', '', '', '', ''),
(2611, 'Admin', '7', 'admin7@mbsadana.com', '$2y$10$p1sjAdhw.VA7ddr9a.01neX/KNESPLVt1bj4ypS8RRvg/njq9ZMdK', 1, '', '', '', NULL, NULL, 1, '2025-09-25 18:11:31', 0, 0, 0, 0, '', '', '', '', '', '', ''),
(2612, 'RIDVAN', 'YILMAZ', 'ridvanyilmaz05@icloud.com', '$2y$10$LYakyCbm2x8yGfFb4g0u8udDpcV6iGkRt5GqI2T52Hnv3gLJ1eZPa', 2, 'İl Hakemi', '05455867593', '49994', '2000-09-05', NULL, 1, '2025-11-24 09:44:37', 1, 1, 0, 1, '12399085018', 'Denizbank', 'TR480013400002025385900001', 'Şırnak', 'B+', 'Adana', 'Yüreğir'),
(2613, 'AYHAN', 'KARTAL', '0.1ayhankartal@gmail.com', '$2y$10$.twY7KwhOnrs6KRuZXfoDe0zGkavqQsmoJycfc0zTWeIm5Rd.Q82C', 2, 'İl Hakemi', '05357360582', '48139', '2000-09-11', NULL, 1, '2025-11-25 12:45:09', 1, 0, 0, 1, '13133187838', 'ZİRAAT BANKASI', 'TR34 0001 0000 1790 2740 1150 02', 'ADANA', 'O RH ', '', 'SEYHAN'),
(2614, 'BARIŞ', 'ÖZGÜNER', 'barisozguner@gmail.com', '$2y$10$ubDia7F7rXlkoNI4Ywily.axpuSuv7Azhz13mu8qNFlZbDT4QwYDy', 2, 'İl Hakemi', '05011082401', '51996', '2003-11-29', NULL, 0, '2025-11-25 12:47:43', 0, 0, 0, 1, '22591044300', 'ZİRAAT BANKASI', 'TR45 0001 0090 1011 3548 8050 01', 'YÜREĞİR', 'ORH+ ', 'ADANA', 'SEYHAN'),
(2615, 'MUSTAFA', 'EROL', 'mustafa_erl_01@hotmail.com', '$2y$10$UaXlG1y5HvqccI9dooX3mOzyOUXPzunViTtVDPhz/SUsAk.pd5nC6', 2, 'İl Hakemi', '05318102272', '45666', '1996-07-04', NULL, 1, '2025-11-25 12:50:14', 1, 1, 0, 1, '13921273446', 'DENİZBANK', 'TR04 0013 4000 0174 7642 8000 01', 'KOZAN', 'ARH+ ', 'ADANA', 'KOZAN'),
(2616, 'UYGAR', 'ZAHİTOĞLU', 'zahitogluuygar2006@gmail.com', '$2y$10$SRSDuzQbdXMtp25euuB9rOH90zfu1BLK/mKaWwauOcN61Xb8iiSAy', 2, 'İl Hakemi', '05379292295', '52000', '2006-03-17', NULL, 0, '2025-11-25 12:54:02', 0, 0, 0, 1, '10510428720', 'DENİZBANK', 'TR37 0013 4000 0234 6130 2000 01', 'SEYHAN', 'A RH ', 'ADANA', 'ÇUKUROVA'),
(2620, 'İBRAHİM', 'KOCA', 'biriyim@hotmail.com', '$2y$10$HFCqQWqNsTTSJF16X5qpGuKX0f/QvyMIk3E35D5i.aJnKTUQdV8iO', 3, 'İL GÖZLEMCİSİ', '5053846260', '6220', '1980-09-05', NULL, 1, '2025-12-09 03:22:06', 1, 0, 0, 1, '14068258778', 'ZİRAAT BANKASI', 'TR610001000621268410625002', 'BAHÇE', 'AH+', 'ADANA', 'SEYHAN'),
(2622, 'YUSUFCAN', 'YALI', 'ysfcny12@gmail.com', '$2y$10$A9QW5v9oBqCpmPvLLEgdMuZN59qBssuyS/Le/4VpI0e12Z1dPUsKG', 2, 'İl Hakemi', '05454531940', '42726', '1999-02-12', NULL, 1, '2025-12-09 13:27:42', 1, 1, 0, 1, '10495410666', 'Denizbank', 'TR160013400001540638200002', 'Adana', 'B+', 'Adana', 'Seyhan'),
(2623, 'KAAN NAZIM', 'KURTOĞLU', 'kurtoglunazim9@gmail.com', '$2y$10$eSkSnJPBPUzsy5C4/5EoaOoBvk0yQo0Ly3eofqALWMtwoO8mrTKMq', 2, 'Aday Hakem', '05396069673', '', '2009-04-04', NULL, 1, '2025-12-18 12:31:46', 1, 1, 0, 1, '19807139356', 'HALKBANKASI', 'TR22 0001 2001 2590 0066 0000 65', 'YÜREĞİR', 'ARH+', 'ADANA', 'YÜREĞİR'),
(2624, 'MUSTAFA KEMAL', 'KOÇ', 'mustafakemallkoc@gamil.com', '$2y$10$tqykjl46LtKaMspSgsvakeuK0FNj9XgtFb9nu34ue6CsVADBbQ6xu', 2, 'Aday Hakem', '05374820555', '', '2001-10-12', NULL, 1, '2026-04-16 17:54:39', 0, 0, 0, 1, '14512281420', 'ZİRAAT BANKASI', 'TR79 0001 0009 0285 5261 0350 03', 'SEYHAN', 'ARH+', 'ADANA', 'SEYHAN'),
(2625, 'BATUHAN', 'GÖKKAN', 'batuhangokkan@idoud.com', '$2y$10$fAys5PJpH2aESu7wfpPTCuuQnMzZZ1r3Ohjdv1HLH0HHPwpjevO.G', 2, 'Aday Hakem', '05443171749', '', '2007-10-25', NULL, 1, '2026-04-16 17:57:52', 0, 0, 0, 1, '12788271602', 'DENİZBANK', 'TR74 0013 4000 0243 4498 4000 01', 'DÖRTYOL / HATAY', 'B RH+', 'ADANA', 'SARIÇAM'),
(2626, 'MURAT ARDA', 'ÇALIŞKANER', 'muratcaliskaner01@gmail.com', '$2y$10$msKZAH6sYRRy0F5Yyd82yO5j5e8N0.K8S7m/eETRvpOlgnhfSzixG', 2, 'İl Hakemi', '05468965259', '51690', '2005-08-22', NULL, 1, '2026-05-21 06:23:54', 0, 0, 0, 0, '10588446084', 'GARANTİ BANKASI', 'TR76 0006 2000 1180 0006 9080 73', 'ADANA', 'Brh+', 'ADANA', 'YÜREĞİR');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `bildirimler`
--
ALTER TABLE `bildirimler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `disiplin_raporlari`
--
ALTER TABLE `disiplin_raporlari`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rapor_id` (`rapor_id`),
  ADD KEY `idx_durum` (`durum`),
  ADD KEY `onaylayan_id` (`onaylayan_id`);

--
-- Tablo için indeksler `dogum_gunu_mailleri`
--
ALTER TABLE `dogum_gunu_mailleri`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_mail` (`user_id`,`mail_tarihi`),
  ADD KEY `idx_mail_tarihi` (`mail_tarihi`);

--
-- Tablo için indeksler `duyurular`
--
ALTER TABLE `duyurular`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `duyuru_okundu`
--
ALTER TABLE `duyuru_okundu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_okuma` (`user_id`,`duyuru_id`),
  ADD KEY `duyuru_id` (`duyuru_id`);

--
-- Tablo için indeksler `egitimler`
--
ALTER TABLE `egitimler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `yukleyen_id` (`yukleyen_id`);

--
-- Tablo için indeksler `egitim_goruntulemeler`
--
ALTER TABLE `egitim_goruntulemeler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_view` (`egitim_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `klasmanlar`
--
ALTER TABLE `klasmanlar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ad` (`ad`);

--
-- Tablo için indeksler `ligler`
--
ALTER TABLE `ligler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `mac_kronometre`
--
ALTER TABLE `mac_kronometre`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `musabaka_id` (`musabaka_id`);

--
-- Tablo için indeksler `mac_notlar`
--
ALTER TABLE `mac_notlar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `mac_notlari_canli`
--
ALTER TABLE `mac_notlari_canli`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_musabaka` (`musabaka_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Tablo için indeksler `mazeretler`
--
ALTER TABLE `mazeretler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `musabakalar`
--
ALTER TABLE `musabakalar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `musabaka_hatirlatmalar`
--
ALTER TABLE `musabaka_hatirlatmalar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `musabaka_kronometre`
--
ALTER TABLE `musabaka_kronometre`
  ADD PRIMARY KEY (`musabaka_id`);

--
-- Tablo için indeksler `musabaka_notlar`
--
ALTER TABLE `musabaka_notlar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `musabaka_on_yukleme`
--
ALTER TABLE `musabaka_on_yukleme`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lig_id` (`lig_id`),
  ADD KEY `idx_stadyum_id` (`stadyum_id`),
  ADD KEY `idx_ev_sahibi` (`ev_sahibi_id`),
  ADD KEY `idx_misafir` (`misafir_id`),
  ADD KEY `idx_hakem` (`hakem_id`),
  ADD KEY `idx_yardimci_1` (`yardimci_1_id`),
  ADD KEY `idx_yardimci_2` (`yardimci_2_id`),
  ADD KEY `idx_dorduncu_hakem` (`dorduncu_hakem_id`),
  ADD KEY `idx_gozlemci` (`gozlemci_id`),
  ADD KEY `idx_durum` (`durum`),
  ADD KEY `idx_tarih` (`tarih`);

--
-- Tablo için indeksler `musaitlik`
--
ALTER TABLE `musaitlik`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `musaitlik_notlari`
--
ALTER TABLE `musaitlik_notlari`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_sezon_gun` (`user_id`,`sezon`,`gun`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `musaitlik_talepleri`
--
ALTER TABLE `musaitlik_talepleri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `raporlar`
--
ALTER TABLE `raporlar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `rapor_detaylari`
--
ALTER TABLE `rapor_detaylari`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sifre_sifirlama`
--
ALTER TABLE `sifre_sifirlama`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `stadyumlar`
--
ALTER TABLE `stadyumlar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `takimlar`
--
ALTER TABLE `takimlar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `bildirimler`
--
ALTER TABLE `bildirimler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `disiplin_raporlari`
--
ALTER TABLE `disiplin_raporlari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dogum_gunu_mailleri`
--
ALTER TABLE `dogum_gunu_mailleri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `duyurular`
--
ALTER TABLE `duyurular`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Tablo için AUTO_INCREMENT değeri `duyuru_okundu`
--
ALTER TABLE `duyuru_okundu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `egitimler`
--
ALTER TABLE `egitimler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Tablo için AUTO_INCREMENT değeri `egitim_goruntulemeler`
--
ALTER TABLE `egitim_goruntulemeler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `klasmanlar`
--
ALTER TABLE `klasmanlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `ligler`
--
ALTER TABLE `ligler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=340;

--
-- Tablo için AUTO_INCREMENT değeri `mac_kronometre`
--
ALTER TABLE `mac_kronometre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `mac_notlar`
--
ALTER TABLE `mac_notlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `mac_notlari_canli`
--
ALTER TABLE `mac_notlari_canli`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `mazeretler`
--
ALTER TABLE `mazeretler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `musabakalar`
--
ALTER TABLE `musabakalar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `musabaka_hatirlatmalar`
--
ALTER TABLE `musabaka_hatirlatmalar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `musabaka_notlar`
--
ALTER TABLE `musabaka_notlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `musabaka_on_yukleme`
--
ALTER TABLE `musabaka_on_yukleme`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8554;

--
-- Tablo için AUTO_INCREMENT değeri `musaitlik`
--
ALTER TABLE `musaitlik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `musaitlik_notlari`
--
ALTER TABLE `musaitlik_notlari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `musaitlik_talepleri`
--
ALTER TABLE `musaitlik_talepleri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `raporlar`
--
ALTER TABLE `raporlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rapor_detaylari`
--
ALTER TABLE `rapor_detaylari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `sifre_sifirlama`
--
ALTER TABLE `sifre_sifirlama`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `stadyumlar`
--
ALTER TABLE `stadyumlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- Tablo için AUTO_INCREMENT değeri `takimlar`
--
ALTER TABLE `takimlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1160;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2627;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `disiplin_raporlari`
--
ALTER TABLE `disiplin_raporlari`
  ADD CONSTRAINT `disiplin_raporlari_ibfk_1` FOREIGN KEY (`rapor_id`) REFERENCES `raporlar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disiplin_raporlari_ibfk_2` FOREIGN KEY (`onaylayan_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `dogum_gunu_mailleri`
--
ALTER TABLE `dogum_gunu_mailleri`
  ADD CONSTRAINT `dogum_gunu_mailleri_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `duyuru_okundu`
--
ALTER TABLE `duyuru_okundu`
  ADD CONSTRAINT `duyuru_okundu_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `duyuru_okundu_ibfk_2` FOREIGN KEY (`duyuru_id`) REFERENCES `duyurular` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `egitimler`
--
ALTER TABLE `egitimler`
  ADD CONSTRAINT `egitimler_ibfk_1` FOREIGN KEY (`yukleyen_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `egitim_goruntulemeler`
--
ALTER TABLE `egitim_goruntulemeler`
  ADD CONSTRAINT `egitim_goruntulemeler_ibfk_1` FOREIGN KEY (`egitim_id`) REFERENCES `egitimler` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `egitim_goruntulemeler_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `mac_kronometre`
--
ALTER TABLE `mac_kronometre`
  ADD CONSTRAINT `mac_kronometre_ibfk_1` FOREIGN KEY (`musabaka_id`) REFERENCES `musabakalar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `mac_notlari_canli`
--
ALTER TABLE `mac_notlari_canli`
  ADD CONSTRAINT `mac_notlari_canli_ibfk_1` FOREIGN KEY (`musabaka_id`) REFERENCES `musabakalar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mac_notlari_canli_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `musabaka_on_yukleme`
--
ALTER TABLE `musabaka_on_yukleme`
  ADD CONSTRAINT `fk_on_yukleme_dorduncu_hakem` FOREIGN KEY (`dorduncu_hakem_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_on_yukleme_ev_sahibi` FOREIGN KEY (`ev_sahibi_id`) REFERENCES `takimlar` (`id`),
  ADD CONSTRAINT `fk_on_yukleme_gozlemci` FOREIGN KEY (`gozlemci_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_on_yukleme_hakem` FOREIGN KEY (`hakem_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_on_yukleme_lig` FOREIGN KEY (`lig_id`) REFERENCES `ligler` (`id`),
  ADD CONSTRAINT `fk_on_yukleme_misafir` FOREIGN KEY (`misafir_id`) REFERENCES `takimlar` (`id`),
  ADD CONSTRAINT `fk_on_yukleme_stadyum` FOREIGN KEY (`stadyum_id`) REFERENCES `stadyumlar` (`id`),
  ADD CONSTRAINT `fk_on_yukleme_yardimci_1` FOREIGN KEY (`yardimci_1_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_on_yukleme_yardimci_2` FOREIGN KEY (`yardimci_2_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `sifre_sifirlama`
--
ALTER TABLE `sifre_sifirlama`
  ADD CONSTRAINT `sifre_sifirlama_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
