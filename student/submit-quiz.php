<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$attempt_id = $_POST['attempt_id'];

// Verify this attempt belongs to the current user
$attempt_query = "SELECT * FROM quiz_attempts WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($attempt_query);
$stmt->bind_param("ii", $attempt_id, $student_id);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();

if (!$attempt) {
    header("Location: index.php");
    exit();
}

// Get submitted answers
$question_ids = $_POST['question_ids'];
$answers = $_POST['answers'];

$total_questions = count($question_ids);
$correct_answers = 0;

// Begin transaction
$conn->begin_transaction();

try {
    // Process each answer
    foreach ($question_ids as $question_id) {
        $user_answer = isset($answers[$question_id]) ? $answers[$question_id] : null;
        
        // Get question details
        $question_query = "SELECT q.*, q.explanation 
                          FROM questions q 
                          WHERE q.id = ?";
        $stmt = $conn->prepare($question_query);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $question = $stmt->get_result()->fetch_assoc();
        
        $is_correct = false;
        
        if ($user_answer !== null) {
            switch ($question['question_type']) {
                case 'multiple_choice':
                    // Check if selected option is correct
                    $option_query = "SELECT is_correct FROM options WHERE id = ? AND question_id = ?";
                    $stmt = $conn->prepare($option_query);
                    $stmt->bind_param("ii", $user_answer, $question_id);
                    $stmt->execute();
                    $result = $stmt->get_result()->fetch_assoc();
                    $is_correct = $result && $result['is_correct'] == 1;
                    break;
                    
                case 'true_false':
                    $is_correct = $user_answer === $question['correct_answer'];
                    break;
                    
                case 'short_answer':
                    // Simple exact match for short answers
                    // You might want to implement more sophisticated matching
                    $is_correct = strtolower(trim($user_answer)) === strtolower(trim($question['correct_answer']));
                    break;
            }
        }
        
        // Insert user's answer
        $insert_answer = "INSERT INTO user_answers (attempt_id, question_id, user_answer, is_correct) 
                         VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_answer);
        $stmt->bind_param("iisi", $attempt_id, $question_id, $user_answer, $is_correct);
        $stmt->execute();
        
        if ($is_correct) {
            $correct_answers++;
        }
    }
    
    // Calculate score as percentage
    $score = ($correct_answers / $total_questions) * 100;
    
    // Update quiz attempt with score and completion time
    $update_attempt = "UPDATE quiz_attempts 
                      SET score = ?, completed_at = CURRENT_TIMESTAMP 
                      WHERE id = ?";
    $stmt = $conn->prepare($update_attempt);
    $stmt->bind_param("di", $score, $attempt_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Redirect to results page
    header("Location: quiz-result.php?attempt_id=" . $attempt_id);
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error (you should implement proper error logging)
    error_log("Error submitting quiz: " . $e->getMessage());
    
    // Redirect with error message
    header("Location: index.php?error=submission_failed");
    exit();
}
?> 