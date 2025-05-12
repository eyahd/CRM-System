-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 12. Mai 2025 um 00:04
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `kundenverwaltung`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunden`
--

CREATE TABLE `kunden` (
  `id` int(11) NOT NULL,
  `typ` enum('privat','geschaeft') NOT NULL,
  `vorname` varchar(50) NOT NULL,
  `nachname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `passwort_hash` varchar(255) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `strasse` varchar(100) DEFAULT NULL,
  `plz` varchar(10) DEFAULT NULL,
  `ort` varchar(100) DEFAULT NULL,
  `land` varchar(50) DEFAULT NULL,
  `firmenname` varchar(100) DEFAULT NULL,
  `ust_id` varchar(20) DEFAULT NULL,
  `bonitaet_score` tinyint(3) UNSIGNED DEFAULT 0,
  `iban` varchar(34) DEFAULT NULL,
  `bic` varchar(11) DEFAULT NULL,
  `registrierungsdatum` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `rolle` enum('admin','kunde') NOT NULL DEFAULT 'kunde',
  `passwort_reset_token` varchar(64) DEFAULT NULL,
  `passwort_reset_expires` datetime DEFAULT NULL,
  `offene_rechnungen` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kunden`
--

INSERT INTO `kunden` (`id`, `typ`, `vorname`, `nachname`, `email`, `passwort_hash`, `telefon`, `strasse`, `plz`, `ort`, `land`, `firmenname`, `ust_id`, `bonitaet_score`, `iban`, `bic`, `registrierungsdatum`, `email_verification_token`, `email_verification_expires`, `rolle`, `passwort_reset_token`, `passwort_reset_expires`, `offene_rechnungen`) VALUES
(1, '', 'admin', '', 'admin@kundenverwaltung.de', '$2y$10$vSakvrCjKx8hsJJCGIlD8OTGkUxfSAAULg21/DLDC.5lf7j/h97yC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-07 01:59:03', NULL, NULL, 'admin', NULL, NULL, 0);


--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `kunden`
--
ALTER TABLE `kunden`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `kunden`
--
ALTER TABLE `kunden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
