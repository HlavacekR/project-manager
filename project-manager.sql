-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Pon 07. srp 2017, 00:22
-- Verze serveru: 10.1.16-MariaDB
-- Verze PHP: 5.6.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `project-manager`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `project`
--

CREATE TABLE `project` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `deadline` date NOT NULL,
  `type` int(11) NOT NULL COMMENT '''1'' => ''časově omezený projekt'', ''2'' => ''Continuous integration''',
  `web_project` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabulky `pr_vs_us`
--

CREATE TABLE `pr_vs_us` (
  `project_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

CREATE TABLE `user` (
  `id` bigint(20) NOT NULL,
  `firstname` varchar(50) COLLATE utf8_bin NOT NULL,
  `lastname` varchar(50) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Vypisuji data pro tabulku `user`
--

INSERT INTO `user` (`id`, `firstname`, `lastname`) VALUES
(1, 'Pavel', 'Růžička'),
(2, 'Petr', 'Novák'),
(3, 'Tomáš', 'Pospíšil'),
(4, 'Marek', 'Vyskočil');

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `pr_vs_us`
--
ALTER TABLE `pr_vs_us`
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Klíče pro tabulku `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `project`
--
ALTER TABLE `project`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT pro tabulku `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `pr_vs_us`
--
ALTER TABLE `pr_vs_us`
  ADD CONSTRAINT `fk__project_id` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk__user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
