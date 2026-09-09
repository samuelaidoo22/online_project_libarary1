-- GCTU Online Project Library System - Database Schema

CREATE DATABASE IF NOT EXISTS gctu_library;
USE gctu_library;

-- 1. Departments Table
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL,
    department_code VARCHAR(10) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 2. Categories Table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;

-- 3. Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('student', 'lecturer', 'admin') DEFAULT 'student',
    department_id INT,
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. Projects Table
CREATE TABLE projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    abstract TEXT NOT NULL,
    keywords VARCHAR(255),
    category_id INT,
    department_id INT,
    file_path VARCHAR(255),
    upload_date DATE NOT NULL,
    uploader_id INT,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    view_count INT DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE SET NULL,
    FOREIGN KEY (uploader_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. Authors Table
CREATE TABLE authors (
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    author_name VARCHAR(150) NOT NULL,
    student_id VARCHAR(50),
    email VARCHAR(100),
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Access Logs Table
CREATE TABLE access_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    project_id INT,
    access_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    access_type ENUM('view_abstract', 'download_full') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Notifications Table
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE SET NULL,
    INDEX idx_notifications_user_read (user_id, is_read, created_at)
) ENGINE=InnoDB;

-- 8. Admin Review Audit History
CREATE TABLE review_audit_logs (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    admin_id INT NOT NULL,
    action ENUM('approved', 'rejected') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_review_audit_created (created_at)
) ENGINE=InnoDB;

-- Seed Initial Data
INSERT INTO departments (department_name, department_code) VALUES 
('Information Technology', 'IT'),
('Mobile and Pervasive Computing', 'MPC'),
('General Science', 'GS'),
('Electrical and Electronic Engineering', 'EEE'),
('Mechanical Engineering', 'ME'),
('Civil Engineering', 'CE'),
('Software Engineering', 'SE'),
('Information Systems', 'IS'),
('Cybersecurity', 'CYB'),
('Data Science', 'DS'),
('Business Administration', 'BA'),
('Digital Marketing', 'DMKT'),
('Electrical Engineering', 'EE'),
('Mathematics and Statistics', 'MATHS'),
('Artificial Intelligence', 'AI'),
('Computer Science', 'CS'),
('Computer Engineering', 'CENG'),
('Engineering', 'ENG');
;

INSERT INTO categories (category_name, description) VALUES 
('Artificial Intelligence', 'AI and Machine Learning projects'),
('Web Development', 'Web-based systems and applications'),
('Network Security', 'Cybersecurity and network infrastructure'),
('Data Science', 'Data analysis and visualization'),
('Software Engineering', 'Software design and development');
