<?php
require_once 'config/database.php'; // Include the database connection
require_once 'includes/auth.php';
requireLogin();
require_once 'includes/functions.php';

$pageTitle = "Dashboard";
$role = $_SESSION['role'];

// Get counts for dashboard
$studentCount = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
$classCount = $conn->query("SELECT COUNT(*) FROM academic_classes")->fetchColumn();
$userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-TAFT SRMS - <?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="assets/css/elegant-dashboard.css"> <!-- New elegant dashboard CSS -->
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
        <p>Role: <?= ucfirst($role) ?></p>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Students</h5>
                        <p class="card-text"><?= $studentCount ?></p>
                        <a href="students.php" class="btn btn-primary">View All</a>
                    </div>
                </div>
            </div>
            <!-- Classes card removed as per user request -->
            <!--
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Classes</h5>
                        <p class="card-text"><?= $classCount ?></p>
                        <a href="classes.php" class="btn btn-primary">View All</a>
                    </div>
                </div>
            </div>
            -->
            <?php if ($role === 'admin'): ?>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                        <p class="card-text"><?= $userCount ?></p>
                        <a href="users.php" class="btn btn-primary">View All</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text">-</p>
                        <a href="reports.php" class="btn btn-primary">Generate Reports</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Print Student Record</h5>
                        <form method="GET" action="print_student_record.php" target="_blank">
                            <div class="form-group">
                                <label for="student_id">Select Student</label>
                                <select id="student_id" name="student_id" required>
                                    <option value="">Select Student</option>
                                    <?php
                                    $students = $conn->query("SELECT student_id, first_name, last_name FROM students ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($students as $student) {
                                        echo '<option value="' . htmlspecialchars($student['student_id']) . '">' . htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Print Student Record</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <h2>Recent Activities</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $logs = $conn->query("SELECT l.*, u.username 
                                        FROM system_logs l 
                                        JOIN users u ON l.user_id = u.user_id 
                                        ORDER BY l.created_at DESC 
                                        LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($logs as $log): ?>
                    <tr>
                        <td><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($log['ip_address']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable(); // Initialize DataTables
        });
    </script>
</body>
</html>
