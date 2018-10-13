-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2018-09-19 16:04:00
-- 服务器版本： 5.7.20-log
-- PHP Version: 5.6.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `channel`
--

-- --------------------------------------------------------

--
-- 表的结构 `channels`
--

CREATE TABLE IF NOT EXISTS `channels` (
  `id` int(10) unsigned NOT NULL,
  `user_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `channel_details`
--

CREATE TABLE IF NOT EXISTS `channel_details` (
  `id` int(10) unsigned NOT NULL,
  `c_date` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `channel_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_add` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `recharge_num` int(11) NOT NULL,
  `recharge_money` int(11) NOT NULL,
  `first_register` int(11) NOT NULL,
  `effective` int(11) NOT NULL,
  `recharge_sum` int(11) NOT NULL,
  `first_pay` int(11) NOT NULL,
  `first_pay_sum` int(11) NOT NULL,
  `first_pay_total` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `migrations`
--

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `password_resets`
--

CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `qudaos`
--

CREATE TABLE IF NOT EXISTS `qudaos` (
  `id` int(10) unsigned NOT NULL,
  `channel_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `recharge_details`
--

CREATE TABLE IF NOT EXISTS `recharge_details` (
  `id` int(10) unsigned NOT NULL,
  `order_id` int(11) NOT NULL,
  `channel_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_money` int(11) NOT NULL,
  `prop_num` int(11) NOT NULL,
  `recharge_time` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `shares`
--

CREATE TABLE IF NOT EXISTS `shares` (
  `id` int(10) unsigned NOT NULL,
  `shareid` int(11) NOT NULL,
  `share_people_num` int(11) NOT NULL,
  `share_num` int(11) NOT NULL,
  `click_num` int(11) NOT NULL,
  `click_people_num` int(11) NOT NULL,
  `register_num` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `total_serverlists`
--

CREATE TABLE IF NOT EXISTS `total_serverlists` (
  `id` int(10) unsigned NOT NULL,
  `c_date` int(11) NOT NULL,
  `channel_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_insl_sum` int(11) NOT NULL,
  `new_add_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_power` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channel_details`
--
ALTER TABLE `channel_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `qudaos`
--
ALTER TABLE `qudaos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recharge_details`
--
ALTER TABLE `recharge_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `recharge_details_order_id_unique` (`order_id`);

--
-- Indexes for table `shares`
--
ALTER TABLE `shares`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `total_serverlists`
--
ALTER TABLE `total_serverlists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `channel_details`
--
ALTER TABLE `channel_details`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `qudaos`
--
ALTER TABLE `qudaos`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `recharge_details`
--
ALTER TABLE `recharge_details`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shares`
--
ALTER TABLE `shares`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `total_serverlists`
--
ALTER TABLE `total_serverlists`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;