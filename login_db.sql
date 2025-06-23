-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 04:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `login_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `people_count` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `fullname`, `phone`, `email`, `people_count`, `booking_date`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn A', '0123456789', 'VanA@gmail.com', 3, '2025-05-30', '2025-05-28 20:27:05', '2025-05-31 00:24:03'),
(2, 'Đức Ngọc', '0367198726', 'phamducyb2004@gmail.com', 3, '2025-06-08', '2025-05-29 04:43:04', '2025-05-29 04:49:51'),
(3, 'Nguyễn Văn B', '0123456784', 'VanB@gmail.com', 1, '2025-06-01', '2025-05-29 04:49:19', '2025-05-29 04:49:19'),
(4, 'Bùi Ngọc Vũ', '0987654354', 'VuPTIT@gmail.com', 2, '2025-06-06', '2025-05-29 07:59:25', '2025-05-29 07:59:25'),
(5, 'Nguyễn Văn B', '0999999999', 'VanB@gmail.com', 2, '2025-06-08', '2025-05-31 04:44:37', '2025-05-31 04:44:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(6) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `job_position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `birthday`, `address`, `email`, `description`, `job_position`) VALUES
(1, 'admin', '$2y$10$8/udHTW05FglFGaP3uS0PuyRklRv3PkEBK1tT4CKocYYJ3BRMU0ta', 'Admin User', '1990-01-01', 'Hà Nội, Việt Nam', 'admin@example.com', 'Quản trị viên hệ thống', 'Quản trị viên'),
(5, 'Quang12345', '$2y$10$HjIQI8gy850N8ouTyZn4a.LFOrQL/RooZ5gQgV6qr8daKYWU7bWxO', 'Nguyễn Ngọc Quang', '2004-12-12', 'Hà Nội', 'QuangPTIT@gmail.com', '', 'Quản Lý'),
(101, 'Dai123', '$2y$10$ECMxL7bcfu8/aZwXKlqqCee8ZoqEv9uFi1qnFsQeUxsWCwMxx/.QS', 'Trần Trọng Đại', '2004-04-15', 'Hà Nam', 'DaiPTIT@gmail.com', '', 'Quản Lý'),
(102, 'Duc123', '$2y$10$Mm5CTTpGIauFUiTJjWp9huXrXAtTL/W95o/ZHpFBFWXisTfGNjqv.', 'Phạm Lý Ngọc Đức', '2004-01-26', 'Yên Bái', 'phamducyb2004@gmail.com', '', 'Quản Lý'),
(103, 'HLD123', '$2y$10$GNBZQcMI4Eohjxx3VJ3bruNlv.hM.D0TPMwDUFYMkBujkxwkHk08C', 'Hồ Lý Đức', '2004-11-11', 'Phú Thọ', 'DucPTIT@gmail.com', '', 'Quản Lý'),
(104, 'Vu123', '$2y$10$TGi.PDlabR7eNZDhcexPS.oaZjUu4UCCuqUlmetudPMeh5TLZ8hXi', 'Bùi Ngọc Vũ', '2004-10-10', 'Hòa Bình', 'VuPTIT@gmail.com', '', 'Quản Lý');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
