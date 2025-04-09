-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2025 at 11:10 AM
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
-- Database: `linkup`
--

-- --------------------------------------------------------

--
-- Table structure for table `ban`
--

CREATE TABLE `ban` (
  `BanID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `BanDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ban`
--

INSERT INTO `ban` (`BanID`, `UserID`, `BanDate`) VALUES
(51, 7, '2025-03-29 05:03:51');

-- --------------------------------------------------------

--
-- Table structure for table `block`
--

CREATE TABLE `block` (
  `BlockID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `BlockedUserId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `block`
--

INSERT INTO `block` (`BlockID`, `UserID`, `BlockedUserId`) VALUES
(1, 9, 1),
(2, 1, 9);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `CommentID` int(11) NOT NULL,
  `PostID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `date` text NOT NULL,
  `comment` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`CommentID`, `PostID`, `UserID`, `date`, `comment`) VALUES
(1, 12, 10, '2025-03-23 18:38:42', 's'),
(2, 12, 10, '2025-03-23 18:42:07', 'dsd'),
(3, 12, 10, '2025-03-23 18:42:07', 'dsd'),
(4, 11, 10, '2025-03-23 18:42:37', 'ds'),
(5, 11, 10, '2025-03-23 18:42:37', 'ds'),
(6, 10, 10, '2025-03-23 18:42:45', 'a'),
(7, 10, 10, '2025-03-23 18:42:45', 'a'),
(8, 10, 10, '2025-03-23 18:43:48', 's'),
(9, 10, 10, '2025-03-23 18:43:48', 's'),
(10, 10, 10, '2025-03-23 18:44:38', 'a'),
(11, 10, 10, '2025-03-23 18:44:38', 'a'),
(12, 10, 10, '2025-03-23 18:45:33', 'd'),
(13, 10, 10, '2025-03-23 18:45:33', 'd'),
(14, 10, 10, '2025-03-23 18:46:15', 's'),
(15, 10, 10, '2025-03-23 18:46:15', 's'),
(16, 10, 10, '2025-03-23 18:47:01', 'c'),
(17, 9, 10, '2025-03-23 18:47:20', 'q'),
(18, 14, 4, '2025-03-24 11:39:38', 'hgh'),
(19, 14, 4, '2025-03-24 11:40:27', 'hello'),
(20, 14, 4, '2025-03-24 11:50:07', 'ss'),
(21, 14, 4, '2025-03-24 11:50:09', 'ss'),
(22, 14, 4, '2025-03-24 11:50:10', 'ss'),
(23, 14, 4, '2025-03-24 11:50:11', 'ss'),
(50, 18, 4, '2025-03-24', 'MARSHMELLOW'),
(54, 14, 4, '2025-03-24', 'good'),
(55, 14, 4, '2025-03-24', 'g'),
(57, 17, 4, '2025-03-24', 'hi'),
(59, 14, 4, '2025-03-24', 'hi'),
(60, 14, 4, '2025-03-24', 'ii'),
(79, 14, 4, '2025-03-27 12:27:32', 'jb'),
(80, 16, 4, '2025-03-27 12:30:52', 'k'),
(81, 14, 4, '2025-03-27 13:13:35', 'ay'),
(83, 14, 4, '2025-03-27 13:15:38', 'hh'),
(84, 14, 4, '2025-03-27 13:15:43', 'hh'),
(85, 14, 4, '2025-03-27 13:15:49', 'oifhesfkjdsfqj'),
(87, 10, 8, '2025-03-27 13:18:22', 'cb'),
(97, 10, 8, '2025-03-27', 'sasas'),
(98, 20, 8, '2025-03-30 15:56:49', 'a'),
(99, 7, 8, '2025-03-30 15:58:17', 'jk'),
(100, 20, 8, '2025-03-30 16:10:23', 'a'),
(101, 20, 8, '2025-03-30 16:51:21', 'b'),
(105, 8, 8, '2025-03-30 17:03:58', 'hell lo'),
(106, 8, 8, '2025-03-30 17:05:14', 'a'),
(107, 8, 8, '2025-03-30 17:05:16', 'b'),
(108, 8, 8, '2025-03-30 17:05:17', 'a'),
(109, 8, 8, '2025-03-30 17:05:17', 'v'),
(110, 8, 8, '2025-03-30 17:05:18', 'd'),
(111, 8, 8, '2025-03-30 17:05:19', 'e');

