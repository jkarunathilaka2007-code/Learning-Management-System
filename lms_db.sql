-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2026 at 06:50 AM
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
-- Database: `lms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `about`
--

CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `desktop_banner` varchar(255) DEFAULT NULL,
  `mobile_banner` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `group_links` text DEFAULT NULL,
  `timetable` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about`
--

INSERT INTO `about` (`id`, `desktop_banner`, `mobile_banner`, `profile_pic`, `bio`, `address`, `contact_number`, `email`, `group_links`, `timetable`, `updated_at`) VALUES
(2, 'uploads/about/1771550222_Gemini_Generated_Image_qdgrwxqdgrwxqdgr.png', 'uploads/about/1771550222_Gemini_Generated_Image_u76za0u76za0u76z.png', 'uploads/about/1771550222_OIP.webp', 'Supun Dulanga (Bsc) is a prominent ICT educator in Sri Lanka, specializing in the Advanced Level (A/L) curriculum. Offering classes in both English and Sinhala mediums, he is recognized for his expert teaching methods. His goal is to empower students with essential technology skills and academic excellence in Information and Communication Technology.', 'No6,Colombo Road , Kandy', '0745698247', 'supun@gmail.com', '[{\"media\":\"whatsapp\",\"town\":\"Bandarawela\",\"group_name\":\"2026 Theory\",\"url\":\"https://chat.whatsapp.com/Jm52UanW5c7CfB2L4Wp2kK\"},{\"media\":\"whatsapp\",\"town\":\"Bandarawela\",\"group_name\":\"2026 Paper\",\"url\":\"https://chat.whatsapp.com/Jm52UanW5c7CfB2L4Wp2kK\"},{\"media\":\"whatsapp\",\"town\":\"Bandarawela\",\"group_name\":\"2026 Revesion\",\"url\":\"https://chat.whatsapp.com/Jm52UanW5c7CfB2L4Wp2kK\"},{\"media\":\"whatsapp\",\"town\":\"Bandarawela\",\"group_name\":\"2027 Theory\",\"url\":\"https://chat.whatsapp.com/Jm52UanW5c7CfB2L4Wp2kK\"}]', '[{\"year\":\"2026\",\"stream\":\"A/L\",\"subject\":\"ICT Theory Team 1\",\"day\":\"Fridaay\",\"time\":\"2.30pm - 5.30pm\"},{\"year\":\"2026\",\"stream\":\"A/L\",\"subject\":\"ICT Theory Team 2\",\"day\":\"Saturday\",\"time\":\"12.30pm - 3.30pm\"},{\"year\":\"2026\",\"stream\":\"A/L\",\"subject\":\"ICT Revesion\",\"day\":\"Wednesday\",\"time\":\"8.30am - 12.30pm\"},{\"year\":\"2026\",\"stream\":\"A/L\",\"subject\":\"ICT Paper\",\"day\":\"Saturday\",\"time\":\"5.30pm - 9.00pm\"}]', '2026-02-20 01:17:02');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'present',
  `date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `class_id`, `status`, `date`, `created_at`) VALUES
(1, 5, 6, 'present', '2026-02-18', '2026-02-18 03:06:21'),
(2, 4, 1, 'present', '2026-02-18', '2026-02-18 03:06:34'),
(3, 4, 2, 'present', '2026-02-18', '2026-02-18 03:23:29'),
(5, 5, 7, 'present', '2026-02-18', '2026-02-18 06:19:29'),
(6, 4, 2, 'present', '2026-02-19', '2026-02-19 03:16:50'),
(7, 8, 2, 'present', '2026-02-19', '2026-02-19 03:17:00'),
(8, 9, 2, 'present', '2026-02-19', '2026-02-19 03:17:05'),
(9, 10, 2, 'present', '2026-02-19', '2026-02-19 03:17:10'),
(10, 11, 2, 'present', '2026-02-19', '2026-02-19 03:17:14'),
(11, 12, 2, 'present', '2026-02-19', '2026-02-19 03:17:19'),
(12, 13, 2, 'present', '2026-02-19', '2026-02-19 03:17:23'),
(13, 14, 2, 'present', '2026-02-19', '2026-02-19 03:17:28'),
(14, 15, 2, 'present', '2026-02-19', '2026-02-19 03:17:32');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `exam_year` varchar(10) DEFAULT NULL,
  `stream` enum('O/L','A/L') DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `class_fee` decimal(10,2) DEFAULT 0.00,
  `fee_type` enum('day','monthly') DEFAULT 'monthly'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `teacher_id`, `exam_year`, `stream`, `subject`, `class_fee`, `fee_type`) VALUES
(1, 2, '2026', 'A/L', 'ICT Theory', 2200.00, 'monthly'),
(2, 2, '2026', 'A/L', 'ICT Paper', 350.00, 'day'),
(5, 2, '2026', 'A/L', 'ICT Revision', 2500.00, 'monthly'),
(6, 2, '2027', 'A/L', 'ICT Paper', 350.00, 'day'),
(7, 2, '2027', 'A/L', 'ICT Theory', 2200.00, 'monthly');

-- --------------------------------------------------------

--
-- Table structure for table `class_fees_payments`
--

CREATE TABLE `class_fees_payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_month` varchar(20) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_fees_payments`
--

