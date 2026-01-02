-- ============================================================================
-- College Student Portal Database Schema
-- Version: 1.0
-- Date: January 2, 2026
-- ============================================================================

-- Create database (run separately if not exists)
-- CREATE DATABASE IF NOT EXISTS student_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- USE student_portal;

-- ============================================================================
-- DROP TABLES (in order to handle foreign key dependencies)
-- ============================================================================
DROP TABLE IF EXISTS schedule;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS admins;

-- ============================================================================
-- TABLE: departments (Create FIRST - referenced by others)
-- ============================================================================
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_code VARCHAR(20) NOT NULL UNIQUE,
    department_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dept_code (department_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE: admins
-- ============================================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin_id (admin_id),
    INDEX idx_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE: students
-- ============================================================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    gender VARCHAR(10) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default.jpg',
    department_id INT NOT NULL,
    current_semester INT DEFAULT NULL,
    enrollment_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_student_id (student_id),
    INDEX idx_student_email (email),
    INDEX idx_student_dept (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE: courses
-- ============================================================================
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(100) NOT NULL,
    credits INT NOT NULL,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_course_code (course_code),
    INDEX idx_course_dept (department_id),
    CONSTRAINT chk_credits CHECK (credits >= 1 AND credits <= 6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE: grades
-- ============================================================================
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    semester INT NOT NULL,
    semester_year VARCHAR(20) DEFAULT NULL,
    grade VARCHAR(5) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY uk_student_course_semester (student_id, course_id, semester),
    INDEX idx_grade_student (student_id),
    INDEX idx_grade_course (course_id),
    INDEX idx_grade_semester (semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE: schedule
-- ============================================================================
CREATE TABLE schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    day_of_week INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room_number VARCHAR(20) DEFAULT NULL,
    instructor_name VARCHAR(100) DEFAULT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_schedule_student (student_id),
    INDEX idx_schedule_course (course_id),
    INDEX idx_schedule_day (day_of_week),
    INDEX idx_schedule_semester (semester),
    CONSTRAINT chk_day_of_week CHECK (day_of_week >= 1 AND day_of_week <= 7),
    CONSTRAINT chk_semester CHECK (semester >= 1 AND semester <= 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- SAMPLE DATA: Departments
-- ============================================================================
INSERT INTO departments (department_code, department_name) VALUES
('CS', 'Computer Science'),
('MATH', 'Mathematics'),
('ENG', 'English'),
('PHY', 'Physics'),
('CHEM', 'Chemistry');

-- ============================================================================
-- SAMPLE DATA: Admin
-- Password: Admin@123 (bcrypt hashed)
-- ============================================================================
INSERT INTO admins (admin_id, name, email, password) VALUES
('ADMIN001', 'System Administrator', 'admin@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================================================
-- SAMPLE DATA: Courses
-- ============================================================================
INSERT INTO courses (course_code, course_name, credits, department_id) VALUES
-- Computer Science (department_id = 1)
('CS101', 'Introduction to Programming', 3, 1),
('CS102', 'Data Structures', 4, 1),
('CS201', 'Algorithms', 4, 1),
('CS202', 'Database Systems', 3, 1),
('CS301', 'Operating Systems', 4, 1),
-- Mathematics (department_id = 2)
('MATH101', 'Calculus I', 4, 2),
('MATH102', 'Calculus II', 4, 2),
('MATH201', 'Linear Algebra', 3, 2),
('MATH202', 'Discrete Mathematics', 3, 2),
-- English (department_id = 3)
('ENG101', 'English Composition', 3, 3),
('ENG102', 'Technical Writing', 3, 3),
-- Physics (department_id = 4)
('PHY101', 'Physics I', 4, 4),
('PHY102', 'Physics II', 4, 4);

-- ============================================================================
-- SAMPLE DATA: Students
-- Password: Student@123 (bcrypt hashed - same as admin for testing)
-- ============================================================================
INSERT INTO students (student_id, name, email, password, phone, date_of_birth, gender, address, department_id, current_semester, enrollment_date) VALUES
('2021CS001', 'John Smith', 'john.smith@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 '5551234567', '2003-05-15', 'Male', '123 College Ave, University City, ST 12345', 1, 4, '2021-08-15'),
 
('2021CS002', 'Jane Doe', 'jane.doe@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 '5559876543', '2002-11-22', 'Female', '456 University Blvd, College Town, ST 67890', 1, 4, '2021-08-15'),
 
('2022MATH01', 'Robert Johnson', 'robert.j@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 '5555551234', '2004-02-28', 'Male', '789 Academic Dr, Study Village, ST 11111', 2, 2, '2022-08-20');

-- ============================================================================
-- SAMPLE DATA: Grades for Student 1 (John Smith - 3 semesters)
-- ============================================================================
-- Semester 1
INSERT INTO grades (student_id, course_id, semester, semester_year, grade) VALUES
(1, 1, 1, 'Fall 2021', 'A'),     -- CS101
(1, 6, 1, 'Fall 2021', 'A-'),    -- MATH101
(1, 10, 1, 'Fall 2021', 'B+'),   -- ENG101
(1, 12, 1, 'Fall 2021', 'B');    -- PHY101

-- Semester 2
INSERT INTO grades (student_id, course_id, semester, semester_year, grade) VALUES
(1, 2, 2, 'Spring 2022', 'A'),   -- CS102
(1, 7, 2, 'Spring 2022', 'B+'),  -- MATH102
(1, 11, 2, 'Spring 2022', 'A-'), -- ENG102
(1, 13, 2, 'Spring 2022', 'B');  -- PHY102

-- Semester 3
INSERT INTO grades (student_id, course_id, semester, semester_year, grade) VALUES
(1, 3, 3, 'Fall 2022', 'A'),     -- CS201
(1, 8, 3, 'Fall 2022', 'B+'),    -- MATH201
(1, 9, 3, 'Fall 2022', 'A-');    -- MATH202

-- ============================================================================
-- SAMPLE DATA: Grades for Student 2 (Jane Doe - 3 semesters)
-- ============================================================================
-- Semester 1
INSERT INTO grades (student_id, course_id, semester, semester_year, grade) VALUES
(2, 1, 1, 'Fall 2021', 'A-'),    -- CS101
(2, 6, 1, 'Fall 2021', 'A'),     -- MATH101
(2, 10, 1, 'Fall 2021', 'A'),    -- ENG101
(2, 12, 1, 'Fall 2021', 'B+');   -- PHY101

-- Semester 2
INSERT INTO grades (student_id, course_id, semester, semester_year, grade) VALUES
(2, 2, 2, 'Spring 2022', 'A-'),  -- CS102
(2, 7, 2, 'Spring 2022', 'A'),   -- MATH102
(2, 11, 2, 'Spring 2022', 'B+'), -- ENG102
(2, 13, 2, 'Spring 2022', 'A-'); -- PHY102

-- Semester 3
INSERT INTO grades (student_id, course_id, semester, semester_year, grade) VALUES
(2, 3, 3, 'Fall 2022', 'B+'),    -- CS201
(2, 8, 3, 'Fall 2022', 'A'),     -- MATH201
(2, 9, 3, 'Fall 2022', 'A');     -- MATH202

-- ============================================================================
-- SAMPLE DATA: Grades for Student 3 (Robert Johnson - 1 semester)
-- ============================================================================
INSERT INTO grades (student_id, course_id, semester, semester_year, grade) VALUES
(3, 6, 1, 'Fall 2022', 'A'),     -- MATH101
(3, 1, 1, 'Fall 2022', 'B+'),    -- CS101
(3, 10, 1, 'Fall 2022', 'B'),    -- ENG101
(3, 12, 1, 'Fall 2022', 'A-');   -- PHY101

-- ============================================================================
-- SAMPLE DATA: Schedule for Student 1 (John Smith - Semester 4)
-- ============================================================================
INSERT INTO schedule (student_id, course_id, day_of_week, start_time, end_time, room_number, instructor_name, semester) VALUES
-- CS202 - Database Systems (Mon/Wed 9:00-10:30)
(1, 4, 1, '09:00:00', '10:30:00', 'CS-201', 'Dr. Sarah Chen', 4),
(1, 4, 3, '09:00:00', '10:30:00', 'CS-201', 'Dr. Sarah Chen', 4),
-- CS301 - Operating Systems (Tue/Thu 11:00-12:30)
(1, 5, 2, '11:00:00', '12:30:00', 'CS-302', 'Prof. Michael Brown', 4),
(1, 5, 4, '11:00:00', '12:30:00', 'CS-302', 'Prof. Michael Brown', 4),
-- MATH201 - Linear Algebra (Mon/Wed/Fri 14:00-15:00)
(1, 8, 1, '14:00:00', '15:00:00', 'MATH-105', 'Dr. Emily White', 4),
(1, 8, 3, '14:00:00', '15:00:00', 'MATH-105', 'Dr. Emily White', 4),
(1, 8, 5, '14:00:00', '15:00:00', 'MATH-105', 'Dr. Emily White', 4);

-- ============================================================================
-- SAMPLE DATA: Schedule for Student 2 (Jane Doe - Semester 4)
-- ============================================================================
INSERT INTO schedule (student_id, course_id, day_of_week, start_time, end_time, room_number, instructor_name, semester) VALUES
-- CS202 - Database Systems (Mon/Wed 9:00-10:30) - Same class as John
(2, 4, 1, '09:00:00', '10:30:00', 'CS-201', 'Dr. Sarah Chen', 4),
(2, 4, 3, '09:00:00', '10:30:00', 'CS-201', 'Dr. Sarah Chen', 4),
-- CS301 - Operating Systems (Tue/Thu 14:00-15:30) - Different section
(2, 5, 2, '14:00:00', '15:30:00', 'CS-303', 'Dr. James Wilson', 4),
(2, 5, 4, '14:00:00', '15:30:00', 'CS-303', 'Dr. James Wilson', 4),
-- MATH202 - Discrete Math (Tue/Thu 09:00-10:30)
(2, 9, 2, '09:00:00', '10:30:00', 'MATH-201', 'Prof. Lisa Anderson', 4),
(2, 9, 4, '09:00:00', '10:30:00', 'MATH-201', 'Prof. Lisa Anderson', 4);

-- ============================================================================
-- SAMPLE DATA: Schedule for Student 3 (Robert Johnson - Semester 2)
-- ============================================================================
INSERT INTO schedule (student_id, course_id, day_of_week, start_time, end_time, room_number, instructor_name, semester) VALUES
-- MATH102 - Calculus II (Mon/Wed/Fri 10:00-11:00)
(3, 7, 1, '10:00:00', '11:00:00', 'MATH-101', 'Dr. David Lee', 2),
(3, 7, 3, '10:00:00', '11:00:00', 'MATH-101', 'Dr. David Lee', 2),
(3, 7, 5, '10:00:00', '11:00:00', 'MATH-101', 'Dr. David Lee', 2),
-- CS102 - Data Structures (Tue/Thu 13:00-14:30)
(3, 2, 2, '13:00:00', '14:30:00', 'CS-102', 'Prof. Jennifer Taylor', 2),
(3, 2, 4, '13:00:00', '14:30:00', 'CS-102', 'Prof. Jennifer Taylor', 2),
-- ENG102 - Technical Writing (Fri 14:00-16:00)
(3, 11, 5, '14:00:00', '16:00:00', 'ENG-201', 'Dr. Mark Robinson', 2);

-- ============================================================================
-- END OF DATABASE SCHEMA AND SAMPLE DATA
-- ============================================================================
