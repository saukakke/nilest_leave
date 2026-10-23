-- NILEST Workers' Annual Leave Management System
CREATE DATABASE IF NOT EXISTS nilest_leave CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nilest_leave;
CREATE TABLE IF NOT EXISTS departments (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL UNIQUE) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY, staff_no VARCHAR(30) NOT NULL UNIQUE, full_name VARCHAR(150) NOT NULL,
 email VARCHAR(150) NOT NULL UNIQUE, phone VARCHAR(30) DEFAULT NULL, department_id INT DEFAULT NULL,
 designation VARCHAR(120) DEFAULT NULL, grade_level VARCHAR(20) DEFAULT NULL, date_employed DATE DEFAULT NULL,
 annual_entitlement INT NOT NULL DEFAULT 30, role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
 status ENUM('active','inactive') NOT NULL DEFAULT 'active', password VARCHAR(255) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS leave_applications (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, leave_year YEAR NOT NULL, start_date DATE NOT NULL,
 end_date DATE NOT NULL, days INT NOT NULL, resumption_date DATE NOT NULL, reason TEXT,
 relief_officer VARCHAR(150) DEFAULT NULL, status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
 remarks TEXT, reviewed_by INT DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
INSERT INTO departments (name) VALUES ('Registry'),('Administration'),('Bursary'),('Library'),('Leather Technology'),('Science Laboratory Technology'),('ICT Unit')
ON DUPLICATE KEY UPDATE name = name;
-- Default accounts. Password for both: Admin@123 (CHANGE AFTER FIRST LOGIN)
INSERT INTO users (staff_no,full_name,email,phone,department_id,designation,grade_level,date_employed,annual_entitlement,role,password) VALUES
('NILEST/ADM/001','System Administrator','admin@nilest.gov.ng','08000000000',2,'Administrative Officer','GL 10','2015-01-05',30,'admin','$2y$10$RkG5PgdFPZg9H//idlIzmusGpBzdjC2BPgxi3wR/t7diAREbumIRq'),
('NILEST/STF/001','Demo Staff','staff@nilest.gov.ng','08011111111',7,'Systems Analyst','GL 08','2020-03-01',30,'staff','$2y$10$RkG5PgdFPZg9H//idlIzmusGpBzdjC2BPgxi3wR/t7diAREbumIRq')
ON DUPLICATE KEY UPDATE staff_no = staff_no;
