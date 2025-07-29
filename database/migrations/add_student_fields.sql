ALTER TABLE students
ADD COLUMN birthdate DATE NULL,
ADD COLUMN gender ENUM('Male', 'Female', 'Other') NULL,
ADD COLUMN address VARCHAR(255) NULL,
ADD COLUMN parent_guardian VARCHAR(255) NULL,
ADD COLUMN contact_number VARCHAR(20) NULL;
