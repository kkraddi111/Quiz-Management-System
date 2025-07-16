<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if teacher ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-teachers.php");
    exit();
}

$teacher_id = intval($_GET['id']);

// Delete the teacher
$delete_sql = "DELETE FROM users WHERE id = $teacher_id AND role = 'teacher'";
if ($conn->query($delete_sql)) {
    $_SESSION['success_message'] = "Teacher deleted successfully.";
} else {
    $_SESSION['error_message'] = "Error deleting teacher: " . $conn->error;
}

header("Location: manage-teachers.php");
exit();