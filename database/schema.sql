CREATE DATABASE IF NOT EXISTS edutranscript;
USE edutranscript;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lecturers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_code VARCHAR(40) NOT NULL UNIQUE,
    full_name VARCHAR(120) NOT NULL,
    programme VARCHAR(120) NOT NULL,
    level_name VARCHAR(40) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_title VARCHAR(160) NOT NULL,
    credit_unit INT NOT NULL,
    ca_max DECIMAL(5,2) NOT NULL DEFAULT 30,
    exam_max DECIMAL(5,2) NOT NULL DEFAULT 70
);

CREATE TABLE course_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecturer_id INT NOT NULL,
    course_id INT NOT NULL,
    academic_session VARCHAR(20) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE result_edit_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecturer_id INT NOT NULL,
    course_id INT NOT NULL,
    can_modify TINYINT(1) NOT NULL DEFAULT 0,
    granted_by_admin_id INT NULL,
    granted_at TIMESTAMP NULL,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (granted_by_admin_id) REFERENCES admins(id)
);

CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    lecturer_id INT NOT NULL,
    ca_score DECIMAL(5,2) NOT NULL,
    exam_score DECIMAL(5,2) NOT NULL,
    total_score DECIMAL(5,2) GENERATED ALWAYS AS (ca_score + exam_score) STORED,
    grade VARCHAR(2) NOT NULL,
    grade_point DECIMAL(3,2) NOT NULL,
    status ENUM('draft', 'submitted', 'approved', 'published') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id)
);
