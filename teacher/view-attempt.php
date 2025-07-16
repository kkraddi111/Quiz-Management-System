<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';

$teacher_id = $_SESSION['user_id'];
$attempt_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch attempt details
$attempt = $conn->query("
    SELECT qa.*, q.title AS quiz_title, c.name AS course_name, u.username
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN courses c ON q.course_id = c.id
    JOIN users u ON qa.user_id = u.id
    WHERE qa.id = '$attempt_id' AND c.teacher_id = '$teacher_id'
")->fetch_assoc();

if (!$attempt) {
    echo "Attempt not found or you don't have permission to view it.";
    exit();
}

// Fetch user answers
$answers = $conn->query("
    SELECT ua.*, q.question_text, q.question_type
    FROM user_answers ua
    JOIN questions q ON ua.question_id = q.id
    WHERE ua.attempt_id = '$attempt_id'
");

?>

<h2>Quiz Attempt Details</h2>
<p>Quiz: <?php echo $attempt['quiz_title']; ?></p>
<p>Course: <?php echo $attempt['course_name']; ?></p>
<p>Student: <?php echo $attempt['username']; ?></p>
<p>Score: <?php echo $attempt['score']; ?>%</p>
<p>Started: <?php echo $attempt['started_at']; ?></p>
<p>Completed: <?php echo $attempt['completed_at']; ?></p>

<h3>Answers</h3>
<table>
    <thead>
        <tr>
            <th>Question</th>
            <th>Student's Answer</th>
            <th>Correct?</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($answer = $answers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $answer['question_text']; ?></td>
                <td><?php echo $answer['user_answer']; ?></td>
                <td><?php echo $answer['is_correct'] ? 'Yes' : 'No'; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="view-results.php?id=<?php echo $attempt['quiz_id']; ?>">Back to Quiz Results</a></p>

<?php require_once '../includes/footer.php'; ?>