-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 02, 2026 at 07:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_expense_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Admin', 'admin@gmail.com', '123456', '2026-07-28 01:18:33');

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE `budget` (
  `budget_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `limit_amount` decimal(10,2) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `year` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget`
--

INSERT INTO `budget` (`budget_id`, `user_id`, `category_id`, `limit_amount`, `month`, `year`) VALUES
(1, 2, 1, 600.00, 'January', 2026),
(2, 2, 1, 500.00, 'January', 2026),
(3, 2, 3, 100.00, 'January', 2026),
(4, 2, 6, 100.00, 'January', 2026),
(5, 4, 1, 500.00, 'December', 2026),
(6, 4, 4, 5000.00, 'July', 2026),
(7, 5, 1, 500.00, 'July', 2026),
(8, 5, 2, 50.00, 'July', 2026),
(9, 5, 3, 100.00, 'July', 2026),
(10, 5, 5, 200.00, 'July', 2026),
(11, 5, 6, 100.00, 'July', 2026),
(12, 5, 7, 50.00, 'July', 2026),
(13, 5, 4, 300.00, 'July', 2026),
(15, 9, 1, 500.00, 'August', 2026),
(16, 9, 3, 100.02, 'August', 2026),
(17, 9, 7, 100.00, 'August', 2026),
(18, 9, 4, 200.00, 'August', 2026),
(19, 9, 5, 100.00, 'August', 2026);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`, `description`) VALUES
(1, 'Food', 'Food and restaurant expenses'),
(2, 'Transport', 'Travel and vehicle expenses'),
(3, 'Shopping', 'Shopping expenses'),
(4, 'Medical', 'Health related expenses'),
(5, 'Education', 'Education expenses'),
(6, 'Bills', 'Electricity, internet and other bills'),
(7, 'Entertainment', 'Entertainment expenses'),
(8, 'Others', 'Other expenses');

-- --------------------------------------------------------

--
-- Table structure for table `expense`
--

CREATE TABLE `expense` (
  `expense_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense`
--

INSERT INTO `expense` (`expense_id`, `user_id`, `category_id`, `title`, `amount`, `payment_method`, `date`, `description`, `created_at`) VALUES
(1, 2, 1, 'tt', 300.00, 'Cash', '2026-06-06', 'aa', '2026-07-27 20:57:15'),
(2, 2, 1, 'f', 450.00, 'Cash', '2026-01-01', '', '2026-07-27 21:08:50'),
(3, 2, 1, 'f', 450.00, 'Cash', '2026-01-01', '', '2026-07-27 21:09:11'),
(4, 2, 3, '', 40.00, 'Cash', '2026-12-03', '', '2026-07-27 22:32:49'),
(5, 2, 1, '', 100.00, 'Cash', '2026-07-28', '', '2026-07-27 22:39:56'),
(6, 2, 1, '', 4.00, 'Cash', '2026-07-28', '', '2026-07-28 00:24:00'),
(7, 2, 2, '', 50.00, 'Cash', '2026-07-28', '', '2026-07-28 00:24:26'),
(8, 2, 3, '', 90.00, 'Cash', '2026-01-01', '', '2026-07-28 09:43:04'),
(9, 2, 6, '', 90.00, 'Cash', '2026-01-11', '', '2026-07-28 10:08:46'),
(10, 2, 6, '', 20.00, 'Cash', '2026-01-20', '', '2026-07-28 10:09:33'),
(11, 4, 1, '', 400.00, 'Cash', '2026-12-28', '', '2026-07-28 15:53:50'),
(12, 4, 1, '', 100.00, 'Cash', '2026-12-28', '', '2026-07-28 15:54:44'),
(13, 4, 1, '', 100.00, 'Cash', '2026-12-28', '', '2026-07-28 15:55:07'),
(14, 4, 1, '', 100.00, 'Cash', '2026-12-28', '', '2026-07-28 15:55:24'),
(15, 4, 6, '', 50.00, 'Cash', '2026-12-28', '', '2026-07-28 15:58:02'),
(16, 4, 4, '', 7000.00, 'Cash', '2026-07-28', '', '2026-07-28 15:58:43'),
(17, 4, 4, '', 100.00, 'Cash', '2026-07-28', '', '2026-07-28 16:00:47'),
(20, 5, 1, '', 300.00, 'Cash', '2026-07-28', '', '2026-07-28 16:53:29'),
(21, 5, 5, '', 300.00, 'Cash', '2026-07-28', '', '2026-07-28 16:54:09'),
(23, 5, 4, '', 200.00, 'Cash', '2026-07-28', '', '2026-07-28 16:59:16'),
(27, 5, 3, '', 300.00, 'Cash', '2026-07-28', '', '2026-07-28 17:29:55'),
(28, 5, 2, '', 50.00, 'Cash', '0000-00-00', '', '2026-07-28 17:39:16'),
(29, 9, 1, '', 400.00, 'Cash', '2026-08-20', '', '2026-07-31 22:45:38'),
(30, 9, 4, '', 200.00, 'Cash', '2026-08-28', '', '2026-07-31 22:45:52'),
(31, 9, 2, '', 50.00, 'Cash', '2026-08-19', '', '2026-07-31 22:46:00'),
(32, 9, 5, '', 110.00, 'Cash', '2026-08-29', '', '2026-07-31 22:46:13'),
(33, 9, 3, '', 90.00, 'Cash', '2026-08-01', '', '2026-07-31 22:46:28'),
(34, 9, 6, '', 500.00, 'Cash', '2026-08-30', '', '2026-07-31 22:47:33'),
(35, 9, 6, 'gash', 40.00, 'Cash', '2026-08-01', '', '2026-07-31 22:57:13');

-- --------------------------------------------------------

--
-- Table structure for table `income`
--

CREATE TABLE `income` (
  `income_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `income`
--

INSERT INTO `income` (`income_id`, `user_id`, `source`, `amount`, `date`, `description`, `created_at`) VALUES
(1, 2, 'Salary', 5000.00, '2026-03-03', 'monthly', '2026-07-27 19:17:09'),
(2, 2, 'Salary', 5000.00, '2026-07-28', '', '2026-07-27 22:39:25'),
(3, 2, 'Investment', 1200.00, '2026-01-25', '', '2026-07-28 10:18:12'),
(4, 4, 'Salary', 5000.00, '2026-07-28', 'hh', '2026-07-28 15:52:30'),
(5, 4, 'Freelance', 1000.00, '2026-07-28', '', '2026-07-28 15:52:46'),
(6, 4, 'Salary', 500.00, '2026-12-28', '', '2026-07-28 15:57:29'),
(7, 5, 'Salary', 500.00, '2026-07-01', '', '2026-07-28 16:52:12'),
(8, 5, 'Freelance', 300.00, '2026-07-01', '', '2026-07-28 16:52:29'),
(9, 5, 'Investment', 500.00, '2026-07-01', '', '2026-07-28 16:52:46'),
(10, 9, 'Salary', 500.00, '2026-08-01', '', '2026-07-31 22:44:34'),
(11, 9, 'Business', 300.00, '2026-08-01', '', '2026-07-31 22:44:44'),
(12, 9, 'Freelance', 100.00, '2026-08-01', '', '2026-07-31 22:44:54'),
(13, 9, 'Investment', 300.00, '2026-08-01', '', '2026-07-31 22:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `alert_level` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `user_id`, `message`, `notification_type`, `status`, `created_at`, `alert_level`, `category_id`) VALUES
(1, 2, 'Alert! Your Food budget has been exceeded.', 'Budget Alert', 'Unread', '2026-07-27 21:08:50', 0, NULL),
(3, 2, 'Alert! Your Food budget has been exceeded.', 'Budget Alert', 'Unread', '2026-07-27 22:39:56', 0, NULL),
(4, 2, 'Alert! Your Food budget has been exceeded.', 'Budget Alert', 'Read', '2026-07-28 00:24:00', 0, NULL),
(6, 3, 'Welcome to Expense Tracker', NULL, 'unread', '2026-07-28 01:39:02', 0, NULL),
(8, 2, '⚠️ Bills Budget Alert.\r\n\r\nYou have used 90% of your budget.\r\n\r\nBudget: $100.00\r\n\r\nSpent: $90.00\r\n\r\nRemaining: $10', 'Budget Alert', 'Unread', '2026-07-28 10:08:46', 90, NULL),
(9, 2, '🚨 Bills Budget Exceeded.\r\n\r\nBudget: $100.00\r\n\r\nSpent: $110.00\r\n\r\nOver Budget: -$10', 'Budget Alert', 'Unread', '2026-07-28 10:09:33', 100, NULL),
(11, 4, '🚨 Food Budget Exceeded.\r\n\r\nBudget: $500.00\r\n\r\nSpent: $500.00\r\n\r\nOver Budget: -$0', 'Budget Alert', 'Read', '2026-07-28 15:54:44', 100, NULL),
(12, 5, '⚠️ Food Budget Alert.\r\n\r\nYou have used 60% of your budget.\r\n\r\nBudget: $500.00\r\n\r\nSpent: $300.00\r\n\r\nRemaining: $200', 'Budget Alert', 'Unread', '2026-07-28 16:53:29', 60, NULL),
(13, 5, '🚨 Education Budget Exceeded.\r\n\r\nBudget: $200.00\r\n\r\nSpent: $300.00\r\n\r\nOver Budget: -$100', 'Budget Alert', 'Unread', '2026-07-28 16:54:09', 100, NULL),
(15, 5, '⚠️ Medical Budget Alert.\r\n\r\nYou have used 67% of your budget.\r\n\r\nBudget: $300.00\r\n\r\nSpent: $200.00\r\n\r\nRemaining: $100', 'Budget Alert', 'Unread', '2026-07-28 16:59:16', 67, NULL),
(16, 5, '⚠️ Balance Alert.\r\n\r\nYou have used 62% of your total income.\r\n\r\nRemaining Balance: $500', 'Balance Alert', 'Unread', '2026-07-28 16:59:16', 50, NULL),
(18, 5, '🚨 Shopping Budget Exceeded.\r\n\r\nBudget: $100.00\r\n\r\nSpent: $300.00\r\n\r\nOver Budget: -$200', 'Budget Alert', 'Unread', '2026-07-28 17:29:55', 100, NULL),
(19, 5, '🚨 Transport Budget Exceeded.\r\n\r\nBudget: $50.00\r\n\r\nSpent: $50.00\r\n\r\nOver Budget: -$0', 'Budget Alert', 'Unread', '2026-07-28 17:39:16', 100, NULL),
(20, 5, '⚠️ Balance Warning.\r\n\r\nYou have used 88% of your total income.\r\n\r\nRemaining Balance: $150', 'Balance Alert', 'Unread', '2026-07-28 17:39:16', 70, NULL),
(21, 9, '⚠️ Food Budget Alert.\r\n\r\nYou have used 80% of your budget.\r\n\r\nBudget: $500.00\r\n\r\nSpent: $400.00\r\n\r\nRemaining: $100', 'Budget Alert', 'Unread', '2026-07-31 22:45:38', 80, NULL),
(22, 9, '🚨 Medical Budget Exceeded.\r\n\r\nBudget: $200.00\r\n\r\nSpent: $200.00\r\n\r\nOver Budget: -$0', 'Budget Alert', 'Unread', '2026-07-31 22:45:52', 100, NULL),
(23, 9, '⚠️ Balance Alert.\r\n\r\nYou have used 50% of your total income.\r\n\r\nRemaining Balance: $600', 'Balance Alert', 'Unread', '2026-07-31 22:45:52', 50, NULL),
(24, 9, '🚨 Education Budget Exceeded.\r\n\r\nBudget: $100.00\r\n\r\nSpent: $110.00\r\n\r\nOver Budget: -$10', 'Budget Alert', 'Unread', '2026-07-31 22:46:13', 100, NULL),
(25, 9, '⚠️ Shopping Budget Alert.\r\n\r\nYou have used 90% of your budget.\r\n\r\nBudget: $100.02\r\n\r\nSpent: $90.00\r\n\r\nRemaining: $10.02', 'Budget Alert', 'Unread', '2026-07-31 22:46:28', 90, NULL),
(26, 9, '⚠️ Balance Warning.\r\n\r\nYou have used 71% of your total income.\r\n\r\nRemaining Balance: $350', 'Balance Alert', 'Unread', '2026-07-31 22:46:28', 70, NULL),
(27, 9, '🚨 Income Exceeded.\r\n\r\nTotal Income: $1200.00\r\n\r\nTotal Expense: $1350.00\r\n\r\nExtra Spending: -$150', 'Balance Alert', 'Unread', '2026-07-31 22:47:33', 100, NULL),
(28, 9, '⚠️ No Budget Set.\r\n\r\nYou added a Bills expense without setting a budget.\r\n\r\nExpense Added: $40.00\r\n\r\nTotal Bills Expense for August 2026: $540.00\r\n\r\nPlease set a Bills budget to track your spending.', 'Budget Alert', 'Unread', '2026-07-31 22:57:13', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified` int(11) DEFAULT 0,
  `verification_code` varchar(10) DEFAULT NULL,
  `reset_code` varchar(10) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `profile_image`, `created_at`, `email_verified`, `verification_code`, `reset_code`, `reset_expiry`) VALUES
(2, 'Mahmudul', 'etanvir5135@gmail.com', '$2y$10$zIq0gZvPApTAhbQvHtND3.4RxuManZ1d5sZ4y1ymKApaRVo7we8fm', '123', 'img.png', '2026-07-27 19:02:58', 1, '', NULL, NULL),
(3, 'Hasan', 'tanvirul5135@gmail.com', '$2y$10$iV24GMNjq/lbO4EAxJCBTeyLKovIbGpHeOEiWOpLoZn1VCMMN59ye', '018', NULL, '2026-07-27 21:42:46', 0, NULL, NULL, NULL),
(4, 'Tomal ', 'tomal@gmail.com', '$2y$10$aAGDT41yVidjCQ2G3V8ZRulnwP89nL.tODoadtVOhO9XfG5H7KY5i', '123', NULL, '2026-07-28 15:51:44', 0, NULL, NULL, NULL),
(5, 'mh', 'mh@gmail.com', '$2y$10$sTdLPLjg/X35CHTCyL0aGuypL7xoGnYLJpJ/fZ4yjerWE2HcboITO', '123', NULL, '2026-07-28 16:48:41', 0, NULL, NULL, NULL),
(8, 'Tanvir Sorker', 'refatvai420@gmail.com', '$2y$10$aCPea6S/.9.sqg2HFApFW.M0L.5KdPutQ.c87ayR5t8OmMKbBlOgy', '01876871072', NULL, '2026-07-31 22:32:35', 0, '770918', NULL, NULL),
(9, 'Tanvir Sorker', 'refatbai420@gmail.com', '$2y$10$2V86v.4E8tkQLLJHyhkET.i41v5BZnjuySD/ZKZpAiR4KPb..oTiy', '01876871072', NULL, '2026-07-31 22:36:21', 1, '', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `budget`
--
ALTER TABLE `budget`
  ADD PRIMARY KEY (`budget_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `expense`
--
ALTER TABLE `expense`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`income_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `budget`
--
ALTER TABLE `budget`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `expense`
--
ALTER TABLE `expense`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
  MODIFY `income_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget`
--
ALTER TABLE `budget`
  ADD CONSTRAINT `budget_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `budget_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `expense`
--
ALTER TABLE `expense`
  ADD CONSTRAINT `expense_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `expense_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `income`
--
ALTER TABLE `income`
  ADD CONSTRAINT `income_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
