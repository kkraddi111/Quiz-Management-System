<?php
require_once 'config/db.php';

// Admin user details
$username = 'admin';
$name = 'Admin User'; // Add this line
$email = 'admin@gmail.com';
$password = 'admin123'; // You should change this to a strong, secure password
$role = 'admin';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin user already exists
$check_sql = "SELECT * FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo "Admin user already exists.";
} else {
    // Prepare the SQL statement
    $sql = "INSERT INTO users (username, name, email, password, role) VALUES (?, ?, ?, ?, ?)";

    // Prepare and bind
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $name, $email, $hashed_password, $role);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Admin user created successfully.";
    } else {
        echo "Error creating admin user: " . $conn->error;
    }

    $stmt->close();
}

$check_stmt->close();

// Verify the admin user was created
$verify_sql = "SELECT * FROM users WHERE username = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("s", $username);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows > 0) {
    $admin_user = $verify_result->fetch_assoc();
    echo "<br>Admin user details:<br>";
    echo "Username: " . $admin_user['username'] . "<br>";
    echo "Name: " . $admin_user['name'] . "<br>";
    echo "Email: " . $admin_user['email'] . "<br>";
    echo "Role: " . $admin_user['role'] . "<br>";
} else {
    echo "<br>Failed to verify admin user creation.";
}

$verify_stmt->close();
$conn->close();
?>