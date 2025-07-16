<?php
// Start the session to access session variables
session_start();

// Include the database configuration file to establish a database connection
require_once '../config/db.php';

// Check if the user is logged in and has an admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the login page if the user is not logged in or not an admin
    header("Location: ../login.php");
    exit();
}

// Check if the teacher ID is provided in the URL parameters
if (!isset($_GET['id'])) {
    // Redirect to the manage teachers page if no teacher ID is provided
    header("Location: manage-teachers.php");
    exit();
}

// Convert the teacher ID from the URL parameter to an integer
$teacher_id = intval($_GET['id']);

// SQL query to update the teacher's status to 'approved' in the database
$approve_sql = "UPDATE users SET status = 'approved' WHERE id = $teacher_id AND role = 'teacher'";

// Execute the SQL query and check if it was successful
if ($conn->query($approve_sql)) {
    // Set a success message in the session if the query was successful
    $_SESSION['success_message'] = "Teacher approved successfully.";
} else {
    // Set an error message in the session if the query failed
    $_SESSION['error_message'] = "Error approving teacher: " . $conn->error;
}

// Redirect to the manage teachers page after processing
header("Location: manage-teachers.php");
exit();