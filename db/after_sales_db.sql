-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2026 at 10:26 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `after_sales_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `line_uid` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `line_uid`, `created_at`) VALUES
(1, 'สมชาย ใจดี', '0812345678', 'somchai@email.com', 'U1111111111111111', '2026-07-02 15:22:19'),
(2, 'วิภา รักเรียน', '0823456789', 'wipa@email.com', 'U2222222222222222', '2026-07-02 15:22:19'),
(3, 'ประเสริฐ มั่งมี', '0834567890', 'prasert@email.com', 'U3333333333333333', '2026-07-02 15:22:19'),
(4, 'นภา สุขสันต์', '0845678901', 'napa@email.com', 'U4444444444444444', '2026-07-02 15:22:19'),
(5, 'กิตติ เก่งกาจ', '0856789012', 'kitti@email.com', 'U5555555555555555', '2026-07-02 15:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `device_type` enum('hardware','software') NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `customer_id`, `serial_number`, `name`, `device_type`, `purchase_date`, `warranty_end`, `note`, `created_at`) VALUES
(1, 1, 'SN-HW-2023-001', 'Epson L3210 เครื่องพิมพ์', 'hardware', '2023-01-15', '2026-01-15', NULL, '2026-07-02 15:22:19'),
(2, 1, 'SN-SW-2023-002', 'Adobe Acrobat Pro 2023', 'software', '2023-03-01', '2025-03-01', NULL, '2026-07-02 15:22:19'),
(3, 2, 'SN-HW-2023-003', 'HP LaserJet Pro M404n', 'hardware', '2023-06-10', '2025-06-10', NULL, '2026-07-02 15:22:19'),
(4, 2, 'SN-SW-2024-004', 'Microsoft Office 365', 'software', '2024-01-01', '2027-01-01', NULL, '2026-07-02 15:22:19'),
(5, 3, 'SN-HW-2024-005', 'Epson EcoTank ET-2850', 'hardware', '2024-02-20', '2027-02-20', NULL, '2026-07-02 15:22:19'),
(6, 3, 'SN-HW-2022-006', 'Brother MFC-L2750DW', 'hardware', '2022-05-01', '2024-05-01', NULL, '2026-07-02 15:22:19'),
(7, 4, 'SN-SW-2024-007', 'Kaspersky Total Security', 'software', '2024-06-15', '2026-06-15', NULL, '2026-07-02 15:22:19'),
(8, 5, 'SN-HW-2024-008', 'Canon PIXMA G3020', 'hardware', '2024-08-01', '2027-08-01', NULL, '2026-07-02 15:22:19'),
(9, 5, 'SN-SW-2023-009', 'QuickBooks Pro 2023', 'software', '2023-09-01', '2025-09-01', NULL, '2026-07-02 15:22:19'),
(10, 4, 'SN-HW-2023-010', 'Fujitsu ScanSnap iX1600', 'hardware', '2023-11-01', '2026-11-01', NULL, '2026-07-02 15:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` varchar(100) NOT NULL,
  `keyword` text NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`id`, `category`, `keyword`, `question`, `answer`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'hardware', 'จอดับ,เปิดไม่ติด', 'จอดับเปิดไม่ติดทำอย่างไร', 'ให้ลองถอดสายไฟออกรอ 30 วินาที แล้วเสียบใหม่ ถ้ายังไม่ติดให้ลองเปลี่ยนสาย HDMI หรือเช็คว่าไฟเลี้ยงทำงานปกติหรือไม่', 1, '2026-07-02 14:18:00', NULL),
