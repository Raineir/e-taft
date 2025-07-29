<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/functions.php';

$pageTitle = "Student Grades";

$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    die("Student ID is required.");
}

// Fetch student info
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

// Handle adding grades for quarterly grading system
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'] ?? null;
    $grade_level_id = $_POST['grade_level_id'] ?? null;
    $grading_periods = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
    $grades = [];
    $errors = [];

    if (!$subject_id) {
        $errors[] = "Subject is required.";
    }
    if (!$grade_level_id) {
        $errors[] = "Grade level is required.";
    }

    foreach ($grading_periods as $period) {
        $grade_value = $_POST[str_replace(' ', '_', strtolower($period))] ?? null;
        if ($grade_value === null || !is_numeric($grade_value)) {
            $errors[] = "Valid grade is required for $period.";
        } else {
            $grades[$period] = $grade_value;
        }
    }

    if (empty($errors)) {
        // Get enrollment_id for the student and grade_level
        $stmtEnroll = $conn->prepare("SELECT e.enrollment_id FROM enrollments e JOIN academic_classes ac ON e.class_id = ac.class_id WHERE e.student_id = ? AND ac.grade_level = ? ORDER BY e.enrollment_date DESC LIMIT 1");
        $stmtEnroll->execute([$student_id, $grade_level_id]);
        $enrollment = $stmtEnroll->fetch(PDO::FETCH_ASSOC);

        if (!$enrollment) {
            $errors[] = "Enrollment not found for the selected grade level.";
        } else {
            $enrollment_id = $enrollment['enrollment_id'];
            // Insert grades for each grading period
            $stmt = $conn->prepare("INSERT INTO grades (enrollment_id, subject_id, grading_period, grade, encoded_by) VALUES (?, ?, ?, ?, ?)");
            $success = true;
            foreach ($grades as $period => $grade_value) {
                if (!$stmt->execute([$enrollment_id, $subject_id, $period, $grade_value, $_SESSION['user_id']])) {
                    $success = false;
                    break;
                }
            }
            if ($success) {
                header("Location: grades.php?student_id=$student_id&added=1");
                exit();
            } else {
                $errors[] = "Failed to add grades.";
            }
        }
    }
}

// Fetch grades for the student grouped by subject and grading period
$stmt = $conn->prepare("SELECT g.subject_id, s.subject_name, ac.grade_level, g.grading_period, g.grade FROM grades g JOIN enrollments e ON g.enrollment_id = e.enrollment_id JOIN subjects s ON g.subject_id = s.subject_id JOIN academic_classes ac ON e.class_id = ac.class_id WHERE e.student_id = ? ORDER BY ac.grade_level, s.subject_name, g.grading_period");
$stmt->execute([$student_id]);
$grades_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize grades by subject and grading period
$grades = [];
foreach ($grades_raw as $grade) {
    $grades[$grade['subject_name']][$grade['grading_period']] = $grade['grade'];
    $grades[$grade['subject_name']]['grade_level'] = $grade['grade_level'];
}

// Fetch all subjects and grade levels for the form
$subjects = $conn->query("SELECT * FROM subjects")->fetchAll(PDO::FETCH_ASSOC);
$grade_levels = $conn->query("SELECT DISTINCT grade_level FROM academic_classes ORDER BY grade_level")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>E-TAFT SRMS - <?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/custom-style.css" />
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1>Grades for <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_GET['added'])): ?>
            <div class="alert alert-success">Grades added successfully!</div>
        <?php endif; ?>

        <h2>Add Grades</h2>
        <form method="POST" action="grades.php?student_id=<?= urlencode($student_id) ?>">
            <div class="form-group">
                <label for="subject_id">Subject</label>
                <select id="subject_id" name="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['subject_id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="grade_level_id">Grade Level</label>
                <select id="grade_level_id" name="grade_level_id" required>
                    <option value="">Select Grade Level</option>
                    <?php foreach ($grade_levels as $gl): ?>
                        <option value="<?= $gl['grade_level'] ?>"><?= htmlspecialchars($gl['grade_level']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php
            $grading_periods = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
            foreach ($grading_periods as $period):
            ?>
            <div class="form-group">
                <label for="<?= str_replace(' ', '_', strtolower($period)) ?>"><?= $period ?></label>
                <input type="number" step="0.01" id="<?= str_replace(' ', '_', strtolower($period)) ?>" name="<?= str_replace(' ', '_', strtolower($period)) ?>" required />
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn">Add Grades</button>
        </form>

        <h2>Grades</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Grade Level</th>
                    <?php foreach ($grading_periods as $period): ?>
                    <th><?= $period ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($grades)): ?>
                    <?php foreach ($grades as $subject_name => $grade_data): ?>
                    <tr>
                        <td><?= htmlspecialchars($subject_name) ?></td>
                        <td><?= htmlspecialchars($grade_data['grade_level']) ?></td>
                        <?php foreach ($grading_periods as $period): ?>
                        <td><?= htmlspecialchars($grade_data[$period] ?? '-') ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="<?= 2 + count($grading_periods) ?>">No grades found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="students.php" class="btn btn-secondary">Back to Students</a>
        <a href="print_student_record.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-primary" target="_blank">Print Student Record</a>
    </div>
</body>
</html>
