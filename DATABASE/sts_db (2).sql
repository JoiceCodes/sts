-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 09:47 AM
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
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `case_number` varchar(8) NOT NULL,
  `notification_subject` varchar(255) NOT NULL,
  `notification_body` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `case_number`, `notification_subject`, `notification_body`, `sent_at`) VALUES
(1, '00000018', 'New Case #00000018 Requires Attention', '\n                        <!DOCTYPE html>\n                        <html lang=\'en\'>\n                        <head>\n                            <meta charset=\'UTF-8\'>\n                            <title>Admin Alert: New Case #00000018</title>\n                            <style>\n                                body { font-family: sans-serif; line-height: 1.5; }\n                                .details { border-collapse: collapse; width: 100%; margin-bottom: 15px; }\n                                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }\n                                .details th { background-color: #f2f2f2; }\n                                strong { color: #003366; }\n                            </style>\n                        </head>\n                        <body>\n                            <h2>New Support Case Logged</h2>\n                            <p>A new support case has been created and requires review/assignment:</p>\n                            <table class=\'details\'>\n                                <tr><th>Case Number:</th><td>00000018</td></tr>\n                                <tr><th>Subject:</th><td>asdasdasdas</td></tr>\n                                <tr><th>Type:</th><td>system failure</td></tr>\n                                <tr><th>Severity:</th><td>Restricted Operations</td></tr>\n                                <tr><th>Product Group:</th><td>XYZ Corporation</td></tr>\n                                <tr><th>Product Name:</th><td>v12.5</td></tr>\n                                <tr><th>Product Version:</th><td>SecureDefender Antivirus Pro</td></tr>\n                                <tr><th>Company:</th><td>Antivirus Solutions</td></tr>\n                                <tr><th>Submitted By:</th><td>Jane Smith (barandonjoice07@gmail.com)</td></tr>\n                                <tr><th>Attachment:</th><td>None</td></tr>\n                            </table>\n                            <p>Please access the admin dashboard to manage this case.</p>\n                            <p><em>This notification was generated automatically and stored in the database.</em></p>\n                        </body>\n                        </html>', '2025-04-10 04:59:44'),
(2, '00000019', 'New Case #00000019 Requires Attention', 'New Support Case Logged\n\nA new support case requires review/assignment:\n\nCase Number: 00000019\nSubject: asdasdasd\nType: asddsad\nSeverity: Production System Down\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nCompany: Antivirus Solutions\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nAttachment: None\n\nPlease access the admin dashboard to manage this case.\n\nThis notification was generated automatically and stored in the database.', '2025-04-10 05:03:24'),
(3, '00000020', 'New Case #00000020 Requires Attention (Restricted Operations)', 'New Support Case Logged\n--------------------------------------\nCase Number:    00000020\nSubject:        adada\nType:           asdad\nSeverity:       Restricted Operations\nProduct Group:  XYZ Corporation\nProduct Name:   v12.5\nProduct Version:SecureDefender Antivirus Pro\nCompany:        Antivirus Solutions\nSubmitted By:   Jane Smith (barandonjoice07@gmail.com)\nAttachment:     None\n--------------------------------------\nPlease access the admin dashboard to manage this case.\n', '2025-04-10 05:42:21'),
(4, '00000021', 'New Case #00000021 Requires Attention (Production System Down)', 'New Support Case Logged\n--------------------------------------\nCase Number:    00000021\nSubject:        dadasdsa\nType:           adada\nSeverity:       Production System Down\nProduct Group:  XYZ Corporation\nProduct Name:   v12.5\nProduct Version:SecureDefender Antivirus Pro\nCompany:        Antivirus Solutions\nSubmitted By:   Jane Smith (barandonjoice07@gmail.com)\nAttachment:     None\n--------------------------------------\nPlease access the admin dashboard to manage this case.\n', '2025-04-10 05:42:38'),
(5, '00000022', 'New Case #00000022 Requires Attention', 'New Support Case Logged\n\nA new support case requires review/assignment:\n\nCase Number: 00000022\nSubject: asdsadsadas\nType: system failure\nSeverity: Question/Inconvenience\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nCompany: Antivirus Solutions\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nAttachment: None\n\nPlease access the admin dashboard to manage this case.\n\nThis notification was generated automatically and stored in the database.', '2025-04-10 05:43:30'),
(6, '00000024', 'New Case #00000024 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000024\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: addadada\nType: asdasd\nSeverity: Restricted Operations\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-10 05:50:03');

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
  `reopen` int(11) NOT NULL,
  `date_accepted` datetime DEFAULT NULL,
  `date_solved` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_number`, `type`, `subject`, `user_id`, `product_group`, `product`, `product_version`, `severity`, `case_status`, `attachment`, `case_owner`, `company`, `last_modified`, `datetime_opened`, `reopen`, `date_accepted`, `date_solved`) VALUES
(35, '00000001', 'test', 'test', 8, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-04-10 06:00:44', '2025-03-12 11:59:45', 0, NULL, NULL),
(36, '00000002', 'test', 'Jsksmslsksn', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-04-10 05:29:34', '2025-03-13 10:19:00', 1, NULL, NULL),
(37, '00000003', 'test3', 'Jejdidksns', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2024-11-13 02:25:50', '2025-03-13 10:24:13', 0, NULL, NULL),
(38, '00000004', 'test4', 'Wjdjwndms', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Question/Inconvenience', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-28 23:07:54', '2025-03-13 10:24:37', 6, NULL, NULL),
(39, '00000005', 'test5', 'test', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-27 07:30:11', '2025-03-13 10:59:44', 0, NULL, NULL),
(41, '00000007', 'test1', 'test1', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'Solved', '', 3, 'Antivirus Solutions', '2025-04-10 05:54:24', '2025-03-27 00:39:21', 6, NULL, NULL),
(44, '00000008', 'test3', 'test3', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Question/Inconvenience', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-28 09:24:09', '2025-03-27 00:54:17', 0, NULL, NULL),
(45, '00000009', 'test4', 'test4', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Question/Inconvenience', 'Solved', '', 3, 'Antivirus Solutions', '2025-03-28 23:08:28', '2025-03-27 01:04:50', 3, NULL, NULL),
(46, '00000010', 'ewaagt', 'asdvsgg', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-28 23:07:23', '2025-03-27 07:30:33', 0, NULL, NULL),
(47, '00000011', 'test2', 'malfunctioning', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-03-29 09:24:56', '2025-03-29 07:09:39', 0, NULL, NULL),
(48, '00000012', 'test2', 'test', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-04-10 06:01:54', '2025-04-10 14:01:54', 0, NULL, NULL),
(49, '00000013', 'test1.0', 'tester', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Question/Inconvenience', 'Waiting in Progress', '1743235518_AI- Powered Personalized Learning Assistant With Task Managfe.pdf', 3, 'Antivirus Solutions', '2025-04-10 05:57:25', '2025-03-29 16:05:18', 1, NULL, NULL),
(50, '00000014', 'system failure', 'Wjdjwndms', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-04-10 06:04:13', '2025-04-10 12:33:46', 0, NULL, NULL),
(51, '00000015', 'asdasd', 'asdasdsa', 8, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-04-10 06:06:42', '2025-04-10 12:47:46', 0, NULL, NULL),
(52, '00000016', 'sAD', 'ADada', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-04-10 07:04:17', '2025-04-10 12:53:19', 0, NULL, NULL),
(53, '00000017', 'sAD', 'ADada', NULL, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'New', '', 3, 'Antivirus Solutions', '2025-04-10 04:53:34', '2025-04-10 12:53:34', 0, NULL, NULL),
(54, '00000018', 'system failure', 'asdasdasdas', NULL, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'New', '', 3, 'Antivirus Solutions', '2025-04-10 04:59:39', '2025-04-10 12:59:39', 0, NULL, NULL),
(55, '00000019', 'asddsad', 'asdasdasd', NULL, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'New', '', 3, 'Antivirus Solutions', '2025-04-10 05:03:21', '2025-04-10 13:03:21', 0, NULL, NULL),
(56, '00000020', 'asdad', 'adada', NULL, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'New', '', 3, 'Antivirus Solutions', '2025-04-10 05:42:21', '2025-04-10 13:42:21', 0, NULL, NULL),
(57, '00000021', 'adada', 'dadasdsa', NULL, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Production System Down', 'New', '', 3, 'Antivirus Solutions', '2025-04-10 05:42:38', '2025-04-10 13:42:38', 0, NULL, NULL),
(58, '00000022', 'system failure', 'asdsadsadas', NULL, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Question/Inconvenience', 'New', '', 3, 'Antivirus Solutions', '2025-04-10 05:43:15', '2025-04-10 13:43:15', 0, NULL, NULL),
(59, '00000023', 'asdasd', 'addadada', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'Solved', '', 3, 'Antivirus Solutions', '2025-04-10 06:29:12', '2025-04-10 13:49:28', 0, '2025-04-10 14:26:15', '2025-04-10 14:29:12'),
(60, '00000024', 'asdasd', 'addadada', 1, 'XYZ Corporation', 'v12.5', 'SecureDefender Antivirus Pro', 'Restricted Operations', 'Waiting in Progress', '', 3, 'Antivirus Solutions', '2025-04-10 06:23:46', '2025-04-10 13:49:59', 0, NULL, NULL);

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
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `case_number`, `sender`, `receiver`, `message`, `attachment_path`, `created_at`, `is_read`) VALUES
(88, '00000001', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000001. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', NULL, '2025-03-12 03:59:50', 1),
(89, '00000001', 'Jane Smith', 'System', 'est', NULL, '2025-03-13 01:33:46', 0),
(90, '00000001', 'Jane Smith', 'System', 'Test', NULL, '2025-03-13 01:33:50', 0),
(91, '00000002', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000002. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', NULL, '2025-03-13 02:19:05', 1),
(92, '00000003', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000003. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', NULL, '2025-03-13 02:24:17', 1),
(93, '00000004', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000004. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', NULL, '2025-03-13 02:24:42', 1),
(94, '00000004', 'Jane Smith', 'John Doe', 'eyy', NULL, '2025-03-13 02:27:53', 1),
(95, '00000005', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000005. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', NULL, '2025-03-13 02:59:48', 1),
(96, '00000006', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000006. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', NULL, '2025-03-27 07:36:36', 1),
(97, '00000007', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000007. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', NULL, '2025-03-27 07:39:25', 1),
(98, '00000008', 'System', 'Jane Smith', 'Dear Jane Smith, The issue reported has been successfully logged as case #00000008. Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone. Thank you. Please do not reply to this email. To update your case, click on the direct link to the case.', NULL, '2025-03-27 07:39:32', 1),
(99, '00000008', 'System', 'Jane Smith', '\r\n                    \r\n                    \r\n                    \r\n                        \r\n                        \r\n                        Case #00000008 Created\r\n                        \r\n                            body {\r\n                                font-family: Arial, sans-serif;\r\n                                line-height: 1.6;\r\n                                color: #333;\r\n                                background-color: #f4f4f4;\r\n                                margin: 0;\r\n                                padding: 0;\r\n                            }\r\n                            .container {\r\n                                max-width: 600px;\r\n                                margin: 20px auto;\r\n                                background-color: #fff;\r\n                                padding: 20px;\r\n                                border-radius: 5px;\r\n                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);\r\n                            }\r\n                            h1 {\r\n                                color: #0056b3;\r\n                                border-bottom: 2px solid #0056b3;\r\n                                padding-bottom: 10px;\r\n                            }\r\n                            p {\r\n                                margin-bottom: 10px;\r\n                            }\r\n                            .highlight {\r\n                                font-weight: bold;\r\n                                color: #0056b3;\r\n                            }\r\n                            .note {\r\n                                font-size: 0.9em;\r\n                                color: #777;\r\n                                font-style: italic;\r\n                            }\r\n                        \r\n                    \r\n                    \r\n                        \r\n                            Case #00000008 Created\r\n                            Dear Jane Smith,\r\n                            Your issue has been successfully logged as case #00000008.\r\n                            \r\n                                Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\r\n                            \r\n                            Thank you.\r\n                            Please do not reply to this email. To update your case, please use the support portal.\r\n                        \r\n                    \r\n                    \r\n                    ', NULL, '2025-03-27 07:51:07', 1),
(100, '00000008', 'System', 'Jane Smith', '\r\n                    \r\n                    \r\n                    \r\n                        \r\n                        \r\n                        Case #00000008 Created\r\n                        \r\n                            body {\r\n                                font-family: Arial, sans-serif;\r\n                                line-height: 1.6;\r\n                                color: #333;\r\n                                background-color: #f4f4f4;\r\n                                margin: 0;\r\n                                padding: 0;\r\n                            }\r\n                            .container {\r\n                                max-width: 600px;\r\n                                margin: 20px auto;\r\n                                background-color: #fff;\r\n                                padding: 20px;\r\n                                border-radius: 5px;\r\n                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);\r\n                            }\r\n                            h1 {\r\n                                color: #0056b3;\r\n                                border-bottom: 2px solid #0056b3;\r\n                                padding-bottom: 10px;\r\n                            }\r\n                            p {\r\n                                margin-bottom: 10px;\r\n                            }\r\n                            .highlight {\r\n                                font-weight: bold;\r\n                                color: #0056b3;\r\n                            }\r\n                            .note {\r\n                                font-size: 0.9em;\r\n                                color: #777;\r\n                                font-style: italic;\r\n                            }\r\n                        \r\n                    \r\n                    \r\n                        \r\n                            Case #00000008 Created\r\n                            Dear Jane Smith,\r\n                            Your issue has been successfully logged as case #00000008.\r\n                            \r\n                                Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\r\n                            \r\n                            Thank you.\r\n                            Please do not reply to this email. To update your case, please use the support portal.\r\n                        \r\n                    \r\n                    \r\n                    ', NULL, '2025-03-27 07:54:21', 1),
(101, '00000009', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000009.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', NULL, '2025-03-27 08:04:53', 1),
(102, '00000001', 'John Doe', 'System', 'test', NULL, '2025-03-27 12:00:51', 0),
(103, '00000001', 'John Doe', 'System', 'test2', NULL, '2025-03-27 12:01:54', 0),
(104, '00000010', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000010.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', NULL, '2025-03-27 14:30:37', 1),
(105, '00000011', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000011.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', NULL, '2025-03-28 08:20:17', 1),
(106, '00000012', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000012.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', NULL, '2025-03-28 23:05:08', 1),
(107, '00000013', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000013.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', NULL, '2025-03-29 08:05:23', 1),
(108, '00000013', 'John Doe', 'System', 'hi teest', NULL, '2025-03-29 08:46:23', 0),
(109, '00000013', 'Jane Smith', 'System', 'test', NULL, '2025-04-07 04:49:24', 0),
(110, '00000013', 'Jane Smith', 'System', 'test', NULL, '2025-04-07 04:49:43', 0),
(111, '00000013', 'Jane Smith', 'System', 'dsfsdfds', '00000013_67f3598acf7fc8.20327931.docx', '2025-04-07 04:50:18', 0),
(112, '00000012', 'Jane Smith', 'John Doe', 'test', NULL, '2025-04-07 04:50:53', 0),
(113, '00000010', 'Jane Smith', 'System', 'test', NULL, '2025-04-07 04:54:08', 0),
(114, '00000010', 'Jane Smith', 'System', 'test2', '00000010_67f35ac0546be7.90472067.pdf', '2025-04-07 04:55:28', 0),
(115, '00000011', 'Jane Smith', 'System', 'here\'s our chapter 1 - 5', '00000011_67f35cbcd897a0.68671604.docx', '2025-04-07 05:03:56', 0),
(116, '00000003', 'Jane Smith', 'System', 'thank you', NULL, '2025-04-07 05:07:24', 0),
(117, '00000013', 'John Doe', 'System', 'okay', NULL, '2025-04-07 05:09:20', 0),
(118, '00000004', 'John Doe', 'System', 'hello', NULL, '2025-04-07 05:11:09', 0),
(119, '00000003', 'Jane Smith', 'System', 'magreply ka', NULL, '2025-04-07 05:15:05', 0),
(120, '00000001', 'Jane Smith', 'System', 'i love you engineer', NULL, '2025-04-07 05:30:11', 0),
(121, '00000001', 'John Doe', 'Jane Smith', 'i love you too...', NULL, '2025-04-07 05:49:51', 1),
(122, '00000001', 'Jane Smith', 'System', 'namimiss na kita', NULL, '2025-04-07 05:54:01', 0),
(123, '00000001', 'Jane Smith', 'System', 'wag tayo dito, sa ig tayoo', NULL, '2025-04-07 05:54:10', 0),
(124, '00000001', 'John Doe', '3', 'namimiss na rin kita', NULL, '2025-04-07 05:54:24', 0),
(125, '00000001', 'John Doe', '3', 'sige sa ig tayo\\', NULL, '2025-04-07 05:54:32', 0),
(126, '00000001', 'John Doe', '3', 'test', NULL, '2025-04-10 04:18:12', 0),
(127, '00000014', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000014.\n\nSubject: Wjdjwndms\nSeverity: Production System Down\nProduct: v12.5\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', NULL, '2025-04-10 04:33:51', 1),
(128, '00000017', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000017.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', NULL, '2025-04-10 04:53:39', 0),
(129, '00000018', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000018.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', NULL, '2025-04-10 04:59:44', 1),
(130, '00000019', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000019.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', NULL, '2025-04-10 05:03:24', 0),
(131, '00000020', 'System', 'Jane Smith', 'Case #00000020 created successfully. Subject: adada', NULL, '2025-04-10 05:42:21', 0),
(132, '00000021', 'System', 'Jane Smith', 'Case #00000021 created successfully. Subject: dadasdsa', NULL, '2025-04-10 05:42:38', 0),
(133, '00000022', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000022.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', NULL, '2025-04-10 05:43:30', 0),
(134, '00000023', 'System', 'Jane Smith', 'Case #00000023 created successfully by Jane Smith.', NULL, '2025-04-10 05:49:33', 1),
(135, '00000024', 'System', 'Jane Smith', 'Case #00000024 created successfully by Jane Smith.', NULL, '2025-04-10 05:50:03', 1),
(136, '00000023', 'Jane Smith', 'System', 'ok', NULL, '2025-04-10 07:06:59', 0);

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
(1, 1, 4, '2025-03-27 12:44:51'),
(3, 1, 3, '2025-03-28 07:31:42'),
(4, 1, 4, '2025-03-28 07:34:54'),
(5, 1, 5, '2025-03-28 23:09:02'),
(6, 1, 5, '2025-03-29 08:51:09');

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
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `case_id`, `recipient_username`, `recipient_email`, `message_subject`, `message_body`, `sent_at`, `is_read`) VALUES
(2, 9, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000009 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000009.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-27 08:04:53', 1),
(3, 45, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Request Received - Case #00000009', 'Hello Jane Smith,\n\nWe have received your request for technical assistance (Case #00000009). One of our support engineers, John Doe, has accepted your case and will assist you during regular support hours.\n\nTo help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility.\n\nThank you,\nTechnical Support Team', '2025-03-27 08:05:34', 1),
(4, 44, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Request Received - Case #00000008', 'Hello Jane Smith,\n\nWe have received your request for technical assistance (Case #00000008). One of our support engineers, John Doe, has accepted your case and will assist you during regular support hours.\n\nTo help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility.\n\nThank you,\nTechnical Support Team', '2025-03-27 09:08:38', 1),
(5, 1, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000001 Transferred', 'Dear Jane Smith,\n\nThis is to inform you that Case #00000001 has been transferred from your ownership to John Doe.\n\nThe new engineer assigned to this case is John Doe.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-27 11:31:43', 1),
(7, 1, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Re-assigned: 00000001', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000001) regarding test has been re-assigned to a different support agent/team for further assistance.\n\nThe new agent handling your case is:\nAgent Name: John Doe\nContact: arexvolkzki@gmail.com\n\nPlease rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\nIf you have any further questions or concerns, feel free to reach out.\n\nThank you for your patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-27 11:36:33', 1),
(9, 2, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case: 00000002 Transferred.', 'Dear Jane Smith,\n\nWe would like to inform you that your case (00000002) regarding Jsksmslsksn has been transferred to a different support agent/team for further assistance.\n\nThe new agent handling your case is:\nAgent Name: Ranz Andrei Ornopia\nContact: ranz@gmail.com\n\nPlease rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\nIf you have any further questions or concerns, feel free to reach out.\n\nThank you for your patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-27 11:44:47', 1),
(10, 10, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000010 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000010.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-27 14:30:37', 1),
(11, 1, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case: 00000001 Transferred.', 'Dear Jane Smith,\n\nWe would like to inform you that your your case (00000001) regarding test has been transferred to a different support agent/team for further assistance.\n\nThe new agent handling your case is:\nAgent Name: Ranz Andrei Ornopia\nContact: ranz@gmail.com\n\nPlease rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\nIf you have any further questions or concerns, feel free to reach out.\n\nThank you for your patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-27 23:27:26', 1),
(13, 9, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000009', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000009) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 07:14:59', 1),
(14, 4, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000004', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000004) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 07:22:14', 1),
(15, 4, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000004', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000004) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 07:25:44', 1),
(16, 4, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000004', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000004) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 07:28:00', 1),
(17, 4, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000004', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000004) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 07:31:15', 1),
(18, 7, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000007', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000007) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 07:34:00', 1),
(19, 41, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Case #00000007 Resolved', 'Hello Jane Smith,\n\nWe are pleased to inform you that your technical assistance case #00000007 has been successfully resolved by John Doe. If you encounter any further issues, feel free to reach out.\n\nWe value your feedback! Please take a moment to rate your experience with John Doe regarding this case by clicking the link below:\nhttp://localhost/sts/rate_engineer.php?id=MQ==&amp;case=MDAwMDAwMDc=\n\nThank you for reaching out to our support team.\n\nBest regards,\nTechnical Support Team', '2025-03-28 07:34:15', 1),
(20, 11, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000011 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000011.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-28 08:20:17', 1),
(21, 9, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000009', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000009) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 09:20:44', 1),
(22, 9, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case: 00000009 Transferred.', 'Dear Jane Smith,\n\nWe would like to inform you that your your case (00000009) regarding test4 has been transferred to a different support agent/team for further assistance.\n\nThe new agent handling your case is:\nAgent Name: John Doe\nContact: arexvolkzki@gmail.com\n\nPlease rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\nIf you have any further questions or concerns, feel free to reach out.\n\nThank you for your patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 09:25:37', 1),
(23, 12, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000012 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000012.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-28 23:05:08', 1),
(24, 46, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Request Received - Case #00000010', 'Hello Jane Smith,\n\nWe have received your request for technical assistance (Case #00000010). One of our support engineers, John Doe, has accepted your case and will assist you during regular support hours.\n\nTo help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility.\n\nThank you,\nTechnical Support Team', '2025-03-28 23:06:31', 1),
(25, 4, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000004', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000004) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 23:07:54', 1),
(26, 45, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Case #00000009 Resolved', 'Hello Jane Smith,\n\nWe are pleased to inform you that your technical assistance case #00000009 has been successfully resolved by John Doe. If you encounter any further issues, feel free to reach out.\n\nWe value your feedback! Please take a moment to rate your experience with John Doe regarding this case by clicking the link below:\nhttp://localhost/sts/rate_engineer.php?id=MQ==&amp;case=MDAwMDAwMDk=\n\nThank you for reaching out to our support team.\n\nBest regards,\nTechnical Support Team', '2025-03-28 23:08:28', 1),
(27, 11, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case: 00000011 Transferred.', 'Dear Jane Smith,\n\nWe would like to inform you that your your case (00000011) regarding malfunctioning has been transferred to a different support agent/team for further assistance.\n\nThe new agent handling your case is:\nAgent Name: Ranz Andrei Ornopia\nContact: ranz@gmail.com\n\nPlease rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\nIf you have any further questions or concerns, feel free to reach out.\n\nThank you for your patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 23:10:36', 1),
(28, 7, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000007', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000007) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-28 23:46:20', 1),
(29, 13, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000013 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000013.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-03-29 08:05:23', 1),
(30, 2, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000002', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000002) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-29 08:07:12', 1),
(31, 49, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Request Received - Case #00000013', 'Hello Jane Smith,\n\nWe have received your request for technical assistance (Case #00000013). One of our support engineers, John Doe, has accepted your case and will assist you during regular support hours.\n\nTo help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility.\n\nThank you,\nTechnical Support Team', '2025-03-29 08:40:23', 1),
(32, 49, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Case #00000013 Resolved', 'Hello Jane Smith,\n\nWe are pleased to inform you that your technical assistance case #00000013 has been successfully resolved by John Doe. If you encounter any further issues, feel free to reach out.\n\nWe value your feedback! Please take a moment to rate your experience with John Doe regarding this case by clicking the link below:\nhttp://localhost/sts/rate_engineer.php?id=MQ==&amp;case=MDAwMDAwMTM=\n\nThank you for reaching out to our support team.\n\nBest regards,\nTechnical Support Team', '2025-03-29 08:48:50', 1),
(33, 11, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case: 00000011 Transferred.', 'Dear Jane Smith,\n\nWe would like to inform you that your your case (00000011) regarding malfunctioning has been transferred to a different support agent/team for further assistance.\n\nThe new agent handling your case is:\nAgent Name: John Doe\nContact: arexvolkzki@gmail.com\n\nPlease rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\nIf you have any further questions or concerns, feel free to reach out.\n\nThank you for your patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-03-29 09:25:01', 1),
(34, 14, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000014 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000014.\n\nSubject: Wjdjwndms\nSeverity: Production System Down\nProduct: v12.5\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-10 04:33:51', 1),
(35, 17, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000017 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000017.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\n', '2025-04-10 04:53:39', 1),
(36, 18, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000018 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000018.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-10 04:59:44', 1),
(37, 19, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000019 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000019.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-10 05:03:24', 1),
(38, 2, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case: 00000002 Transferred.', 'Dear Jane Smith,\n\nWe would like to inform you that your your case (00000002) regarding Jsksmslsksn has been transferred to a different support agent/team for further assistance.\n\nThe new agent handling your case is:\nAgent Name: John Doe\nContact: arexvolkzki@gmail.com\n\nPlease rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\nIf you have any further questions or concerns, feel free to reach out.\n\nThank you for your patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-04-10 05:29:38', 1),
(39, 20, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000020 Created', 'Dear Jane Smith,\n\nYour support case has been successfully logged with Case #00000020.\n\nSubject: adada\nSeverity: Restricted Operations\n\nOur team will review your case and get back to you.\n\nPlease Note: For immediate assistance on high severity issues outside business hours, please contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To view or update your case, please use the support portal.', '2025-04-10 05:42:21', 1),
(40, 21, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000021 Created', 'Dear Jane Smith,\n\nYour support case has been successfully logged with Case #00000021.\n\nSubject: dadasdsa\nSeverity: Production System Down\n\nOur team will review your case and get back to you.\n\nPlease Note: For immediate assistance on high severity issues outside business hours, please contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To view or update your case, please use the support portal.', '2025-04-10 05:42:38', 1),
(41, 22, 'Jane Smith', 'barandonjoice07@gmail.com', 'New Case #00000022 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000022.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-10 05:43:30', 1),
(42, 23, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000023 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000023.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-10 05:49:33', 1),
(43, 24, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000024 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000024.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-10 05:50:03', 1),
(44, 41, 'Jane Smith', 'barandonjoice07@gmail.com', 'Support Case #00000007 Has Been Resolved', 'Hello Jane Smith,\n\nWe\'re pleased to inform you that your support case #00000007 has been marked as resolved by our engineer, John Doe.\n\nIf you believe the issue is not fully resolved, please reply to this message or contact us through the support portal within 7 days to reopen the case.\n\nWe value your feedback! Please take a moment to rate your support experience with John Doe by visiting the link below:\nhttp://localhost/sts/rate_engineer.php?id=MQ==&amp;case=MDAwMDAwMDc=\n\nThank you for choosing our services.\n\nBest regards,\nTechnical Support Team\nThis is an automated message. Please use the support portal for inquiries.', '2025-04-10 05:54:24', 1),
(45, 13, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000013', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000013) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-04-10 05:57:25', 1),
(46, 60, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Request Accepted - Case #00000024', 'Hello Jane Smith,\n\nWe have received your request for technical assistance (Case #00000024). One of our support engineers, John Doe, has accepted your case and will assist you during regular support hours.\n\nTo help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility via the support portal.\n\nThank you,\nTechnical Support Team', '2025-04-10 06:23:46', 1),
(47, 59, 'Jane Smith', 'barandonjoice07@gmail.com', 'Technical Assistance Request Accepted - Case #00000023', 'Hello Jane Smith,\n\nWe have received your request for technical assistance (Case #00000023). One of our support engineers, John Doe, has accepted your case and will assist you during regular support hours.\n\nTo help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility via the support portal.\n\nThank you,\nTechnical Support Team', '2025-04-10 06:26:15', 1),
(48, 59, 'Jane Smith', 'barandonjoice07@gmail.com', 'Support Case #00000023 Has Been Resolved', 'Hello Jane Smith,\n\nWe\'re pleased to inform you that your support case #00000023 has been marked as resolved by our engineer, John Doe.\n\nIf you believe the issue is not fully resolved, please reply to this message or contact us through the support portal within 7 days to reopen the case.\n\nWe value your feedback! Please take a moment to rate your support experience with John Doe by visiting the link below:\nhttp://localhost/sts/rate_engineer.php?id=MQ==&amp;case=MDAwMDAwMjM=\n\nThank you for choosing our services.\n\nBest regards,\nTechnical Support Team\nThis is an automated message. Please use the support portal for inquiries.', '2025-04-10 06:29:12', 1);

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
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('smtp_from_email', 'isecurenetworkgroup@gmail.com'),
('smtp_from_name', 'Support Team'),
('smtp_host', 'smtp.gmail.com'),
('smtp_password', 'rszs tbmh hina yqss'),
('smtp_port', '587'),
('smtp_secure', 'TLS'),
('smtp_username', 'isecurenetworkgroup@gmail.com');

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
  `role` enum('User','Engineer','Technical Head','Administrator') NOT NULL DEFAULT 'User',
  `account_status` enum('Active','Deactivated','','') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `role`, `account_status`, `created_at`) VALUES
(1, 'John Doe', 'arexvolkzki@gmail.com', 'johndoe', '123', 'Engineer', 'Active', '2025-02-18 03:57:37'),
(3, 'Jane Smith', 'barandonjoice07@gmail.com', 'janesmith', '123', 'User', 'Active', '2025-02-20 03:59:40'),
(4, 'Pedro Santos', 'johndoe@example.com', 'pedrosantos', '123', 'Technical Head', 'Active', '2025-02-20 04:18:49'),
(5, 'Shan Cai Loyola', 'jcbrndn31@gmail.com', 'Shan', '123', 'User', 'Active', '2025-02-20 07:25:51'),
(7, 'Juan Cruz', 'juancruz@example.com', 'juancruz', '123', 'Administrator', 'Active', '2025-02-20 07:29:09'),
(8, 'Ranz Andrei Ornopia', 'ranz@gmail.com', 'Ranz', '123', 'Engineer', 'Active', '2025-02-20 07:55:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_case_number` (`case_number`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receiver_read` (`receiver`,`is_read`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipient_read` (`recipient_username`,`is_read`);

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
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

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
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT for table `engineer_ratings`
--
ALTER TABLE `engineer_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `forgot_password_requests`
--
ALTER TABLE `forgot_password_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

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
