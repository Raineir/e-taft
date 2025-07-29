<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/functions.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Printable Student Record</title>
    <style>
        /* Basic styles for printable report */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        h1, h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        th {
            background-color: #ddd;
        }
        .section-title {
            background-color: #bbb;
            font-weight: bold;
            padding: 6px;
            margin-top: 20px;
        }
        .personal-info td {
            text-align: left;
            border: none;
            padding: 2px 6px;
        }
        .no-border {
            border: none;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h1>Republic of the Philippines</h1>
    <h2>Department of Education</h2>
    <h2>Learner Permanent Record for Elementary School (SF10-ES)</h2>

    <div class="section-title">LEARNER'S PERSONAL INFORMATION</div>
    <table class="personal-info">
        <tr>
            <td><strong>Last Name:</strong> <?= htmlspecialchars($student['last_name']) ?></td>
            <td><strong>First Name:</strong> <?= htmlspecialchars($student['first_name']) ?></td>
            <td><strong>Middle Name:</strong> <?= htmlspecialchars($student['middle_name']) ?></td>
        </tr>
        <tr>
            <td><strong>LRN:</strong> <?= htmlspecialchars($student['lrn'] ?? '') ?></td>
            <td><strong>Birthdate:</strong> <?= htmlspecialchars($student['birthdate'] ?? '') ?></td>
            <td><strong>Sex:</strong> <?= htmlspecialchars($student['sex'] ?? '') ?></td>
        </tr>
        <tr>
            <td colspan="3"><strong>Address:</strong> <?= htmlspecialchars($student['address'] ?? '') ?></td>
        </tr>
    </table>

    <div class="section-title">SCHOLASTIC RECORD</div>
    <table>
        <thead>
            <tr>
                <th>Learning Areas</th>
                <th>1st Quarter</th>
                <th>2nd Quarter</th>
                <th>3rd Quarter</th>
                <th>4th Quarter</th>
                <th>Final Rating</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grades as $subject_name => $gradeData): ?>
            <tr>
                <td><?= htmlspecialchars($subject_name) ?></td>
                <td><?= htmlspecialchars($gradeData['1st Quarter'] ?? '-') ?></td>
                <td><?= htmlspecialchars($gradeData['2nd Quarter'] ?? '-') ?></td>
                <td><?= htmlspecialchars($gradeData['3rd Quarter'] ?? '-') ?></td>
                <td><?= htmlspecialchars($gradeData['4th Quarter'] ?? '-') ?></td>
                <td><?= calculateFinalRating($gradeData) ?></td>
                <td><?= (calculateFinalRating($gradeData) >= 75) ? 'Passed' : 'Failed' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($grades)): ?>
            <tr>
                <td colspan="7">No grades found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <button class="no-print" onclick="window.print()">Print this page</button>
</body>
</html>
