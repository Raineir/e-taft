<?php
$pageTitle = "Registration Successful";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-TAFT SRMS - <?= $pageTitle ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box text-center">
            <h2>Registration Submitted</h2>
            <div class="alert alert-success">
                <p>Your teacher account registration has been submitted for approval.</p>
                <p>You will receive an email once your account has been activated by the administrator.</p>
            </div>
            <a href="login.php" class="btn">Back to Login</a>
        </div>
    </div>
</body>
</html>
