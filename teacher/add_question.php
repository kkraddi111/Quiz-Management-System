<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        $course_id = $_POST['course_id'];
        $question_text = $_POST['question_text'];
        $question_type = $_POST['question_type'];
        $difficulty = $_POST['difficulty'];
        $explanation = $_POST['explanation'];

        // Verify the course belongs to the teacher
        $course_check = "SELECT id FROM courses WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($course_check);
        $stmt->bind_param("ii", $course_id, $teacher_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Unauthorized course access");
        }

        // Insert the question
        $insert_question = "INSERT INTO questions (course_id, question_text, question_type, difficulty, explanation) 
                           VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_question);
        $stmt->bind_param("issss", $course_id, $question_text, $question_type, $difficulty, $explanation);
        
        if (!$stmt->execute()) {
            throw new Exception("Error inserting question: " . $conn->error);
        }

        $question_id = $conn->insert_id;

        // Handle different question types
        if ($question_type === 'multiple_choice') {
            $options = json_decode($_POST['options'], true);
            if (!$options) {
                throw new Exception("Invalid options data");
            }

            // Insert options
            $insert_option = "INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_option);

            foreach ($options as $option) {
                $is_correct = $option['is_correct'] ? 1 : 0;
                $stmt->bind_param("isi", $question_id, $option['text'], $is_correct);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting option: " . $conn->error);
                }
            }

            // Set correct answer
            $update_correct = "UPDATE questions SET correct_answer = ? WHERE id = ?";
            $stmt = $conn->prepare($update_correct);
            $correct_option = array_filter($options, function($opt) { return $opt['is_correct']; });
            $correct_answer = reset($correct_option)['text'];
            $stmt->bind_param("si", $correct_answer, $question_id);
            $stmt->execute();

        } else if ($question_type === 'true_false') {
            // For true/false questions
            $correct_answer = $_POST['correct_answer'];
            $update_correct = "UPDATE questions SET correct_answer = ? WHERE id = ?";
            $stmt = $conn->prepare($update_correct);
            $stmt->bind_param("si", $correct_answer, $question_id);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Question added successfully',
            'question_id' => $question_id
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
}
?> 