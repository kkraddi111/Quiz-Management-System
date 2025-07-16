<?php
session_start();
require_once '../config/db.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type'];
    $difficulty = $_POST['difficulty'];
    $explanation = $_POST['explanation'];

    // Insert question into database
    $sql = "INSERT INTO questions (course_id, question_text, question_type, difficulty, explanation) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $bind_result = $stmt->bind_param("issss", $course_id, $question_text, $question_type, $difficulty, $explanation);
    if ($bind_result === false) {
        die("Error binding parameters: " . $stmt->error);
    }

    if ($stmt->execute()) {
        $question_id = $stmt->insert_id;

        // Handle options based on question type
        if ($question_type === 'multiple_choice') {
            $options = $_POST['options'];
            $correct_option = isset($_POST['correct_option']) ? intval($_POST['correct_option']) : 0;
            $option_sql = "INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
            $option_stmt = $conn->prepare($option_sql);
            
            if ($option_stmt === false) {
                die("Error preparing option statement: " . $conn->error);
            }

            foreach ($options as $index => $option) {
                $is_correct = ($index == $correct_option) ? 1 : 0;
                $option_stmt->bind_param("isi", $question_id, $option, $is_correct);
                if (!$option_stmt->execute()) {
                    die("Error executing option statement: " . $option_stmt->error);
                }
            }
        } elseif ($question_type === 'true_false') {
            $correct_answer = $_POST['correct_answer'];
            $option_sql = "INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
            $option_stmt = $conn->prepare($option_sql);
            
            if ($option_stmt === false) {
                die("Error preparing true/false option statement: " . $conn->error);
            }

            // Insert 'True' option
            $is_correct = ($correct_answer === 'true') ? 1 : 0;
            $option_stmt->bind_param("isi", $question_id, "True", $is_correct);
            if (!$option_stmt->execute()) {
                die("Error executing true option statement: " . $option_stmt->error);
            }
            
            // Insert 'False' option
            $is_correct = ($correct_answer === 'false') ? 1 : 0;
            $option_stmt->bind_param("isi", $question_id, "False", $is_correct);
            if (!$option_stmt->execute()) {
                die("Error executing false option statement: " . $option_stmt->error);
            }
        }

        echo "Question added successfully!";
    } else {
        echo "Error adding question: " . $stmt->error;
    }
} else {
    echo "Invalid request method";
}