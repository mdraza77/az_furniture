-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2025 at 10:17 PM
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
-- Database: `az_furniture`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','editor') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `full_name`, `email`, `password_hash`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(3, 'Md Raza', 'mdraza8297@gmail.com', '4b036ff4854c5d6365ac8bf6edd1f7b3', 'admin', 1, '2025-05-02 22:43:32', '2025-04-29 08:22:57', '2025-05-02 17:13:32');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_price` decimal(10,2) GENERATED ALWAYS AS (`price` * `quantity`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Sofa Bed / Sleeper Sofa', 'sofa-bed-/-sleeper-sofa', 'Dual-purpose sofa that converts into a bed. Perfect for guest rooms or small apartments.', NULL, 1, 3, '2025-04-29 11:33:01', '2025-04-30 19:07:59'),
(4, 'Chesterfield Sofa', 'chesterfield-sofa', 'Known for deep button tufting and rolled arms, often in leather. It adds a luxurious, vintage charm to interiors.', NULL, 1, 3, '2025-04-29 16:56:49', '2025-04-30 19:07:43'),
(5, 'Sectional Sofa', 'sectional-sofa', 'Large L- or U-shaped sofa made of multiple joined sections. Ideal for spacious living rooms and family gatherings.', NULL, 1, 3, '2025-04-29 22:13:03', '2025-04-30 19:06:56'),
(6, 'Loveseat', 'loveseat', 'A compact two-seater sofa. Best suited for small spaces or cozy corners.', NULL, 1, 3, '2025-04-30 19:08:16', '2025-04-30 19:08:16'),
(7, 'Mid-Century Modern Sofa', 'mid-century-modern-sofa', 'Characterized by clean lines and wooden legs. Combines simplicity with retro design.', NULL, 1, 3, '2025-04-30 19:08:36', '2025-04-30 19:08:36'),
(8, 'Chaise Lounge', 'chaise-lounge', 'An extended sofa seat to stretch out your legs. Adds elegance and comfort to bedrooms or lounges.', NULL, 1, 3, '2025-04-30 19:08:56', '2025-05-02 15:41:43');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'COD',
  `shipping_address` text DEFAULT NULL,
  `address_line1` text DEFAULT NULL,
  `address_line2` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `full_name`, `total_amount`, `status`, `payment_method`, `shipping_address`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `phone_number`, `created_at`, `updated_at`) VALUES
(55, 14, 'Md Faiz Ansari', 40597.00, 'processing', 'COD', NULL, 'Mg Road', 'DD', 'Purulia', 'West Bengal', '741249', 'India', '9883517964', '2025-05-02 13:10:18', '2025-05-02 18:36:32'),
(56, 14, 'Md Faiz Ansari', 4499.00, 'shipped', 'COD', NULL, 'Mg Road', 'fd', 'Purulia', 'West Bengal', '741249', 'India', '9883517964', '2025-05-02 14:49:05', '2025-05-02 19:07:44'),
(57, 9, 'Md Raza', 100397.00, 'delivered', 'UPI', NULL, 'Ruhia Road ', 'Islampur', 'Islampur', 'West Bengal', '733202', 'India', '7477650108', '2025-05-02 17:05:07', '2025-05-02 19:45:37'),
(58, 12, 'Safikul Alam', 121791.00, 'shipped', 'COD', NULL, 'Buduganj', 'Ramganj', 'Ramganj', 'West Bengal', '733207', 'India', '9800362856', '2025-05-02 18:22:21', '2025-05-02 18:31:51'),
(59, 12, 'Safikul Alam', 322918.60, 'processing', 'COD', NULL, 'Buduganj', 'Ramganj', 'Ramganj', 'West Bengal', '733207', 'India', '9800362856', '2025-05-02 18:24:22', '2025-05-02 18:36:51'),
(60, 12, 'Safikul Alam', 8998.00, 'delivered', 'COD', NULL, 'Buduganj', 'Ramganj', 'Ramganj', 'West Bengal', '733207', 'India', '9800362856', '2025-05-02 18:29:14', '2025-05-02 19:09:42');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`price` * `quantity`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`) VALUES
(58, 55, 5, 'Chumbak Colonial Loveseat', 40597.00, 1),
(59, 56, 13, 'Mid-Century Modern Sofa', 4499.00, 1),
(60, 57, 13, 'Mid-Century Modern Sofa', 4499.00, 3),
(61, 57, 12, 'Janet 2 Seater Fabric Loveseat', 21804.00, 1),
(62, 57, 16, 'Urban Ladder Sofia Sofa Cum Bed', 24499.00, 1),
(63, 57, 5, 'Chumbak Colonial Loveseat', 40597.00, 1),
(64, 58, 5, 'Chumbak Colonial Loveseat', 40597.00, 3),
(65, 59, 12, 'Janet 2 Seater Fabric Loveseat', 21804.00, 1),
(66, 59, 5, 'Chumbak Colonial Loveseat', 40597.00, 1),
(67, 59, 4, 'Ebba Chaise Sectional Sofa', 260517.60, 1),
(68, 60, 13, 'Mid-Century Modern Sofa', 4499.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `color_code` varchar(7) DEFAULT '#000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `card_holder` varchar(255) NOT NULL,
  `card_number` varchar(255) NOT NULL,
  `expiry_month` int(11) NOT NULL,
  `expiry_year` int(11) NOT NULL,
  `card_type` varchar(50) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `upi_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `user_id`, `card_holder`, `card_number`, `expiry_month`, `expiry_year`, `card_type`, `is_default`, `created_at`, `upi_id`) VALUES
