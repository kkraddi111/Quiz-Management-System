<?php
session_start();
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $conn->real_escape_string($_POST['role']);

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: signup.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: signup.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: signup.php");
        exit();
    }

    // Check if username or email already exists
    $check_user = $conn->query("SELECT * FROM users WHERE username = '$username' OR email = '$email'");
    if ($check_user->num_rows > 0) {
        $_SESSION['error'] = "Username or email already exists.";
        header("Location: signup.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Set initial status based on role
    $status = ($role == 'teacher') ? 'pending' : 'approved';

    // Insert new user into the database
    $sql = "INSERT INTO users (username, email, password, role, status) VALUES ('$username', '$email', '$hashed_password', '$role', '$status')";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Registration successful. You can now log in.";
        if ($role == 'teacher') {
            $_SESSION['success'] .= " Your account is pending approval.";
        }
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
        header("Location: signup.php");
        exit();
    }
} else {
    header("Location: signup.php");
    exit();
}