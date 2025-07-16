<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $question_id = $_POST['id'];

    // Check if the question belongs to a course taught by this teacher
    $check_query = "SELECT q.id FROM questions q JOIN courses c ON q.course_id = c.id WHERE q.id = ? AND c.teacher_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $question_id, $teacher_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Question not found or unauthorized access"]);
        exit();
    }

    // Delete the question and its options
    $conn->begin_transaction();

    try {
        // Delete options
        $delete_options_query = "DELETE FROM options WHERE question_id = ?";
        $delete_options_stmt = $conn->prepare($delete_options_query);
        $delete_options_stmt->bind_param("i", $question_id);
        $delete_options_stmt->execute();

        // Delete question
        $delete_question_query = "DELETE FROM questions WHERE id = ?";
        $delete_question_stmt = $conn->prepare($delete_question_query);
        $delete_question_stmt->bind_param("i", $question_id);
        $delete_question_stmt->execute();

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Question deleted successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Error deleting question: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
