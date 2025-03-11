-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2025 at 08:22 AM
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
  `user_id` int(11) DEFAULT NULL,
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

INSERT INTO `cases` (`id`, `case_number`, `type`, `subject`, `user_id`, `product_group`, `product`, `product_version`, `severity`, `case_status`, `attachment`, `case_owner`, `company`, `last_modified`, `datetime_opened`, `reopen`) VALUES
(23, '00000001', 'test2', 'Jwjdk', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Solved', '1741673320_Screenshot_10-3-2025_13329_.jpeg', '3', 'Antivirus Solutions', '2025-03-11 06:52:13', '2025-03-11 14:10:28', 1),
(25, '00000002', 'test2', 'Jeididmsbsjs', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Solved', '1741676414_images (1).jpg', '3', 'Antivirus Solutions', '2025-03-11 07:05:31', '2025-03-11 15:00:37', 1);

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
(74, '00000001', 'Jane Smith', 'John Doe', 'hi', '2025-03-11 06:24:53'),
(75, '00000001', 'John Doe', 'Jane Smith', 'huhu', '2025-03-11 06:24:59'),
(76, '00000001', 'Jane Smith', 'John Doe', 'haha', '2025-03-11 06:26:27'),
(77, '00000001', 'Jane Smith', 'John Doe', 'hehe', '2025-03-11 06:26:48'),
(78, '00000001', 'John Doe', 'Jane Smith', 'hihi', '2025-03-11 06:26:58'),
(79, '00000001', 'Pedro Santos', 'Jane Smith', 'Hello, ako \'to', '2025-03-11 06:29:04'),
(80, '00000001', 'Pedro Santos', 'Jane Smith', 'Eyyy', '2025-03-11 06:29:11'),
(81, '00000001', 'Pedro Santos', 'Jane Smith', 'yolo', '2025-03-11 06:29:20'),
(82, '00000001', 'Juan Cruz', 'Jane Smith', 'Eyyyy🤙', '2025-03-11 06:30:23'),
(83, '00000001', 'Pedro Santos', 'Jane Smith', 'break it down, ‘yo🤘', '2025-03-11 06:30:52'),
(84, '00000002', 'Jane Smith', 'John Doe', 'hi', '2025-03-11 07:00:57');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `company` varchar(255) NOT NULL,
  `product_group` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_category` enum('Progress','Neverfail','Sophos','Trustwave','SecPod - SanerNow') NOT NULL,
  `product_type` enum('OpenEdge','Sitefinity','Moveit','DataDirect','WhatsUp Gold','Neverfail Continuity Engine','Sophos Intercept X','Sophos XG Firewall','Sophos Email','Sophos Central','Sophos MDR','Trustwave SpiderLabs','Trustwave MailMarshal','Trustwave DbProtect','Trustwave Secure Web Gateway','Trustwave Managed SIEM','SanerNow Vulnerability Management','SanerNow Patch Management','SanerNow Compliance Management','SanerNow Asset Exposure Management','SanerNow Endpoint Threat Detection & Response') NOT NULL,
  `product_version` varchar(50) NOT NULL,
  `supported_platforms` varchar(255) NOT NULL,
  `license_type` varchar(100) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `license_duration` date NOT NULL,
  `status` enum('Active','Deactivated','','') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `company`, `product_group`, `product_name`, `product_category`, `product_type`, `product_version`, `supported_platforms`, `license_type`, `serial_number`, `license_duration`, `status`, `created_at`) VALUES
(3, 'XYZ Corporation', 'Antivirus Solutions', 'SecureDefender Antivirus Pro', 'Progress', 'OpenEdge', 'v12.5', 'Windows, Linux', 'Enterprise', '123', '2026-05-10', 'Deactivated', '2025-03-10 05:46:43');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `product_category` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `product_category`, `created_at`) VALUES
(1, 'Progress', '2025-03-11 02:34:32'),
(2, 'Neverfail', '2025-03-11 02:34:32'),
(3, 'Sophos', '2025-03-11 02:34:32'),
(4, 'Trustwave', '2025-03-11 02:34:32'),
(5, 'SecPod - SanerNow', '2025-03-11 02:34:32');

-- --------------------------------------------------------

--
-- Table structure for table `product_types`
--

CREATE TABLE `product_types` (
  `id` int(11) NOT NULL,
  `product_category_id` int(11) NOT NULL,
  `product_type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_types`
--

INSERT INTO `product_types` (`id`, `product_category_id`, `product_type`, `created_at`) VALUES
(1, 1, 'OpenEdge', '2025-03-11 02:39:56'),
(2, 1, 'Sitefinity', '2025-03-11 02:39:56'),
(3, 1, 'Moveit', '2025-03-11 02:39:56'),
(4, 1, 'DataDirect', '2025-03-11 02:39:56'),
(5, 1, 'WhatsUp Gold', '2025-03-11 02:39:56'),
(6, 2, 'Neverfail Continuity Engine', '2025-03-11 02:39:56'),
(7, 3, 'Intercept X', '2025-03-11 02:39:56'),
(8, 3, 'XG Firewall', '2025-03-11 02:39:56'),
(9, 3, 'Sophos Email', '2025-03-11 02:39:56'),
(10, 3, 'Sophos Central', '2025-03-11 02:39:56'),
(11, 3, 'Sophos MDR', '2025-03-11 02:39:56'),
(12, 4, 'SpiderLabs', '2025-03-11 02:39:56'),
(13, 4, 'MailMarshal', '2025-03-11 02:39:56'),
(14, 4, 'DbProtect', '2025-03-11 02:39:56'),
(15, 4, 'Secure Web Gateway', '2025-03-11 02:39:56'),
(16, 4, 'Managed SIEM', '2025-03-11 02:39:56'),
(17, 5, 'SanerNow Vulnerability Management', '2025-03-11 02:39:56'),
(18, 5, 'SanerNow Patch Management', '2025-03-11 02:39:56'),
(19, 5, 'SanerNow Compliance Management', '2025-03-11 02:39:56'),
(20, 5, 'SanerNow Asset Exposure Management', '2025-03-11 02:39:56'),
(21, 5, 'SanerNow Endpoint Threat Detection & Response', '2025-03-11 02:39:56');

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
  `account_status` enum('Active','Deactivated','','') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `role`, `account_status`, `created_at`) VALUES
(1, 'John Doe', 'arexvolkzki@gmail.com', 'johndoe', '123', 'Engineer', 'Active', '2025-02-18 03:57:37'),
(3, 'Jane Smith', 'barandonjoice07@gmail.com', 'janesmith', '123', 'User', 'Active', '2025-02-20 03:59:40'),
(4, 'Pedro Santos', 'johndoe@example.com', 'pedrosantos', '123', 'Technical Engineer', 'Active', '2025-02-20 04:18:49'),
(5, 'Shan Cai Loyola', 'jcbrndn31@gmail.com', 'Shan', '123', 'User', 'Active', '2025-02-20 07:25:51'),
(7, 'Juan Cruz', 'juancruz@example.com', 'juancruz', '123', 'Technical Head', 'Active', '2025-02-20 07:29:09'),
(8, 'Ranz Andrei Ornopia', 'ranz@gmail.com', 'Ranz', '123', 'Engineer', 'Active', '2025-02-20 07:55:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `case_number` (`case_number`),
  ADD KEY `fk_cases_user` (`user_id`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_types`
--
ALTER TABLE `product_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_category_id` (`product_category_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `fk_cases_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_types`
--
ALTER TABLE `product_types`
  ADD CONSTRAINT `product_types_ibfk_1` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
