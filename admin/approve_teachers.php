<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/functions.php';

$pageTitle = "Approve Teacher Accounts";

// Handle approval
if (isset($_GET['approve'])) {
    $user_id = $_GET['approve'];
    $stmt = $conn->prepare("UPDATE users SET is_approved = TRUE WHERE user_id = ?");
    if ($stmt->execute([$user_id])) {
        // Get user email to notify them
        $stmt = $conn->prepare("SELECT email, full_name FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Send approval email (you would implement this, or remove for purely offline)
        // sendAccountApprovedEmail($user['email'], $user['full_name']);
        
        logActivity($_SESSION['user_id'], "Approved teacher account ID: $user_id");
        header("Location: approve_teachers.php?approved=1");
        exit();
    }
}

// Handle rejection
if (isset($_GET['reject'])) {
    $user_id = $_GET['reject'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'teacher' AND is_approved = FALSE");
    if ($stmt->execute([$user_id])) {
        logActivity($_SESSION['user_id'], "Rejected teacher account ID: $user_id");
        header("Location: approve_teachers.php?rejected=1");
        exit();
    }
}

// Get pending teachers
$stmt = $conn->query("SELECT * FROM users WHERE role = 'teacher' AND is_approved = FALSE ORDER BY registration_date");
$pendingTeachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-TAFT SRMS - <?= $pageTitle ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/jquery.dataTables.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        
        <?php if (isset($_GET['approved'])): ?>
            <div class="alert alert-success">Teacher account approved successfully!</div>
        <?php elseif (isset($_GET['rejected'])): ?>
            <div class="alert alert-success">Teacher account rejected successfully!</div>
        <?php endif; ?>
        
        <?php if (empty($pendingTeachers)): ?>
            <div class="alert alert-info">No pending teacher accounts for approval.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>Registration Date</th>
                            <th>Teacher ID</th>
                            <th>Full Name</th>
                            <th>Specialization</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingTeachers as $teacher): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($teacher['registration_date'])) ?></td>
                            <td><?= htmlspecialchars($teacher['teacher_id']) ?></td>
                            <td><?= htmlspecialchars($teacher['full_name']) ?></td>
                            <td><?= htmlspecialchars($teacher['specialization']) ?></td>
                            <td><?= htmlspecialchars($teacher['email']) ?></td>
                            <td><?= htmlspecialchars($teacher['username']) ?></td>
                            <td class="actions">
                                <a href="approve_teachers.php?approve=<?= $teacher['user_id'] ?>" class="btn btn-success">Approve</a>
                                <a href="approve_teachers.php?reject=<?= $teacher['user_id'] ?>" class="btn btn-danger btn-delete">Reject</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../../assets/js/jquery.dataTables.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
