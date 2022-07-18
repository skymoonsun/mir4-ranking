-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 18 Tem 2022, 17:20:57
-- Sunucu sürümü: 5.5.68-MariaDB
-- PHP Sürümü: 7.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `mir4_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `table_clan`
--

CREATE TABLE `table_clan` (
  `CLAN_ID` int(11) NOT NULL,
  `CLAN_RANK` int(11) NOT NULL,
  `CLAN_NAME` varchar(255) NOT NULL,
  `CLAN_LEADER` varchar(255) NOT NULL,
  `CLAN_PS` int(11) NOT NULL,
  `CLAN_SUSPEND` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `table_progress`
--

CREATE TABLE `table_progress` (
  `PROGRESS_ID` int(11) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `OLD_PS` int(11) NOT NULL,
  `NEW_PS` int(11) NOT NULL,
  `PS_PROGRESS` int(11) NOT NULL,
  `RANK_PROGRESS` int(11) NOT NULL,
  `DATE` int(11) NOT NULL,
  `DATE_TEXT` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `table_user`
--

CREATE TABLE `table_user` (
  `USER_ID` int(11) NOT NULL,
  `USER_CLAN` int(11) NOT NULL,
  `USER_RANK` int(11) NOT NULL,
  `USER_NAME` varchar(255) NOT NULL,
  `USER_POWER_SCORE` int(11) NOT NULL,
  `USER_SUSPEND` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `table_clan`
--
ALTER TABLE `table_clan`
  ADD PRIMARY KEY (`CLAN_ID`);

--
-- Tablo için indeksler `table_progress`
--
ALTER TABLE `table_progress`
  ADD PRIMARY KEY (`PROGRESS_ID`);

--
-- Tablo için indeksler `table_user`
--
ALTER TABLE `table_user`
  ADD PRIMARY KEY (`USER_ID`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `table_clan`
--
ALTER TABLE `table_clan`
  MODIFY `CLAN_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `table_progress`
--
ALTER TABLE `table_progress`
  MODIFY `PROGRESS_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `table_user`
--
ALTER TABLE `table_user`
  MODIFY `USER_ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
