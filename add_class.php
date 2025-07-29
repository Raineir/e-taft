<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$pageTitle = "Add Class";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name']);
    $grade_level = trim($_POST['grade_level']);
    $section = trim($_POST['section']);
    
    $errors = [];
    
    if (empty($class_name)) {
        $errors[] = "Class name is required.";
    }
    if (empty($grade_level)) {
        $errors[] = "Grade level is required.";
    }
    if (empty($section)) {
        $errors[] = "Section is required.";
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO classes (class_name, grade_level, section) VALUES (?, ?, ?)");
        if ($stmt->execute([$class_name, $grade_level, $section])) {
            header("Location: classes.php?added=1");
            exit();
        } else {
            $error = "Failed to add class. Please try again.";
        }
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
        <form action="add_class.php" method="POST">
            <div class="form-group">
                <label for="class_name">Class Name</label>
                <input type="text" id="class_name" name="class_name" class="form-control" required />
            </div>
            <div class="form-group">
                <label for="grade_level">Grade Level</label>
                <input type="number" id="grade_level" name="grade_level" class="form-control" required min="1" max="12" />
            </div>
            <div class="form-group">
                <label for="section">Section</label>
                <input type="text" id="section" name="section" class="form-control" required />
            </div>
            <button type="submit" class="btn">Add Class</button>
            <a href="classes.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
