-- ============================================================
-- EWU Library Management System — Database Schema & Seed Data
-- CSE302 Database Systems Final Project
-- 8 tables: users, members, authors, categories, publishers,
--           books, borrows, fines
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS ewu_library CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ewu_library;

-- ------------------------------------------------------------
-- users  (login accounts; strong entity)
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,   -- bcrypt hash, never plaintext
  `role` enum('Admin','Member') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed passwords (plaintext shown for the report only — never store like this):
--   admin  -> admin123
--   john   -> john123
--   sarah  -> sarah123
--   tanvir -> tanvir123
INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin',  'admin@ewu.edu.bd',  '$2b$10$w4zQqc7G3WXeobx.QQGZOuYU4fWnKyAiBc3ixP.WqO4TzqGVPL7WG', 'Admin',  '2026-07-30 16:20:13'),
(2, 'john',   'john@ewu.edu.bd',   '$2b$10$OYVGvwQwzWwj6/2sx0TwoOxzgZYO/vhDV2g.YEpiMzbmLzAZTbGCe', 'Member', '2026-07-30 16:20:13'),
(3, 'sarah',  'sarah@ewu.edu.bd',  '$2b$10$T3eODIbBqsgP1dDR9v2x9OpQXetAnzTWdyhCfz8WiaENbbrwJfOg2', 'Member', '2026-07-30 16:20:13'),
(4, 'tanvir', 'tanvir@ewu.edu.bd', '$2b$10$vdvKvv78KeJhR4PRzqxn.OYsSEzRkuBD2fb/tbDbaQcAZ6Us4d1T2', 'Member', '2026-07-30 16:20:13');

ALTER TABLE `users` AUTO_INCREMENT = 5;

-- ------------------------------------------------------------
-- members  (weak entity — identified by user_id + own key; 1:1 with users)
-- ------------------------------------------------------------
CREATE TABLE `members` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `student_id` (`student_id`),
  CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `members` (`member_id`, `user_id`, `student_id`, `full_name`, `phone`, `department`) VALUES
(1, 2, '2023-1-60-001', 'John Smith', '01711111111', 'CSE'),
(2, 3, '2023-1-60-002', 'Sarah Ahmed', '01822222222', 'EEE'),
(3, 4, '2023-1-60-003', 'Tanvir Hasan', '01933333333', 'BBA');

ALTER TABLE `members` AUTO_INCREMENT = 4;

-- ------------------------------------------------------------
-- authors / categories / publishers (strong entities, 1:N into books)
-- ------------------------------------------------------------
CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `author_name` varchar(100) NOT NULL,
  `country` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `authors` (`author_id`, `author_name`, `country`) VALUES
(1, 'Abraham Silberschatz', 'USA'),
(2, 'Thomas H. Cormen', 'USA'),
(3, 'Herbert Schildt', 'USA'),
(4, 'Bjarne Stroustrup', 'Denmark');

ALTER TABLE `authors` AUTO_INCREMENT = 5;

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Database'),
(2, 'Programming'),
(3, 'Networking'),
(4, 'Mathematics');

ALTER TABLE `categories` AUTO_INCREMENT = 5;

