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

// Fetch quiz attempts
$quiz_attempts = $conn->query("
    SELECT qa.*, q.title AS quiz_title
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.user_id = '$student_id'
    ORDER BY qa.completed_at DESC
");

// Calculate overall progress
$total_quizzes = $quiz_attempts->num_rows;
$completed_quizzes = 0;
$total_score = 0;

$quiz_data = [];
while ($attempt = $quiz_attempts->fetch_assoc()) {
    if ($attempt['completed_at'] !== null) {
        $completed_quizzes++;
        $total_score += $attempt['score'];
    }
    $quiz_data[] = $attempt;
}

$average_score = $completed_quizzes > 0 ? $total_score / $completed_quizzes : 0;
$completion_rate = $total_quizzes > 0 ? ($completed_quizzes / $total_quizzes) * 100 : 0;

?>

<h2>Your Quiz Progress</h2>

<div class="progress-summary">
    <h3>Overall Progress</h3>
    <div class="progress-bar">
        <div class="progress" style="width: <?php echo $completion_rate; ?>%;"><?php echo round($completion_rate, 2); ?>%</div>
    </div>
    <p>Completed Quizzes: <?php echo $completed_quizzes; ?> / <?php echo $total_quizzes; ?></p>
    <p>Average Quiz Score: <?php echo round($average_score, 2); ?>%</p>
</div>

<h3>Quiz History</h3>
<table>
    <thead>
        <tr>
            <th>Quiz Title</th>
            <th>Score</th>
            <th>Completed</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($quiz_data as $attempt): ?>
            <tr>
                <td><?php echo $attempt['quiz_title']; ?></td>
                <td><?php echo $attempt['score']; ?>%</td>
                <td><?php echo $attempt['completed_at'] ? $attempt['completed_at'] : 'Not completed'; ?></td>
                <td>
                    <?php if ($attempt['completed_at']): ?>
                        <a href="view-result.php?attempt_id=<?php echo $attempt['id']; ?>">View Result</a>
                    <?php else: ?>
                        <a href="take-quiz.php?id=<?php echo $attempt['quiz_id']; ?>">Continue Quiz</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><a href="index.php">Back to Dashboard</a></p>

<?php require_once '../includes/footer.php'; ?>