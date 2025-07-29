<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$pageTitle = "Edit Class";

if (!isset($_GET['id'])) {
    header("Location: classes.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM classes WHERE class_id = ?");
$stmt->execute([$id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    header("Location: classes.php");
    exit();
}

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
        $stmt = $conn->prepare("UPDATE classes SET class_name = ?, grade_level = ?, section = ? WHERE class_id = ?");
        if ($stmt->execute([$class_name, $grade_level, $section, $id])) {
            header("Location: classes.php?updated=1");
            exit();
        } else {
            $error = "Failed to update class. Please try again.";
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
        <form action="edit_class.php?id=<?= $class['class_id'] ?>" method="POST">
            <div class="form-group">
                <label for="class_name">Class Name</label>
                <input type="text" id="class_name" name="class_name" class="form-control" value="<?= htmlspecialchars($class['class_name']) ?>" required />
            </div>
            <div class="form-group">
                <label for="grade_level">Grade Level</label>
                <input type="number" id="grade_level" name="grade_level" class="form-control" value="<?= htmlspecialchars($class['grade_level']) ?>" required min="1" max="12" />
            </div>
            <div class="form-group">
                <label for="section">Section</label>
                <input type="text" id="section" name="section" class="form-control" value="<?= htmlspecialchars($class['section']) ?>" required />
            </div>
            <button type="submit" class="btn">Update Class</button>
            <a href="classes.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
