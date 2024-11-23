-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 23 Kas 2024, 19:10:39
-- Sunucu sürümü: 10.4.28-MariaDB
-- PHP Sürümü: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `start`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `doner`
--

CREATE TABLE `doner` (
  `id` int(11) NOT NULL,
  `eposta` varchar(200) NOT NULL,
  `sifre` varchar(200) NOT NULL,
  `token` varchar(200) DEFAULT NULL,
  `captcha` varchar(250) DEFAULT NULL,
  `captchaexpiry` datetime DEFAULT NULL,
  `verification` tinyint(1) DEFAULT NULL,
  `ipaddress` varchar(45) DEFAULT NULL,
  `yetki` tinyint(1) DEFAULT NULL,
  `kayit` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `doner`
--

INSERT INTO `doner` (`id`, `eposta`, `sifre`, `token`, `captcha`, `captchaexpiry`, `verification`, `ipaddress`, `yetki`, `kayit`) VALUES
(117, 'taha@hotmail.com', 'youtubertahakeskin.crazy_boy27', 'c508d16482ead3061c668de6443a6f266863d20f875d6df04ce6da84bdf9863c', 'c508d16482ead3061c668de6443a6f266863d20f875d6df04ce6da84bdf9863c', '2030-11-19 21:06:30', 1, '172.31.255.255', 1, '2024-11-15 18:06:30');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `doner`
--
ALTER TABLE `doner`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `doner`
--
ALTER TABLE `doner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
