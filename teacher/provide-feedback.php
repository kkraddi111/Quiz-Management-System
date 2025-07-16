<?php
session_start();
require_once '../config/db.php';
require_once '../includes/header.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

// Fetch attempt details
$attempt = $conn->query("
    SELECT qa.*, u.username AS student_name, q.title AS quiz_title
    FROM quiz_attempts qa
    JOIN users u ON qa.user_id = u.id
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.id = '$attempt_id' AND q.created_by = '$teacher_id'
")->fetch_assoc();

if (!$attempt) {
    echo "Quiz attempt not found or you don't have permission to view it.";
    exit();
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $feedback = $conn->real_escape_string($_POST['feedback']);
    $conn->query("UPDATE quiz_attempts SET teacher_feedback = '$feedback' WHERE id = '$attempt_id'");
    $success = "Feedback submitted successfully.";
    $attempt['teacher_feedback'] = $feedback;
}

// Fetch user answers
$answers = $conn->query("
    SELECT ua.*, q.question_text, q.question_type, q.correct_answer
    FROM user_answers ua
    JOIN questions q ON ua.question_id = q.id
    WHERE ua.attempt_id = '$attempt_id'
");

?>

<h2>Provide Feedback</h2>
<p>Student: <?php echo $attempt['student_name']; ?></p>
<p>Quiz: <?php echo $attempt['quiz_title']; ?></p>
<p>Score: <?php echo $attempt['score']; ?>%</p>
<p>Completed: <?php echo $attempt['completed_at']; ?></p>

<?php if (isset($success)): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<h3>Student Answers</h3>
<table>
    <thead>
        <tr>
            <th>Question</th>
            <th>Student's Answer</th>
            <th>Correct Answer</th>
            <th>Result</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($answer = $answers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $answer['question_text']; ?></td>
                <td><?php echo $answer['user_answer']; ?></td>
                <td><?php echo $answer['correct_answer']; ?></td>
                <td><?php echo $answer['is_correct'] ? 'Correct' : 'Incorrect'; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<form method="post">
    <div class="form-group">
        <label for="feedback">Feedback:</label>
        <textarea id="feedback" name="feedback" rows="4" required><?php echo $attempt['teacher_feedback'] ?? ''; ?></textarea>
    </div>
    <button type="submit" name="submit_feedback">Submit Feedback</button>
</form>

<p><a href="view-results.php?id=<?php echo $attempt['quiz_id']; ?>">Back to Quiz Results</a></p>

<?php require_once '../includes/footer.php'; ?>