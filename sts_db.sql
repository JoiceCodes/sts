-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2025 at 07:53 AM
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
-- Database: `sts_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `id` int(11) NOT NULL,
  `case_number` varchar(50) NOT NULL,
  `type` varchar(255) NOT NULL,
  `subject` text NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `product_group` varchar(255) NOT NULL,
  `product` varchar(255) NOT NULL,
  `product_version` varchar(50) NOT NULL,
  `severity` enum('Production System Down','Restricted Operations','Question/Inconvenience','') NOT NULL,
  `case_status` enum('Open','Waiting in Progress','Closed','Solved') NOT NULL DEFAULT 'Open',
  `attachment` varchar(255) NOT NULL,
  `case_owner` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `datetime_opened` datetime DEFAULT current_timestamp(),
  `reopen` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_number`, `type`, `subject`, `contact_name`, `product_group`, `product`, `product_version`, `severity`, `case_status`, `attachment`, `case_owner`, `company`, `last_modified`, `datetime_opened`, `reopen`) VALUES
(1, '00762544', 'Technical Support', 'Linux Disk Utilization Report', '', 'Network Monitoring', '', '', '', 'Open', '', 'James Carlisle', '', '2025-03-05 02:32:02', '2025-02-18 12:29:59', 0),
(7, '02585922', 'Technical Support', 'Jsjsjsjjs', 'John Doe', 'Network Monitoring', 'd27fb5e814c08773b4f9', '24.0.1', 'Production System Down', 'Waiting in Progress', 'uploads/67b6a8fd3aa047.42345979.pdf', 'Jane Smith', '0a1b06489824263d4239', '2025-03-05 02:36:50', '2025-02-20 12:01:01', 0),
(8, '90027591', 'Technical Support', 'Ndkwkdmdmdl', 'John Doe', 'Network Monitoring', 'a17a61515d1a82636bdd', '24.0.1', 'Production System Down', 'Open', 'uploads/67b6b30f94ce38.46841737.pdf', 'Jane Smith', '732eb093c79f80eabd0c', '2025-03-03 07:04:37', '2025-02-20 12:43:59', 0);

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `case_number` varchar(50) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `case_number`, `sender`, `receiver`, `message`, `created_at`) VALUES
(61, '02585922', 'John Doe', 'Jane Smith', 'test', '2025-03-03 06:55:54'),
(62, '02585922', 'Jane Smith', 'John Doe', 'test', '2025-03-03 07:05:21'),
(63, '02585922', 'John Doe', 'Jane Smith', 'Hi', '2025-03-05 01:12:17'),
(64, '02585922', 'John Doe', 'Jane Smith', 'Hi', '2025-03-05 01:17:36'),
(65, '02585922', 'Jane Smith', 'John Doe', 'Hello', '2025-03-05 01:17:51'),
(66, '02585922', 'Jane Smith', 'John Doe', 'Hello 2', '2025-03-05 01:19:01'),
(67, '02585922', 'Jane Smith', 'John Doe', 'Ako to', '2025-03-05 01:29:13'),
(68, '02585922', 'John Doe', 'Jane Smith', 'Hi', '2025-03-05 01:29:15'),
(69, '02585922', 'John Doe', 'Jane Smith', 'uwu', '2025-03-05 01:29:23'),
(70, '02585922', 'Jane Smith', 'John Doe', 'Hoy', '2025-03-05 01:30:43'),
(71, '02585922', 'John Doe', 'Jane Smith', 'uwu~', '2025-03-05 01:30:46'),
(72, '02585922', 'John Doe', 'Jane Smith', 'Hdisi', '2025-03-05 01:30:48');

-- --------------------------------------------------------

--
-- Table structure for table `platforms`
--

CREATE TABLE `platforms` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `platform` enum('Windows','MacOS') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platforms`
--

INSERT INTO `platforms` (`id`, `product_id`, `platform`) VALUES
(3, 3, 'Windows'),
(4, 3, 'MacOS');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_category` varchar(255) NOT NULL,
  `product_type` varchar(255) NOT NULL,
  `product_version` varchar(50) NOT NULL,
  `license_type` varchar(100) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `license_duration` int(11) NOT NULL CHECK (`license_duration` >= 0),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `product_category`, `product_type`, `product_version`, `license_type`, `serial_number`, `license_duration`, `created_at`) VALUES
(3, 'test', 'test', 'test', '20', 'test', '123', 123123, '2025-03-10 05:46:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('User','Engineer','Technical Engineer','Technical Head') NOT NULL DEFAULT 'User',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'John Doe', 'arexvolkzki@gmail.com', 'johndoe', '1234', 'Engineer', '2025-02-18 03:57:37'),
(3, 'Jane Smith', 'barandonjoice07@gmail.com', 'janesmith', '123', 'User', '2025-02-20 03:59:40'),
(4, 'Pedro Santos', 'johndoe@example.com', 'pedrosantos', '123', 'Technical Engineer', '2025-02-20 04:18:49'),
(5, 'Shan Cai Loyola', 'jcbrndn31@gmail.com', 'Shan', 'password', 'User', '2025-02-20 07:25:51'),
(7, 'Juan Cruz', 'juancruz@example.com', 'juancruz', '123', 'Technical Head', '2025-02-20 07:29:09'),
(8, 'Ranz Andrei Ornopia', 'ranz@gmail.com', 'Ranz', 'paswword', 'Engineer', '2025-02-20 07:55:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `case_number` (`case_number`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `platforms`
--
ALTER TABLE `platforms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `platforms`
--
ALTER TABLE `platforms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `platforms`
--
ALTER TABLE `platforms`
  ADD CONSTRAINT `platforms_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
