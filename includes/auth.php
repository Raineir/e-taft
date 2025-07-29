<?php
require_once 'config/database.php'; // Include the database connection

// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']); // Check if user_id is set in session
}

function login($username, $password) {
    global $conn; // Use the global $conn variable

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    // Fetch the user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging: log user data
    error_log("Login attempt for username: $username");
    if ($user) {
        error_log("User found: " . print_r($user, true));
    } else {
        error_log("User not found for username: $username");
    }

    // Verify the password and check role is teacher
    if ($user && password_verify($password, $user['password'])) {
        // Optionally check if user role is teacher or other roles
        if ($user['role'] === 'teacher' || $user['role'] === 'admin') {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            return true;
        } else {
            error_log("User role is not teacher or admin: " . $user['role']);
            return false;
        }
    }
    return false;
}

// CSRF token generation and validation functions
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// New logout function for better code organization
function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