-- --------------------------------------------------------

--
-- Table structure for table `deactivation`
--

CREATE TABLE `deactivation` (
  `DeactivationID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `DeactivationStart` datetime NOT NULL,
  `DeactivationEnd` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filter`
--

CREATE TABLE `filter` (
  `FilterID` int(11) NOT NULL,
  `PostID` int(11) DEFAULT NULL,
  `BlacklistedWord` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `filter`
--

INSERT INTO `filter` (`FilterID`, `PostID`, `BlacklistedWord`) VALUES
(26, NULL, 'stupid'),
(29, NULL, 'shit'),
(30, NULL, 'bad'),
(31, NULL, 'black');

-- --------------------------------------------------------

--
-- Table structure for table `follow`
--

CREATE TABLE `follow` (
  `FollowID` int(11) NOT NULL,
  `FollowingID` int(11) NOT NULL,
  `FollowStatus` varchar(50) NOT NULL,
  `FollowerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follow`
--

INSERT INTO `follow` (`FollowID`, `FollowingID`, `FollowStatus`, `FollowerID`) VALUES
(2, 9, 'approved', 8);

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `LikeID` int(11) NOT NULL,
  `PostID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`LikeID`, `PostID`, `UserID`) VALUES
(1, 10, 10),
(14, 9, 4),
(23, 14, 8),
(31, 20, 4),
(32, 11, 4),
(44, 17, 4),
(58, 16, 4),
(80, 10, 8),
(81, 12, 8),
(83, 7, 8),
(90, 9, 8),
(91, 8, 8),
(92, 20, 8);

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `PostID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `fileImage` varchar(255) NOT NULL,
  `date` text NOT NULL,
  `captionText` varchar(200) NOT NULL,
  `titleText` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`PostID`, `UserID`, `fileImage`, `date`, `captionText`, `titleText`) VALUES
(7, 2, '119709175_p0_master1200.png', '2025-03-22', 'q', 'ww'),
(8, 2, 'Bron.jpg', '2025-03-22', 'q', 'q'),
(9, 2, 'profile.jpg', '2025-03-23', 'ds', 'd'),
(10, 10, 'image_2025-03-23_221803612.png', '2025-03-23', 's', 's'),
(11, 10, 'stare.gif', '2025-03-23', 'd', 'xd'),
(12, 10, 'huh.jpg', '2025-03-23', 'd', 'd'),
(14, 4, 'j8bEWn2E9uo-HD.jpg', '2025-03-24', 'flow', 'Wake Up'),
(16, 4, 'Designer.jpeg', '2025-03-24', 'design', 'Designer'),
(17, 4, 'hqdefault.png', '2025-03-24', 'how', 'What'),
(18, 4, 'id2.jpg', '2025-03-24', 'run', 'Let\'s go'),
(20, 8, 'Bron.jpg', '2025-03-24', '123', '123');

-- --------------------------------------------------------

--
-- Table structure for table `reportedpost`
--

CREATE TABLE `reportedpost` (
  `ReportPostID` int(11) NOT NULL,
  `PostID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Reason` text NOT NULL,
  `ReportDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reportedpost`
--

INSERT INTO `reportedpost` (`ReportPostID`, `PostID`, `UserID`, `Reason`, `ReportDate`) VALUES
(13, 10, 1, '11', '2025-03-27 03:30:47'),
(14, 11, 1, '11', '2025-03-27 03:30:52'),
(15, 14, 4, '111', '2025-03-27 03:35:03'),
(16, 20, 1, 'bad', '2025-03-29 12:44:33');

-- --------------------------------------------------------

--
-- Table structure for table `reporteduser`
--

CREATE TABLE `reporteduser` (
  `ReportUserID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `ReportedUserID` int(11) NOT NULL,
  `Reason` text NOT NULL,
  `ReportDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `screentimereminder`
--

CREATE TABLE `screentimereminder` (
  `ScreenTimeReminderID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Duration` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(128) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `blocked_users` varchar(128) DEFAULT NULL,
  `manage_visibility` varchar(128) NOT NULL DEFAULT 'public',
  `screen_time_reminder` time DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password`, `reset_token_hash`, `reset_token_expires_at`, `profile_picture`, `bio`, `status`, `blocked_users`, `manage_visibility`, `screen_time_reminder`, `role`) VALUES
(1, 'Ian', 'Ianlim5', 'ianlim575@gmail.com', '$2y$10$FST3.w1T.yxnfTDQIjJJxemHI7MNT6bdz8XH9hp3wDGAZRlfo/hMO', NULL, NULL, NULL, NULL, 'active', NULL, 'public', '00:01:00', 'user'),
(2, 'Ian Lim', 'ianlim05', 'TP073235@mail.apu.edu.my', '$2y$10$dkcDEj527eG/V4z6/rPrhe61P8hBhtne1WWYMgoXOsciiO0sTpFJ2', NULL, NULL, NULL, NULL, 'active', NULL, 'public', NULL, 'user'),
(4, 'Gay', 'ian123', 'gaykun@email.com', '$2y$10$.o7FVg7MbXs90S0n8aZA4OHLJH8/Aqi/3tRJ205slgo7cjEqpLuFq', NULL, NULL, 'profile.jpg', 'OKOKOKKOOK', 'active', NULL, 'private', '00:01:00', 'user'),
(6, '', 'admin', '', '$2y$10$iyC3h/fq64R2V2/2WX.M7eMgwvDDaI7r3a8O1/kzlDuctMCM5Mi1G', NULL, NULL, NULL, NULL, 'active', NULL, 'public', NULL, 'admin'),
(7, 'hi', 'hi', 'dyasingh2006@gmail.com', '$2y$10$zWAuGWRrYaKohaAxujC//uBA.0weLxLp.ThF5MtmQg/y9TkE29NXe', '29115d5444a2a55076896a75f5a743ed7f5707f32f82e4ddcdac9b044f44914e', '2025-03-24 13:33:35', NULL, NULL, 'active', NULL, 'public', NULL, 'user'),
(8, 'Chan', 'Chan', 'jun20050604@gmail.com', '$2y$10$h6W2VfzVdGC0.Ug5O/9Mde2CJ9EMbPWqDnYF5v55o.xw6jJd66lj.', NULL, NULL, NULL, NULL, 'active', NULL, 'public', NULL, 'user'),
(9, 'momo', 'momo', 'doanhlam0809@hotmail.com', '$2y$10$AmJbo/zHB/IVwATIXOynT.lXgvMG.oJfuP7yTrJEJ1wW.OHoVOKN6', NULL, NULL, NULL, NULL, 'active', NULL, 'public', NULL, 'user'),
(10, 'Ang Kuan Hern', 'Hermen', 'itzme0822@gmail.com', '$2y$10$WkP3DYjFM3/iIrR1Chp8K.wjNi.WiHnDQKUFqZ4TFheu7xe5IPy1e', NULL, NULL, NULL, NULL, 'active', NULL, 'public', NULL, 'user'),
(11, 'Tan', 'tan12', 'tan@gmail.com', '$2y$10$w9Aep85OoWERoMid1hHBXu9Z9bOMRgDj9YVdZXkeulp9W30xVJS5.', NULL, NULL, NULL, NULL, 'active', NULL, 'public', NULL, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `warning`
--

CREATE TABLE `warning` (
  `WarningID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `WarningDate` datetime NOT NULL,
  `WarningReason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warning`
--

INSERT INTO `warning` (`WarningID`, `UserID`, `WarningDate`, `WarningReason`) VALUES
(4, 4, '2025-03-24 01:08:34', 'HI YOUR NAME NO GOD'),
(5, 4, '2025-03-24 17:57:30', 'HI YOUR NAME NO GOD'),
(6, 4, '2025-03-24 17:57:59', '12121');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ban`
--
ALTER TABLE `ban`
  ADD PRIMARY KEY (`BanID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `block`
--
ALTER TABLE `block`
  ADD PRIMARY KEY (`BlockID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`CommentID`),
  ADD KEY `PostID` (`PostID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `deactivation`
--
ALTER TABLE `deactivation`
  ADD PRIMARY KEY (`DeactivationID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `filter`
--
ALTER TABLE `filter`
  ADD PRIMARY KEY (`FilterID`),
  ADD KEY `PostID` (`PostID`);

--
-- Indexes for table `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`FollowID`),
  ADD KEY `FollowingID` (`FollowingID`),
  ADD KEY `FollowerID` (`FollowerID`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`LikeID`),
  ADD KEY `PostID` (`PostID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`PostID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `reportedpost`
--
ALTER TABLE `reportedpost`
  ADD PRIMARY KEY (`ReportPostID`),
  ADD KEY `PostID` (`PostID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `reporteduser`
--
ALTER TABLE `reporteduser`
  ADD PRIMARY KEY (`ReportUserID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `ReportedUserID` (`ReportedUserID`);

--
-- Indexes for table `screentimereminder`
--
ALTER TABLE `screentimereminder`
  ADD PRIMARY KEY (`ScreenTimeReminderID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reset_token_hash` (`reset_token_hash`);

--
-- Indexes for table `warning`
--
ALTER TABLE `warning`
  ADD PRIMARY KEY (`WarningID`),
  ADD KEY `UserID` (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ban`
--
ALTER TABLE `ban`
  MODIFY `BanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `block`
--
ALTER TABLE `block`
  MODIFY `BlockID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `CommentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `deactivation`
--
ALTER TABLE `deactivation`
  MODIFY `DeactivationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `filter`
--
ALTER TABLE `filter`
  MODIFY `FilterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `follow`
--
ALTER TABLE `follow`
  MODIFY `FollowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `LikeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `PostID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `reportedpost`
--
ALTER TABLE `reportedpost`
  MODIFY `ReportPostID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reporteduser`
--
ALTER TABLE `reporteduser`
  MODIFY `ReportUserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `warning`
--
ALTER TABLE `warning`
  MODIFY `WarningID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ban`
--
ALTER TABLE `ban`
  ADD CONSTRAINT `ban_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`);

--
-- Constraints for table `block`
--
ALTER TABLE `block`
  ADD CONSTRAINT `block_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`PostID`) REFERENCES `post` (`PostID`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`);

--
-- Constraints for table `deactivation`
--
ALTER TABLE `deactivation`
  ADD CONSTRAINT `deactivation_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`);

--
-- Constraints for table `filter`
--
ALTER TABLE `filter`
  ADD CONSTRAINT `filter_ibfk_1` FOREIGN KEY (`PostID`) REFERENCES `post` (`PostID`);

--
-- Constraints for table `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `follow_ibfk_1` FOREIGN KEY (`FollowingID`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `follow_ibfk_2` FOREIGN KEY (`FollowerID`) REFERENCES `users` (`id`);

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`PostID`) REFERENCES `post` (`PostID`),
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`);

--
-- Constraints for table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`);

--
-- Constraints for table `reportedpost`
--
ALTER TABLE `reportedpost`
  ADD CONSTRAINT `reportedpost_ibfk_1` FOREIGN KEY (`PostID`) REFERENCES `post` (`PostID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportedpost_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reporteduser`
--
ALTER TABLE `reporteduser`
  ADD CONSTRAINT `reporteduser_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reporteduser_ibfk_2` FOREIGN KEY (`ReportedUserID`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `screentimereminder`
--
ALTER TABLE `screentimereminder`
  ADD CONSTRAINT `screentimereminder_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`);

--
-- Constraints for table `warning`
--
ALTER TABLE `warning`
  ADD CONSTRAINT `warning_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
