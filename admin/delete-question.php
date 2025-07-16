<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if question ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-questions.php");
    exit();
}

$question_id = $_GET['id'];

// Delete the question and its associated options
$conn->begin_transaction();

try {
    // Delete options associated with the question
    $delete_options_query = "DELETE FROM options WHERE question_id = ?";
    $stmt = $conn->prepare($delete_options_query);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();

    // Delete the question
    $delete_question_query = "DELETE FROM questions WHERE id = ?";
    $stmt = $conn->prepare($delete_question_query);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();

    $conn->commit();
    $_SESSION['success_message'] = "Question deleted successfully.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Error deleting question: " . $e->getMessage();
}

header("Location: manage-questions.php");
exit();