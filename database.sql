-- Student Management System Database
-- Create database
CREATE DATABASE IF NOT EXISTS student_mgmt;
USE student_mgmt;

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    stu_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    cnic VARCHAR(20) NOT NULL UNIQUE,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    adm_date DATE NOT NULL,
    ag_no VARCHAR(50) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    class VARCHAR(50),
    section VARCHAR(20),
    degree VARCHAR(100) NOT NULL,
    phone_no VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    stu_status ENUM('Active', 'Inactive', 'Graduated', 'Suspended') DEFAULT 'Active',
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    role ENUM('student', 'admin', 'faculty') DEFAULT 'student',
    is_focal_person TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ag_no (ag_no),
    INDEX idx_department (department),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
-- NOTE: You can generate a new password hash using: php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
-- Or use the setup_admin.php script to create/update admin user
INSERT INTO students (name, father_name, dob, cnic, gender, email, adm_date, ag_no, department, degree, phone_no, address, password, role)
VALUES ('Admin User', 'Admin Father', '1990-01-01', '12345-1234567-1', 'Male', 'admin@university.edu', '2020-01-01', 'ADMIN001', 'Administration', 'Administration', '03001234567', 'University Campus', '$2y$10$VwmEE13Lmr.oaWTYNCbf8.umB7In9LmkHwlY9yX0sy1cxVvcrmcY6', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- The password hash above is for 'admin123'
-- IMPORTANT: Change the admin password after first login for security!

-- Insert default faculty user (password: faculty123)
INSERT INTO students (name, father_name, dob, cnic, gender, email, adm_date, ag_no, department, degree, phone_no, address, password, role)
VALUES ('Faculty User', 'Faculty Father', '1980-01-01', '12345-1234567-2', 'Male', 'faculty@university.edu', '2010-01-01', 'FACULTY001', 'Computer Science', 'PhD Computer Science', '03001234568', 'Faculty Campus', '$2y$10$VGFsJaAERqoJVtF5AT8psuXzz6XxNP9PM7VQU3WGK/172Zb9HnVOG', 'faculty')
ON DUPLICATE KEY UPDATE email=email;
-- The password hash above is for 'faculty123'
-- IMPORTANT: Change the faculty password after first login for security!

-- Create news table
CREATE TABLE IF NOT EXISTS news (
    news_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES students(stu_id) ON DELETE CASCADE,
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create events table
CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES students(stu_id) ON DELETE CASCADE,
    INDEX idx_created_by (created_by),
    INDEX idx_event_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create notices table
CREATE TABLE IF NOT EXISTS notices (
    notice_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    expiry_date DATE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES students(stu_id) ON DELETE CASCADE,
    INDEX idx_created_by (created_by),
    INDEX idx_priority (priority),
    INDEX idx_expiry_date (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


