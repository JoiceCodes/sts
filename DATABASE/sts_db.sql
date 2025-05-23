-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2025 at 01:32 PM
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
(6, '00000024', 'New Case #00000024 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000024\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: addadada\nType: asdasd\nSeverity: Restricted Operations\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-10 05:50:03'),
(7, '00000025', 'New Case #00000025 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000025\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: fsasfaasf\nType: fassa\nSeverity: Production System Down\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-11 03:41:02'),
(8, '00000026', 'New Case #00000026 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000026\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: dasdasd\nType: asdas\nSeverity: Restricted Operations\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-11 03:58:00'),
(9, '00000027', 'New Case #00000027 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000027\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: dsffdsdsf\nType: grdsgdsg\nSeverity: Production System Down\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-11 04:56:04'),
(10, '00000028', 'New Case #00000028 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000028\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: fsdfsdfsd\nType: dfsfdsfds\nSeverity: Restricted Operations\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-11 04:58:18'),
(11, '00000029', 'New Case #00000029 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000029\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: sss\nType: sss\nSeverity: Production System Down\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-11 05:03:09'),
(12, '00000030', 'New Case #00000030 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000030\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: czxczxczxc\nType: zxczxczx\nSeverity: Production System Down\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-11 05:07:26'),
(13, '00000031', 'New Case #00000031 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000031\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: dsfsdfsd\nType: dsfsdf\nSeverity: Production System Down\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-11 05:10:06'),
(14, '00000032', 'New Case #00000032 Requires Attention from Antivirus Solutions', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000032\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Antivirus Solutions\n\n--- Case Details ---\nSubject: ewrewrw\nType: rserse\nSeverity: Production System Down\nProduct Group: XYZ Corporation\nProduct Name: v12.5\nProduct Version: SecureDefender Antivirus Pro\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-11 05:11:08'),
(15, '00000033', 'New Case #00000033 Requires Attention from ', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000033\nSubmitted By: Lance Casas (lancecasas0801@gmail.com)\nCompany: \n\n--- Case Details ---\nSubject: ayaw gumana\nType: Firewall\nSeverity: Production System Down\nProduct Group: \nProduct Name: \nProduct Version: \nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-25 08:35:02'),
(16, '00000034', 'New Case #00000034 Requires Attention from test', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000034\nSubmitted By: Lance Casas (lancecasas0801@gmail.com)\nCompany: test\n\n--- Case Details ---\nSubject: \nType: \nSeverity: \nProduct Group: Software\nProduct Name: \nProduct Version: v2.0\nAttachment: 1745570587_680b4b1b044564.20877145.jpg\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-25 08:43:12'),
(17, '00000035', 'New Case #00000035 Requires Attention from test', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000035\nSubmitted By: Lance Casas (lancecasas0801@gmail.com)\nCompany: test\n\n--- Case Details ---\nSubject: test\nType: Firewall\nSeverity: Production System Down\nProduct Group: Software\nProduct Name: \nProduct Version: v2.0\nAttachment: 1745570649_680b4b595c60d3.72300149.jpg\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-25 08:44:14'),
(18, '00000036', 'New Case #00000036 Requires Attention from ', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000036\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: \n\n--- Case Details ---\nSubject: \nType: \nSeverity: \nProduct Group: \nProduct Name: \nProduct Version: \nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-25 22:48:17'),
(19, '00000033', 'New Case #00000033 Requires Attention from ', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000033\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: \n\n--- Case Details ---\nSubject: \nType: \nSeverity: \nProduct Group: \nProduct Name: \nProduct Version: \nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-26 00:39:23'),
(20, '00000034', 'New Case #00000034 Requires Attention from ', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000034\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: \n\n--- Case Details ---\nSubject: \nType: \nSeverity: \nProduct Group: \nProduct Name: \nProduct Version: \nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-26 00:40:37'),
(21, '00000035', 'New Case #00000035 Requires Attention from ', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000035\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: \n\n--- Case Details ---\nSubject: \nType: \nSeverity: \nProduct Group: \nProduct Name: \nProduct Version: \nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-26 00:43:17'),
(22, '00000036', 'New Case #00000036 Requires Attention from XYZ Corporation', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000036\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: XYZ Corporation\n\n--- Case Details ---\nSubject: test\nType: test2\nSeverity: Production System Down\nProduct Group: Software\nProduct Name: \nProduct Version: v2.0\nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-26 03:57:00'),
(23, '00000037', 'New Case #00000037 Requires Attention from XYZ Corporation', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000037\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: XYZ Corporation\n\n--- Case Details ---\nSubject: antivirus malfunction\nType: Antivirus\nSeverity: Production System Down\nProduct Group: \nProduct Name: \nProduct Version: \nAttachment: 1745640393_680c5bc978d7c3.22961980.pdf\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-26 04:06:38'),
(24, '00000038', 'New Case #00000038 Requires Attention from Progress Software', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000038\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\n\n--- Case Details ---\nSubject: \nType: \nSeverity: 1 - Production System Down\nProduct Group: Software\nProduct Name: MOVEit Cloud\nProduct Version: v12.521\nAttachment: 1745899203_68104ec3dc8370.69823053.png\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-29 04:00:08'),
(25, '00000039', 'New Case #00000039 Requires Attention from Progress Software', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000039\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\n\n--- Case Details ---\nSubject: \nType: \nSeverity: 1 - Production System Down\nProduct Group: Software\nProduct Name: MOVEit Cloud\nProduct Version: v12.521\nAttachment: 1745899208_68104ec8a638f8.13734989.png\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-29 04:00:12'),
(26, '00000040', 'New Case #00000040 Requires Attention from Progress Software', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000040\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\n\n--- Case Details ---\nSubject: \nType: \nSeverity: 2 - Restricted Operations\nProduct Group: Software\nProduct Name: WhatsUp Gold\nProduct Version: v12.521\nAttachment: 1745899268_68104f049e9f15.48266682.png\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-29 04:01:12'),
(27, '00000041', 'New Case #00000041 Requires Attention from Sophos', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000041\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Sophos\n\n--- Case Details ---\nSubject: \nType: \nSeverity: 3 - Question/Inconvenience\nProduct Group: \nProduct Name: Sophos Firewall\nProduct Version: \nAttachment: None Uploaded\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-29 04:04:13'),
(28, '00000042', 'New Case #00000042 Requires Attention from Progress Software', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000042\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\n\n--- Case Details ---\nSubject: \nType: \nSeverity: 1 - Production System Down\nProduct Group: Software\nProduct Name: MOVEit Cloud\nProduct Version: v12.521\nAttachment: 1745900578_68105422ed72a8.22108186.png\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-29 04:23:03'),
(29, '00000043', 'New Case #00000043 Requires Attention from Progress Software', 'New Support Case Logged\n===================================\nA new support case requires review/assignment:\n\nCase Number: 00000043\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\n\n--- Case Details ---\nSubject: \nType: \nSeverity: 3 - Question/Inconvenience\nProduct Group: Software\nProduct Name: MOVEit Cloud\nProduct Version: v12.521\nAttachment: 1745900856_68105538d980b4.51122210.png\n\nPlease access the admin dashboard or support portal to view the full case details and manage assignment.\n===================================', '2025-04-29 04:27:40'),
(30, 'CASE-202', 'New Case #CASE-20250429-123834-3 (1 - Production System Down) from Progress Software', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-123834-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\n\n--- Case Details ---\nSubject: Configuration Problem\nType/Product: WS_FTP\nSeverity: 1 - Production System Down\nProduct Group: Antivirus Solutions\nProduct Version: v12.5\nAttachment: CASE-20250429-123834-3_681057ca707cb_unnamed.png\n\nPlease access the admin dashboard to manage assignment.\n===================================', '2025-04-29 04:38:38'),
(31, 'CASE-202', 'New Case #CASE-20250429-124811-3 (3 - Question/Inconvenience) from Sophos', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-124811-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Sophos\n\n--- Case Details ---\nSubject: Bug Report\nType/Product: Sophos Antivirus\nSeverity: 3 - Question/Inconvenience\nProduct Group: Software\nProduct Version: v12.521\nAttachment: CASE-20250429-124811-3_68105a0b66382_unnamed.png\n\nPlease access the admin dashboard to manage assignment.\n===================================', '2025-04-29 04:48:15'),
(32, 'CASE-202', 'New Case #CASE-20250429-125122-3 (3 - Question/Inconvenience) from Progress Software', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-125122-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\n\n--- Case Details ---\nSubject: Usage Question\nType/Product: WS_FTP\nSeverity: 3 - Question/Inconvenience\nProduct Group: Software\nProduct Version: v12.521\nAttachment: CASE-20250429-125122-3_68105aca9f723_unnamed.png\n\nPlease access the admin dashboard to manage assignment.\n===================================', '2025-04-29 04:51:26'),
(33, 'CASE-202', 'New Case #CASE-20250429-130710-3 (2 - Restricted Operations) from Progress Software', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-130710-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\nSerial Number: 111\n\n--- Case Details ---\nSubject: Usage Question\nProduct: MOVEit Cloud\nSeverity: 2 - Restricted Operations\nProduct Group: Software\nProduct Version: v12.521\nAttachment: CASE-20250429-130710-3_68105e7e645c9_unnamed.png\n\nPlease access the admin dashboard to view and assign this case.\n===================================', '2025-04-29 05:07:14'),
(34, 'CASE-202', 'New Case #CASE-20250429-131401-3 (Question/Inconvenience) from Progress Software', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-131401-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\nSerial Number: 111\n\n--- Case Details ---\nSubject: Bug Report\nProduct: WS_FTP\nSeverity: Question/Inconvenience\nProduct Group: Software\nProduct Version: v12.521\nAttachment: CASE-20250429-131401-3_681060197d1ab_unnamed.png\n\nPlease access the admin dashboard to view and assign this case.\n===================================', '2025-04-29 05:14:05'),
(35, 'CASE-202', 'New Case #CASE-20250429-133837-3 (Restricted Operations) from Progress Software', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-133837-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\nSerial Number: 111\n\n--- Case Details ---\nSubject: Usage Question\nProduct: WhatsUp Gold\nSeverity: Restricted Operations\nProduct Group: Software\nProduct Version: v12.521\nAttachment: CASE-20250429-133837-3_681065ddaf93c_unnamed.png\n\nPlease access the admin dashboard to view and assign this case.\n===================================', '2025-04-29 05:38:41'),
(36, 'CASE-202', 'New Case #CASE-20250429-134244-3 (Restricted Operations) from Progress Software', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-134244-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\nSerial Number: 123\n\n--- Case Details ---\nSubject: Usage Question\nProduct: MOVEit\nSeverity: Restricted Operations\nProduct Group: Antivirus Solutions\nProduct Version: v12.5\nAttachment: CASE-20250429-134244-3_681066d41f9d3_unnamed.png\n\nPlease access the admin dashboard to view and assign this case.\n===================================', '2025-04-29 05:42:48'),
(37, 'CASE-202', 'New Case #CASE-20250429-160108-3 (Restricted Operations) from Sophos', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-160108-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Sophos\nSerial Number: 123\n\n--- Case Details ---\nSubject: Bug Report\nProduct: Sophos Antivirus\nSeverity: Restricted Operations\nProduct Group: Antivirus Solutions\nProduct Version: v12.5\nAttachment: CASE-20250429-160108-3_6810874475a43_unnamed.png\n\nPlease access the admin dashboard to view and assign this case.\n===================================', '2025-04-29 08:01:12'),
(38, 'CASE-202', 'New Case #CASE-20250429-170102-3 (Production System Down) from Progress Software', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-170102-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\nSerial Number: 123\n\n--- Case Details ---\nSubject: Installation Issue\nProduct: WS_FTP\nSeverity: Production System Down\nProduct Group: Antivirus Solutions\nProduct Version: v12.5\nAttachment: CASE-20250429-170102-3_6810954e52bc6_unnamed.png\n\nPlease access the admin dashboard to view and assign this case.\n===================================', '2025-04-29 09:01:06'),
(39, 'CASE-202', 'New Case #CASE-20250429-170823-3 (Question/Inconvenience) from Progress Software', 'New Support Case Logged\n===================================\nCase Number: CASE-20250429-170823-3\nSubmitted By: Jane Smith (barandonjoice07@gmail.com)\nCompany: Progress Software\nSerial Number: 111\n\n--- Case Details ---\nSubject: Configuration Problem\nProduct: WhatsUp Gold\nSeverity: Question/Inconvenience\nProduct Group: Software\nProduct Version: v12.521\nAttachment: CASE-20250429-170823-3_6810970754024_unnamed.png\n\nAdmin dashboard access needed.\n===================================', '2025-04-29 09:08:27');

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
  `priority` int(11) DEFAULT NULL,
  `is_escalated` tinyint(1) NOT NULL DEFAULT 0,
  `case_status` enum('New','Waiting in Progress','Closed','Solved') NOT NULL DEFAULT 'New',
  `attachment` varchar(255) DEFAULT NULL,
  `case_owner` int(11) NOT NULL,
  `company` varchar(255) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `datetime_opened` datetime DEFAULT current_timestamp(),
  `reopen` int(11) NOT NULL,
  `date_accepted` datetime DEFAULT NULL,
  `date_solved` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by_user_id` int(11) DEFAULT NULL,
  `current_assignee_user_id` int(11) DEFAULT NULL,
  `last_updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `last_updated_by_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_number`, `type`, `subject`, `user_id`, `product_group`, `product`, `product_version`, `severity`, `priority`, `is_escalated`, `case_status`, `attachment`, `case_owner`, `company`, `last_modified`, `datetime_opened`, `reopen`, `date_accepted`, `date_solved`, `created_at`, `created_by_user_id`, `current_assignee_user_id`, `last_updated_at`, `last_updated_by_user_id`) VALUES
(90, 'CASE-20250429-131401-3', 'WS_FTP', 'Bug Report', 1, 'Software', 'WS_FTP', 'v12.521', 'Production System Down', 4, 1, 'Waiting in Progress', '0', 3, 'Progress Software', '2025-04-29 07:39:09', '2025-04-29 13:14:01', 0, NULL, NULL, '2025-04-28 21:34:17', NULL, NULL, '2025-04-28 23:39:09', NULL),
(91, 'CASE-20250429-133837-3', 'WhatsUp Gold', 'Usage Question', 1, 'Software', 'WhatsUp Gold', 'v12.521', 'Question/Inconvenience', 3, 1, 'Waiting in Progress', '0', 3, 'Progress Software', '2025-04-29 07:58:59', '2025-04-29 13:38:37', 0, NULL, NULL, '2025-04-28 21:38:37', NULL, NULL, '2025-04-28 23:58:59', NULL),
(92, 'CASE-20250429-134244-3', 'MOVEit', 'Usage Question', 1, 'Antivirus Solutions', 'MOVEit', 'v12.5', 'Restricted Operations', NULL, 0, 'Solved', '0', 3, 'Progress Software', '2025-04-29 06:39:54', '2025-04-29 13:42:44', 0, NULL, '2025-04-28 22:39:54', '2025-04-28 21:42:44', NULL, NULL, '2025-04-28 22:39:54', NULL),
(93, 'CASE-20250429-160108-3', 'Sophos Antivirus', 'Bug Report', 8, 'Antivirus Solutions', 'Sophos Antivirus', 'v12.5', 'Restricted Operations', NULL, 0, 'Waiting in Progress', '0', 3, 'Sophos', '2025-05-09 05:57:38', '2025-04-29 16:01:08', 1, NULL, NULL, '2025-04-29 00:01:08', NULL, NULL, '2025-05-09 13:57:38', NULL),
(94, 'CASE-20250429-170102-3', 'WS_FTP', 'Installation Issue', 3, 'Antivirus Solutions', 'WS_FTP', 'v12.5', 'Restricted Operations', NULL, 0, 'Waiting in Progress', '0', 3, 'Progress Software', '2025-05-09 05:56:29', '2025-04-29 17:01:02', 0, NULL, NULL, '2025-04-29 01:01:02', NULL, NULL, '2025-05-09 13:56:29', NULL),
(95, 'CASE-20250429-170823-3', 'WhatsUp Gold', 'Configuration Problem', 3, 'Software', 'WhatsUp Gold', 'v12.521', 'Question/Inconvenience', NULL, 0, 'New', '0', 3, 'Progress Software', '2025-04-30 01:08:23', '2025-04-29 17:08:23', 0, NULL, NULL, '2025-04-29 01:08:23', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `case_history`
--

CREATE TABLE `case_history` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `case_history`
--

INSERT INTO `case_history` (`id`, `case_id`, `user_id`, `action_type`, `details`, `old_value`, `new_value`, `timestamp`) VALUES
(2, 91, 1, 'ESCALATE', 'Priority changed from P3 to P1 by John Doe.', 'P3', 'P1', '2025-04-28 23:12:43'),
(3, 91, 1, 'ESCALATE', 'Priority changed from P1 to P3 by John Doe.', 'P1', 'P3', '2025-04-28 23:18:12'),
(4, 91, 1, 'ESCALATE', 'Priority changed from P3 to P4 by John Doe.', 'P3', 'P4', '2025-04-28 23:19:32'),
(5, 91, 1, 'ESCALATE', 'Priority changed from P4 to P2 by John Doe.', 'P4', 'P2', '2025-04-28 23:29:28'),
(6, 91, 1, 'ESCALATE', 'Priority changed from P2 to P4 by John Doe.', 'P2', 'P4', '2025-04-28 23:29:56'),
(7, 91, 1, 'ESCALATE', 'Priority changed from P4 to P3 by John Doe.', 'P4', 'P3', '2025-04-28 23:30:22'),
(8, 91, 1, 'ESCALATE', 'Priority changed from P3 to P2 by John Doe.', 'P3', 'P2', '2025-04-28 23:36:48'),
(10, 91, 1, 'ESCALATE', 'Priority changed from P2 to P1 by John Doe.', 'P2', 'P1', '2025-04-28 23:41:49'),
(12, 91, 1, 'ESCALATE', 'Priority changed from P2 to P3 by John Doe. Severity text updated accordingly.', 'P2', 'P3', '2025-04-28 23:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `case_logs`
--

CREATE TABLE `case_logs` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `log_timestamp` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(112, '00000012', 'Jane Smith', 'John Doe', 'test', NULL, '2025-04-07 04:50:53', 1),
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
(131, '00000020', 'System', 'Jane Smith', 'Case #00000020 created successfully. Subject: adada', NULL, '2025-04-10 05:42:21', 1),
(132, '00000021', 'System', 'Jane Smith', 'Case #00000021 created successfully. Subject: dadasdsa', NULL, '2025-04-10 05:42:38', 1),
(133, '00000022', 'System', 'Jane Smith', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000022.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', NULL, '2025-04-10 05:43:30', 1),
(134, '00000023', 'System', 'Jane Smith', 'Case #00000023 created successfully by Jane Smith.', NULL, '2025-04-10 05:49:33', 1),
(135, '00000024', 'System', 'Jane Smith', 'Case #00000024 created successfully by Jane Smith.', NULL, '2025-04-10 05:50:03', 1),
(136, '00000023', 'Jane Smith', 'System', 'ok', NULL, '2025-04-10 07:06:59', 0),
(137, '00000025', 'System', 'Jane Smith', 'Case #00000025 created successfully by Jane Smith.', NULL, '2025-04-11 03:41:02', 0),
(138, '00000026', 'System', 'Jane Smith', 'Case #00000026 created successfully by Jane Smith.', NULL, '2025-04-11 03:58:00', 0),
(139, '00000027', 'System', 'Jane Smith', 'Case #00000027 created successfully by Jane Smith.', NULL, '2025-04-11 04:56:04', 0),
(140, '00000028', 'System', 'Jane Smith', 'Case #00000028 created successfully by Jane Smith.', NULL, '2025-04-11 04:58:18', 0),
(141, '00000029', 'System', 'Jane Smith', 'Case #00000029 created successfully by Jane Smith.', NULL, '2025-04-11 05:03:09', 0),
(142, '00000030', 'System', 'Jane Smith', 'Case #00000030 created successfully by Jane Smith.', NULL, '2025-04-11 05:07:26', 0),
(143, '00000031', 'System', 'Jane Smith', 'Case #00000031 created successfully by Jane Smith.', NULL, '2025-04-11 05:10:06', 0),
(144, '00000032', 'System', 'Jane Smith', 'Case #00000032 created successfully by Jane Smith.', NULL, '2025-04-11 05:11:08', 0),
(145, '00000033', 'System', 'Lance Casas', 'Case #00000033 created successfully by Lance Casas.', NULL, '2025-04-25 08:35:02', 0),
(146, '00000034', 'System', 'Lance Casas', 'Case #00000034 created successfully by Lance Casas.', NULL, '2025-04-25 08:43:12', 0),
(147, '00000035', 'System', 'Lance Casas', 'Case #00000035 created successfully by Lance Casas.', NULL, '2025-04-25 08:44:14', 0),
(148, '00000036', 'System', 'Jane Smith', 'Case #00000036 created successfully by Jane Smith.', NULL, '2025-04-25 22:48:17', 0),
(149, '00000033', 'System', 'Jane Smith', 'Case #00000033 created successfully by Jane Smith.', NULL, '2025-04-26 00:39:23', 0),
(150, '00000034', 'System', 'Jane Smith', 'Case #00000034 created successfully by Jane Smith.', NULL, '2025-04-26 00:40:37', 0),
(151, '00000035', 'System', 'Jane Smith', 'Case #00000035 created successfully by Jane Smith.', NULL, '2025-04-26 00:43:17', 0),
(152, '00000036', 'System', 'Jane Smith', 'Case #00000036 created successfully by Jane Smith.', NULL, '2025-04-26 03:57:00', 0),
(153, '00000037', 'System', 'Jane Smith', 'Case #00000037 created successfully by Jane Smith.', NULL, '2025-04-26 04:06:38', 0),
(154, '00000038', 'System', 'Jane Smith', 'Case #00000038 created successfully by Jane Smith.', NULL, '2025-04-29 04:00:08', 0),
(155, '00000039', 'System', 'Jane Smith', 'Case #00000039 created successfully by Jane Smith.', NULL, '2025-04-29 04:00:12', 0),
(156, '00000040', 'System', 'Jane Smith', 'Case #00000040 created successfully by Jane Smith.', NULL, '2025-04-29 04:01:12', 0),
(157, '00000041', 'System', 'Jane Smith', 'Case #00000041 created successfully by Jane Smith.', NULL, '2025-04-29 04:04:13', 0),
(158, '00000042', 'System', 'Jane Smith', 'Case #00000042 created successfully by Jane Smith.', NULL, '2025-04-29 04:23:03', 0),
(159, '00000043', 'System', 'Jane Smith', 'Case #00000043 created successfully by Jane Smith.', NULL, '2025-04-29 04:27:40', 0),
(160, 'CASE-20250429-123834-3', 'System', 'Jane Smith', 'Case #CASE-20250429-123834-3 created by Jane Smith.', NULL, '2025-04-29 04:38:38', 0),
(161, 'CASE-20250429-124811-3', 'System', 'Jane Smith', 'Case #CASE-20250429-124811-3 created by Jane Smith.', NULL, '2025-04-29 04:48:15', 0),
(162, 'CASE-20250429-125122-3', 'System', 'Jane Smith', 'Case #CASE-20250429-125122-3 created by Jane Smith.', NULL, '2025-04-29 04:51:26', 0),
(163, 'CASE-20250429-130710-3', 'System', 'Jane Smith', 'Case #CASE-20250429-130710-3 created by Jane Smith. Severity: 2 - Restricted Operations.', NULL, '2025-04-29 05:07:14', 0),
(164, 'CASE-20250429-131401-3', 'System', 'Jane Smith', 'Case #CASE-20250429-131401-3 created by Jane Smith. Severity: Question/Inconvenience.', NULL, '2025-04-29 05:14:05', 0),
(165, 'CASE-20250429-133837-3', 'System', 'Jane Smith', 'Case #CASE-20250429-133837-3 created by Jane Smith. Severity: Restricted Operations.', NULL, '2025-04-29 05:38:41', 0),
(166, 'CASE-20250429-134244-3', 'System', 'Jane Smith', 'Case #CASE-20250429-134244-3 created by Jane Smith. Severity: Restricted Operations.', NULL, '2025-04-29 05:42:48', 0),
(167, 'CASE-20250429-160108-3', 'System', 'Jane Smith', 'Case #CASE-20250429-160108-3 created by Jane Smith. Severity: Restricted Operations.', NULL, '2025-04-29 08:01:12', 0),
(168, 'CASE-20250429-170102-3', 'System', 'Jane Smith', 'Case #CASE-20250429-170102-3 created by Jane Smith. Severity: Production System Down.', NULL, '2025-04-29 09:01:06', 0),
(169, 'CASE-20250429-170823-3', 'System', 'Jane Smith', 'Case #CASE-20250429-170823-3 created by Jane Smith. Severity: Question/Inconvenience.', NULL, '2025-04-29 09:08:27', 0);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`) VALUES
(1, 'test'),
(2, 'XYZ Corporation');

-- --------------------------------------------------------

--
-- Table structure for table `customer_products`
--

CREATE TABLE `customer_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `serial_number` varchar(100) NOT NULL,
  `product_group` varchar(255) DEFAULT NULL,
  `product_type` varchar(100) DEFAULT NULL,
  `product_version` varchar(50) DEFAULT NULL,
  `license_type` varchar(100) DEFAULT NULL,
  `license_duration` varchar(100) DEFAULT NULL COMMENT 'e.g., "365 days" or "1 year"',
  `license_date_start` date DEFAULT NULL,
  `end_license_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_products`
--

INSERT INTO `customer_products` (`id`, `customer_name`, `company`, `serial_number`, `product_group`, `product_type`, `product_version`, `license_type`, `license_duration`, `license_date_start`, `end_license_date`, `created_at`, `updated_at`) VALUES
(4, 'test2', 'XYZ Corporation', '123', 'Antivirus Solutions', 'OpenEdge', 'v12.5', 'Enterprise', '19 days', '2025-04-21', '2025-05-10', '2025-03-10 13:46:00', '2025-04-21 03:42:09'),
(5, 'Jane Smith', 'XYZ Corporation', '123', 'Antivirus Solutions', 'OpenEdge', 'v12.5', 'Enterprise', '365 days', '2025-04-21', '2026-04-21', '2025-03-10 13:46:00', '2025-04-21 05:24:22'),
(6, 'Jane Smith', 'XYZ Corporation', '123', 'Antivirus Solutions', 'OpenEdge', 'v12.5', 'Enterprise', '15 days', '2025-04-25', '2025-05-10', '2025-03-10 13:46:00', '2025-04-25 00:56:37'),
(7, 'Jane Smith', 'XYZ Corporation', '123', 'Antivirus Solutions', 'OpenEdge', 'v12.5', 'Enterprise', '27 days', '2025-04-25', '2025-05-22', '2025-03-10 13:46:00', '2025-04-25 00:59:34'),
(8, 'Jane Smith', 'XYZ Corporation', '123', 'Antivirus Solutions', 'OpenEdge', 'v12.5', 'Enterprise', '39 days', '2025-05-10', '2025-06-18', '2025-03-10 13:46:00', '2025-04-25 01:01:36'),
(10, 'Jane Smith', 'test', '321', 'Software', 'OpenEdge', 'v12.54', 'Perpetual', '16 days', '2025-04-25', '2025-05-10', '2025-04-25 06:45:00', '2025-04-25 08:04:52'),
(11, 'Lance Casas', 'test', 'A1B2C3D4', 'Software', 'Firewall', 'v2.0', 'Subscription', '16 days', '2025-04-25', '2025-05-10', '2025-04-25 08:31:00', '2025-04-25 08:31:51');

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
-- Table structure for table `gmail_app_password`
--

CREATE TABLE `gmail_app_password` (
  `id` int(11) NOT NULL,
  `user_id` varchar(11) NOT NULL,
  `app_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gmail_app_password`
--

INSERT INTO `gmail_app_password` (`id`, `user_id`, `app_password`) VALUES
(1, '3', 'khmlzdtykxsbhsgc'),
(2, '7', 'rszstbmhhinayqss'),
(3, '1', 'pbqqnphymnsbjjjn'),
(4, '8', 'cxdhqogvmosgwan'),
(5, '12', 'gqqyrqzyxcdbqqyz'),
(6, '8', 'oabh ahfz mjvx tmmf'),
(7, '8', 'oabhahfzmjvxtmmf');

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
  `imap_message_no` int(10) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `case_id`, `recipient_username`, `recipient_email`, `message_subject`, `message_body`, `sent_at`, `imap_message_no`, `is_read`) VALUES
(94, 25, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000025 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000025.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-11 03:41:02', NULL, 1),
(95, NULL, 'Jane Smith', NULL, 'Support Case #00000025 Created Successfully', '', '2025-04-11 03:40:58', 565, 1),
(96, 26, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000026 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000026.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-11 03:58:00', NULL, 1),
(97, 27, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000027 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000027.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-11 04:56:04', NULL, 1),
(98, 28, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000028 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000028.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-11 04:58:18', NULL, 1),
(99, 29, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000029 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000029.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-11 05:03:09', NULL, 1),
(100, 30, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000030 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000030.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-11 05:07:26', NULL, 1),
(101, 31, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000031 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000031.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-11 05:10:06', NULL, 1),
(102, 32, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000032 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000032.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-11 05:11:08', NULL, 1),
(103, 33, 'Lance Casas', 'lancecasas0801@gmail.com', 'Case #00000033 Created', 'Dear Lance Casas,\n\nYour issue has been successfully logged as case #00000033.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-25 08:35:02', NULL, 0),
(104, 34, 'Lance Casas', 'lancecasas0801@gmail.com', 'Case #00000034 Created', 'Dear Lance Casas,\n\nYour issue has been successfully logged as case #00000034.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-25 08:43:12', NULL, 0),
(105, 35, 'Lance Casas', 'lancecasas0801@gmail.com', 'Case #00000035 Created', 'Dear Lance Casas,\n\nYour issue has been successfully logged as case #00000035.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-25 08:44:14', NULL, 0),
(106, 71, 'Lance Casas', 'lancecasas0801@gmail.com', 'Technical Assistance Request Accepted - Case #00000035', 'Hello Lance Casas,\n\nWe have received your request for technical assistance (Case #00000035). One of our support engineers, Ranz Andrei Ornopia, has accepted your case and will assist you during regular support hours.\n\nTo help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility via the support portal.\n\nThank you,\nTechnical Support Team', '2025-04-25 08:45:40', NULL, 0),
(107, 36, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000036 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000036.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-25 22:48:17', NULL, 0),
(108, 33, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000033 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000033.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-26 00:39:23', NULL, 0),
(109, 34, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000034 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000034.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-26 00:40:37', NULL, 0),
(110, 35, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000035 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000035.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-26 00:43:17', NULL, 0),
(111, 36, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000036 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000036.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-26 03:57:00', NULL, 0),
(112, 37, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000037 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000037.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-26 04:06:38', NULL, 0),
(113, 77, 'Jane Smith', 'barandonjoice07@gmail.com', 'Support Case #00000037 Has Been Resolved', 'Hello Jane Smith,\n\nWe\'re pleased to inform you that your support case #00000037 has been marked as resolved by our engineer, John Doe.\n\nIf you believe the issue is not fully resolved, please reply to this message or contact us through the support portal within 7 days to reopen the case.\n\nWe value your feedback! Please take a moment to rate your support experience with John Doe by visiting the link below:\nhttp://localhost/sts/rate_engineer.php?id=MQ==&amp;case=MDAwMDAwMzc=\n\nThank you for choosing our services.\n\nBest regards,\nTechnical Support Team\nThis is an automated message. Please use the support portal for inquiries.', '2025-04-26 04:34:27', NULL, 0),
(114, 37, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: 00000037', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (00000037) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-04-26 04:41:15', NULL, 0),
(115, 38, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000038 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000038.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-29 04:00:08', NULL, 0),
(116, 39, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000039 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000039.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-29 04:00:12', NULL, 0),
(117, 40, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000040 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000040.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-29 04:01:12', NULL, 0),
(118, 41, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000041 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000041.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-29 04:04:13', NULL, 0),
(119, 42, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000042 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000042.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-29 04:23:03', NULL, 0),
(120, 43, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #00000043 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #00000043.\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-29 04:27:40', NULL, 0),
(121, 84, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-123834-3 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #CASE-20250429-123834-3.\n\nDetails:\n- Subject: Configuration Problem\n- Type/Product: WS_FTP\n- Severity: 1 - Production System Down\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-29 04:38:38', NULL, 0),
(122, 86, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-124811-3 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #CASE-20250429-124811-3.\n\nDetails:\n- Subject: Bug Report\n- Type/Product: Sophos Antivirus\n- Severity: 3 - Question/Inconvenience\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-29 04:48:15', NULL, 0),
(123, 87, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-125122-3 Created', 'Dear Jane Smith,\n\nYour issue has been successfully logged as case #CASE-20250429-125122-3.\n\nDetails:\n- Subject: Usage Question\n- Type/Product: WS_FTP\n- Severity: 3 - Question/Inconvenience\n\nPlease Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\nThank you,\nTechnical Support Team\n\nPlease do not reply to this email. To update your case, please use the support portal.', '2025-04-29 04:51:26', NULL, 0),
(124, 89, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-130710-3 Created', 'Dear Jane Smith,\n\nYour support case has been successfully logged with Case Number: CASE-20250429-130710-3.\n\nDetails:\n- Subject: Usage Question\n- Product: MOVEit Cloud\n- Severity: 2 - Restricted Operations\n\nPlease Note: Customers needing immediate assistance on a Severity 1 issue (Production System Down) opened outside of normal business hours must contact us by phone for the quickest response.\n\nYou can view and update your case through the support portal.\n\nThank you,\nTechnical Support Team\n\n--\nPlease do not reply directly to this automated email.', '2025-04-29 05:07:14', NULL, 0),
(125, 90, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-131401-3 Created', 'Dear Jane Smith,\n\nYour support case has been successfully logged with Case Number: CASE-20250429-131401-3.\n\nDetails:\n- Subject: Bug Report\n- Product: WS_FTP\n- Severity: Question/Inconvenience\n\nPlease Note: Customers needing immediate assistance on a Severity 1 issue (Production System Down) opened outside of normal business hours must contact us by phone for the quickest response.\n\nYou can view and update your case through the support portal.\n\nThank you,\nTechnical Support Team\n\n--\nPlease do not reply directly to this automated email.', '2025-04-29 05:14:05', NULL, 0),
(126, 91, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-133837-3 Created', 'Dear Jane Smith,\n\nYour support case has been successfully logged with Case Number: CASE-20250429-133837-3.\n\nDetails:\n- Subject: Usage Question\n- Product: WhatsUp Gold\n- Severity: Restricted Operations\n\nPlease Note: Customers needing immediate assistance on a Severity 1 issue (Production System Down) opened outside of normal business hours must contact us by phone for the quickest response.\n\nYou can view and update your case through the support portal.\n\nThank you,\nTechnical Support Team\n\n--\nPlease do not reply directly to this automated email.', '2025-04-29 05:38:41', NULL, 0),
(127, 92, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-134244-3 Created', 'Dear Jane Smith,\n\nYour support case has been successfully logged with Case Number: CASE-20250429-134244-3.\n\nDetails:\n- Subject: Usage Question\n- Product: MOVEit\n- Severity: Restricted Operations\n\nPlease Note: Customers needing immediate assistance on a Severity 1 issue (Production System Down) opened outside of normal business hours must contact us by phone for the quickest response.\n\nYou can view and update your case through the support portal.\n\nThank you,\nTechnical Support Team\n\n--\nPlease do not reply directly to this automated email.', '2025-04-29 05:42:48', NULL, 0),
(128, 92, 'Jane Smith', 'barandonjoice07@gmail.com', 'Support Case #CASE-20250429-134244-3 Has Been Resolved', 'Hello Jane Smith,\n\nWe\'re pleased to inform you that your support case #CASE-20250429-134244-3 has been marked as resolved by our engineer, John Doe.\n\nIf you believe the issue is not fully resolved, please reply to this message or contact us through the support portal within 7 days to reopen the case.\n\nWe value your feedback! Please take a moment to rate your support experience with John Doe by visiting the link below:\nhttp://localhost/sts/rate_engineer.php?id=MQ==&amp;case=Q0FTRS0yMDI1MDQyOS0xMzQyNDQtMw==\n\nThank you for choosing our services.\n\nBest regards,\nTechnical Support Team\nThis is an automated message. Please use the support portal for inquiries.', '2025-04-29 06:39:54', NULL, 0),
(129, 93, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-160108-3 Created', 'Dear Jane Smith,\n\nYour support case has been successfully logged with Case Number: CASE-20250429-160108-3.\n\nDetails:\n- Subject: Bug Report\n- Product: Sophos Antivirus\n- Severity: Restricted Operations\n\nPlease Note: Customers needing immediate assistance on a Severity 1 issue (Production System Down) opened outside of normal business hours must contact us by phone for the quickest response.\n\nYou can view and update your case through the support portal.\n\nThank you,\nTechnical Support Team\n\n--\nPlease do not reply directly to this automated email.', '2025-04-29 08:01:12', NULL, 0),
(130, 94, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-170102-3 Created', 'Dear Jane Smith,\n\nYour support case has been successfully logged with Case Number: CASE-20250429-170102-3.\n\nDetails:\n- Subject: Installation Issue\n- Product: WS_FTP\n- Severity: Production System Down\n\nPlease Note: Customers needing immediate assistance on a Severity 1 issue (Production System Down) opened outside of normal business hours must contact us by phone for the quickest response.\n\nYou can view and update your case through the support portal.\n\nThank you,\nTechnical Support Team\n\n--\nPlease do not reply directly to this automated email.', '2025-04-29 09:01:06', NULL, 0),
(131, 95, 'Jane Smith', 'barandonjoice07@gmail.com', 'Case #CASE-20250429-170823-3 Created', 'Dear Jane Smith,\n\nYour support case has been logged with Case Number: CASE-20250429-170823-3.\n\nDetails:\n- Subject: Configuration Problem\n- Product: WhatsUp Gold\n- Severity: Question/Inconvenience\n\nThank you,\nSupport Team', '2025-04-29 09:08:27', NULL, 0),
(132, 0, 'Jane Smith', 'barandonjoice07@gmail.com', 'Ticket Reopened: CASE-20250429-160108-3', 'Dear Jane Smith,\n\nWe would like to inform you that your support ticket (CASE-20250429-160108-3) regarding  has been reopened.\n\nOur team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\nIf you have any additional details or questions, please feel free to share them with us.\n\nThank you for your continued patience and cooperation.\n\nBest regards,\ni-Secure Networks and Business Solutions Inc.', '2025-05-09 05:57:38', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_group` varchar(255) NOT NULL,
  `product_type` varchar(250) NOT NULL,
  `product_version` varchar(50) NOT NULL,
  `license_type` varchar(100) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `status` enum('Active','Deactivated','','') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_group`, `product_type`, `product_version`, `license_type`, `serial_number`, `status`, `created_at`) VALUES
(3, 'Antivirus Solutions', 'OpenEdge', 'v12.5', 'Enterprise', '123', 'Active', '2025-03-10 05:46:43'),
(5, 'Software', 'OpenEdge', 'v12.54', 'Perpetual', '321', 'Active', '2025-04-25 06:45:08'),
(6, 'Software', 'test2', 'v12.521', 'Perpetual', '111', 'Active', '2025-04-25 06:50:31'),
(7, 'Software', 'Firewall', 'v2.0', 'Subscription', 'A1B2C3D4', 'Active', '2025-04-25 08:31:02');

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
('smtp_password', 'rszstbmhhinayqss'),
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
(7, 'Juan Cruz', 'isecurenetworkgroup@gmail.com', 'juancruz', '123', 'Administrator', 'Active', '2025-02-20 07:29:09'),
(8, 'Ranz Andrei Ornopia', 'rnzndrrnp2@gmail.com ', 'Ranz', '123', 'Engineer', 'Active', '2025-02-20 07:55:17'),
(12, 'Lance Casas', 'lancecasas0801@gmail.com', 'lance', '$2y$10$rcmikMtzHkENEb5KW7nxLOB95pGH7FHbPA08.SUqyVGKqQoQyhNQi', 'User', 'Active', '2025-04-25 08:29:07'),
(14, 'lawrence lawrence', 'vc08339@gmail.com', 'lawrence', '$2y$10$d6Ten1NIuyDM33AwbQfsgeeAOVBXmWUvKICMH2izWrrwcW58kN08S', 'User', 'Active', '2025-04-29 09:30:33');

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
  ADD KEY `fk_case_owner` (`case_owner`),
  ADD KEY `fk_cases_created_by` (`created_by_user_id`),
  ADD KEY `fk_cases_current_assignee` (`current_assignee_user_id`),
  ADD KEY `fk_cases_last_updated_by` (`last_updated_by_user_id`);

--
-- Indexes for table `case_history`
--
ALTER TABLE `case_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_case_history_case_id` (`case_id`),
  ADD KEY `idx_case_history_user_id` (`user_id`),
  ADD KEY `idx_case_history_timestamp` (`timestamp`);

--
-- Indexes for table `case_logs`
--
ALTER TABLE `case_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_caselogs_user` (`user_id`),
  ADD KEY `idx_caselogs_case_id` (`case_id`),
  ADD KEY `idx_caselogs_event_type` (`event_type`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receiver_read` (`receiver`,`is_read`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`);

--
-- Indexes for table `customer_products`
--
ALTER TABLE `customer_products`
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
-- Indexes for table `gmail_app_password`
--
ALTER TABLE `gmail_app_password`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `case_history`
--
ALTER TABLE `case_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `case_logs`
--
ALTER TABLE `case_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer_products`
--
ALTER TABLE `customer_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
-- AUTO_INCREMENT for table `gmail_app_password`
--
ALTER TABLE `gmail_app_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `fk_case_owner` FOREIGN KEY (`case_owner`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cases_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cases_current_assignee` FOREIGN KEY (`current_assignee_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cases_last_updated_by` FOREIGN KEY (`last_updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cases_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `case_logs`
--
ALTER TABLE `case_logs`
  ADD CONSTRAINT `fk_caselogs_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_caselogs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
