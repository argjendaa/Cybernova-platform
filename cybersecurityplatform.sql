-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 06:31 PM
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
-- Database: `cybersecurityplatform`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `user_id`, `message`, `level`, `created_at`) VALUES
(1, 1, 'Suspicious login detected', 'High', '2026-04-22 19:18:24'),
(2, 1, 'Weak password detected', 'Medium', '2026-04-22 19:18:24'),
(3, 1, 'Firewall disabled', 'Critical', '2026-04-22 19:18:24');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `report_name` varchar(255) DEFAULT NULL,
  `result` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `report_name`, `result`, `created_at`) VALUES
(1, 1, 'Weekly Security Report', 'System is mostly secure with minor issues', '2026-04-22 19:18:24');

-- --------------------------------------------------------

--
-- Table structure for table `scans`
--

CREATE TABLE `scans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scans`
--

INSERT INTO `scans` (`id`, `user_id`, `website`, `status`, `score`, `scanned_at`) VALUES
(1, 1, 'example.com', 'Completed', 85, '2026-04-22 19:18:24'),
(2, 1, 'testsite.com', 'Completed', 72, '2026-04-22 19:18:24'),
(3, 1, 'site1.com', 'Completed', 80, '2026-04-20 21:01:52'),
(4, 1, 'site1.com', 'Completed', 82, '2026-04-21 21:01:52'),
(5, 1, 'site1.com', 'Completed', 78, '2026-04-22 21:01:52'),
(6, 1, 'site1.com', 'Completed', 85, '2026-04-23 21:01:52'),
(7, 1, 'site1.com', 'Completed', 88, '2026-04-24 21:01:52'),
(8, 1, 'site1.com', 'Completed', 90, '2026-04-25 21:01:52'),
(9, 1, 'site1.com', 'Completed', 92, '2026-04-26 21:01:52');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `notifications` tinyint(1) DEFAULT 1,
  `theme` varchar(50) DEFAULT 'dark'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `user_id`, `notifications`, `theme`) VALUES
