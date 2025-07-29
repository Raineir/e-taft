<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/functions.php';

$pageTitle = "Print Student Records";

// Fetch all students with enrollment info (grade level, section, strand)
$sql = "SELECT s.*, ac.grade_level, ac.section, ac.strand
        FROM students s
        LEFT JOIN enrollments e ON s.student_id = e.student_id
        LEFT JOIN academic_classes ac ON e.class_id = ac.class_id
        ORDER BY s.last_name, s.first_name";
$stmt = $conn->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch grades for all students
$gradesStmt = $conn->prepare("SELECT sg.*, s.name AS subject_name, gl.name AS grade_level_name
                             FROM student_grades sg
                             JOIN subjects s ON sg.subject_id = s.subject_id
                             JOIN grade_levels gl ON sg.grade_level_id = gl.grade_level_id
                             WHERE sg.student_id = ?
                             ORDER BY gl.grade_level_id, s.name");

// Group grades by student_id
$studentGrades = [];
foreach ($students as $student) {
    $gradesStmt->execute([$student['student_id']]);
    $studentGrades[$student['student_id']] = $gradesStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>E-TAFT SRMS - <?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/custom-style.css" />
    <style>
        @media print {
            .no-print { display: none; }
        }
        table { border-collapse: collapse; width: 100%; margin-bottom: 40px; }
        th, td { border: 1px solid #000; padding: 8px; }
        h2 { margin-top: 40px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <button class="btn no-print" onclick="window.print()">Print Records</button>

        <?php foreach ($students as $student): ?>
            <section style="page-break-after: always;">
                <h2><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></h2>
                <p>
                    <strong>LRN:</strong> <?= htmlspecialchars($student['lrn']) ?><br />
                    <strong>Grade Level:</strong> <?= htmlspecialchars($student['grade_level'] ?? 'N/A') ?><br />
                    <strong>Section:</strong> <?= htmlspecialchars($student['section'] ?? 'N/A') ?><br />
                    <strong>Strand:</strong> <?= htmlspecialchars($student['strand'] ?? 'N/A') ?><br />
                    <strong>Birthdate:</strong> <?= htmlspecialchars($student['birthdate']) ?><br />
                    <strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?><br />
                    <strong>Status:</strong> <?= htmlspecialchars($student['status']) ?><br />
                </p>

                <h3>Grades</h3>
                <?php if (!empty($studentGrades[$student['student_id']])): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Grade Level</th>
                                <th>Grade</th>
                                <th>Grading Period</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentGrades[$student['student_id']] as $grade): ?>
                                <tr>
                                    <td><?= htmlspecialchars($grade['subject_name']) ?></td>
                                    <td><?= htmlspecialchars($grade['grade_level_name']) ?></td>
                                    <td><?= htmlspecialchars($grade['grade']) ?></td>
                                    <td><?= htmlspecialchars($grade['grading_period']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No grades found.</p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
</body>
</html>
