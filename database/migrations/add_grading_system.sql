-- Migration script to add grading system tables

-- Table for subjects
CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Table for grade_levels (e.g., Grade 6, Grade 7, SHS Grade 11, etc.)
CREATE TABLE IF NOT EXISTS grade_levels (
    grade_level_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT
);

-- Table for student_grades linking students, subjects, grade levels, and grades
CREATE TABLE IF NOT EXISTS student_grades (
    student_grade_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    grade_level_id INT NOT NULL,
    grade DECIMAL(5,2) NOT NULL,
    grading_period VARCHAR(50) DEFAULT 'Final',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (grade_level_id) REFERENCES grade_levels(grade_level_id) ON DELETE CASCADE
);
