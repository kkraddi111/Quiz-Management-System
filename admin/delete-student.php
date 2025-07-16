<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if student ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-students.php");
    exit();
}

$student_id = intval($_GET['id']);

// Delete the student
$delete_sql = "DELETE FROM users WHERE id = $student_id AND role = 'student'";
if ($conn->query($delete_sql)) {
    $_SESSION['success_message'] = "Student deleted successfully.";
} else {
    $_SESSION['error_message'] = "Error deleting student: " . $conn->error;
}

header("Location: manage-students.php");
exit();