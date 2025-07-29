<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/auth.php';
require_once 'includes/functions.php';

$pageTitle = "Teacher Registration";

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Register form submitted");
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $teacher_id = trim($_POST['teacher_id']);
    $specialization = trim($_POST['specialization']);

    // Validate inputs
    $errors = [];
    
    if (empty($username) || strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters long.";
    }
    
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }
    
    if (empty($teacher_id)) {
        $errors[] = "Teacher ID is required.";
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Username or email already exists.";
    }
    
    // Check if teacher ID exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE teacher_id = ?");
    $stmt->execute([$teacher_id]);
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Teacher ID already registered.";
    }

    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user (not approved yet)
        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role, teacher_id, specialization) 
                              VALUES (?, ?, ?, ?, 'teacher', ?, ?)");
        
        if ($stmt->execute([$username, $hashed_password, $full_name, $email, $teacher_id, $specialization])) {
            // Log activity
            logActivity($conn->lastInsertId(), "Teacher registered (pending approval)");
            
            // Send email to admin (you would implement this, or remove for purely offline)
            // sendApprovalEmail($email, $full_name);
            
            $success = true;
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-TAFT SRMS - <?= $pageTitle ?></title>
    <link rel="stylesheet" href="assets/css/elegant-style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #222;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .register-container {
            background: #fff;
            padding: 50px 60px;
            border-radius: 15px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            width: 420px;
            max-width: 95%;
            box-sizing: border-box;
        }
        h2 {
            text-align: center;
            margin-bottom: 40px;
            font-weight: 600;
            font-size: 2.2rem;
            color: #333;
            letter-spacing: 1.5px;
        }
        label {
            font-weight: 500;
            color: #555;
            display: block;
            margin-bottom: 8px;
            font-size: 1rem;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1.8px solid #ddd;
            border-radius: 8px;
            font-size: 1.1rem;
            color: #333;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box;
        }
        .form-control:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 8px rgba(102, 126, 234, 0.6);
        }
        .btn {
            width: 100%;
            padding: 14px 0;
            background: #667eea;
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 600;
            font-size: 1.3rem;
            cursor: pointer;
            transition: background 0.3s ease;
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
        }
        .btn:hover {
            background: #5563c1;
        }
        .alert {
            background: #ff6b6b;
            color: #fff;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1rem;
            text-align: center;
        }
        a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
        p.mt-3 {
            text-align: center;
            margin-top: 25px;
            font-size: 1rem;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Teacher Registration</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
<form action="register.php" method="POST" class="needs-validation">
                <div class="form-group">
                    <label for="teacher_id">Teacher ID</label>
                    <input type="text" id="teacher_id" name="teacher_id" class="form-control" required>
                    <div class="invalid-feedback">Please enter your teacher ID.</div>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                    <div class="invalid-feedback">Please enter your full name.</div>
                </div>
                
                <div class="form-group">
                    <label for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" class="form-control" required>
                    <div class="invalid-feedback">Please enter your specialization.</div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                    <div class="invalid-feedback">Please enter a valid email.</div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required minlength="4">
                    <div class="invalid-feedback">Username must be at least 4 characters.</div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="8">
                    <div class="invalid-feedback">Password must be at least 8 characters.</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    <div class="invalid-feedback">Passwords must match.</div>
                </div>
                
                <button type="submit" class="btn">Register</button>
                <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
    // Client-side validation
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                // Check password match
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('confirm_password');
                
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords must match");
                } else {
                    confirmPassword.setCustomValidity('');
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    });
    </script>
</body>