(2, 'hardware', 'ร้อน,ร้อนเกิน,พัดลมดัง', 'เครื่องร้อนผิดปกติแก้อย่างไร', 'ให้ทำความสะอาดช่องระบายอากาศด้วยลมอัด ตรวจสอบว่าวางเครื่องในที่อากาศถ่ายเทได้ หากพัดลมยังดังผิดปกติควรแจ้งซ่อม', 1, '2026-07-02 14:18:00', NULL),
(3, 'software', 'ลิขสิทธิ์,activate,activation', 'ลิขสิทธิ์ซอฟต์แวร์หมดอายุทำอย่างไร', 'กรุณาติดต่อแอดมินเพื่อต่ออายุลิขสิทธิ์ หรือแจ้งเลข Serial ซอฟต์แวร์มาเพื่อตรวจสอบสถานะ', 1, '2026-07-02 14:18:00', NULL),
(4, 'hardware', 'กระดาษติด,ดึงกระดาษไม่ได้,ฟีดกระดาษ', 'กระดาษติดในเครื่องพิมพ์ทำอย่างไร', 'ปิดเครื่องก่อน แล้วค่อยๆ ดึงกระดาษออกตามทิศทางที่กระดาษวิ่ง อย่าดึงย้อนทิศ จากนั้นเปิดฝาหลังเช็คว่ามีกระดาษค้างอยู่หรือไม่ เปิดเครื่องใหม่แล้วลองพิมพ์ทดสอบ', 1, '2026-07-02 15:22:20', NULL),
(5, 'hardware', 'หมึกหมด,เติมหมึก,หมึกไม่ออก', 'หมึกเพิ่งเติมแต่ยังพิมพ์ไม่ออก', 'ลอง print head cleaning ผ่านเมนูบนเครื่องพิมพ์ 1-2 รอบ หากยังไม่ได้ให้ทำ deep cleaning แต่อย่าทำเกิน 3 รอบ เพราะจะสิ้นเปลืองหมึก ถ้ายังไม่หายอาจมีปัญหาที่ท่อหมึกแจ้งซ่อมได้เลย', 1, '2026-07-02 15:22:20', NULL),
(6, 'hardware', 'พิมพ์สีเพี้ยน,สีผิด,สีไม่ตรง', 'เครื่องพิมพ์พิมพ์สีไม่ตรง', 'ให้ทำ color calibration ผ่านซอฟต์แวร์ของเครื่องพิมพ์ และทดลอง print head cleaning 1 รอบ ถ้าสียังเพี้ยนให้ตรวจสอบว่าตลับหมึกสีนั้นใกล้หมดหรือไม่', 1, '2026-07-02 15:22:20', NULL),
(7, 'software', 'license,ลิขสิทธิ์,activate,หมดอายุ', 'ซอฟต์แวร์แจ้งว่าลิขสิทธิ์หมดอายุ', 'กรุณาแจ้ง Serial Number ของซอฟต์แวร์มาที่แอดมิน เพื่อตรวจสอบสถานะและดำเนินการต่ออายุให้ครับ', 1, '2026-07-02 15:22:20', NULL),
(8, 'software', 'อัปเดต,update,ข้อมูลหาย,หลังอัปเดต', 'ข้อมูลหายหลังจากอัปเดตซอฟต์แวร์', 'อย่าเพิ่งใช้งานซอฟต์แวร์นั้นต่อ เพื่อป้องกันข้อมูลถูกเขียนทับ แจ้งซ่อมมาได้เลย ช่างจะช่วย restore ข้อมูลให้ครับ', 1, '2026-07-02 15:22:20', NULL),
(9, 'hardware', 'scanner,สแกนไม่ได้,error,สแกนเนอร์', 'Scanner ใช้งานไม่ได้ขึ้น error', 'ลองถอด USB แล้วเสียบใหม่ หรือลองเปลี่ยน port USB ถ้ายังไม่ได้ให้ถอนการติดตั้ง driver แล้วติดตั้งใหม่จากเว็บผู้ผลิต ถ้ายังไม่หายแจ้งซ่อมได้เลยครับ', 1, '2026-07-02 15:22:20', NULL),
(10, 'general', 'ประกัน,warranty,ระยะเวลา,สิทธิ์ประกัน', 'ตรวจสอบระยะเวลาประกันทำอย่างไร', 'พิมพ์ Serial Number ของอุปกรณ์มาได้เลยครับ ระบบจะแจ้งสถานะประกันและวันหมดอายุให้ทันที', 1, '2026-07-02 15:22:20', NULL),
(11, 'general', 'แจ้งซ่อม,ส่งซ่อม,repair,ซ่อม', 'ขั้นตอนการแจ้งซ่อมเป็นอย่างไร', 'กดปุ่ม \"แจ้งซ่อม\" แล้วพิมพ์ Serial Number ของอุปกรณ์ที่ต้องการซ่อม จากนั้นระบุอาการและปัญหา เจ้าหน้าที่จะตรวจสอบสิทธิ์ประกันและดำเนินการต่อไปครับ', 1, '2026-07-02 15:22:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `partners`
--

CREATE TABLE `partners` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partners`
--

INSERT INTO `partners` (`id`, `company_name`, `contact_name`, `phone`, `email`, `is_active`, `created_at`) VALUES
(1, 'บริษัท เซอร์วิสโปร จำกัด', 'คุณมานะ', '0844444444', 'servicepro@email.com', 1, '2026-07-02 15:22:19'),
(2, 'ร้าน ไอทีเซ็นเตอร์', 'คุณสุรีย์', '0855555555', 'itcenter@email.com', 1, '2026-07-02 15:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `technicians`
--

CREATE TABLE `technicians` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technicians`
--

INSERT INTO `technicians` (`id`, `name`, `phone`, `email`, `is_active`, `created_at`) VALUES
(1, 'ช่างอนุชา สมาร์ท', '0811111111', 'anucha@3wsupport.com', 1, '2026-07-02 15:22:19'),
(2, 'ช่างวีระ เทคโน', '0822222222', 'weera@3wsupport.com', 1, '2026-07-02 15:22:19'),
(3, 'ช่างพิชัย แก้ไข', '0833333333', 'pichai@3wsupport.com', 1, '2026-07-02 15:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `device_id` int(10) UNSIGNED NOT NULL,
  `ticket_type` enum('hardware','software') NOT NULL,
  `issue_desc` text NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('pending','approved','assigned','in_progress','wait_quote','wait_confirm','escalated','completed','closed') DEFAULT 'pending',
  `technician_id` int(10) UNSIGNED DEFAULT NULL,
  `tech_start_date` date DEFAULT NULL,
  `tech_end_date` date DEFAULT NULL,
  `tech_note` text DEFAULT NULL,
  `partner_id` int(10) UNSIGNED DEFAULT NULL,
  `quote_amount` decimal(10,2) DEFAULT NULL,
  `quote_detail` text DEFAULT NULL,
  `quote_accepted` tinyint(1) DEFAULT NULL,
  `tracking_no` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `closed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `customer_id`, `device_id`, `ticket_type`, `issue_desc`, `note`, `status`, `technician_id`, `tech_start_date`, `tech_end_date`, `tech_note`, `partner_id`, `quote_amount`, `quote_detail`, `quote_accepted`, `tracking_no`, `created_at`, `updated_at`, `closed_at`) VALUES
(1, 1, 1, 'hardware', 'เครื่องพิมพ์ไม่ยอมดึงกระดาษ กระดาษติดบ่อยมาก', 'เกิดขึ้นทุกครั้งที่พิมพ์เกิน 5 แผ่น', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-02 15:22:19', NULL, NULL),
(2, 2, 3, 'hardware', 'เครื่องพิมพ์เลเซอร์พิมพ์แล้วมีรอยเปื้อนดำทุกแผ่น', NULL, 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-01 15:22:19', NULL, NULL),
(3, 3, 5, 'hardware', 'หมึกหมดบ่อยผิดปกติ เติมหมึกแล้วก็หมดเร็ว อาจมีรอยรั่ว', NULL, 'assigned', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-30 15:22:19', NULL, NULL),
(4, 4, 10, 'hardware', 'Scanner ไม่สแกนได้ ขึ้น error code E4 ทุกครั้ง', 'ลองถอดสายแล้วเสียบใหม่แล้วยังไม่หาย', 'in_progress', 2, '2026-07-01', '2026-07-05', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-29 15:22:19', NULL, NULL),
(5, 2, 3, 'hardware', 'ลูกกลิ้งภายในชำรุด ต้องเปลี่ยนอะไหล่', NULL, 'wait_quote', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, '2026-06-28 15:22:19', NULL, NULL),
(6, 3, 6, 'hardware', 'ชุด drum หมดอายุ พิมพ์แล้วภาพไม่ชัด', NULL, 'wait_confirm', NULL, NULL, NULL, NULL, 1, '2500.00', 'ค่าเปลี่ยน drum unit 1,800 บาท + ค่าแรง 700 บาท', NULL, NULL, '2026-06-27 15:22:19', NULL, NULL),
(7, 1, 2, 'software', 'Adobe Acrobat เปิดไฟล์ PDF ไม่ได้ ขึ้น license error', 'ลอง activate ใหม่แล้วยังไม่ได้', 'in_progress', 3, '2026-07-02', '2026-07-02', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-01 15:22:19', NULL, NULL),
(8, 5, 8, 'hardware', 'เครื่องพิมพ์สีเพี้ยน สีแดงออกเป็นสีส้ม', NULL, 'completed', 1, '2026-06-25', '2026-06-28', 'ทำความสะอาดหัวพิมพ์และ calibrate สีใหม่ เสร็จแล้ว', NULL, NULL, NULL, NULL, NULL, '2026-06-25 15:22:19', NULL, NULL),
(9, 4, 7, 'software', 'Kaspersky แจ้งเตือน virus ตลอดเวลา false positive', NULL, 'closed', 2, '2026-06-20', '2026-06-20', 'อัปเดต virus definition และ whitelist โปรแกรมที่ถูกบล็อก', NULL, NULL, NULL, NULL, NULL, '2026-06-18 15:22:19', NULL, NULL),
(10, 5, 9, 'software', 'QuickBooks ข้อมูลบัญชีหายหลังอัปเดต ต้องการกู้ข้อมูล', 'สำคัญมาก ข้อมูล 3 เดือนหาย', 'escalated', 3, NULL, NULL, 'ปัญหาเกินขอบเขต ต้องส่งให้ partner ที่เชี่ยวชาญ QuickBooks', NULL, NULL, NULL, NULL, NULL, '2026-06-30 15:22:19', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_logs`
--

CREATE TABLE `ticket_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_logs`
--

INSERT INTO `ticket_logs` (`id`, `ticket_id`, `user_id`, `old_status`, `new_status`, `message`, `created_at`) VALUES
(1, 2, 1, 'pending', 'approved', 'Admin อนุมัติ ticket', '2026-07-01 16:22:19'),
(2, 3, 1, 'pending', 'approved', 'Admin อนุมัติ ticket', '2026-06-30 16:22:19'),
(3, 3, 1, 'approved', 'assigned', 'Assign ให้ช่างอนุชา', '2026-06-30 17:22:19'),
(4, 4, 1, 'pending', 'approved', 'Admin อนุมัติ ticket', '2026-06-29 15:22:19'),
(5, 4, 1, 'approved', 'assigned', 'Assign ให้ช่างวีระ', '2026-06-29 16:22:19'),
(6, 4, 2, 'assigned', 'in_progress', 'ช่างรับงาน กำหนดวันซ่อม 1-5 ก.ค.', '2026-06-29 17:22:19'),
(7, 5, 1, 'pending', 'approved', 'Admin อนุมัติ ticket', '2026-06-28 16:22:19'),
(8, 5, 1, 'approved', 'wait_quote', 'ส่งให้ Partner เซอร์วิสโปร', '2026-06-28 17:22:19'),
(9, 6, 1, 'pending', 'approved', 'Admin อนุมัติ ticket', '2026-06-27 16:22:19'),
(10, 6, 1, 'approved', 'wait_quote', 'ส่งให้ Partner เซอร์วิสโปร', '2026-06-27 17:22:19'),
(11, 6, 1, 'wait_quote', 'wait_confirm', 'Partner ส่งใบเสนอราคา 2,500 บาท', '2026-06-28 11:22:19'),
(12, 8, 1, 'pending', 'approved', 'Admin อนุมัติ ticket', '2026-06-25 15:22:19'),
(13, 8, 1, 'approved', 'assigned', 'Assign ให้ช่างอนุชา', '2026-06-25 16:22:19'),
(14, 8, 2, 'assigned', 'in_progress', 'ช่างรับงาน', '2026-06-25 23:22:19'),
(15, 8, 2, 'in_progress', 'completed', 'ซ่อมเสร็จแล้ว', '2026-06-26 15:22:19'),
(16, 9, 1, 'pending', 'approved', 'Admin อนุมัติ ticket', '2026-06-18 15:22:19'),
(17, 9, 1, 'approved', 'assigned', 'Assign ให้ช่างวีระ', '2026-06-18 16:22:19'),
(18, 9, 2, 'assigned', 'in_progress', 'ช่างรับงาน Remote', '2026-06-18 17:22:19'),
(19, 9, 2, 'in_progress', 'closed', 'แก้ไขเสร็จ ปิด ticket', '2026-06-18 21:22:19'),
(20, 10, 1, 'pending', 'approved', 'Admin อนุมัติ ticket', '2026-06-30 15:22:19'),
(21, 10, 1, 'approved', 'assigned', 'Assign ให้ช่างพิชัย', '2026-06-30 16:22:19'),
(22, 10, 3, 'assigned', 'escalated', 'ช่างแจ้ง: ปัญหาเกินขอบเขต ต้องส่ง Partner', '2026-06-30 17:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','technician','partner') NOT NULL,
  `ref_id` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `name`, `role`, `ref_id`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin', NULL, 1, '2026-07-02 14:18:00'),
(2, 'anucha', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ช่างอนุชา สมาร์ท', 'technician', 1, 1, '2026-07-02 15:22:19'),
(3, 'weera', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ช่างวีระ เทคโน', 'technician', 2, 1, '2026-07-02 15:22:19'),
(4, 'pichai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ช่างพิชัย แก้ไข', 'technician', 3, 1, '2026-07-02 15:22:19'),
(5, 'servicepro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'บริษัท เซอร์วิสโปร จำกัด', 'partner', 1, 1, '2026-07-02 15:22:19'),
(6, 'itcenter', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ร้าน ไอทีเซ็นเตอร์', 'partner', 2, 1, '2026-07-02 15:22:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `line_uid` (`line_uid`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `technicians`
--
ALTER TABLE `technicians`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `technician_id` (`technician_id`),
  ADD KEY `partner_id` (`partner_id`);

--
-- Indexes for table `ticket_logs`
--
ALTER TABLE `ticket_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `technicians`
--
ALTER TABLE `technicians`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ticket_logs`
--
ALTER TABLE `ticket_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`),
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`technician_id`) REFERENCES `technicians` (`id`),
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`);

--
-- Constraints for table `ticket_logs`
--
ALTER TABLE `ticket_logs`
  ADD CONSTRAINT `ticket_logs_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
