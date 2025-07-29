-- Migration script to create academic_years table

CREATE TABLE IF NOT EXISTS academic_years (
    academic_year_id INT AUTO_INCREMENT PRIMARY KEY,
    year_name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create academic_classes table that links classes to academic years
CREATE TABLE IF NOT EXISTS academic_classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    grade_level VARCHAR(50) NOT NULL,
    section VARCHAR(50) NOT NULL,
    academic_year_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(academic_year_id) ON DELETE CASCADE
);

-- Insert a default academic year
INSERT INTO academic_years (year_name, start_date, end_date, is_current) 
VALUES ('2023-2024', '2023-06-01', '2024-03-31', 1);