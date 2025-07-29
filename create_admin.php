   <?php
   require_once 'config/database.php';

   // Define the admin user details
   $username = 'admin';
   $password = 'admin'; // This will be hashed
   $full_name = 'Administrator';
   $email = 'admin@example.com'; // Use a valid email address
   $role = 'admin';

   // Hash the password
   $hashed_password = password_hash($password, PASSWORD_DEFAULT);

   // Prepare the SQL statement
   $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");

   // Execute the statement
   if ($stmt->execute([$username, $hashed_password, $full_name, $email, $role])) {
       echo "Admin user created successfully.";
   } else {
       echo "Error creating admin user.";
   }
   ?>
   