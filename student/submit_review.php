<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    // Check if the user has already reviewed this course
    $check_query = "SELECT id FROM course_reviews WHERE course_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $course_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "You have already reviewed this course"]);
        exit();
    }

    // Insert the new review
    $insert_query = "INSERT INTO course_reviews (course_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iiis", $course_id, $user_id, $rating, $review_text);

    if ($insert_stmt->execute()) {
        // Update the course's average rating and rating count
        $update_query = "UPDATE courses SET 
                         average_rating = (SELECT AVG(rating) FROM course_reviews WHERE course_id = ?),
                         rating_count = (SELECT COUNT(*) FROM course_reviews WHERE course_id = ?)
                         WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("iii", $course_id, $course_id, $course_id);
        $update_stmt->execute();

        echo json_encode(["success" => true, "message" => "Review submitted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error submitting review"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
