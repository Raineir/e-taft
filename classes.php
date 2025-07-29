<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$pageTitle = "Class Management";

// Fetch all classes
$stmt = $conn->prepare("SELECT * FROM classes ORDER BY grade_level, section");
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle class deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ?");
    if ($stmt->execute([$id])) {
        header("Location: classes.php?deleted=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>E-TAFT SRMS - <?= $pageTitle ?></title>
    <link rel="stylesheet" href="assets/css/custom-style.css" />
    <link rel="stylesheet" href="assets/css/jquery.dataTables.min.css" />
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Class deleted successfully!</div>
        <?php endif; ?>
        <div style="margin-bottom: 20px;">
            <a href="add_class.php" class="btn">Add New Class</a>
        </div>
        <div class="table-responsive">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th>Class Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?= htmlspecialchars($class['grade_level']) ?></td>
                        <td><?= htmlspecialchars($class['section']) ?></td>
                        <td><?= htmlspecialchars($class['class_name']) ?></td>
                        <td>
                            <a href="edit_class.php?id=<?= $class['class_id'] ?>" class="btn">Edit</a>
                            <a href="classes.php?delete=<?= $class['class_id'] ?>" class="btn btn-danger btn-delete" onclick="return confirm('Are you sure you want to delete this class?');">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="assets/js/jquery-3.5.1.min.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                "order": []
            });
        });
    </script>
</body>
</html>
