-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: mysql-server
-- Generation Time: May 31, 2022 at 06:24 AM
-- Server version: 8.0.27
-- PHP Version: 8.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cuoiki`
--
CREATE DATABASE IF NOT EXISTS `ewallet` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `ewallet`;

-- --------------------------------------------------------

--
-- Table structure for table `deposit`
--

CREATE TABLE `deposit` (
  `id` int NOT NULL,
  `idtrans` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `cardnumber` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `network`
--

CREATE TABLE `network` (
  `networkid` int NOT NULL,
  `networkname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fee` decimal(5,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `network`
--

INSERT INTO `network` (`networkid`, `networkname`, `fee`) VALUES
(11111, 'viettel', '0.00'),
(22222, 'mobifone', '0.00'),
(33333, 'vinaphone', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `otp_pass` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `otp_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE `state` (
  `idState` int NOT NULL,
  `description` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `state`
--

INSERT INTO `state` (`idState`, `description`) VALUES
(0, 'Chưa xác minh'),
(1, 'Đã xác minh'),
(2, 'Bị vô hiệu hóa'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `times_login`
--

CREATE TABLE `times_login` (
  `id` int NOT NULL,
  `times` int DEFAULT '0',
  `datelock` datetime DEFAULT NULL,
  `oldState` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `topupcard`
--

CREATE TABLE `topupcard` (
  `id` int NOT NULL,
  `idtrans` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `cardseri` char(13) COLLATE utf8_unicode_ci NOT NULL,
  `cardcode` char(12) COLLATE utf8_unicode_ci NOT NULL,
  `networkname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `price` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `idtrans` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `transtype` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `datetrans` datetime NOT NULL,
  `amount` int NOT NULL,
  `approval` tinyint(1) NOT NULL,
  `receiver` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer`
--

CREATE TABLE `transfer` (
  `id` int NOT NULL,
  `idtrans` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `note` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `feepaid` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `idUser` char(6) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `passwordTrans` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `front` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `back` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `idState` int DEFAULT 0,
  `createAT` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updateAT` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `balance` bigint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `idUser`, `email`, `name`, `password`,`passwordTrans`, `phone`, `birthday`, `address`, `front`, `back`, `idState`, `createAT`, `updateAT`, `balance`) VALUES
(0,0,"admin@gmail.com", 'admin', '14e1b600b1fd579f47433b88e8d85291','14e1b600b1fd579f47433b88e8d85291', '0968278202', NULL, NULL, NULL, NULL, 3, '2022-05-23 12:50:17', '2022-05-31 05:06:46', 0);

-- --------------------------------------------------------

--
-- Table structure for table `debidcard`
--

CREATE TABLE `debidcard` (
  `cardnumber` varchar(255) NOT NULL,
  `cccd` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `balance` bigint NOT NULL DEFAULT 1000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `debidcard`
--

CREATE TABLE `usercards` (
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `cardnumber` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `withdraw`
--

CREATE TABLE `withdraw` (
  `id` int NOT NULL,
  `idtrans` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `cardnumber` varchar(255) NOT NULL,
  `note` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `debidcard`
--
ALTER TABLE `debidcard`
  ADD PRIMARY KEY (`cardnumber`);

--
-- Indexes for table `deposit`
--
ALTER TABLE `deposit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idtrans` (`idtrans`);

--
-- Indexes for table `network`
--
ALTER TABLE `network`
  ADD PRIMARY KEY (`networkid`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD KEY `email` (`email`);

--
-- Indexes for table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`idState`);

--
-- Indexes for table `times_login`
--
ALTER TABLE `times_login`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `topupcard`
--
ALTER TABLE `topupcard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idtrans` (`idtrans`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`idtrans`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `transfer`
--
ALTER TABLE `transfer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idtrans` (`idtrans`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `idUser` (`idUser`),
  ADD KEY `idState` (`idState`);

--
-- Indexes for table `withdraw`
--
ALTER TABLE `withdraw`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idtrans` (`idtrans`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `deposit`
--
ALTER TABLE `deposit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `topupcard`
--
ALTER TABLE `topupcard`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `transfer`
--
ALTER TABLE `transfer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `withdraw`
--
ALTER TABLE `withdraw`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deposit`
--

ALTER TABLE `deposit`
  ADD CONSTRAINT `deposit_ibfk_1` FOREIGN KEY (`idtrans`) REFERENCES `transactions` (`idtrans`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `otp`
--
ALTER TABLE `otp`
  ADD CONSTRAINT `otp_ibfk_1` FOREIGN KEY (`email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `times_login`
--
ALTER TABLE `times_login`
  ADD CONSTRAINT `times_login_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`);

--
-- Constraints for table `topupcard`
--
ALTER TABLE `topupcard`
  ADD CONSTRAINT `topupcard_ibfk_1` FOREIGN KEY (`idtrans`) REFERENCES `transactions` (`idtrans`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transfer`
--
ALTER TABLE `transfer`
  ADD CONSTRAINT `transfer_ibfk_1` FOREIGN KEY (`idtrans`) REFERENCES `transactions` (`idtrans`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`idState`) REFERENCES `state` (`idState`);

--
-- Constraints for table `withdraw`
--
ALTER TABLE `withdraw`
  ADD CONSTRAINT `withdraw_ibfk_1` FOREIGN KEY (`idtrans`) REFERENCES `transactions` (`idtrans`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `usercards`
  ADD CONSTRAINT `usercard_ibfk_1` FOREIGN KEY (`email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `usercards`
  ADD CONSTRAINT `usercard_ibfk_2` FOREIGN KEY (`cardnumber`) REFERENCES `debidcard` (`cardnumber`) ON DELETE CASCADE ON UPDATE CASCADE;  
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
