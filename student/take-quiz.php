<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';

$student_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch quiz details
$quiz = $conn->query("SELECT * FROM quizzes WHERE id = '$quiz_id'")->fetch_assoc();

if (!$quiz) {
    echo "Quiz not found.";
    exit();
}

// Check if there's an existing incomplete attempt
$existing_attempt = $conn->query("SELECT id FROM quiz_attempts WHERE user_id = '$student_id' AND quiz_id = '$quiz_id' AND completed_at IS NULL")->fetch_assoc();

if ($existing_attempt) {
    $attempt_id = $existing_attempt['id'];
} else {
    // Create a new attempt
    $conn->query("INSERT INTO quiz_attempts (user_id, quiz_id, started_at) VALUES ('$student_id', '$quiz_id', NOW())");
    $attempt_id = $conn->insert_id;
}

// Fetch questions for this quiz
$questions = $conn->query("SELECT * FROM questions WHERE quiz_id = '$quiz_id' ORDER BY RAND()");

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_quiz'])) {
    $score = 0;
    $total_questions = $questions->num_rows;

    foreach ($_POST['answers'] as $question_id => $user_answer) {
        $question = $conn->query("SELECT * FROM questions WHERE id = '$question_id'")->fetch_assoc();
        
        if ($question['question_type'] == 'multiple_choice') {
            $correct_option = $conn->query("SELECT id FROM options WHERE question_id = '$question_id' AND is_correct = 1")->fetch_assoc();
            $is_correct = ($user_answer == $correct_option['id']) ? 1 : 0;
        } else {
            // For true/false and short answer questions, compare directly
            $is_correct = (strtolower($user_answer) == strtolower($question['correct_answer'])) ? 1 : 0;
        }

        if ($is_correct) {
            $score++;
        }

        // Insert or update user answer
        $conn->query("INSERT INTO user_answers (attempt_id, question_id, user_answer, is_correct) 
                      VALUES ('$attempt_id', '$question_id', '$user_answer', '$is_correct')
                      ON DUPLICATE KEY UPDATE user_answer = '$user_answer', is_correct = '$is_correct'");
    }

    $final_score = ($score / $total_questions) * 100;

    // Update quiz attempt with score and completion time
    $conn->query("UPDATE quiz_attempts SET score = '$final_score', completed_at = NOW() WHERE id = '$attempt_id'");

    // Redirect to results page
    header("Location: view-result.php?attempt_id=$attempt_id");
    exit();
}

?>

<h2>Take Quiz: <?php echo $quiz['title']; ?></h2>

<form action="" method="post">
    <?php $question_number = 1; ?>
    <?php while ($question = $questions->fetch_assoc()): ?>
        <div class="question">
            <h3>Question <?php echo $question_number; ?>:</h3>
            <p><?php echo $question['question_text']; ?></p>
            
            <?php if ($question['question_type'] == 'multiple_choice'): ?>
                <?php
                $options = $conn->query("SELECT * FROM options WHERE question_id = '{$question['id']}' ORDER BY RAND()");
                while ($option = $options->fetch_assoc()):
                ?>
                    <label>
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="<?php echo $option['id']; ?>" required>
                        <?php echo $option['option_text']; ?>
                    </label><br>
                <?php endwhile; ?>
            <?php elseif ($question['question_type'] == 'true_false'): ?>
                <label>
                    <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="true" required> True
                </label><br>
                <label>
                    <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="false" required> False
                </label>
            <?php else: ?>
                <input type="text" name="answers[<?php echo $question['id']; ?>]" required>
            <?php endif; ?>
        </div>
        <?php $question_number++; ?>
    <?php endwhile; ?>

    <button type="submit" name="submit_quiz">Submit Quiz</button>
</form>

<?php require_once '../includes/footer.php'; ?>