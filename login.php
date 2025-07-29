<?php
require_once 'includes/auth.php'; // Include the authentication functions

// Start the session at the beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    header("Location: index.php"); // Redirect to dashboard if already logged in
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Invalid request.";
    } else {
        // Sanitize inputs
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = $_POST['password']; // Password should not be sanitized to avoid altering it

        if (login($username, $password)) {
            session_regenerate_id(true); // Prevent session fixation
            header("Location: index.php"); // Redirect to dashboard on successful login
            exit();
        } else {
            // More detailed error messages
            global $conn;
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = "User not found.";
            } elseif (!password_verify($password, $user['password'])) {
                $error = "Incorrect password.";
            } elseif ($user['role'] !== 'teacher') {
                $error = "User role is not teacher.";
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-TAFT SRMS</title>
    <link rel="stylesheet" href="assets/css/elegant-style.css"> <!-- Link to the new elegant CSS file -->
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h2>Login</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <p class="mt-3">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
