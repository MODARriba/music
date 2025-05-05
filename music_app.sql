-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 05:11 AM
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
-- Database: `music_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `playlists`
--

CREATE TABLE `playlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlists`
--

INSERT INTO `playlists` (`id`, `user_id`, `name`, `created_at`) VALUES
(1, 4, 'Fav', '2025-04-24 15:56:52');

-- --------------------------------------------------------

--
-- Table structure for table `playlist_songs`
--

CREATE TABLE `playlist_songs` (
  `id` int(11) NOT NULL,
  `playlist_id` int(11) DEFAULT NULL,
  `song_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlist_songs`
--

INSERT INTO `playlist_songs` (`id`, `playlist_id`, `song_id`) VALUES
(3, 1, 13),
(4, 1, 16),
(5, 1, 15);

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `artist` varchar(100) DEFAULT NULL,
  `length` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cover_image` varchar(255) DEFAULT NULL,
  `audio_file` varchar(255) DEFAULT NULL,
  `music_file` varchar(255) DEFAULT NULL,
  `pinned` tinyint(1) DEFAULT 0,
  `liked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`id`, `user_id`, `title`, `artist`, `length`, `created_at`, `cover_image`, `audio_file`, `music_file`, `pinned`, `liked`) VALUES
(8, 4, ' Professional Rapper ', 'Lil Dicky - (Feat. Snoop Dogg)', '5:54', '2025-04-23 10:18:45', 'uploads/6808be8507f68_download.jpg', NULL, 'uploads/6808be850819e_68022ceb89231_Lil Dicky - Professional Rapper (Feat. Snoop Dogg)-yt.savetube.me.mp3', 0, 0),
(9, 4, 'Panini', 'Lil Nas X ', '2:24', '2025-04-23 10:21:18', 'uploads/6808bf1e57ab2_download (1).jpg', NULL, 'uploads/6808bf1e57d99_Lil Nas X - Panini (Official Video).mp3', 0, 0),
(10, 4, 'Greece', 'Drake', '3:38', '2025-04-24 06:50:08', 'uploads/6809df201fbcb_DJ_Khaled_Greece.jpg', NULL, 'uploads/6809df202076c_DJ Khaled ft. Drake - GREECE (Official Visualizer).mp3', 0, 1),
(11, 4, 'Timeless', 'The Weeknd, Playboi Carti', '4:15', '2025-04-24 06:52:33', 'uploads/6809dfb19f109_maxresdefault.jpg', NULL, 'uploads/6809dfb19f8ec_The Weeknd, Playboi Carti - Timeless (Official Lyric Video).mp3', 0, 0),
(12, 4, 'Fair Trade', 'Drake  ft. Travis Scott', '3:45', '2025-04-24 10:52:58', 'uploads/680a180a29532_download.jpg', NULL, 'uploads/680a180a2988c_Drake - Fair Trade (Audio) ft. Travis Scott.mp3', 0, 0),
(13, 4, '16', 'Baby Kheem', '3:45', '2025-04-24 10:54:43', 'uploads/680a1873057fb_download (1).jpg', NULL, 'uploads/680a1873059c5_Baby Keem - 16 (Lyrics).mp3', 0, 1),
(14, 4, 'Die Trying', 'Drake', '3:45', '2025-04-24 10:56:27', 'uploads/680a18dbdac11_download (2).jpg', NULL, 'uploads/680a18dbdae9a_PARTYNEXTDOOR & DRAKE - DIE TRYING.mp3', 0, 0),
(15, 4, 'Cant take a Joke', 'Drake', '3:45', '2025-04-24 10:58:30', 'uploads/680a1956ba826_download (3).jpg', NULL, 'uploads/680a1956baada_Drake - Can\'t Take a Joke (Lyrics).mp3', 1, 0),
(16, 4, 'Dead and cold', 'powfu', '3:45', '2025-04-24 17:26:03', 'uploads/680a742b81d02_download (4).jpg', NULL, 'uploads/680a742b82053_Dead and Cold.mp3', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Mode', 'recoverymodar44@gmail.com', '$2y$10$vie6tS3NiG3Md80oCZnDy.5Uas7DMOWe3ZMofAKe2dSVNEZn9SxqC', '2025-04-07 20:24:13'),
(2, 'rahul', 'recoverymodar44@gmail.com', '$2y$10$hR9kWu5p3izMvr8RouQLu.AKjbgTh.HKF94C/NzRAYkw0lv0sapdi', '2025-04-07 20:27:29'),
(3, 'sam', 'recoverymodar44@gmail.com', '$2y$10$YkLeLxv7AGFTJjKxLSBx0u9Jzwu16Wt2nwg0OK461YWef3PQ6pdEq', '2025-04-07 20:39:24'),
(4, 'samAltman', 'sam@gmail.com', '$2y$10$USagxpnKK7CqoYkGdGFewuq74E3bVwOO5phrgk7rPCpw4yz9phzQ2', '2025-04-07 20:40:24'),
(5, 'kamal', 'kamal@gmail.com', '$2y$10$7hsBX1cQLIVO4pdNrWKLOeU40JcaPy/E1bQHT7qoWvK6khygYMcaS', '2025-04-25 06:05:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `playlist_songs`
--
ALTER TABLE `playlist_songs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `playlists`
--
ALTER TABLE `playlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `playlist_songs`
--
ALTER TABLE `playlist_songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
