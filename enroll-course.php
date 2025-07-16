<?php
session_start();
require_once 'config/db.php';

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $_SESSION['error'] = "You must be logged in as a student to enroll in courses.";
    header("Location: login.php");
    exit();
}

// Check if a course_id is provided
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    $_SESSION['error'] = "Invalid course selection.";
    header("Location: available-courses.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id']);

// Check if the enrollments table exists
$check_table = "SHOW TABLES LIKE 'enrollments'";
$table_result = $conn->query($check_table);
if ($table_result->num_rows == 0) {
    // Create the enrollments table if it doesn't exist
    $create_table = "CREATE TABLE enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        course_id INT NOT NULL,
        enrollment_date DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (course_id) REFERENCES courses(id),
        UNIQUE KEY (user_id, course_id)
    )";
    if (!$conn->query($create_table)) {
        error_log("Error creating enrollments table: " . $conn->error);
        $_SESSION['error'] = "There was an error with the enrollment system. Please try again later.";
        header("Location: available-courses.php");
        exit();
    }
}

// Check if the student is already enrolled in this course
$check_enrollment = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
$stmt = $conn->prepare($check_enrollment);
if ($stmt === false) {
    error_log("Error preparing check enrollment statement: " . $conn->error);
    $_SESSION['error'] = "There was an error checking your enrollment. Please try again.";
    header("Location: available-courses.php");
    exit();
}

$stmt->bind_param("ii", $student_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "You are already enrolled in this course.";
    header("Location: available-courses.php");
    exit();
}

// Enroll the student in the course
$enroll_query = "INSERT INTO enrollments (user_id, course_id, enrollment_date) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($enroll_query);
if ($stmt === false) {
    error_log("Error preparing enroll statement: " . $conn->error);
    $_SESSION['error'] = "There was an error enrolling in the course. Please try again.";
    header("Location: available-courses.php");
    exit();
}

$stmt->bind_param("ii", $student_id, $course_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "You have successfully enrolled in the course!";
    header("Location: student/my_courses.php");
    exit();
} else {
    error_log("Error executing enroll statement: " . $stmt->error);
    $_SESSION['error'] = "There was an error enrolling in the course. Please try again.";
    header("Location: available-courses.php");
    exit();
}

$stmt->close();
$conn->close();