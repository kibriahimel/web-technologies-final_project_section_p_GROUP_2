-- ============================================================
-- Library Management System - Database SQL
-- University Project | Run on XAMPP / phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('member','librarian','manager','admin') DEFAULT 'member',
    branch_id INT DEFAULT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_pic VARCHAR(255) DEFAULT 'default.png',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: branches
-- ============================================================
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(100),
    manager_id INT DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: branch_policies
-- ============================================================
CREATE TABLE IF NOT EXISTS branch_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    max_borrow_days INT DEFAULT 14,
    max_books_per_member INT DEFAULT 5,
    fine_per_day DECIMAL(5,2) DEFAULT 5.00,
    reservation_days INT DEFAULT 3,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: genres
-- ============================================================
CREATE TABLE IF NOT EXISTS genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    genre_name VARCHAR(100) NOT NULL,
    description TEXT
);

-- ============================================================
-- TABLE: books
-- ============================================================
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    isbn VARCHAR(50),
    genre_id INT DEFAULT NULL,
    publisher VARCHAR(150),
    publish_year YEAR,
    description TEXT,
    cover_image VARCHAR(255) DEFAULT 'no_cover.png',
    total_copies INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: branch_inventory
-- ============================================================
CREATE TABLE IF NOT EXISTS branch_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    book_id INT NOT NULL,
    available_copies INT DEFAULT 0,
    total_copies INT DEFAULT 0
);

-- ============================================================
-- TABLE: borrow_records
-- ============================================================
CREATE TABLE IF NOT EXISTS borrow_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    branch_id INT NOT NULL,
    librarian_id INT DEFAULT NULL,
    status ENUM('pending','active','returned','rejected') DEFAULT 'pending',
    borrow_date DATE DEFAULT NULL,
    due_date DATE DEFAULT NULL,
    return_date DATE DEFAULT NULL,
    renewals_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- ============================================================
-- TABLE: reservations
-- ============================================================
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    branch_id INT NOT NULL,
    reservation_date DATE DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    status ENUM('pending','active','cancelled','fulfilled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: fines
-- ============================================================
CREATE TABLE IF NOT EXISTS fines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    borrow_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(8,2) DEFAULT 0.00,
    reason VARCHAR(255),
    status ENUM('unpaid','paid') DEFAULT 'unpaid',
    paid_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: book_reviews
-- ============================================================
CREATE TABLE IF NOT EXISTS book_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: announcements
-- ============================================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    branch_id INT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: inter_branch_requests
-- ============================================================
CREATE TABLE IF NOT EXISTS inter_branch_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    from_branch_id INT NOT NULL,
    to_branch_id INT NOT NULL,
    requested_by INT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    approved_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: reading_lists
-- ============================================================
CREATE TABLE IF NOT EXISTS reading_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Branches
INSERT INTO branches (branch_name, location, phone, email) VALUES
('Main Library', '123 University Road, Dhaka', '01711000001', 'main@library.com'),
('North Branch', '45 North Campus, Dhaka', '01711000002', 'north@library.com'),
('South Branch', '78 South Gate, Dhaka', '01711000003', 'south@library.com');

-- Branch Policies
INSERT INTO branch_policies (branch_id, max_borrow_days, max_books_per_member, fine_per_day, reservation_days) VALUES
(1, 14, 5, 5.00, 3),
(2, 10, 4, 3.00, 2),
(3, 7, 3, 2.00, 2);

-- Genres
INSERT INTO genres (genre_name, description) VALUES
('Science Fiction', 'Books about futuristic technology and space'),
('History', 'Books about historical events and figures'),
('Programming', 'Books about coding and software'),
('Literature', 'Classic and modern literary works'),
('Mathematics', 'Books about math and logic');

-- Users (password = "password123" hashed with MD5 for simplicity in beginner project)
INSERT INTO users (full_name, email, password, role, branch_id, phone) VALUES
('Admin User', 'admin@library.com', MD5('password123'), 'admin', 1, '01700000001'),
('Main Manager', 'manager@library.com', MD5('password123'), 'manager', 1, '01700000002'),
('John Librarian', 'librarian@library.com', MD5('password123'), 'librarian', 1, '01700000003'),
('Alice Member', 'alice@member.com', MD5('password123'), 'member', NULL, '01700000004'),
('Bob Member', 'bob@member.com', MD5('password123'), 'member', NULL, '01700000005');

-- Update branches with manager
UPDATE branches SET manager_id = 2 WHERE id = 1;

-- Books
INSERT INTO books (title, author, isbn, genre_id, publisher, publish_year, description) VALUES
('The Great Adventure', 'John Smith', '978-0001', 4, 'Penguin Books', 2015, 'An adventure novel full of surprises.'),
('Introduction to PHP', 'Jane Doe', '978-0002', 3, 'TechPress', 2020, 'A beginner guide to PHP programming.'),
('World History Vol 1', 'Mark Brown', '978-0003', 2, 'HistoryHouse', 2010, 'A comprehensive world history.'),
('Clean Code', 'Robert Martin', '978-0004', 3, 'Prentice Hall', 2008, 'Writing readable and maintainable code.'),
('Cosmos', 'Carl Sagan', '978-0005', 1, 'Random House', 1980, 'A journey through the universe.');

-- Branch Inventory
INSERT INTO branch_inventory (branch_id, book_id, available_copies, total_copies) VALUES
(1, 1, 3, 3),
(1, 2, 2, 2),
(1, 3, 1, 2),
(2, 4, 3, 3),
(2, 5, 2, 2),
(3, 1, 1, 2),
(3, 3, 2, 2);

-- Borrow Records
-- Borrow Records

INSERT INTO borrow_records 
(user_id, book_id, branch_id, borrow_date, due_date, status, approved_by) 
VALUES
(4, 1, 1, '2025-04-01', '2025-04-15', 'returned', 3),
(5, 2, 1, '2025-04-10', '2025-04-24', 'approved', 3);

-- Book Reviews
INSERT INTO book_reviews (book_id, user_id, rating, review_text) VALUES
(1, 4, 5, 'Amazing book! Loved every page.'),
(2, 5, 4, 'Very helpful for beginners.');

-- Announcements
INSERT INTO announcements (title, content, branch_id, created_by) VALUES
('Library Closed on Friday', 'The library will be closed for maintenance on Friday.', 1, 3),
('New Books Arrived', 'We have received 50 new books this month!', NULL, 1);

-- Reading Lists
INSERT INTO reading_lists (user_id, book_id) VALUES
(4, 3),
(4, 5),
(5, 1);

-- Fines
INSERT INTO fines (borrow_id, user_id, amount, reason, status) VALUES
(1, 4, 10.00, 'Book returned 2 days late', 'paid');
