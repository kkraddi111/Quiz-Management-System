<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    exit('Unauthorized access');
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = $_POST['question_id'];
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type'];
    $difficulty = $_POST['difficulty'];
    $correct_answer = $_POST['correct_answer'];
    $explanation = $_POST['explanation'];

    // Update the question
    $query = "UPDATE questions q
              JOIN courses c ON q.course_id = c.id
              SET q.question_text = ?, 
                  q.question_type = ?, 
                  q.difficulty = ?,
                  q.correct_answer = ?,
                  q.explanation = ?
              WHERE q.id = ? AND c.teacher_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssii", 
        $question_text, 
        $question_type, 
        $difficulty, 
        $correct_answer,
        $explanation,
        $question_id, 
        $teacher_id
    );
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Error: ' . $conn->error;
    }
} else {
    echo 'Invalid request method';
}
