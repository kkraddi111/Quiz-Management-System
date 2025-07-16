<?php
session_start();
require_once '../config/db.php';

// Define the base URL correctly
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project/";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Debug information
    error_log("Student login attempt - Username: $username");

    $sql = "SELECT * FROM users WHERE username = '$username' AND role = 'student'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Debug information
            error_log("Student login successful - User ID: {$user['id']}");

            // Update last login time
            $update_login_time = "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}";
            $conn->query($update_login_time);

            // Redirect to student dashboard
            header("Location: " . $base_url . "student/index.php");
            exit();
        }
    }

    // If login fails
    $_SESSION['error'] = "Invalid username or password.";
    header("Location: " . $base_url . "login.php");
    exit();
} else {
    // If someone tries to access this file directly without POST data
    header("Location: " . $base_url . "login.php");
    exit();
}