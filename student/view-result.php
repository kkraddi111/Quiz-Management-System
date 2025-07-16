<?php
session_start();
require_once '../config/db.php';
require_once '../includes/header.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

// Fetch attempt details
$attempt = $conn->query("
    SELECT qa.*, q.title AS quiz_title
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.id = '$attempt_id' AND qa.user_id = '$student_id'
")->fetch_assoc();

if (!$attempt) {
    echo "Quiz attempt not found.";
    exit();
}

// Fetch user answers
$answers = $conn->query("
    SELECT ua.*, q.question_text, q.question_type, q.correct_answer, o.option_text AS user_answer_text
    FROM user_answers ua
    JOIN questions q ON ua.question_id = q.id
    LEFT JOIN options o ON ua.user_answer = o.id
    WHERE ua.attempt_id = '$attempt_id'
");

?>

<h2>Quiz Result: <?php echo $attempt['quiz_title']; ?></h2>
<p>Score: <?php echo $attempt['score']; ?>%</p>
<p>Completed: <?php echo $attempt['completed_at']; ?></p>

<h3>Your Answers</h3>
<table>
    <thead>
        <tr>
            <th>Question</th>
            <th>Your Answer</th>
            <th>Correct Answer</th>
            <th>Result</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($answer = $answers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $answer['question_text']; ?></td>
                <td>
                    <?php
                    if ($answer['question_type'] == 'multiple_choice') {
                        echo $answer['user_answer_text'];
                    } else {
                        echo $answer['user_answer'];
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ($answer['question_type'] == 'multiple_choice') {
                        $correct_option = $conn->query("SELECT option_text FROM options WHERE question_id = '{$answer['question_id']}' AND is_correct = 1")->fetch_assoc();
                        echo $correct_option['option_text'];
                    } else {
                        echo $answer['correct_answer'];
                    }
                    ?>
                </td>
                <td><?php echo $answer['is_correct'] ? 'Correct' : 'Incorrect'; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="index.php">Back to Dashboard</a></p>

<?php require_once '../includes/footer.php'; ?>