(1, 1, 1, 'dark');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` text NOT NULL,
  `username` text NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `verified` tinyint(4) DEFAULT 0,
  `token` varchar(255) DEFAULT NULL,
  `is_verified` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `business_name`, `email`, `password`, `created_at`, `name`, `username`, `first_name`, `last_name`, `phone`, `verified`, `token`, `is_verified`) VALUES
(1, 'CyberNova LLC', 'admin@cyber.com', '$2y$10$examplehash', '2026-04-22 19:18:24', '', '', NULL, NULL, NULL, 0, NULL, 0),
(2, 'dfghtrhjryjfryjdfghsdfv', 'argjenda@gmail.com', '$2y$10$5ar4uF2qqGpWMsjXzLmmdOWSiYxA1vAsY/W4vk8udZNK6WXT0Hbwi', '2026-04-25 22:18:06', '', '', NULL, NULL, NULL, 0, NULL, 0),
(3, 'zsxdcfghjukilo;p', 'istrefiargjenda6@gmail.com', '$2y$10$9KJ7mfWbspP8sA4WYIR/I.VXTo3DV2KBNenlLrdtPeD4abEnVb1CC', '2026-04-26 21:04:02', '', '', NULL, NULL, NULL, 0, NULL, 0),
(4, 'xcvbnm,./', 'zxcfvgbhnj@gmail.com', '$2y$10$8cdcQ4V7zn3gomiMkw1VO.HDU6.cx2hXZsxyCSwPROO/R9T3HEPZy', '2026-04-26 21:07:56', '', '', NULL, NULL, NULL, 0, NULL, 0),
(5, 'zdcvdbgfjgy', 'asdf@gmail.com', '$2y$10$xhI1dBoXPgjVDGBuYPmOju5JiULuI0G7YbMkdclbdIg0DpMB6.LSO', '2026-04-26 21:09:41', '', '', NULL, NULL, NULL, 0, NULL, 0),
(6, 'dfghtrhjryjfryjdfghsdfv', 'sdfghjk@gmail.com', '$2y$10$bPWHp9Rirwi61M/2l/dGzuo104rNcn0/i6K2IYu/XbIcRgAWROqFe', '2026-04-26 22:06:53', '', '', NULL, NULL, NULL, 0, NULL, 0),
(7, 'cyberNova', 'argjenda6@gmail.com', '$2y$10$8K8z.C1pyCefGvE8pPQ0YO4cWm59hFar7JMoLNSrJsKtCOsLFFVyy', '2026-04-27 10:01:38', '', '', 'Argjenda', 'Istrefi', '1234567890', 0, 'aae88460b46199abf62ea1a451ff147d87803de93524fb9b1fa0b2241863f1e0', 0),
(8, 'xcvbnm', 'istrefi@gmail.com', '$2y$10$4izPNkBlf7.qYocliZBphOzA5Nr3La/xTzQxkadINPk7./bORJwAK', '2026-04-27 22:39:28', '', '', 'azsxdfghjk', 'sdfghjklsdfghjk', '0987654321', 0, 'a4067b238a88d78607df5e51b13a86849319d4dedfc9dc4b0c61872570cf5372', 0),
(9, 'lkyhgvfdcxsxs', 'istref@gmail.com', '$2y$10$LbcROSlZ3L1py1PIbqWtkubfG/WIvcK5RIEz8u0gIXwcewPsg5zoG', '2026-04-27 22:42:15', '', '', 'Argjenda', 'Istrefi', '1234876543', 0, 'b9148a232a045cfa6b84fe76d65c39e40896cbd534dc24afd67108c7f6e4273e', 0),
(10, 'CyberNova-platform', 'istrefiar@gmail.com', '$2y$10$Gqtd4UZ4gZ/Qg8hE7NOfrek0czFrJvOKzyx3GND2MiMlwSDus0hE2', '2026-04-27 22:43:29', '', '', 'azsxdfghjk', 'sdfghjklsdfghjk', '0987654321', 0, 'a2de65e254aba035a4267dee969deb77ed9f2783926cd69ad0873a38dcce8af3', 0),
(11, 'xvsdgrsbrsfb', 'ist@gmail.com', '$2y$10$P45jwupA.6kdfukQMO239uYHHX4/1yctkiH2rCDyHDJZfuOQSKzAq', '2026-04-27 22:47:33', '', '', 'Argjenda', 'Istrefi', '1234567890', 0, '6961b1b8a2b0416f530ec92686e1b21a818c5fbb7b009775ea39c63e09826ca0', 0),
(12, 'xsckldvfngldjfd', 'argjendaaaaa@gmail.com', '$2y$10$6ty9e1l3T30kHxABWKQWoudnGMecceoGBPhGAVDkGzfHGkbROCNPW', '2026-04-27 22:49:27', '', '', 'Argjenda', 'Istrefi', '234567898765432', 1, NULL, 0),
(13, 'CyberNova-platform', 'argjendaa@gmail.com', '$2y$10$seLgNPqiUY8zwcKemGNpfes25ubmKmFvXFfDBlfDYrwlr2S.jV/IG', '2026-04-27 22:51:40', '', '', 'argjenda', 'istrefi', '234567898765432', 1, NULL, 0),
(14, 'dfghtrhjryjfryjdfghsdfv', 'aris@gmail.com', '$2y$10$FFEltx1nrBQb8ZOMDx2hMeAJ7J4kT.Y1yfQkhghJSBP4AYPOlEg2S', '2026-04-28 15:49:45', '', '', 'Argjenda', 'Istrefi', '1234567890', 0, NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `scans`
--
ALTER TABLE `scans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `scans`
--
ALTER TABLE `scans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scans`
--
ALTER TABLE `scans`
  ADD CONSTRAINT `scans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
