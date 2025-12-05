-- Create database and tables for flight booking system
-- Note: Run this in phpMyAdmin or MySQL client

DROP DATABASE IF EXISTS flight_db;
CREATE DATABASE flight_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flight_db;

-- Users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Flights table
CREATE TABLE flights (
  id INT AUTO_INCREMENT PRIMARY KEY,
  flight_number VARCHAR(20) NOT NULL,
  origin VARCHAR(100) NOT NULL,
  destination VARCHAR(100) NOT NULL,
  departure_time DATETIME NOT NULL,
  arrival_time DATETIME NOT NULL,
  seats_total INT NOT NULL,
  seats_available INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_route_time (origin, destination, departure_time),
  INDEX idx_departure (departure_time)
) ENGINE=InnoDB;

-- Bookings table
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  flight_id INT NOT NULL,
  booking_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  num_passengers INT NOT NULL DEFAULT 1,
  passenger_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  document_id VARCHAR(50) DEFAULT NULL,
  status ENUM('pending_payment','confirmed','cancelled') NOT NULL DEFAULT 'pending_payment',
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT fk_bookings_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_flights FOREIGN KEY (flight_id) REFERENCES flights(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_flight (flight_id)
) ENGINE=InnoDB;

-- Payments table
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method VARCHAR(50) NOT NULL,
  status ENUM('paid','failed','pending') NOT NULL DEFAULT 'paid',
  transaction_ref VARCHAR(100) DEFAULT NULL,
  paid_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_bookings FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  INDEX idx_booking (booking_id)
) ENGINE=InnoDB;

-- Seed users
-- For convenience, initial passwords are in plaintext and will be re-hashed on first login by the app.
INSERT INTO users (name, email, password, role) VALUES
('Test User', 'user@gmail.com', 'Test1234', 'user'),
('Admin', 'admin@gmail.com', 'Admin1234', 'admin');

-- Seed flights
INSERT INTO flights (flight_number, origin, destination, departure_time, arrival_time, seats_total, seats_available, price) VALUES
('VN101', 'Hanoi', 'Ho Chi Minh City', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY + 2 HOUR), 150, 150, 1299000.00),
('VN202', 'Ho Chi Minh City', 'Da Nang', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY + 1 HOUR), 120, 120, 899000.00),
('QH303', 'Da Nang', 'Hanoi', DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY + 1 HOUR + 30 MINUTE), 100, 100, 990000.00),
('VJ404', 'Hanoi', 'Nha Trang', DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(NOW(), INTERVAL 4 DAY + 2 HOUR + 15 MINUTE), 180, 180, 1099000.00);