CREATE TABLE `publishers` (
  `publisher_id` int(11) NOT NULL AUTO_INCREMENT,
  `publisher_name` varchar(100) NOT NULL,
  `address` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`publisher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `publishers` (`publisher_id`, `publisher_name`, `address`, `phone`) VALUES
(1, 'McGraw-Hill', 'New York', '111111111'),
(2, 'Pearson', 'London', '222222222'),
(3, 'O''Reilly Media', 'California', '333333333');

ALTER TABLE `publishers` AUTO_INCREMENT = 4;

-- ------------------------------------------------------------
-- books  (strong entity; M:1 into authors/categories/publishers)
-- ------------------------------------------------------------
CREATE TABLE `books` (
  `book_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `isbn` varchar(30) DEFAULT NULL,
  `publication_year` year(4) DEFAULT NULL,
  `edition` varchar(20) DEFAULT NULL,
  `total_copies` int(11) DEFAULT 1,
  `available_copies` int(11) DEFAULT 1,
  `shelf_no` varchar(20) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `publisher_id` int(11) NOT NULL,
  PRIMARY KEY (`book_id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `author_id` (`author_id`),
  KEY `category_id` (`category_id`),
  KEY `publisher_id` (`publisher_id`),
  CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`),
  CONSTRAINT `books_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  CONSTRAINT `books_ibfk_3` FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`publisher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `books` (`book_id`, `title`, `isbn`, `publication_year`, `edition`, `total_copies`, `available_copies`, `shelf_no`, `author_id`, `category_id`, `publisher_id`) VALUES
(1, 'Database System Concepts', '9780073523323', '2019', '7th', 5, 4, 'A-01', 1, 1, 1),
(2, 'Introduction to Algorithms', '9780262046305', '2022', '4th', 4, 3, 'A-02', 2, 2, 2),
(3, 'Java: The Complete Reference', '9781260440232', '2021', '12th', 6, 4, 'B-01', 3, 2, 1),
(5, 'Intro. to C++', '1462778128', '1988', '2nd', 3, 2, 'B-02', 2, 2, 1);

ALTER TABLE `books` AUTO_INCREMENT = 6;

-- ------------------------------------------------------------
-- borrows  (associative entity resolving the M:N between members & books)
-- ------------------------------------------------------------
CREATE TABLE `borrows` (
  `borrow_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('Borrowed','Returned','Overdue') DEFAULT 'Borrowed',
  PRIMARY KEY (`borrow_id`),
  KEY `member_id` (`member_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `borrows_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`),
  CONSTRAINT `borrows_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `borrows` (`borrow_id`, `member_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`) VALUES
(1, 1, 1, '2026-07-20', '2026-07-27', '2026-07-26', 'Returned'),
(2, 2, 2, '2026-07-22', '2026-07-29', '2026-07-31', 'Returned'),
(3, 1, 1, '2026-07-30', '2026-08-06', NULL, 'Borrowed'),
(4, 2, 5, '2026-07-30', '2026-08-06', NULL, 'Borrowed'),
(5, 2, 5, '2026-07-30', '2026-08-06', '2026-07-30', 'Returned'),
(6, 1, 2, '2026-07-31', '2026-08-07', NULL, 'Borrowed'),
(7, 3, 3, '2026-07-31', '2026-08-07', NULL, 'Borrowed');

ALTER TABLE `borrows` AUTO_INCREMENT = 8;

-- ------------------------------------------------------------
-- fines  (weak entity — existence-dependent on a borrows row; 1:1 with borrows)
-- ------------------------------------------------------------
CREATE TABLE `fines` (
  `fine_id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) NOT NULL,
  `amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `status` enum('Paid','Unpaid') DEFAULT 'Unpaid',
  PRIMARY KEY (`fine_id`),
  UNIQUE KEY `borrow_id` (`borrow_id`),
  CONSTRAINT `fk_fine_borrow` FOREIGN KEY (`borrow_id`) REFERENCES `borrows` (`borrow_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `fines` (`fine_id`, `borrow_id`, `amount`, `status`) VALUES
(2, 2, 40.00, 'Unpaid');

ALTER TABLE `fines` AUTO_INCREMENT = 3;

-- ============================================================
-- Additional seed records
-- ============================================================

-- ------------------------------------------------------------
-- more authors
-- ------------------------------------------------------------
INSERT INTO `authors` (`author_id`, `author_name`, `country`) VALUES
(5, 'Robert Sedgewick', 'USA'),
(6, 'Andrew S. Tanenbaum', 'Netherlands'),
(7, 'Ramez Elmasri', 'USA'),
(8, 'Kenneth H. Rosen', 'USA');

ALTER TABLE `authors` AUTO_INCREMENT = 9;

-- ------------------------------------------------------------
-- more categories
-- ------------------------------------------------------------
INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(5, 'Operating Systems'),
(6, 'Artificial Intelligence'),
(7, 'Software Engineering');

ALTER TABLE `categories` AUTO_INCREMENT = 8;

