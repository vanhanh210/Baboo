-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2025 at 04:33 AM
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
-- Database: `baboohouse`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `building_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `identification_card` varchar(50) DEFAULT NULL,
  `signed_date` date DEFAULT NULL,
  `deposit_term` date DEFAULT NULL,
  `lease_term` varchar(255) DEFAULT NULL,
  `payment_term` varchar(50) DEFAULT NULL,
  `lease_start_date` date DEFAULT NULL,
  `lease_end_date` date DEFAULT NULL,
  `photo_urls` text DEFAULT NULL,
  `created_date` date DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buildings`
--

CREATE TABLE `buildings` (
  `building_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `rental_price` text DEFAULT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `owner_phone` varchar(50) DEFAULT NULL,
  `building_type` varchar(50) DEFAULT NULL,
  `electricity_price` decimal(10,2) DEFAULT NULL,
  `water_price` decimal(10,2) DEFAULT NULL,
  `service_price` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `last_modified` datetime DEFAULT NULL,
  `photo_urls` text DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buildings`
--

INSERT INTO `buildings` (`building_id`, `user_id`, `name`, `street`, `district`, `city`, `rental_price`, `owner_name`, `owner_phone`, `building_type`, `electricity_price`, `water_price`, `service_price`, `description`, `last_modified`, `photo_urls`, `approved`) VALUES
(31, 2, 'Baboohouse', '29 Temple', 'Thanh Khê', 'Đà Nẵng', '5 - 2,500', 'Nguyenvana', '94839200', 'Nhà ở', 3000.00, 2500.00, '50k/ tháng phí dịch vụ, 100k/ 1 năm phí cơ sở vật chất', 'Đầy đủ cơ sở vật chất hạ tầng, tiện nghi và môi trường văn hoá lành mạnh.', '2025-02-27 22:33:17', NULL, 1),
(32, 2, 'Toà nhà Vincom Plaza', '93 Lê Văn Việt', 'Quận 5', 'Hồ Chí Minh', '10 - 10', 'Việt Cương', '0938412583', 'Căn hộ/ Chung cư', 3500.00, 3000.00, '50k/ tháng phí dịch vụ, 100k/ 1 năm phí cơ sở vật chất', 'Có ban công, tủ lạnh, máy giặc, máy lạnh.', '2025-02-28 00:34:23', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `management_income`
--

CREATE TABLE `management_income` (
  `management_income_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `month` tinyint(2) DEFAULT NULL,
  `commission` decimal(10,2) DEFAULT NULL,
  `received_commission` decimal(10,2) DEFAULT NULL,
  `actual_income` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `building_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `building_id`, `booking_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(34, 1, NULL, NULL, 'Test thông báo nội bộ', '<p>Test <strong>dwqd thhoong</strong></p>', 'admin', 1, '2025-02-21 20:53:59'),
(41, 2, NULL, NULL, 'Nguyễn Văn A ơii', '<p>nghe gọi trả lời</p>', 'private', 1, '2025-02-25 09:19:54'),
(42, 2, NULL, NULL, 'Nguyễn Văn A ơii', '<p>nghe gọi trả lời</p>', 'private', 1, '2025-02-25 09:20:20'),
(43, 1, 31, NULL, NULL, 'Yêu cầu toà nhà \'Baboohouse\' đang chờ duyệt.', 'building', 1, '2025-02-27 22:33:24'),
(44, 2, 31, NULL, NULL, 'Toà nhà \'Baboohouse\' đã được duyệt.', 'status', 0, '2025-02-27 22:34:46'),
(51, 1, 32, NULL, NULL, 'Yêu cầu toà nhà \'Toà nhà Vincom Plaza\' đang chờ duyệt.', 'building', 0, '2025-02-28 00:34:32'),
(52, 2, 32, NULL, NULL, 'Toà nhà \'Toà nhà Vincom Plaza\' đã được duyệt.', 'status', 0, '2025-02-28 00:36:22');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `building_id` int(11) DEFAULT NULL,
  `room_name` varchar(255) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `rental_price` decimal(10,2) DEFAULT NULL,
  `room_type` varchar(50) DEFAULT NULL,
  `room_status` varchar(50) DEFAULT NULL,
  `photo_urls` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `building_id`, `room_name`, `area`, `rental_price`, `room_type`, `room_status`, `photo_urls`) VALUES
(55, 31, 'Phòng 34', 40.00, 5.00, 'Phòng đơn', 'Còn trống', '[]'),
(56, 31, 'Phòng 102', 40.00, 2500.00, 'Phòng đôi', 'Còn trống', NULL),
(57, 32, 'B102', 50.00, 10.00, 'Phòng đơn', 'Còn trống', '[]'),
(58, 32, 'C305', 50.00, 10.00, 'Phòng đôi', 'Còn trống', '[]');

-- --------------------------------------------------------

--
-- Table structure for table `sale_income`
--

CREATE TABLE `sale_income` (
  `sale_income_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `commission` decimal(10,2) DEFAULT NULL,
  `received_commission` decimal(10,2) DEFAULT NULL,
  `actual_income` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `hometown` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `last_access` datetime DEFAULT NULL,
  `photo_urls` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `address`, `hometown`, `birthdate`, `phone`, `email`, `username`, `password`, `role`, `last_access`, `photo_urls`) VALUES
(1, 'Quản Trị Viên', '123 Đường Nguyễn Thị Minh Khai, Phường 6, Quận 3, Hồ Chí Minh', 'Hồ Chí Minh', '1985-02-15', '0901234567', 'admin@baboohouse.vn', 'admin', 'hashed_password', 'admin', '2025-02-27 23:21:32', NULL),
(2, 'Nguyễn Văn A', '456 Đường Lê Lai, Phường Bến Nghé, Quận 1, Hồ Chí Minh', 'Hà Nội', '1990-01-01', '0912345678', 'a.nguyen@example.com', 'nguyenvana', 'matkhau123', 'user', '2025-02-28 09:58:13', NULL),
(3, 'Trần Thị B', '789 Đường Trần Hưng Đạo, Phường 7, Quận 5, Hồ Chí Minh', 'Đà Nẵng', '1988-03-15', '0923456789', 'b.tran@example.com', 'tranthib', 'matkhau123', 'user', '2025-02-28 09:58:23', NULL),
(4, 'Lê Văn C', '321 Đường Nguyễn Thái Bình, Phường 12, Quận 3, Hồ Chí Minh', 'Hải Phòng', '1995-05-25', '0934567890', 'c.le@example.com', 'levanc', 'matkhau123', 'user', '2025-02-20 02:45:35', NULL),
(5, 'Phạm Thị D', '654 Đường Nguyễn Huệ, Phường Bến Nghé, Quận 1, Hồ Chí Minh', 'Nha Trang', '1992-07-30', '0945678901', 'd.pham@example.com', 'phamthid', 'matkhau123', 'user', '2025-02-20 02:45:35', NULL),
(6, 'Đỗ Văn E', '987 Đường Lê Lợi, Phường 6, Quận 1, Hồ Chí Minh', 'Vũng Tàu', '1989-09-20', '0956789012', 'e.do@example.com', 'dovanE', 'matkhau123', 'user', '2025-02-20 02:45:35', NULL),
(7, 'Ngô Thị F', '135 Đường Hùng Vương, Phường 10, Quận 5, Hồ Chí Minh', 'Cần Thơ', '1991-11-11', '0967890123', 'f.ngo@example.com', 'ngothif', 'matkhau123', 'user', '2025-02-20 02:45:35', NULL),
(8, 'Bùi Văn G', '246 Đường Võ Văn Kiệt, Phường 2, Quận 1, Hồ Chí Minh', 'Long An', '1993-12-12', '0978901234', 'g.bui@example.com', 'buivang', 'matkhau123', 'user', '2025-02-20 02:45:35', NULL),
(9, 'Vũ Thị H', '369 Đường Lê Văn Sỹ, Phường 13, Quận 3, Hồ Chí Minh', 'Tiền Giang', '1994-04-04', '0989012345', 'h.vu@example.com', 'vuthih', 'matkhau123', 'user', '2025-02-20 02:45:35', NULL),
(10, 'Nguyễn Văn I', '159 Đường Trần Quốc Thảo, Phường 9, Quận 3, Hồ Chí Minh', 'Ninh Thuận', '1987-06-06', '0990123456', 'i.nguyen@example.com', 'nguyenvani', 'matkhau123', 'user', '2025-02-20 02:45:35', NULL),
(11, 'Lê Thị K', '753 Đường Nguyễn Văn Cừ, Phường 2, Quận 5, Hồ Chí Minh', 'Bạc Liêu', '1996-08-08', '0901234567', 'k.le@example.com', 'lethik', 'matkhau123', 'user', '2025-02-20 02:45:35', NULL),
(12, 'Quốc Đạt', '24 Tôn Thất Thuyết, Hải Châu, Đà Nẵng', 'Đà Nẵng', '2024-12-02', '0938412583', 'quocdat1202@gmail.com', 'manager', 'matkhau123', 'manager', '2025-02-28 00:34:51', NULL);


CREATE TABLE `contracts_payment` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `year` smallint(4) DEFAULT NULL,
  `month` tinyint(2) DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Chưa thanh toán',
  `value` decimal(10,2) DEFAULT NULL,
  `created_date` datetime DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indexes for dumped tables
--

ALTER TABLE `contracts_payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);
--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `building_id` (`building_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `buildings`
--
ALTER TABLE `buildings`
  ADD PRIMARY KEY (`building_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `management_income`
--
ALTER TABLE `management_income`
  ADD PRIMARY KEY (`management_income_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `building_id` (`building_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD KEY `building_id` (`building_id`);

--
-- Indexes for table `sale_income`
--
ALTER TABLE `sale_income`
  ADD PRIMARY KEY (`sale_income_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `buildings`
--
ALTER TABLE `buildings`
  MODIFY `building_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `management_income`
--
ALTER TABLE `management_income`
  MODIFY `management_income_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `sale_income`
--
ALTER TABLE `sale_income`
  MODIFY `sale_income_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_income`
--
ALTER TABLE `contracts_payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;


--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`building_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `buildings`
--
ALTER TABLE `buildings`
  ADD CONSTRAINT `buildings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `management_income`
--
ALTER TABLE `management_income`
  ADD CONSTRAINT `management_income_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `management_income_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`building_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`building_id`) ON DELETE CASCADE;

--
-- Constraints for table `contracts_payment`
--
ALTER TABLE `contracts_payment`
  ADD CONSTRAINT `contracts_payment_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;


--
-- Constraints for table `sale_income`
--
ALTER TABLE `sale_income`
  ADD CONSTRAINT `sale_income_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_income_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