INSERT INTO `class_fees_payments` (`id`, `student_id`, `class_id`, `teacher_id`, `amount`, `payment_month`, `payment_date`) VALUES
(7, 4, 1, 2, 2200.00, 'February', '2026-02-18 04:04:40'),
(8, 4, 2, 2, 350.00, 'February', '2026-02-18 04:21:26'),
(9, 4, 2, 2, 350.00, 'February', '2026-02-19 04:29:22'),
(10, 11, 2, 2, 350.00, 'February', '2026-02-19 03:15:25');

-- --------------------------------------------------------

--
-- Table structure for table `class_towns`
--

CREATE TABLE `class_towns` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `town` varchar(100) DEFAULT NULL,
  `institute_name` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_towns`
--

INSERT INTO `class_towns` (`id`, `teacher_id`, `town`, `institute_name`) VALUES
(1, 2, 'Bandarawela', 'Gurukula'),
(3, 2, 'Badulla', 'Minac');

-- --------------------------------------------------------

--
-- Table structure for table `exam_papers`
--

CREATE TABLE `exam_papers` (
  `paper_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `paper_name` varchar(255) NOT NULL,
  `max_marks` int(11) DEFAULT 100,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_papers`
--

INSERT INTO `exam_papers` (`paper_id`, `class_id`, `paper_name`, `max_marks`, `added_date`) VALUES
(1, 2, 'Paper1', 1000, '2026-02-18 06:36:37'),
(2, 2, 'Paper2', 1000, '2026-02-18 06:57:02');

-- --------------------------------------------------------

--
-- Table structure for table `live_classes`
--

CREATE TABLE `live_classes` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `admin_link` text DEFAULT NULL,
  `live_date` date NOT NULL,
  `live_time` time NOT NULL,
  `meeting_link` text NOT NULL,
  `media` varchar(50) DEFAULT NULL,
  `status` enum('Upcoming','Live','Ended') DEFAULT 'Upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `live_classes`
--

INSERT INTO `live_classes` (`id`, `teacher_id`, `class_id`, `topic`, `admin_link`, `live_date`, `live_time`, `meeting_link`, `media`, `status`, `created_at`) VALUES
(6, 2, 2, 'Logic gates | K-Map', 'https://us05web.zoom.us/meeting/81306015569?meetingMasterEventId=hHoAu6mZSlKSvubjm4BteQ', '2026-02-19', '12:30:00', 'https://us05web.zoom.us/j/81306015569?pwd=bh19PVkplAY7XZgzfKj0EGt67oA67y.1', 'Zoom', 'Upcoming', '2026-02-19 04:37:10'),
(7, 2, 2, 'Logic gates | K-Map', 'https://studio.youtube.com/video/zH30UN1aXik/livestreaming', '2026-02-19', '12:30:00', 'https://www.youtube.com/live/zH30UN1aXik?si=fP7sh1loivXXhAgv', 'YouTube', 'Upcoming', '2026-02-19 04:37:10'),
(8, 2, 2, 'Networking', 'https://us05web.zoom.us/meeting/81306015569?meetingMasterEventId=hHoAu6mZSlKSvubjm4BteQ', '2026-02-20', '11:30:00', 'https://us05web.zoom.us/j/81306015569?pwd=bh19PVkplAY7XZgzfKj0EGt67oA67y.1', 'YouTube', 'Upcoming', '2026-02-20 03:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `teacher_id`, `class_id`, `title`, `message`, `created_at`) VALUES
(6, 2, 2, 'Holiday Notice', 'het clas na', '2026-02-19 03:00:20');

-- --------------------------------------------------------

--
-- Table structure for table `past_papers`
--

CREATE TABLE `past_papers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `year` varchar(10) NOT NULL,
  `category` enum('Paper','Marking') NOT NULL,
  `file_source` enum('upload','link') NOT NULL,
  `paper_file` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `past_papers`