-- ------------------------------------------------------------
-- more publishers
-- ------------------------------------------------------------
INSERT INTO `publishers` (`publisher_id`, `publisher_name`, `address`, `phone`) VALUES
(4, 'Wiley', 'New Jersey', '444444444'),
(5, 'Addison-Wesley', 'Boston', '555555555');

ALTER TABLE `publishers` AUTO_INCREMENT = 6;

-- ------------------------------------------------------------
-- more books
-- ------------------------------------------------------------
INSERT INTO `books` (`book_id`, `title`, `isbn`, `publication_year`, `edition`, `total_copies`, `available_copies`, `shelf_no`, `author_id`, `category_id`, `publisher_id`) VALUES
(6, 'Algorithms', '9780321573513', '2011', '4th', 4, 3, 'A-03', 5, 2, 5),
(7, 'Modern Operating Systems', '9780133591620', '2014', '4th', 3, 3, 'C-01', 6, 5, 2),
(8, 'Fundamentals of Database Systems', '9780133970777', '2015', '7th', 5, 5, 'A-04', 7, 1, 2),
(9, 'Discrete Mathematics and Its Applications', '9780073383095', '2018', '8th', 4, 2, 'D-01', 8, 4, 1),
(10, 'Computer Networks', '9780132126953', '2010', '5th', 3, 3, 'C-02', 6, 3, 2);

ALTER TABLE `books` AUTO_INCREMENT = 11;

-- ------------------------------------------------------------
-- more users / members
-- ------------------------------------------------------------
-- Seed passwords (plaintext shown for the report only — never store like this):
--   nabila -> nabila123
--   rakib  -> rakib123
INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(5, 'nabila', 'nabila@ewu.edu.bd', '$2b$10$2Y833QwnFkacV9x0iFgDB.FJGmoXtzrbe5nYz4AmRlb41/vSex4MG', 'Member', '2026-08-01 09:15:00'),
(6, 'rakib',  'rakib@ewu.edu.bd',  '$2b$10$Qc9Zx8lBQJvJs7Rxjli3XuwBVm26m2z3nUSy7IhdsknuRhpbab9UC', 'Member', '2026-08-01 09:20:00');

ALTER TABLE `users` AUTO_INCREMENT = 7;

INSERT INTO `members` (`member_id`, `user_id`, `student_id`, `full_name`, `phone`, `department`) VALUES
(4, 5, '2023-1-60-004', 'Nabila Islam', '01744444444', 'CSE'),
(5, 6, '2023-1-60-005', 'Rakib Hossain', '01655555555', 'ME');

ALTER TABLE `members` AUTO_INCREMENT = 6;

-- ------------------------------------------------------------
-- more borrows
-- ------------------------------------------------------------
INSERT INTO `borrows` (`borrow_id`, `member_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`) VALUES
(8, 4, 6, '2026-08-01', '2026-08-08', NULL, 'Borrowed'),
(9, 5, 8, '2026-07-15', '2026-07-22', '2026-07-20', 'Returned'),
(10, 3, 9, '2026-07-25', '2026-08-01', NULL, 'Overdue'),
(11, 4, 3, '2026-07-28', '2026-08-04', NULL, 'Borrowed');

ALTER TABLE `borrows` AUTO_INCREMENT = 12;

-- ------------------------------------------------------------
-- more fines
-- ------------------------------------------------------------
INSERT INTO `fines` (`fine_id`, `borrow_id`, `amount`, `status`) VALUES
(3, 10, 20.00, 'Unpaid');

ALTER TABLE `fines` AUTO_INCREMENT = 4;

-- ============================================================
-- Additional seed records (batch 2)
-- ============================================================

-- ------------------------------------------------------------
-- more authors
-- ------------------------------------------------------------
INSERT INTO `authors` (`author_id`, `author_name`, `country`) VALUES
(9, 'Donald Knuth', 'USA'),
(10, 'Martin Fowler', 'UK'),
(11, 'Stuart Russell', 'UK');

ALTER TABLE `authors` AUTO_INCREMENT = 12;

-- ------------------------------------------------------------
-- more categories
-- ------------------------------------------------------------
INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(8, 'Computer Architecture'),
(9, 'Data Science');

