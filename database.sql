-- Smart Complaint Management System with Auto-Escalation
-- Database Schema (rebuilt: proper user/admin separation)
-- Import this file via phpMyAdmin (localhost/phpmyadmin -> Import)

CREATE DATABASE IF NOT EXISTS complaint_system;
USE complaint_system;

DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admin;

-- Regular users (people who submit complaints)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Admins (people who manage complaints)
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Complaints, each linked to the user who submitted it
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category ENUM('Electricity', 'Water', 'Cleaning', 'Internet', 'Other') NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    is_escalated TINYINT(1) DEFAULT 0,
    date_submitted DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_resolved DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- NOTE: No accounts are inserted here on purpose.
-- After importing this database, open includes/create_accounts.php ONCE in
-- your browser (localhost/complaint-box/includes/create_accounts.php).
-- It creates:
--   Admin  -> Rajesh Gaikwad | username: rajesh | password: rajesh123
--   User   -> Yash Haldankar | email: yash@example.com | password: yash123
-- Delete that file after running it once, for security.