(1, 14, 'Md Faiz Ansari', '************3456', 7, 2030, 'visa', 0, '2025-05-02 09:58:44', NULL),
(3, 14, '', '', 0, 0, 'upi', 1, '2025-05-02 10:06:04', '9883517964@ybl'),
(4, 9, '', '', 0, 0, 'upi', 1, '2025-05-02 17:00:44', 'mdraza8397@paytm');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `designer` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `sku`, `description`, `price`, `sale_price`, `category_id`, `stock_quantity`, `designer`, `image_url`, `is_featured`, `is_active`, `created_by`, `created_at`, `updated_at`, `last_updated`) VALUES
(3, 'Albus 5-Piece Sectional Sofa', 'single-sofa', 'AFD-ALBUS-5PC-002', 'ABC', 253628.43, 253628.43, 5, 3, 'AFD Furniture', 'assets/images/products/68127a6bea74f.webp', 1, 1, 3, '2025-04-29 11:23:23', '2025-04-30 19:30:51', '2025-04-30 19:30:51'),
(4, 'Ebba Chaise Sectional Sofa', 'amir-khusru', 'DTL-EBBA-CHAISE-001', 'A modern L-shaped sectional with a chaise, offering both comfort and style for contemporary living spaces.', 260517.60, 260517.60, 4, 5, 'Dtale Modern', 'assets/images/products/681279dd3053a.jpg', 1, 1, 3, '2025-04-29 11:26:35', '2025-04-30 19:28:29', '2025-04-30 19:28:29'),
(5, 'Chumbak Colonial Loveseat', '', 'CHUMBAK-COLONIAL-GREEN-002', 'A vibrant and contemporary loveseat made with durable Sheesham wood and high-density foam for added comfort.', 40597.00, 40597.00, 6, 10, 'Az Furniture', 'assets/images/products/68127913bf138.webp', 1, 1, NULL, '2025-04-29 19:26:37', '2025-04-30 19:25:07', '2025-04-30 19:25:07'),
(12, 'Janet 2 Seater Fabric Loveseat', 'chair', 'UL-JANET-ADRIAN-001', 'A compact and stylish loveseat upholstered in Adrian Velvet, perfect for modern living spaces.', 21804.00, 21804.00, 6, 15, 'Urban Ladder', 'assets/images/products/68127879d0e4d.jpg', 1, 1, NULL, '2025-04-29 22:15:17', '2025-04-30 19:22:33', '2025-04-30 19:22:33'),
(13, 'Mid-Century Modern Sofa', 'ar', 'SFA-MIDMOD-002', 'Inspired by 1950s design, this Mid-Century Modern Sofa blends retro charm with today\'s comfort and elegance.', 6799.00, 4499.00, 7, 12, 'RetroSpace', 'assets/images/products/681277a6b6dc6.jpeg', 1, 1, NULL, '2025-04-29 22:18:03', '2025-05-01 15:07:24', '2025-05-01 15:07:24'),
(15, 'Chaise Lounge Sofa', 'ff', 'SFA-CHAISE-001', 'Elegant and comfortable, this Chaise Lounge Sofa is perfect for relaxing and adds luxury to any space.', 8740.00, 6399.00, 8, 8, 'ModernCraft', 'assets/images/products/681518c1bea21.webp', 0, 1, NULL, '2025-04-30 14:54:38', '2025-05-02 19:10:57', '2025-05-02 19:10:57'),
(16, 'Urban Ladder Sofia Sofa Cum Bed', 'urban-ladder-sofia-sofa-cum-bed', 'UL-SOFA-BED-001', 'Compact yet spacious, this sofa bed is perfect for small spaces, easily converting into a cozy bed for guests.', 29999.00, 24499.00, 1, 7, 'Urban Ladder', 'assets/images/products/681373f874f79.jpg', 0, 1, NULL, '2025-04-30 19:36:13', '2025-05-02 18:43:47', '2025-05-02 18:43:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default_user_image.png',
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `role` enum('user','admin') DEFAULT 'user',
  `is_email_verified` tinyint(1) DEFAULT 0,
  `is_phone_verified` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_password_token` varchar(255) DEFAULT NULL,
  `reset_password_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `profile_image`, `phone_number`, `address`, `city`, `state`, `postal_code`, `country`, `role`, `is_email_verified`, `is_phone_verified`, `is_active`, `last_login`, `created_at`, `updated_at`, `reset_password_token`, `reset_password_expires`) VALUES
