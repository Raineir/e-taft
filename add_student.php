<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/functions.php';

$pageTitle = "Add Student";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    }

    $lrn = trim($_POST['lrn']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);
    $status = trim($_POST['status']);
    $birthdate = trim($_POST['birthdate']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);
    $parent_guardian = trim($_POST['parent_guardian']);
    $contact_number = trim($_POST['contact_number']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($lrn)) {
        $errors[] = "LRN is required.";
    }
    
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }
    
    if (empty($status)) {
        $errors[] = "Status is required.";
    }

    // Check if LRN already exists
    $stmt = $conn->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "LRN already exists.";
    }

    if (empty($errors)) {
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Insert new student
            $stmt = $conn->prepare("INSERT INTO students (lrn, first_name, last_name, middle_name, status, birthdate, gender, address, parent_guardian, contact_number) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt->execute([$lrn, $first_name, $last_name, $middle_name, $status, $birthdate, $gender, $address, $parent_guardian, $contact_number])) {
                throw new Exception("Failed to add student");
            }
            
            // Get the new student ID
            $student_id = $conn->lastInsertId();
            
            // Process old grades if submitted
            if (isset($_POST['old_grades']) && isset($_POST['old_grades']['subject_name']) && !empty($_POST['old_grades']['subject_name'])) {
                $subject_names = $_POST['old_grades']['subject_name'];
                $first_quarters = $_POST['old_grades']['1st_quarter'] ?? [];
                $second_quarters = $_POST['old_grades']['2nd_quarter'] ?? [];
                $third_quarters = $_POST['old_grades']['3rd_quarter'] ?? [];
                $fourth_quarters = $_POST['old_grades']['4th_quarter'] ?? [];
                $grade_level = $_POST['grade_level'] ?? null;
                
                if ($grade_level) {
                    // Get the current academic year
                    $academic_year_stmt = $conn->query("SELECT * FROM academic_years WHERE is_current = 1");
                    $academic_year = $academic_year_stmt->fetch(PDO::FETCH_ASSOC);
                    $academic_year_id = $academic_year ? $academic_year['academic_year_id'] : null;
                    
                    // If no current academic year, use the most recent one
                    if (!$academic_year_id) {
                        $academic_year_stmt = $conn->query("SELECT * FROM academic_years ORDER BY academic_year_id DESC LIMIT 1");
                        $academic_year = $academic_year_stmt->fetch(PDO::FETCH_ASSOC);
                        $academic_year_id = $academic_year ? $academic_year['academic_year_id'] : null;
                    }
                    
                    if ($academic_year_id) {
                        // Find class for this grade level
                        $class_stmt = $conn->prepare("SELECT * FROM academic_classes WHERE grade_level = ? AND academic_year_id = ? LIMIT 1");
                        $class_stmt->execute([$grade_level, $academic_year_id]);
                        $class = $class_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // If class doesn't exist, create it
                        if (!$class) {
                            // Get a default section name (A)
                            $section = 'A';
                            
                            // Create a new class for this grade level
                            $create_class_stmt = $conn->prepare("INSERT INTO academic_classes 
                                (grade_level, section, academic_year_id, created_at) 
                                VALUES (?, ?, ?, NOW())");
                            
                            $create_class_stmt->execute([$grade_level, $section, $academic_year_id]);
                            $class_id = $conn->lastInsertId();
                        } else {
                            $class_id = $class['class_id'];
                        }
                        
                        // Insert grades for each subject
                        $insert_grade_stmt = $conn->prepare("INSERT INTO student_grades 
                            (student_id, subject_id, class_id, first_quarter, second_quarter, third_quarter, fourth_quarter, is_manual_entry) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                        
                        // Prepare statement to check if subject exists
                        $check_subject_stmt = $conn->prepare("SELECT subject_id FROM subjects WHERE subject_name = ? LIMIT 1");
                        
                        // Prepare statement to create new subject
                        $create_subject_stmt = $conn->prepare("INSERT INTO subjects (subject_name, created_at) VALUES (?, NOW())");
                        
                        foreach ($subject_names as $index => $subject_name) {
                            if (!empty($subject_name)) {
                                // Check if the subject already exists
                                $check_subject_stmt->execute([$subject_name]);
                                $existing_subject = $check_subject_stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($existing_subject) {
                                    // Use existing subject ID
                                    $subject_id = $existing_subject['subject_id'];
                                } else {
                                    // Create a new subject
                                    $create_subject_stmt->execute([$subject_name]);
                                    $subject_id = $conn->lastInsertId();
                                }
                                
                                $first_quarter = !empty($first_quarters[$index]) ? $first_quarters[$index] : null;
                                $second_quarter = !empty($second_quarters[$index]) ? $second_quarters[$index] : null;
                                $third_quarter = !empty($third_quarters[$index]) ? $third_quarters[$index] : null;
                                $fourth_quarter = !empty($fourth_quarters[$index]) ? $fourth_quarters[$index] : null;
                                
                                $insert_grade_stmt->execute([
                                    $student_id,
                                    $subject_id,
                                    $class_id,
                                    $first_quarter,
                                    $second_quarter,
                                    $third_quarter,
                                    $fourth_quarter
                                ]);
                            }
                        }
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            logActivity($_SESSION['user_id'], "Added student: $first_name $last_name");
            header("Location: students.php?added=1");
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $error = "Failed to add student: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-TAFT SRMS - <?= $pageTitle ?></title>
    <link rel="stylesheet" href="assets/css/custom-style.css">
    <style>
        /* SF10 Form Styles */
        .sf10-container {
            border: 1px solid #ccc;
            padding: 15px;
            margin-top: 20px;
            background-color: #f9f9f9;
        }
        .sf10-header {
            text-align: center;
            margin-bottom: 15px;
        }
        .sf10-header h3 {
            margin: 5px 0;
            color: #004085;
        }
        .sf10-section {
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .sf10-section h4 {
            background-color: #004085;
            color: white;
            padding: 5px 10px;
            margin-bottom: 10px;
        }
        .sf10-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .sf10-grid-item {
            margin-bottom: 10px;
        }
        .sf10-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sf10-table th, .sf10-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .sf10-table th {
            background-color: #f2f2f2;
        }
        .sf10-toggle {
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
            margin-bottom: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form action="add_student.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">
            
            <div class="sf10-toggle" onclick="toggleBasicInfo()">
                <strong>▼ Basic Student Information</strong>
            </div>
            
            <div id="basic-info-section">
                <div class="form-group">
                    <label for="lrn">LRN</label>
                    <input type="text" id="lrn" name="lrn" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="parent_guardian">Parent/Guardian</label>
                    <input type="text" id="parent_guardian" name="parent_guardian" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            
            <!-- SF10 Form Section -->
            <div class="sf10-toggle" onclick="toggleSF10()">
                <strong>▼ School Form 10 (SF10) - Learner's Permanent Academic Record</strong>
            </div>
            
            <div id="sf10-section" class="sf10-container">
                <div class="sf10-header">
                    <h3>Republic of the Philippines</h3>
                    <h3>Department of Education</h3>
                    <h3>Learner's Permanent Academic Record</h3>
                    <h4>School Form 10 (SF10)</h4>
                </div>
                
                <div class="sf10-section">
                    <h4>LEARNER'S PERSONAL INFORMATION</h4>
                    <p>This section will be populated after saving the student information.</p>
                </div>
                
                <div class="sf10-section">
                    <h4>SCHOLASTIC RECORD</h4>
                    <p>Add previous grades for this student:</p>
                    
                    <?php
                    // Fetch all subjects for the form
                    $subjects = $conn->query("SELECT * FROM subjects")->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Define Philippine high school grade levels only (7-12)
                    // Instead of fetching from database, we'll define them explicitly to ensure all options are available
                    $philippine_grade_levels = [
                        ['grade_level' => 'Grade 7'],  // Junior High School - First Year
                        ['grade_level' => 'Grade 8'],  // Junior High School - Second Year
                        ['grade_level' => 'Grade 9'],  // Junior High School - Third Year
                        ['grade_level' => 'Grade 10'], // Junior High School - Fourth Year
                        ['grade_level' => 'Grade 11'], // Senior High School - First Year
                        ['grade_level' => 'Grade 12']  // Senior High School - Second Year
                    ];
                    ?>
                    
                    <div class="manual-grades-section">
                        <h5>Add Previous Grades</h5>
                        <div class="form-group">
                            <label for="grade_level">Grade Level</label>
                            <select id="grade_level" name="grade_level" class="form-control">
                                <option value="">Select Grade Level</option>
                                <?php foreach ($philippine_grade_levels as $gl): ?>
                                    <option value="<?= $gl['grade_level'] ?>"><?= htmlspecialchars($gl['grade_level']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="subjects-container">
                            <!-- This section will be dynamically populated with subject fields when a grade level is selected -->
                            <div class="no-subjects-message">Please select a grade level to add subjects and grades.</div>
                        </div>
                        
                        <button type="button" class="btn btn-secondary" id="add-subject-btn">Add Another Subject</button>
                    </div>
                </div>
                
                <div class="sf10-section">
                    <h4>CERTIFICATION</h4>
                    <p>I CERTIFY that this is a true record of the student.</p>
                    <div style="margin-top: 20px; display: flex; justify-content: space-between;">
                        <div style="text-align: center;">
                            <div style="border-bottom: 1px solid #000; width: 200px; margin-bottom: 5px;"></div>
                            <p>School Principal</p>
                        </div>
                        <div style="text-align: center;">
                            <div style="border-bottom: 1px solid #000; width: 200px; margin-bottom: 5px;"></div>
                            <p>Date</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn">Add Student</button>
                <a href="students.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <script>
        function toggleBasicInfo() {
            const section = document.getElementById('basic-info-section');
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
        }
        
        function toggleSF10() {
            const section = document.getElementById('sf10-section');
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
        }
        
        // When the page loads, set up the grade level change handler
        document.addEventListener('DOMContentLoaded', function() {
            const gradeLevelSelect = document.getElementById('grade_level');
            const subjectsContainer = document.getElementById('subjects-container');
            const addSubjectBtn = document.getElementById('add-subject-btn');
            
            if (gradeLevelSelect && subjectsContainer) {
                gradeLevelSelect.addEventListener('change', function() {
                    if (this.value) {
                        // Clear the no subjects message
                        subjectsContainer.innerHTML = '';
                        // Add the first subject row
                        addSubjectRow();
                    } else {
                        subjectsContainer.innerHTML = '<div class="no-subjects-message">Please select a grade level to add subjects and grades.</div>';
                    }
                });
            }
            
            if (addSubjectBtn) {
                addSubjectBtn.addEventListener('click', function() {
                    if (gradeLevelSelect.value) {
                        addSubjectRow();
                    } else {
                        alert('Please select a grade level first.');
                    }
                });
            }
            
            function addSubjectRow() {
                const subjectRow = document.createElement('div');
                subjectRow.className = 'subject-row';
                subjectRow.style.marginBottom = '15px';
                subjectRow.style.padding = '10px';
                subjectRow.style.border = '1px solid #ddd';
                subjectRow.style.borderRadius = '5px';
                
                // Create subject input field (typeable)
                const subjectGroup = document.createElement('div');
                subjectGroup.className = 'form-group';
                subjectGroup.innerHTML = `
                    <label>Subject</label>
                    <input type="text" name="old_grades[subject_name][]" class="form-control" placeholder="Enter subject name" required>
                `;
                
                // Create grade inputs for each quarter
                const gradesContainer = document.createElement('div');
                gradesContainer.style.display = 'grid';
                gradesContainer.style.gridTemplateColumns = 'repeat(4, 1fr)';
                gradesContainer.style.gap = '10px';
                gradesContainer.style.marginTop = '10px';
                
                const quarters = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
                quarters.forEach(quarter => {
                    const quarterInput = document.createElement('div');
                    quarterInput.className = 'form-group';
                    quarterInput.innerHTML = `
                        <label>${quarter}</label>
                        <input type="number" step="0.01" min="0" max="100" name="old_grades[${quarter.toLowerCase().replace(' ', '_')}][]" class="form-control" placeholder="Grade">
                    `;
                    gradesContainer.appendChild(quarterInput);
                });
                
                // Create remove button
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-danger';
                removeBtn.style.marginTop = '10px';
                removeBtn.textContent = 'Remove Subject';
                removeBtn.addEventListener('click', function() {
                    subjectRow.remove();
                });
                
                // Add all elements to the subject row
                subjectRow.appendChild(subjectGroup);
                subjectRow.appendChild(gradesContainer);
                subjectRow.appendChild(removeBtn);
                
                // Add the subject row to the container
                subjectsContainer.appendChild(subjectRow);
            }
        });
    </script>
</body>
</html>
