-- Migration script to create student_grades table

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