--

INSERT INTO `past_papers` (`id`, `title`, `year`, `category`, `file_source`, `paper_file`, `created_at`) VALUES
(2, 'Pure Maths 2', '2011', 'Paper', 'link', 'https://chat.whatsapp.com/Jm52UanW5c7CfB2L4Wp2kK', '2026-02-20 02:22:54'),
(3, 'applied1', '2012', 'Paper', 'upload', 'paper_1771554424_9795.pdf', '2026-02-20 02:27:04'),
(4, 'applied ', '2012', 'Marking', 'link', 'https://chat.whatsapp.com/Jm52UanW5c7CfB2L4Wp2kK', '2026-02-20 02:27:23');

-- --------------------------------------------------------

--
-- Table structure for table `pdf_files`
--

CREATE TABLE `pdf_files` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `pdf_title` varchar(255) NOT NULL,
  `sub_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pdf_files`
--

INSERT INTO `pdf_files` (`id`, `class_id`, `pdf_title`, `sub_name`, `file_path`, `uploaded_at`) VALUES
(2, 2, 'Logic Gate', 'Tute 01', 'uploads/pdf/doc_1771468342_775.pdf', '2026-02-19 02:32:22');

-- --------------------------------------------------------

--
-- Table structure for table `recordings`
--

CREATE TABLE `recordings` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `video_title` varchar(255) NOT NULL,
  `video_id` varchar(100) NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `folder_name` varchar(100) DEFAULT 'General',
  `sub_title` varchar(255) DEFAULT '',
  `duration` varchar(50) DEFAULT '00:00',
  `status` enum('released','not_released','scheduled') DEFAULT 'released',
  `expire_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recordings`
--

