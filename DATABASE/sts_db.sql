-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2025 at 06:08 PM
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
  `case_status` enum('New','Waiting in Progress','Closed','Solved') NOT NULL DEFAULT 'New',
  `attachment` varchar(255) NOT NULL,
  `case_owner` int(11) NOT NULL,
  `company` varchar(255) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `datetime_opened` datetime DEFAULT current_timestamp(),
  `reopen` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_number`, `type`, `subject`, `user_id`, `product_group`, `product`, `product_version`, `severity`, `case_status`, `attachment`, `case_owner`, `company`, `last_modified`, `datetime_opened`, `reopen`) VALUES
(35, '00000001', 'test', 'test', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-27 11:37:00', '2025-03-12 11:59:45', 0),
(36, '00000002', 'test', 'Jsksmslsksn', 8, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-27 11:44:43', '2025-03-13 10:19:00', 1),
(37, '00000003', 'test3', 'Jejdidksns', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2024-11-13 02:25:50', '2025-03-13 10:24:13', 0),
(38, '00000004', 'test4', 'Wjdjwndms', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Question/Inconvenience', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-27 14:34:35', '2025-03-13 10:24:37', 1),
(39, '00000005', 'test5', 'test', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-27 07:30:11', '2025-03-13 10:59:44', 0),
(41, '00000007', 'test1', 'test1', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'Solved', '', 3, 'Antivirus Solutions', '2025-03-27 12:47:09', '2025-03-27 00:39:21', 0),
(44, '00000008', 'test3', 'test3', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-27 09:08:38', '2025-03-27 00:54:17', 0),
(45, '00000009', 'test4', 'test4', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Question/Inconvenience', 'Solved', '', 3, 'Antivirus Solutions', '2025-03-27 12:40:08', '2025-03-27 01:04:50', 0),
(46, '00000010', 'ewaagt', 'asdvsgg', NULL, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Question/Inconvenience', 'New', '', 3, 'Antivirus Solutions', '2025-03-27 14:30:33', '2025-03-27 07:30:33', 0);

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
(88, '00000001', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000001. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', '2025-03-12 03:59:50'),
(89, '00000001', 'Jane Smith', 'System', 'est', '2025-03-13 01:33:46'),
(90, '00000001', 'Jane Smith', 'System', 'Test', '2025-03-13 01:33:50'),
(91, '00000002', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000002. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', '2025-03-13 02:19:05'),
(92, '00000003', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000003. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', '2025-03-13 02:24:17'),
(93, '00000004', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000004. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', '2025-03-13 02:24:42'),
(94, '00000004', 'Jane Smith', 'John Doe', 'eyy', '2025-03-13 02:27:53'),
(95, '00000005', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000005. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', '2025-03-13 02:59:48'),
(96, '00000006', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000006. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', '2025-03-27 07:36:36'),
(97, '00000007', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000007. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', '2025-03-27 07:39:25'),
(98, '00000008', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000008. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', '2025-03-27 07:39:32'),
(99, '00000008', 'System', 'Jane Smith', '\r\n                    \r\n                    \r\n                    \r\n                        \r\n                        \r\n                        Case #00000008 Created\r\n                        \r\n                            body {\r\n                                font-family: Arial, sans-serif;\r\n                                line-height: 1.6;\r\n                                color: #333;\r\n                                background-color: #f4f4f4;\r\n                                margin: 0;\r\n                                padding: 0;\r\n                            }\r\n                            .container {\r\n                                max-width: 600px;\r\n                                margin: 20px auto;\r\n                                background-color: #fff;\r\n                                padding: 20px;\r\n                                border-radius: 5px;\r\n                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);\r\n                            }\r\n                            h1 {\r\n                                color: #0056b3;\r\n                                border-bottom: 2px solid #0056b3;\r\n                                padding-bottom: 10px;\r\n                            }\r\n                            p {\r\n                                margin-bottom: 10px;\r\n                            }\r\n                            .highlight {\r\n                                font-weight: bold;\r\n                                color: #0056b3;\r\n                            }\r\n                            .note {\r\n                                font-size: 0.9em;\r\n                                color: #777;\r\n                                font-style: italic;\r\n                            }\r\n                        \r\n                    \r\n                    \r\n                        \r\n                            Case #00000008 Created\r\n                            Dear Jane Smith,\r\n                            Your issue has been successfully logged as case #00000008.\r\n                            \r\n                                Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\r\n                            \r\n                            Thank you.\r\n                            Please do not reply to this email. To update your case, please use the support portal.\r\n                        \r\n                    \r\n                    \r\n                    ', '2025-03-27 07:51:07'),
(100, '00000008', 'System', 'Jane Smith', '\r\n                    \r\n                    \r\n                    \r\n                        \r\n                        \r\n                        Case #00000008 Created\r\n                        \r\n                            body {\r\n                                font-family: Arial, sans-serif;\r\n                                line-height: 1.6;\r\n                                color: #333;\r\n                                background-color: #f4f4f4;\r\n                                margin: 0;\r\n                                padding: 0;\r\n                            }\r\n                            .container {\r\n                                max-width: 600px;\r\n                                margin: 20px auto;\r\n                                background-color: #fff;\r\n                                padding: 20px;\r\n                                border-radius: 5px;\r\n                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);\r\n                            }\r\n                            h1 {\r\n                                color: #0056b3;\r\n                                border-bottom: 2px solid #0056b3;\r\n                                padding-bottom: 10px;\r\n                            }\r\n                            p {\r\n                                margin-bottom: 10px;\r\n                            }\r\n                            .highlight {\r\n                                font-weight: bold;\r\n                                color: #0056b3;\r\n                            }\r\n                            .note {\r\n                                font-size: 0.9em;\r\n                                color: #777;\r\n                                font-style: italic;\r\n                            }\r\n                        \r\n                    \r\n                    \r\n                        \r\n                            Case #00000008 Created\r\n                            Dear Jane Smith,\r\n                            Your issue has been successfully logged as case #00000008.\r\n                            \r\n                                Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\r\n                            \r\n                            Thank you.\r\n                            Please do not reply to this email. To update your case, please use the support portal.\r\n                        \r\n                    \r\n                    \r\n                    ', '2025-03-27 07:54:21'),
(101, '00000009', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000009.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-27 08:04:53'),
(102, '00000001', 'John Doe', 'System', 'test', '2025-03-27 12:00:51'),
(103, '00000001', 'John Doe', 'System', 'test2', '2025-03-27 12:01:54'),
(104, '00000010', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000010.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-27 14:30:37');

-- --------------------------------------------------------

--
-- Table structure for table `engineer_ratings`
--

CREATE TABLE `engineer_ratings` (
  `id` int(11) NOT NULL,
  `engineer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `rated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `engineer_ratings`
--

INSERT INTO `engineer_ratings` (`id`, `engineer_id`, `rating`, `rated_at`) VALUES
(1, 1, 4, '2025-03-27 12:44:51');

-- --------------------------------------------------------

--
-- Table structure for table `forgot_password_requests`
--

CREATE TABLE `forgot_password_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_time` datetime DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `recipient_username` varchar(255) DEFAULT NULL,
  `recipient_email` varchar(255) DEFAULT NULL,
  `message_subject` varchar(255) DEFAULT NULL,
  `message_body` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `case_id`, `recipient_username`, `recipient_email`, `message_subject`, `message_body`, `sent_at`) VALUES
(2, 9, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000009 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000009.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-27 08:04:53'),
(3, 45, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Request Received - Case #00000009', 'Hello Jane Smith,\n\nWe have received your request for technical assistance (Case #00000009). One of our support engineers, John Doe, has accepted your case and will assist you during regular support hours.\n\nTo help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility.\n\nThank you,\nTechnical Support Team', '2025-03-27 08:05:34'),
(4, 44, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Request Received - Case #00000008', 'Hello Jane Smith,\n\nWe have received your request for technical assistance (Case #00000008). One of our support engineers, John Doe, has accepted your case and will assist you during regular support hours.\n\nTo help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility.\n\nThank you,\nTechnical Support Team', '2025-03-27 09:08:38'),
(5, 1, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000001 Transferred', 'Dear Jane Smith,\n\nThis is to inform you that Case #00000001 has been transferred from your ownership to John Doe.\n\nThe new engineer assigned to this case is John Doe.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-27 11:31:43'),
(7, 1, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Re-assigned: 00000001', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000001) regarding test has been re-assigned to a different support agent/team for further assistance.\n\nThe new agent handling your case is:\nAgent Name: John Doe\nContact: arexvolkzki@gmail.com\n\nPlease rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\nIf you have any further questions or concerns, feel free to reach out.\n\nThank you for your patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-27 11:36:33'),
(9, 2, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case: 00000002 Transferred.', 'Dear Jane Smith,\n\nWe would like to inform you that your case (00000002) regarding Jsksmslsksn has been transferred to a different support agent/team for further assistance.\n\nThe new agent handling your case is:\nAgent Name: Ranz Andrei Ornopia\nContact: ranz@gmail.com\n\nPlease rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\nIf you have any further questions or concerns, feel free to reach out.\n\nThank you for your patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-27 11:44:47'),
(10, 10, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000010 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000010.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-27 14:30:37');

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
  ADD KEY `fk_cases_user` (`user_id`),
  ADD KEY `fk_case_owner` (`case_owner`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `engineer_ratings`
--
ALTER TABLE `engineer_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `engineer_id` (`engineer_id`);

--
-- Indexes for table `forgot_password_requests`
--
ALTER TABLE `forgot_password_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `engineer_ratings`
--
ALTER TABLE `engineer_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `forgot_password_requests`
--
ALTER TABLE `forgot_password_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `fk_case_owner` FOREIGN KEY (`case_owner`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cases_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `engineer_ratings`
--
ALTER TABLE `engineer_ratings`
  ADD CONSTRAINT `engineer_ratings_ibfk_1` FOREIGN KEY (`engineer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `forgot_password_requests`
--
ALTER TABLE `forgot_password_requests`
  ADD CONSTRAINT `forgot_password_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `product_types`
--
ALTER TABLE `product_types`
  ADD CONSTRAINT `product_types_ibfk_1` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
