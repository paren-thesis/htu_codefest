-- HTU COMPSSA CODEFEST 2025 - Database Setup
-- Departmental Dues Management System

-- Create database
CREATE DATABASE IF NOT EXISTS htu_codefest_25;
USE htu_codefest_25;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS programmes;

-- Create roles table
CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create programmes table
CREATE TABLE programmes (
    programme_id INT PRIMARY KEY AUTO_INCREMENT,
    programme_name VARCHAR(100) NOT NULL UNIQUE,
    programme_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create users table (for authentication)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

-- Create students table
CREATE TABLE students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    index_no VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    academic_year VARCHAR(20) NOT NULL,
    programme_id INT NOT NULL,
    position VARCHAR(50) DEFAULT 'student',
    start_date DATE,
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (programme_id) REFERENCES programmes(programme_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create payments table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    receipt_no VARCHAR(50) NOT NULL UNIQUE,
    payment_date DATE NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Insert default roles
INSERT INTO roles (role_name, description) VALUES
('administrator', 'Full system access and control'),
('supervisor', 'Can view reports and manage students'),
('cashier', 'Can process payments and view student data'),
('student', 'Can view own data and payment history'),
('lecturer', 'Can view student data and reports');

-- Insert programmes from the CSV data
INSERT INTO programmes (programme_name, programme_code) VALUES
('BTech Information Communication Technology', 'BTech ICT'),
('BTech Computer Science', 'BTech CS'),
('HND Information Communication Technology', 'HND ICT'),
('HND Computer Science', 'HND CS');

-- Create indexes for better performance
CREATE INDEX idx_students_index_no ON students(index_no);
CREATE INDEX idx_students_email ON students(email);
CREATE INDEX idx_payments_student_id ON payments(student_id);
CREATE INDEX idx_payments_receipt_no ON payments(receipt_no);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);

-- Create a view for student payment summary
CREATE VIEW student_payment_summary AS
SELECT 
    s.student_id,
    s.index_no,
    s.first_name,
    s.surname,
    s.email,
    s.academic_year,
    p.programme_name,
    s.position,
    COALESCE(SUM(pay.amount), 0) as total_paid,
    COUNT(pay.payment_id) as payment_count,
    s.created_at
FROM students s
LEFT JOIN programmes p ON s.programme_id = p.programme_id
LEFT JOIN payments pay ON s.student_id = pay.student_id
GROUP BY s.student_id, s.index_no, s.first_name, s.surname, s.email, s.academic_year, p.programme_name, s.position, s.created_at;

-- Create default administrator user (password: admin123)
-- Note: This hash will be updated by the fix_admin_password.php script
INSERT INTO users (username, password_hash, email, role_id) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@htu.edu.gh', 1);

-- Grant permissions (if using MySQL 8.0+)
-- CREATE USER 'htu_codefest_user'@'localhost' IDENTIFIED BY 'secure_password_123';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON htu_codefest_25.* TO 'htu_codefest_user'@'localhost';
-- FLUSH PRIVILEGES; 