INSERT INTO `recordings` (`id`, `class_id`, `video_title`, `video_id`, `added_date`, `folder_name`, `sub_title`, `duration`, `status`, `expire_date`) VALUES
(8, 2, 'K-Map', '5ck-JOWerVA', '2026-02-18 16:43:00', 'Logic Gates', 'part 01', '01h 00m', 'not_released', NULL),
(9, 6, 'K-Map', '5ck-JOWerVA', '2026-02-18 16:43:32', 'Logic Gates', 'part 02', '01h 00m', 'released', NULL),
(10, 2, 'K-Map', '5ck-JOWerVA', '2026-02-18 16:44:29', 'Logic Gates', 'part 03', '00h 01m', 'released', NULL),
(11, 2, 'Paper1', '5ck-JOWerVA', '2026-02-20 04:40:55', 'Paper Discussions', '', '01h 00m', 'released', NULL),
(12, 2, 'Paper2', '5ck-JOWerVA', '2026-02-20 04:41:38', 'Paper Discussions', '', '01h 00m', 'not_released', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `recording_history`
--

CREATE TABLE `recording_history` (
  `history_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `recording_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_watched` int(11) DEFAULT 0 COMMENT 'In seconds'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recording_history`
--

INSERT INTO `recording_history` (`history_id`, `student_id`, `recording_id`, `start_time`, `end_time`, `duration_watched`) VALUES
(3, 4, 8, '2026-02-20 04:00:03', '2026-02-20 04:00:28', 25),
(5, 4, 8, '2026-02-20 04:22:37', '2026-02-20 04:22:50', 13),
(7, 4, 10, '2026-02-20 04:28:11', '2026-02-20 04:28:27', 16),
(8, 4, 10, '2026-02-20 04:32:11', '2026-02-20 04:32:23', 12),
(9, 4, 10, '2026-02-19 04:33:01', '2026-02-19 04:33:35', 34),
(10, 4, 10, '2026-02-20 10:15:53', '2026-02-20 10:15:55', 2),
(11, 4, 12, '2026-02-20 10:16:08', '2026-02-20 10:16:09', 1),
(12, 18, 10, '2026-02-20 11:08:59', '2026-02-20 11:09:10', 11);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gmail` varchar(255) NOT NULL,
  `nic_number` varchar(20) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) NOT NULL,
  `town` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL DEFAULT '123456789',
  `profile_pic` varchar(255) DEFAULT 'default_st.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `full_name`, `gmail`, `nic_number`, `school`, `dob`, `address`, `contact_number`, `town`, `password`, `profile_pic`, `created_at`, `status`) VALUES
(4, 'Janith Theekshana', 'jkarunathilaka2007@gmail.com', '200707401798', 'Bandarawela central college', '2007-03-14', 'Allimale,MakulElla,Bandarawela', '0705267767', 'Bandarawela', '$2y$10$IckSCC3MIZIR.4AL60hWzuoi8ALw6i6aeasnJ.wbiLboHGbwMsK6K', '1771478759_12a12d15c2274c180fa91c5888e8e49e.jpg', '2026-02-18 02:41:11', 'active'),
(5, 'Kavithra Karunathilaka', 'ksenethmi@gmail.com', NULL, NULL, NULL, NULL, '0743659874', 'Badulla', '$2y$10$Aw3Mt.0FB8utfhiE1Gi6veIpwJGuCdxa6.2EIuPI3VuH9C8.87XbS', 'default_st.png', '2026-02-18 02:41:54', 'active'),
(6, 'Nimal Perera', 'nimalp@gmail.com', NULL, NULL, NULL, NULL, '0712345678', 'Badulla', '123456789', 'default_st.png', '2025-01-04 18:30:00', 'active'),
(7, 'Saman Kumara', 'samank@gmail.com', NULL, NULL, NULL, NULL, '0723456789', 'Bandarawela', '123456789', 'default_st.png', '2025-01-05 18:30:00', 'active'),
(8, 'Dilani Fernando', 'dilanif@gmail.com', NULL, NULL, NULL, NULL, '0754567890', 'Badulla', '123456789', 'default_st.png', '2025-01-06 18:30:00', 'active'),
(9, 'Kasun Jayawardena', 'kasunj@gmail.com', NULL, NULL, NULL, NULL, '0765678901', 'Bandarawela', '123456789', 'default_st.png', '2025-01-07 18:30:00', 'active'),
(10, 'Tharindu Silva', 'tharindus@gmail.com', NULL, NULL, NULL, NULL, '0706789012', 'Badulla', '123456789', 'default_st.png', '2025-01-08 18:30:00', 'active'),
(11, 'Madushi Rathnayake', 'madushir@gmail.com', NULL, NULL, NULL, NULL, '0717890123', 'Bandarawela', '123456789', 'default_st.png', '2025-01-09 18:30:00', 'active'),
(12, 'Chamika Bandara', 'chamikab@gmail.com', NULL, NULL, NULL, NULL, '0728901234', 'Badulla', '123456789', 'default_st.png', '2025-01-10 18:30:00', 'active'),
(13, 'Piyumi Senanayake', 'piyumis@gmail.com', NULL, NULL, NULL, NULL, '0759012345', 'Bandarawela', '123456789', 'default_st.png', '2025-01-11 18:30:00', 'active'),
(14, 'Lahiru Wijesinghe', 'lahiruw@gmail.com', NULL, NULL, NULL, NULL, '0760123456', 'Badulla', '123456789', 'default_st.png', '2025-01-12 18:30:00', 'active'),
(15, 'Sachini Gunawardena', 'sachinig@gmail.com', NULL, NULL, NULL, NULL, '0701234567', 'Bandarawela', '123456789', 'default_st.png', '2025-01-13 18:30:00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_classes`
--

INSERT INTO `student_classes` (`id`, `student_id`, `class_id`) VALUES
(1, 4, 1),
(2, 4, 2),
(3, 5, 6),
(7, 6, 6),
(8, 7, 7),
(9, 8, 2),
(10, 9, 2),
(11, 10, 2),
(12, 11, 2),
(13, 12, 2),
(14, 13, 2),
(15, 14, 2),
(16, 15, 2);

-- --------------------------------------------------------

--
-- Table structure for table `student_marks`
--

CREATE TABLE `student_marks` (
  `mark_id` int(11) NOT NULL,
  `paper_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `marks_obtained` decimal(5,2) NOT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_marks`
--

INSERT INTO `student_marks` (`mark_id`, `paper_id`, `student_id`, `marks_obtained`, `percentage`, `added_at`) VALUES
(1, 1, 4, 970.00, 97.00, '2026-02-18 06:36:37'),
(2, 2, 12, 100.00, 10.00, '2026-02-18 06:57:02'),
(3, 2, 8, 125.00, 12.50, '2026-02-18 06:57:02'),
(4, 2, 4, -1.00, 0.00, '2026-02-18 06:57:02'),
(5, 2, 9, 220.00, 22.00, '2026-02-18 06:57:02'),
(6, 2, 5, 300.00, 30.00, '2026-02-18 06:57:02'),
(7, 2, 14, 750.00, 75.00, '2026-02-18 06:57:02'),
(8, 2, 11, 725.00, 72.50, '2026-02-18 06:57:02'),
(9, 2, 13, 801.00, 80.10, '2026-02-18 06:57:02'),
(10, 2, 15, 501.00, 50.10, '2026-02-18 06:57:02'),
(11, 2, 10, 599.00, 59.90, '2026-02-18 06:57:02');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gmail` varchar(100) NOT NULL,
  `nic_number` varchar(20) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT 'default_user.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`id`, `name`, `gmail`, `nic_number`, `contact_number`, `password`, `profile_pic`, `created_at`) VALUES
(2, 'Supun Dulanga', 'supun@gmail.com', '1869543215v', '0743659874', '$2y$10$oPKH1HWfU8KoNzuElI9xkOBAa7qKY/BKcqKRmpVNmy9RYWjaZi17W', '1771344653_OIP (2).jpg', '2026-02-17 12:16:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `class_fees_payments`
--
ALTER TABLE `class_fees_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_towns`
--
ALTER TABLE `class_towns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_papers`
--
ALTER TABLE `exam_papers`
  ADD PRIMARY KEY (`paper_id`);

--
-- Indexes for table `live_classes`
--
ALTER TABLE `live_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `past_papers`
--
ALTER TABLE `past_papers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pdf_files`
--
ALTER TABLE `pdf_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `recordings`
--
ALTER TABLE `recordings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `recording_history`
--
ALTER TABLE `recording_history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gmail` (`gmail`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD PRIMARY KEY (`mark_id`),
  ADD UNIQUE KEY `paper_id` (`paper_id`,`student_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gmail` (`gmail`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `class_fees_payments`
--
ALTER TABLE `class_fees_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `class_towns`
--
ALTER TABLE `class_towns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `exam_papers`
--
ALTER TABLE `exam_papers`
  MODIFY `paper_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `live_classes`
--
ALTER TABLE `live_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `past_papers`
--
ALTER TABLE `past_papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pdf_files`
--
ALTER TABLE `pdf_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `recordings`
--
ALTER TABLE `recordings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `recording_history`
--
ALTER TABLE `recording_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `student_marks`
--
ALTER TABLE `student_marks`
  MODIFY `mark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `live_classes`
--
ALTER TABLE `live_classes`
  ADD CONSTRAINT `live_classes_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `pdf_files`
--
ALTER TABLE `pdf_files`
  ADD CONSTRAINT `pdf_files_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `recordings`
--
ALTER TABLE `recordings`
  ADD CONSTRAINT `recordings_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
