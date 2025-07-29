<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/functions.php';

$pageTitle = "Student Management";

// Check user role for permission
$userRole = $_SESSION['role'] ?? 'guest';

require_once 'includes/export.php';

// Handle export requests
if (isset($_GET['export'])) {
    $exportType = $_GET['export'];
    // Fetch all students for export
    if ($searchQuery || $strandFilter) {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $students = getAllStudents();
    }

    if ($exportType === 'excel') {
        exportStudentsToCSV($students);
    } elseif ($exportType === 'pdf') {
        exportStudentsToHTML($students);
    }
}

// Handle strand filter
$strandFilter = $_GET['strand'] ?? '';

// Handle search
$searchQuery = $_GET['q'] ?? '';

// Fetch students with optional search and strand filter
if ($searchQuery || $strandFilter) {
    $sql = "SELECT s.*, ac.grade_level, ac.section, ac.strand
            FROM students s
            LEFT JOIN enrollments e ON s.student_id = e.student_id
            LEFT JOIN academic_classes ac ON e.class_id = ac.class_id
            WHERE (s.first_name LIKE ? OR s.last_name LIKE ? OR s.lrn LIKE ?)";
    $params = ["%$searchQuery%", "%$searchQuery%", "%$searchQuery%"];

    if ($strandFilter) {
        $sql .= " AND ac.strand = ?";
        $params[] = $strandFilter;
    }

    $sql .= " GROUP BY s.student_id ORDER BY s.last_name, s.first_name";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $students = getAllStudents();
}

// Handle student deletion
if (isset($_GET['delete'])) {
    if ($userRole !== 'teacher' && $userRole !== 'admin') {
        header("HTTP/1.1 403 Forbidden");
        echo "You do not have permission to delete students.";
        exit();
    }
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    if ($stmt->execute([$id])) {
        logActivity($_SESSION['user_id'], "Deleted student ID: $id");
        header("Location: students.php?deleted=1");
        exit();
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
    <link rel="stylesheet" href="assets/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Student added successfully!</div>
        <?php elseif (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Student updated successfully!</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Student deleted successfully!</div>
        <?php endif; ?>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <?php if ($userRole === 'teacher' || $userRole === 'admin'): ?>
                <a href="add_student.php" class="btn">Add New Student</a>
            <?php endif; ?>
            <form id="search-form" style="display: flex; gap: 10px;">
                <input type="text" id="search-query" name="q" class="form-control" placeholder="Search by name or LRN" 
                       value="<?= htmlspecialchars($searchQuery) ?>">
                <select id="strand" name="strand" class="form-control">
                    <option value="">All Strands</option>
                    <option value="STEM" <?= ($strandFilter === 'STEM') ? 'selected' : '' ?>>STEM</option>
                    <option value="HUMSS" <?= ($strandFilter === 'HUMSS') ? 'selected' : '' ?>>HUMSS</option>
                    <option value="ABM" <?= ($strandFilter === 'ABM') ? 'selected' : '' ?>>ABM</option>
                    <option value="TVL" <?= ($strandFilter === 'TVL') ? 'selected' : '' ?>>TVL</option>
                    <option value="GAS" <?= ($strandFilter === 'GAS') ? 'selected' : '' ?>>GAS</option>
                </select>
                <button type="submit" class="btn">Search</button>
                <?php if ($searchQuery || $strandFilter): ?>
                    <a href="students.php" class="btn btn-danger">Clear</a>
                <?php endif; ?>
            </form>
            <div>
                <a href="students.php?export=excel" class="btn btn-success">Export to Excel</a>
                <a href="students.php?export=pdf" class="btn btn-danger">Export to PDF</a>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>LRN</th>
                        <th>Name</th>
                        <th>Grade Level</th>
                        <th>Strand</th>
                        <th>Birthdate</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): 
                        // Get current enrollment for the student
                        $enrollment = $conn->prepare("SELECT e.*, ac.grade_level, ac.section, ac.strand 
                                                    FROM enrollments e 
                                                    JOIN academic_classes ac ON e.class_id = ac.class_id 
                                                    WHERE e.student_id = ? 
                                                    ORDER BY e.enrollment_date DESC 
                                                    LIMIT 1");
                        $enrollment->execute([$student['student_id']]);
                        $currentClass = $enrollment->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($student['lrn']) ?></td>
                        <td><?= htmlspecialchars($student['last_name']) ?>, <?= htmlspecialchars($student['first_name']) ?> <?= htmlspecialchars($student['middle_name']) ?></td>
                        <td>
                            <?php if ($currentClass): ?>
                                Grade <?= $currentClass['grade_level'] ?> - <?= $currentClass['section'] ?>
                            <?php else: ?>
                                Not enrolled
                            <?php endif; ?>
                        </td>
                        <td><?= $currentClass['strand'] ?? 'N/A' ?></td>
                        <td><?= $student['birthdate'] ?? '' ?></td>
                        <td><?= $student['gender'] ?? '' ?></td>
                        <td><?= $student['status'] ?></td>
                        <td class="actions">
                            <a href="view_student.php?id=<?= $student['student_id'] ?>" class="btn">View</a>
                            <a href="grades.php?student_id=<?= $student['student_id'] ?>" class="btn btn-info">View Grades</a>
                            <?php if ($userRole === 'teacher' || $userRole === 'admin'): ?>
                                <a href="edit_student.php?id=<?= $student['student_id'] ?>" class="btn">Edit</a>
                                <a href="students.php?delete=<?= $student['student_id'] ?>" class="btn btn-danger btn-delete">Delete</a>
                                <a href="print_student_record.php?student_id=<?= $student['student_id'] ?>" target="_blank" class="btn btn-info">Print Record</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="assets/js/jquery-3.5.1.min.js"></script>
</body>
</html>
