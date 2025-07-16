<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$teacher_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];

// Verify that the teacher has permission to edit this course
$permission_check = $conn->prepare("SELECT 1 FROM course_teachers WHERE course_id = ? AND teacher_id = ?");
$permission_check->bind_param("ii", $course_id, $teacher_id);
$permission_check->execute();
$result = $permission_check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this course']);
    exit();
}

$name = $conn->real_escape_string($_POST['name']);
$description = $conn->real_escape_string($_POST['description']);

$update_sql = "UPDATE courses SET name = ?, description = ? WHERE id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("ssi", $name, $description, $course_id);

if ($stmt->execute()) {
    // Handle image upload if a new image was provided
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] == 0) {
        // ... (image upload code) ...
    }
    
    echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating course: ' . $conn->error]);
}

$stmt->close();
$conn->close();