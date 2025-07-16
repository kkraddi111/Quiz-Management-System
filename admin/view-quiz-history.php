<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';

// Fetch quiz history with student, quiz, and course information
$history = $conn->query("
    SELECT qa.id, u.username AS student_name, q.title AS quiz_title, c.name AS course_name, qa.score, qa.started_at, qa.completed_at
    FROM quiz_attempts qa
    JOIN users u ON qa.user_id = u.id
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN courses c ON q.course_id = c.id
    ORDER BY qa.completed_at DESC
");
?>

<h2>Quiz History</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Quiz</th>
            <th>Course</th>
            <th>Score</th>
            <th>Started</th>
            <th>Completed</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($attempt = $history->fetch_assoc()): ?>
            <tr>
                <td><?php echo $attempt['id']; ?></td>
                <td><?php echo $attempt['student_name']; ?></td>
                <td><?php echo $attempt['quiz_title']; ?></td>
                <td><?php echo $attempt['course_name']; ?></td>
                <td><?php echo $attempt['score']; ?></td>
                <td><?php echo $attempt['started_at']; ?></td>
                <td><?php echo $attempt['completed_at']; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>