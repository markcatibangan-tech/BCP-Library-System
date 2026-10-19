CREATE DATABASE IF NOT EXISTS library_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_system;

CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(120) NOT NULL,
 username VARCHAR(60) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('student','staff') NOT NULL DEFAULT 'student',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS books (
 id INT AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(180) NOT NULL,
 author VARCHAR(140) NOT NULL,
 category VARCHAR(100) DEFAULT '',
 isbn VARCHAR(30) DEFAULT '',
 copies INT NOT NULL DEFAULT 1,
 available INT NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservations (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 book_id INT NOT NULL,
 status ENUM('Pending','Approved','Rejected','Returned') NOT NULL DEFAULT 'Pending',
 reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Demo staff account: username admin / password admin123
-- Change the password after first login.
INSERT IGNORE INTO users (full_name, username, password, role)
VALUES ('Library Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'staff');

INSERT INTO books (title, author, category, isbn, copies, available)
SELECT 'Introduction to Computing', 'Peter Norton', 'Computer Studies', '9780000000011', 5, 5
WHERE NOT EXISTS (SELECT 1 FROM books WHERE title='Introduction to Computing');
INSERT INTO books (title, author, category, isbn, copies, available)
SELECT 'Database System Concepts', 'Abraham Silberschatz', 'Computer Studies', '9780000000012', 3, 3
WHERE NOT EXISTS (SELECT 1 FROM books WHERE title='Database System Concepts');
INSERT INTO books (title, author, category, isbn, copies, available)
SELECT 'Computer Networks', 'Andrew S. Tanenbaum', 'Computer Studies', '9780000000013', 4, 4
WHERE NOT EXISTS (SELECT 1 FROM books WHERE title='Computer Networks');
