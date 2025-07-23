-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-07-2025 a las 01:19:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `discarchar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image_url`, `is_active`, `created_at`) VALUES
(1, 'Electrónicos', 'Productos electrónicos y tecnología', NULL, 1, '2025-07-12 23:21:32'),
(5, 'Embutidos', 'Productos embutidos y fiambres', NULL, 1, '2025-07-12 23:33:12'),
(6, 'Cortes de cerdo', 'Cortes frescos de cerdo', NULL, 1, '2025-07-12 23:33:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `billing_name` varchar(100) DEFAULT NULL,
  `billing_phone` varchar(20) DEFAULT NULL,
  `billing_email` varchar(100) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_postal_code` varchar(10) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_address`, `billing_name`, `billing_phone`, `billing_email`, `billing_city`, `billing_postal_code`, `notes`, `created_at`, `updated_at`) VALUES
(3, 2, 599.99, 'cancelled', 'Calle carabobo, cruce con miranda\r\nEdificio Icabaru', 'Yeaan', '04126380255', 'jeanguerrero04@gmail.com', 'Los teques', '1201', '', '2025-07-12 23:28:09', '2025-07-13 01:42:38'),
(4, 2, 1499.98, 'cancelled', 'Calle carabobo, cruce con miranda\r\nEdificio Icabaru', 'Yeaan', '04126380255', 'jeanguerrero04@gmail.com', 'Los teques', '1201', '', '2025-07-12 23:28:41', '2025-07-13 01:42:34'),
(5, 2, 6.70, 'shipped', 'Calle carabobo, cruce con miranda\r\nEdificio Icabaru', 'Yeaan', '04126380255', 'jeanguerrero04@gmail.com', 'Los teques', '1201', '', '2025-07-12 23:33:41', '2025-07-13 01:42:43'),
(6, 2, 3.50, 'shipped', 'Calle carabobo, cruce con miranda\r\nEdificio Icabaru', 'Yeaan', '04126380255', 'jeanguerrero04@gmail.com', 'Los teques', '1201', '', '2025-07-12 23:39:36', '2025-07-13 01:42:46'),
(7, 2, 7.00, 'cancelled', 'Calle carabobo, cruce con miranda\r\nEdificio Icabaru', 'Yeaan', '04126380255', 'jeanguerrero04@gmail.com', 'Los teques', '1201', '', '2025-07-12 23:44:30', '2025-07-19 03:07:09'),
(10, 2, 3.50, 'delivered', 'Calle carabobo, cruce con miranda\r\nEdificio Icabaru', 'Yeaan', '04126380255', 'jeanguerrero04@gmail.com', 'Los teques', '1201', '', '2025-07-13 01:01:09', '2025-07-19 03:07:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(5, 5, 24, 1, 2.80),
(6, 5, 21, 1, 3.90),
(7, 6, 17, 1, 3.50),
(8, 7, 17, 2, 3.50),
(14, 10, 17, 1, 3.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock_quantity`, `category_id`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(17, 'a1', 'ws', 3.50, 15, 5, NULL, 1, '2025-07-12 23:33:12', '2025-07-19 03:06:37'),
(18, 'MORCILLA ARTESANAL C/PICANTE', '', 3.70, 17, 5, NULL, 1, '2025-07-12 23:33:12', '2025-07-13 00:23:36'),
(19, 'MORCILLA ARTESANAL DULCE PICANTE', '', 3.80, 14, 5, NULL, 1, '2025-07-12 23:33:12', '2025-07-13 00:23:36'),
(20, 'MEZCLA ARTESANAL DE CHORIZO CRIOLLO (PASTA)', '', 4.20, 11, 5, NULL, 1, '2025-07-12 23:33:12', '2025-07-13 00:23:36'),
(21, 'CHORIZO ARTESANAL AJO', '', 3.90, 24, 5, NULL, 1, '2025-07-12 23:33:12', '2025-07-12 23:33:41'),
(22, 'CHORIZO ARTESANAL AHUMADO', '', 4.10, 22, 5, NULL, 1, '2025-07-12 23:33:12', '2025-07-12 23:33:12'),
(23, 'CHORIZO ARTESANAL PICANTE', '', 4.00, 20, 5, NULL, 1, '2025-07-12 23:33:12', '2025-07-12 23:33:12'),
(24, 'HUESO COPA DE CERDO', '', 2.80, 29, 6, NULL, 1, '2025-07-12 23:33:12', '2025-07-12 23:33:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(1, 'admin', 'admin@discarchar.com', '$2y$10$F/SLl6wzKijfIqHWrC0Zfu0cD4k8qS.I6H6aLREWQf/YOeMwb0XuO', 'admin', '2025-07-12 23:21:32', '2025-07-22 23:14:39', '2025-07-22 23:14:39', 1),
(2, 'Yeaan', 'jeanguerrero04@gmail.com', '$2y$10$7qyoN2HHl0dKtr9dV4EzwOqfSmirt0T6.CzPvGAPmMStNB8DOIumG', 'user', '2025-07-12 23:24:23', '2025-07-22 23:15:05', '2025-07-22 23:15:05', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
