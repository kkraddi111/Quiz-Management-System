<?php
session_start();
require_once 'config/db.php';

// Define the base URL correctly
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project/";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']);

    // Debug information
    error_log("Login attempt - Username: $username, Role: $role");

    $sql = "SELECT * FROM users WHERE username = '$username' AND role = '$role'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Debug information
            error_log("Login successful - User ID: {$user['id']}, Role: {$user['role']}");

            switch ($role) {
                case 'student':
                    $redirect_url = $base_url . "student/index.php";
                    break;
                case 'teacher':
                    if ($user['status'] != 'approved') {
                        $_SESSION['error'] = "Your teacher account is pending approval.";
                        $redirect_url = $base_url . "login.php";
                    } else {
                        $redirect_url = $base_url . "teacher/index.php";
                    }
                    break;
                case 'admin':
                    $redirect_url = $base_url . "admin/index.php";
                    break;
                default:
                    $_SESSION['error'] = "Invalid role.";
                    $redirect_url = $base_url . "login.php";
            }

            // Debug information
            error_log("Redirecting to: " . $redirect_url);
            
            header("Location: " . $redirect_url);
            exit();
        }
    }

    $_SESSION['error'] = "Invalid username, password, or role.";
    header("Location: " . $base_url . "login.php");
    exit();
} else {
    // If someone tries to access this file directly without POST data
    header("Location: " . $base_url . "login.php");
    exit();
}