(9, 'Md Raza', 'mdraza8297@gmail.com', '4b036ff4854c5d6365ac8bf6edd1f7b3', 'assets/images/profile/6814f9764985b_1000019435.jpg', '7477650108', 'Ruhia', 'Islampur', 'West Bengal', '733202', 'India', 'user', 0, 0, 1, NULL, '2025-04-29 17:29:36', '2025-05-02 16:57:26', NULL, NULL),
(10, 'Taksin Raja', 'taksinraja01@gmail.com', 'dec80808998837f4fc8c19a8fe869a6f', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 0, 0, 1, NULL, '2025-04-29 19:49:45', '2025-04-29 19:49:45', NULL, NULL),
(11, 'Sidra Naz', 'sidranaz@gmail.com', 'ccb7af19bac5da55ff290986b46e93ad', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 0, 0, 1, NULL, '2025-04-29 20:03:38', '2025-04-29 20:03:38', NULL, NULL),
(12, 'Safikul Alam', 'safikul@gmail.com', '50dea2ced4c3b74fabe37daefd0d3197', 'assets/images/profile/6813df1129287_safikul alam.jpg', NULL, NULL, '', 'West Bengal', '733207', NULL, 'user', 0, 0, 1, NULL, '2025-04-30 18:28:51', '2025-05-01 23:06:56', NULL, NULL),
(13, 'dd dy', 'dd@gmail.com', '7e081caf901195f420ef4d375ea2c6f5', 'assets/images/profile/6813ff0d2d39c_Remote Working 3D Model.png', NULL, NULL, NULL, NULL, NULL, NULL, 'user', 0, 0, 1, NULL, '2025-05-01 23:07:48', '2025-05-01 23:09:01', NULL, NULL),
(14, 'Md Faiz Ansari', 'faiz@gmail.com', 'd26ef54a77c30fc4aa7b6cf1f553381a', 'assets/images/profile/6814e00b082c8_Walpaper3.jpg', '9883517964', 'Mg Road', 'Purulia', 'West Bengal', '741249', 'India', 'user', 0, 0, 1, NULL, '2025-05-02 09:22:43', '2025-05-02 15:08:59', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `address_type` enum('home','office','other') DEFAULT 'home',
  `full_name` varchar(100) NOT NULL,
  `address_line1` text NOT NULL,
  `address_line2` text DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) DEFAULT 'India',
  `phone_number` varchar(15) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address_type`, `full_name`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `phone_number`, `is_default`, `created_at`, `updated_at`) VALUES
(7, 9, 'home', 'Md Raza', 'Ruhia Road ', 'Ruhia', 'Islampur', 'West Bengal', '733202', 'India', '7477650108', 1, '0000-00-00 00:00:00', '2025-05-02 17:06:44'),
(8, 12, 'home', 'Safikul Alam', 'Buduganj', 'Ramganj', 'Ramganj\n', 'West Bengal', '733207', 'India', '9800362856', 1, '2025-05-01 20:46:14', '2025-05-01 23:06:27'),
(9, 9, 'home', 'Md Raza ', 'Makaut Boys Hostel, Haringhata, Nadia', 'Near BSF Camp', 'Kalyani', 'West Bengal', '741249', 'India', '7477650108', 0, '2025-05-02 09:09:50', '2025-05-02 17:08:23'),
(10, 14, 'home', 'Md Faiz Ansari', 'Mg Road', '', 'Purulia', 'West Bengal', '741249', 'India', '9883517964', 1, '2025-05-02 09:24:02', '2025-05-02 12:27:02'),
(12, 9, 'office', 'Amir Khusru', 'Alfalah Masjid Road, Gaibinagar, Nandura, 443404', '', 'Nandura', 'Maharashtra', '443404', 'India', '9679071411', 0, '2025-05-02 17:10:45', '2025-05-02 17:10:45');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_order_items_order` (`order_id`);

--
-- Indexes for table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone_number`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_addresses` (`user_id`,`is_default`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD CONSTRAINT `admin_activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
