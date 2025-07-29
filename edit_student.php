<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/functions.php';

$pageTitle = "Edit Student";

if (!isset($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: students.php");
    exit();
}

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

    if (empty($errors)) {
        // Update student
        $stmt = $conn->prepare("UPDATE students SET lrn = ?, first_name = ?, last_name = ?, middle_name = ?, status = ?, birthdate = ?, gender = ?, address = ?, parent_guardian = ?, contact_number = ? 
                                WHERE student_id = ?");
        
        if ($stmt->execute([$lrn, $first_name, $last_name, $middle_name, $status, $birthdate, $gender, $address, $parent_guardian, $contact_number, $id])) {
            logActivity($_SESSION['user_id'], "Updated student: $first_name $last_name");
            header("Location: students.php?updated=1");
            exit();
        } else {
            $error = "Failed to update student. Please try again.";
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
        
        <form action="edit_student.php?id=<?= $student['student_id'] ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">
            
            <div class="sf10-toggle" onclick="toggleBasicInfo()">
                <strong>▼ Basic Student Information</strong>
            </div>
            
            <div id="basic-info-section">
                <div class="form-group">
                    <label for="lrn">LRN</label>
                    <input type="text" id="lrn" name="lrn" class="form-control" value="<?= htmlspecialchars($student['lrn']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" class="form-control" value="<?= htmlspecialchars($student['middle_name']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?= htmlspecialchars($student['birthdate']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $student['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($student['address']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="parent_guardian">Parent/Guardian</label>
                    <input type="text" id="parent_guardian" name="parent_guardian" class="form-control" value="<?= htmlspecialchars($student['parent_guardian']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control" value="<?= htmlspecialchars($student['contact_number']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?= $student['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $student['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
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
                    <div class="sf10-grid">
                        <div class="sf10-grid-item">
                            <label>Last Name:</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" readonly>
                        </div>
                        <div class="sf10-grid-item">
                            <label>First Name:</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" readonly>
                        </div>
                        <div class="sf10-grid-item">
                            <label>Middle Name:</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['middle_name']) ?>" readonly>
                        </div>
                        <div class="sf10-grid-item">
                            <label>LRN:</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['lrn']) ?>" readonly>
                        </div>
                        <div class="sf10-grid-item">
                            <label>Birthdate:</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['birthdate']) ?>" readonly>
                        </div>
                        <div class="sf10-grid-item">
                            <label>Sex:</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['gender']) ?>" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="sf10-section">
                    <h4>SCHOLASTIC RECORD</h4>
                    
                    <?php
                    // Fetch grades for the student grouped by subject and grading period
                    $stmt = $conn->prepare("SELECT g.subject_id, s.subject_name, ac.grade_level, g.grading_period, g.grade 
                                          FROM grades g 
                                          JOIN enrollments e ON g.enrollment_id = e.enrollment_id 
                                          JOIN subjects s ON g.subject_id = s.subject_id 
                                          JOIN academic_classes ac ON e.class_id = ac.class_id 
                                          WHERE e.student_id = ? 
                                          ORDER BY ac.grade_level, s.subject_name, g.grading_period");
                    $stmt->execute([$id]);
                    $grades_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Organize grades by grade level, then by subject and grading period
                    $grades_by_level = [];
                    foreach ($grades_raw as $grade) {
                        $level = $grade['grade_level'];
                        $subject = $grade['subject_name'];
                        $period = $grade['grading_period'];
                        $grades_by_level[$level][$subject][$period] = $grade['grade'];
                    }
                    
                    // Helper function to calculate final rating
                    function calculateFinalRating($gradeData) {
                        $total = 0;
                        $count = 0;
                        foreach (['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'] as $period) {
                            if (isset($gradeData[$period])) {
                                $total += $gradeData[$period];
                                $count++;
                            }
                        }
                        return $count ? round($total / $count, 2) : '-';
                    }
                    
                    // Display grades by grade level
                    if (!empty($grades_by_level)) {
                        foreach ($grades_by_level as $level => $subjects) {
                            echo "<div class='sf10-level-section'>";
                            echo "<h5>Grade Level: $level</h5>";
                            echo "<table class='sf10-table'>";
                            echo "<thead><tr>";
                            echo "<th>Learning Areas</th>";
                            echo "<th>1st Quarter</th>";
                            echo "<th>2nd Quarter</th>";
                            echo "<th>3rd Quarter</th>";
                            echo "<th>4th Quarter</th>";
                            echo "<th>Final Rating</th>";
                            echo "<th>Remarks</th>";
                            echo "</tr></thead>";
                            echo "<tbody>";
                            
                            foreach ($subjects as $subject_name => $periods) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($subject_name) . "</td>";
                                echo "<td>" . htmlspecialchars($periods['1st Quarter'] ?? '-') . "</td>";
                                echo "<td>" . htmlspecialchars($periods['2nd Quarter'] ?? '-') . "</td>";
                                echo "<td>" . htmlspecialchars($periods['3rd Quarter'] ?? '-') . "</td>";
                                echo "<td>" . htmlspecialchars($periods['4th Quarter'] ?? '-') . "</td>";
                                
                                $final = calculateFinalRating($periods);
                                echo "<td>" . $final . "</td>";
                                echo "<td>" . ($final >= 75 ? 'Passed' : 'Failed') . "</td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No grades found for this student.</p>";
                    }
                    ?>
                </div>
                
                <div class="sf10-section">
                    <h4>CERTIFICATION</h4>
                    <p>I CERTIFY that this is a true record of <?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']) ?>.</p>
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
                <button type="submit" class="btn">Update Student</button>
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
    </script>
</body>
</html>
