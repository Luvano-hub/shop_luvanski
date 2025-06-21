-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 08:31 PM
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
-- Database: `shop_luvanski`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

CREATE TABLE `favourites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favourites`
--

INSERT INTO `favourites` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(14, 10, 22, '2025-06-13 12:30:41'),
(15, 10, 14, '2025-06-13 18:10:15');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `delivery_method` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `speed` varchar(50) NOT NULL,
  `delivery_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `card_last4` char(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `status` enum('pending','processing','shipped','delivered') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `delivery_method`, `address`, `speed`, `delivery_date`, `total_amount`, `card_last4`, `created_at`, `status`) VALUES
(9, 10, 'courier', '44 Mandela road, Waterfall, Midrand, 1234', 'fast', '2025-06-13', 4390.00, '4321', '2025-06-05 23:30:59', 'delivered'),
(10, 10, 'courier', '123BOb, kak, Jozi, 1234', 'fast', '2025-06-19', 1290.00, '5731', '2025-06-12 23:58:27', 'processing'),
(11, 10, 'courier', '20 Van Jaarsveld, Edenvale, Edenvale, 1666', 'normal', '2025-06-20', 2100.00, '3215', '2025-06-13 18:23:35', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `seller_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `seller_id`) VALUES
(14, 9, 17, '1988 Gucci Handbag', 1, 2500.00, 11),
(15, 9, 21, 'Lego Bugatti ', 1, 1500.00, 9),
(16, 9, 14, 'Camping Stan Cup', 2, 150.00, 9),
(17, 10, 23, 'Hennessy', 2, 600.00, 9),
(18, 11, 14, 'Camping Stan Cup', 1, 150.00, 9),
(19, 11, 21, 'Lego Bugatti ', 1, 1500.00, 9),
(20, 11, 20, 'Black Gravity T shirt ', 2, 200.00, 8);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(255) NOT NULL DEFAULT 'Uncategorized',
  `rating` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `store_id`, `name`, `description`, `price`, `seller_id`, `quantity`, `image`, `created_at`, `category`, `rating`) VALUES
(12, 2, 'Eyelash curler', 'Suitable for getting the perfect current eyelashes for any type. ', 70.00, 8, 10, 'uploads/6841dfe79522f_Eyelash curler.jpg', '2025-06-05 18:20:23', 'Beauty', 0),
(13, 2, 'Makeup kit', 'This makeup kit has all the essentials, from all the colours to all the brushes. All you need in one kit ', 120.00, 8, 5, 'uploads/Makeup kit.jpg', '2025-06-05 18:22:10', 'Beauty', 0),
(14, 3, 'Camping Stan Cup', 'This cup is durable, rugged and suitable for any camping setting', 150.00, 9, 20, 'uploads/6841e166d5639_1) Camping Stanley Cup.jpg', '2025-06-05 18:26:46', 'Camping', 0),
(15, 3, 'Camping mattress ', 'This mattress is suitable for any camping settings. Comes with patching kit and pump ', 450.00, 9, 3, 'uploads/6841e1aa9f11e_2) Camping Mattres.jpg', '2025-06-05 18:27:54', 'Camping', 0),
(16, 4, 'Gucci Handbag', 'Authentic Italian leather, made in italy. ', 2000.00, 11, 2, 'uploads/68420781aa604_1) Gucci Handbag.jpg', '2025-06-05 21:09:21', 'Luxury', 0),
(17, 4, '1988 Gucci Handbag', 'Authentic vintage Italian leather made in Rome, Italy.', 2500.00, 11, 2, 'uploads/684208156a217_2) Gucci Handbag 2.jpg', '2025-06-05 21:11:49', 'Luxury', 0),
(18, 4, 'Vintage Italian Gucci Handbag', 'Vintage Italian Gucci Handbag, made from The finest leather. 1993', 2000.00, 11, 2, 'uploads/68420856753ef_3) Gucci vintage handbag.jpg', '2025-06-05 21:12:54', 'Luxury', 0),
(19, 2, 'Full Twilight series books ', 'This is the full collection of the Twilight series books ', 800.00, 8, 1, 'uploads/684209091e5f2_2) Full twilight book series..jpg', '2025-06-05 21:15:53', 'Books', 0),
(20, 2, 'Black Gravity T shirt ', 'homemade T-shirt, made to order by hand.', 200.00, 8, 50, 'uploads/68420986bbc07_2) Black Grapic T.jpg', '2025-06-05 21:17:58', 'Clothing & Accessories', 0),
(21, 3, 'Lego Bugatti ', 'Lego Replica of the Bugatti Bolide, made with the help of Bugatti engineers for Precision and performance ', 1500.00, 9, 7, 'uploads/68420a3c61212_1) Lego Bugatti.jpg', '2025-06-05 21:21:00', 'Toys', 0),
(22, 3, 'Play Station 5 Slim', 'Sony presents the playstation 5 Slim. A more slimmed-down version of the original PlayStation 5 but for all the Same functionality but more compact.', 10000.00, 9, 4, 'uploads/684af65056732_3) PS5 Slim with controller.jpg', '2025-06-12 15:46:24', 'Gaming', 0),
(23, 3, 'Hennessy', 'bla', 600.00, 9, 5, 'uploads/684b4c8f25bf5_1) Hennessy .jpg', '2025-06-12 21:54:23', 'Liquor', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `user_id` int(11) NOT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `bank_info` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `store_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `bank_info` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `seller_id`, `store_name`, `description`, `location`, `bank_info`) VALUES
(2, 8, 'Girly Girl Store', 'This store caters to girls. You can find anything from beauty products, books, clothing and anything you can imagine.', 'Centurion ', NULL),
(3, 9, 'Manly Man Store', 'This store is catered to manly products, from camping equipment, gaming stuff, liquor, sports and so on. ', 'Johannesburg ', NULL),
(4, 11, 'Lux Luxury Store', 'The story is created to sell luxury products. Items are expensive but rare – not a lot of stock.', 'Cape Town', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('buyer','seller') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(8, 'Girl store', 'girlstore@gmail.com', '$2y$10$inng.Xy7k2YsEGyOcJPzL.qk16OSPVZqR.LjO9IAXjsxXKmVQ45EC', 'seller', '2025-06-05 18:15:49'),
(9, 'Man store', 'manstore@gmail.com', '$2y$10$Pqnd5efcghtKZR1Z4bVsiu/H04B0vUcaPUMs2gPelv3P.KJPhvghy', 'seller', '2025-06-05 18:23:31'),
(10, 'Luvano', 'luvano@gmail.com', '$2y$10$VKQopbCHRvk4EXf92ogeceANq/Zhi2s37u6HEPCcAKfY3vS3VQJjS', 'buyer', '2025-06-05 18:29:09'),
(11, 'Lux Luxury store', 'Lux@gmail.com', '$2y$10$Vwml5NM7qLacEKJoJhp4uu8wRPvsULENWFLz/otPnjelGD8Guegu2', 'seller', '2025-06-05 21:05:19'),
(12, 'admin', 'admin@example.com', '$2y$10$0omiFCwHNwQj31jsAm79h.QBg5WXIjrviSJ0hP4BnUYjQXxOnQeni', '', '2025-06-12 09:53:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `favourites`
--
ALTER TABLE `favourites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favourite` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

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
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`store_id`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `favourites`
--
ALTER TABLE `favourites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favourites`
--
ALTER TABLE `favourites`
  ADD CONSTRAINT `favourites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `favourites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sellers`
--
ALTER TABLE `sellers`
  ADD CONSTRAINT `sellers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
