<?php
require_once 'auth.php';
requireLogin();

function isActive($page) {
    $currentFile = basename($_SERVER['PHP_SELF']);
    return $currentFile === $page ? 'active' : '';
}
?>
<header>
    <div class="header-container">
        <div class="logo">E-TAFT SRMS</div>
        <ul class="nav-links">
            <li><a class="<?= isActive('index.php') ?>" href="index.php">Dashboard</a></li>
            <li><a class="<?= isActive('students.php') ?>" href="students.php">Students</a></li>
            <!-- Classes link removed as per user request -->
            <!-- <li><a class="<?= isActive('classes.php') ?>" href="classes.php">Classes</a></li> -->
            <li><a class="<?= isActive('users.php') ?>" href="users.php">Users</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
        <div class="user-info">
            <img src="assets/images/default-profile.png" alt="Profile Picture" class="profile-pic">
            <span><?= htmlspecialchars($_SESSION['full_name']) ?></span>
        </div>
    </div>
</header>
