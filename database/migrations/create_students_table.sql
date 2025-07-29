CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    lrn VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    birthdate DATE,
    gender ENUM('Male', 'Female', 'Other'),
    address VARCHAR(255),
    parent_guardian VARCHAR(255),
    contact_number VARCHAR(20),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
