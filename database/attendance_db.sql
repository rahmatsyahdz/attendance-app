-- Attendance Application Database Schema
-- Created: 2025-12-12

-- Create database
CREATE DATABASE IF NOT EXISTS attendance_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_db;

-- Departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    department_id INT,
    phone VARCHAR(20),
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    clock_in TIME,
    clock_out TIME,
    clock_in_photo VARCHAR(255),
    clock_out_photo VARCHAR(255),
    clock_in_location VARCHAR(255),
    clock_out_location VARCHAR(255),
    status ENUM('present', 'late', 'absent') DEFAULT 'present',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, date),
    INDEX idx_date (date),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default departments
INSERT INTO departments (name, description) VALUES
('IT', 'Information Technology Department'),
('HR', 'Human Resources Department'),
('Finance', 'Finance Department'),
('Marketing', 'Marketing Department'),
('Operations', 'Operations Department');

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role, department_id, phone) VALUES
('Administrator', 'admin@attendance.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '081234567890');

-- Insert sample employee (password: employee123)
INSERT INTO users (name, email, password, role, department_id, phone) VALUES
('John Doe', 'john@attendance.com', '$2y$10$vZ9z5OZ9Z5OZ9Z5OZ9Z5Ou.F5F5F5F5F5F5F5F5F5F5F5F5F5F5F5', 'employee', 2, '081234567891');