ALTER TABLE `categories` AUTO_INCREMENT = 10;

-- ------------------------------------------------------------
-- more publishers
-- ------------------------------------------------------------
INSERT INTO `publishers` (`publisher_id`, `publisher_name`, `address`, `phone`) VALUES
(6, 'Cambridge University Press', 'Cambridge', '666666666');

ALTER TABLE `publishers` AUTO_INCREMENT = 7;

-- ------------------------------------------------------------
-- more books
-- ------------------------------------------------------------
INSERT INTO `books` (`book_id`, `title`, `isbn`, `publication_year`, `edition`, `total_copies`, `available_copies`, `shelf_no`, `author_id`, `category_id`, `publisher_id`) VALUES
(11, 'The Art of Computer Programming, Vol. 1', '9780201896831', '1997', '3rd', 3, 2, 'E-01', 9, 2, 5),
(12, 'Refactoring: Improving the Design of Existing Code', '9780134757599', '2018', '2nd', 4, 4, 'E-02', 10, 7, 2),
(13, 'Artificial Intelligence: A Modern Approach', '9780134610993', '2020', '4th', 5, 4, 'F-01', 11, 6, 2),
(14, 'Computer Organization and Design', '9780128203316', '2020', '6th', 3, 2, 'F-02', 2, 8, 6);

ALTER TABLE `books` AUTO_INCREMENT = 15;

-- ------------------------------------------------------------
-- more users / members
-- ------------------------------------------------------------
-- Seed passwords (plaintext shown for the report only — never store like this):
--   mahin  -> mahin123
--   fariha -> fariha123
--   shuvo  -> shuvo123
INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(7, 'mahin',  'mahin@ewu.edu.bd',  '$2b$10$PSHd4XofzXz0nNjSKDXn0emtaAbioCIG4RALlk4OIPl15psZRJk0e', 'Member', '2026-08-02 10:05:00'),
(8, 'fariha', 'fariha@ewu.edu.bd', '$2b$10$Bh3L1VS6TBwMmYPRubIcC.kJwoXROHnoAaeiL9iJPHH5jCwvyGkUm', 'Member', '2026-08-02 10:10:00'),
(9, 'shuvo',  'shuvo@ewu.edu.bd',  '$2b$10$RlrP4grwDWK4Oi1goBusJOapos13gSPPx4O8ZVRx6x15YB/mHREX6', 'Member', '2026-08-02 10:15:00');

ALTER TABLE `users` AUTO_INCREMENT = 10;

INSERT INTO `members` (`member_id`, `user_id`, `student_id`, `full_name`, `phone`, `department`) VALUES
(6, 7, '2023-1-60-006', 'Mahin Chowdhury', '01766666666', 'CSE'),
(7, 8, '2023-1-60-007', 'Fariha Rahman', '01877777777', 'ETE'),
(8, 9, '2023-1-60-008', 'Shuvo Das', '01988888888', 'BBA');

ALTER TABLE `members` AUTO_INCREMENT = 9;

-- ------------------------------------------------------------
-- more borrows
-- ------------------------------------------------------------
INSERT INTO `borrows` (`borrow_id`, `member_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`) VALUES
(12, 6, 11, '2026-08-02', '2026-08-09', NULL, 'Borrowed'),
(13, 7, 13, '2026-08-02', '2026-08-09', NULL, 'Borrowed'),
(14, 8, 12, '2026-07-18', '2026-07-25', '2026-07-24', 'Returned'),
(15, 5, 14, '2026-07-20', '2026-07-27', NULL, 'Overdue'),
(16, 6, 9, '2026-08-03', '2026-08-10', NULL, 'Borrowed');

ALTER TABLE `borrows` AUTO_INCREMENT = 17;

-- ------------------------------------------------------------
-- more fines
-- ------------------------------------------------------------
INSERT INTO `fines` (`fine_id`, `borrow_id`, `amount`, `status`) VALUES
(4, 15, 30.00, 'Unpaid');

ALTER TABLE `fines` AUTO_INCREMENT = 5;

COMMIT;
