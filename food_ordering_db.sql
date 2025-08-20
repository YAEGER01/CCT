-- -----------------------------------------------------
-- Database: food_ordering_db
-- -----------------------------------------------------
DROP DATABASE IF EXISTS food_ordering_db;
CREATE DATABASE food_ordering_db;
USE food_ordering_db;

-- -----------------------------------------------------
-- Table: users
-- -----------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','seller') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: meals
-- -----------------------------------------------------
CREATE TABLE `meals` (
  `meal_id` int(11) NOT NULL AUTO_INCREMENT,
  `meal_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `rice_options` varchar(255) DEFAULT NULL,
  `rice_price_1` decimal(10,2) DEFAULT NULL,
  `rice_price_2` decimal(10,2) DEFAULT NULL,
  `drinks` varchar(255) DEFAULT NULL,
  `drinks_price` decimal(10,2) DEFAULT NULL,
  `seller_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`meal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: cart
-- -----------------------------------------------------
CREATE TABLE `cart` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  meal_id INT NOT NULL,
  meal_name VARCHAR(100) NOT NULL,
  quantity INT DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  rice_option VARCHAR(50),
  rice_price DECIMAL(10,2) DEFAULT 0.00,
  drinks VARCHAR(50),
  drink_price DECIMAL(10,2) DEFAULT 0.00,
  total_price DECIMAL(10,2) NOT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (meal_id) REFERENCES meals(meal_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: orders
-- -----------------------------------------------------
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  meal_id INT NOT NULL,
  quantity INT NOT NULL,
  rice_option VARCHAR(100),
  rice_price DECIMAL(10,2) DEFAULT 0.00,
  drinks VARCHAR(100),
  drinks_price DECIMAL(10,2) DEFAULT 0.00,
  status ENUM('pending','completed','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (meal_id) REFERENCES meals(meal_id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Table: transactions
-- -----------------------------------------------------
CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  seller_id INT NOT NULL,
  order_id INT NOT NULL,
  meal_id INT NOT NULL,
  quantity INT NOT NULL,
  rice_option VARCHAR(100),
  rice_price DECIMAL(10,2) DEFAULT 0.00,
  drinks VARCHAR(100),
  drinks_price DECIMAL(10,2) DEFAULT 0.00,
  total_price DECIMAL(10,2) NOT NULL,
  transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (meal_id) REFERENCES meals(meal_id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Table: accepted_orders
-- -----------------------------------------------------
CREATE TABLE accepted_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  user_id INT NOT NULL,
  meal_id INT NOT NULL,
  quantity INT NOT NULL,
  status VARCHAR(50) DEFAULT 'accepted',
  price DECIMAL(10,2) NOT NULL,
  rice_option VARCHAR(255),
  rice_price DECIMAL(10,2) DEFAULT 0,
  drinks VARCHAR(255),
  drinks_price DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
