<?php

function exportStudentsToCSV($students) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_export_' . date('Ymd_His') . '.csv"');

    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, ['LRN', 'Last Name', 'First Name', 'Middle Name', 'Grade Level', 'Section', 'Strand', 'Birthdate', 'Gender', 'Status']);

    // Data rows
    foreach ($students as $student) {
        fputcsv($output, [
            $student['lrn'],
            $student['last_name'],
            $student['first_name'],
            $student['middle_name'],
            $student['grade_level'] ?? '',
            $student['section'] ?? '',
            $student['strand'] ?? '',
            $student['birthdate'],
            $student['gender'],
            $student['status']
        ]);
    }

    fclose($output);
    exit();
}

function exportStudentsToHTML($students) {
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="students_export_' . date('Ymd_His') . '.html"');

    echo '<html><head><title>Student Records Export</title></head><body>';
    echo '<h1>Student Records</h1>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<thead><tr>';
    echo '<th>LRN</th><th>Last Name</th><th>First Name</th><th>Middle Name</th><th>Grade Level</th><th>Section</th><th>Strand</th><th>Birthdate</th><th>Gender</th><th>Status</th>';
    echo '</tr></thead><tbody>';

    foreach ($students as $student) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($student['lrn']) . '</td>';
        echo '<td>' . htmlspecialchars($student['last_name']) . '</td>';
        echo '<td>' . htmlspecialchars($student['first_name']) . '</td>';
        echo '<td>' . htmlspecialchars($student['middle_name']) . '</td>';
        echo '<td>' . htmlspecialchars($student['grade_level'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['section'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['strand'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['birthdate']) . '</td>';
        echo '<td>' . htmlspecialchars($student['gender']) . '</td>';
        echo '<td>' . htmlspecialchars($student['status']) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</body></html>';
    exit();
}
?>
