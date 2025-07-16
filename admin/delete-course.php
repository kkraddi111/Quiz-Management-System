<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if course ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-courses.php");
    exit();
}

$course_id = intval($_GET['id']);

// Delete the course
$delete_sql = "DELETE FROM courses WHERE id = $course_id";
if ($conn->query($delete_sql)) {
    $_SESSION['success_message'] = "Course deleted successfully.";
} else {
    $_SESSION['error_message'] = "Error deleting course: " . $conn->error;
}

header("Location: manage-courses.php");
exit();