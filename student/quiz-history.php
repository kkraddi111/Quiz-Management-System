<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Updated query to include number of questions and difficulty
$attempts_query = "SELECT qa.*, q.title AS quiz_title, 
                         c.name AS course_name, c.id AS course_id,
                         q.difficulty,
                         (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                  FROM quiz_attempts qa
                  JOIN quizzes q ON qa.quiz_id = q.id
                  JOIN courses c ON q.course_id = c.id
                  WHERE qa.user_id = ? 
                  AND qa.completed_at IS NOT NULL
                  ORDER BY qa.completed_at DESC";
$stmt = $conn->prepare($attempts_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$attempts_result = $stmt->get_result();

$page_title = "Quiz History";
require_once '../includes/student_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-history"></i> Quiz History</h1>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <?php if ($attempts_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Quiz</th>
                                <th>Course</th>
                                <th>Questions</th>
                                <th>Level</th>
                                <th>Score</th>
                                <th>Completion Date</th>
                                <th>Time Taken</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($attempt = $attempts_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                    <td>
                                        <a href="view-course.php?id=<?php echo $attempt['course_id']; ?>">
                                            <?php echo htmlspecialchars($attempt['course_name']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo $attempt['question_count']; ?> Questions
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst(htmlspecialchars($attempt['difficulty'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $attempt['score'] >= 70 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo number_format($attempt['score'], 1); ?>%
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y, g:i A', strtotime($attempt['completed_at'])); ?></td>
                                    <td>
                                        <?php
                                        $start_time = strtotime($attempt['started_at']);
                                        $end_time = strtotime($attempt['completed_at']);
                                        $time_taken = $end_time - $start_time;
                                        
                                        // Format time taken
                                        if ($time_taken < 60) {
                                            echo $time_taken . " seconds";
                                        } else {
                                            $minutes = floor($time_taken / 60);
                                            $seconds = $time_taken % 60;
                                            echo $minutes . " minute" . ($minutes != 1 ? "s" : "") . " " . 
                                                 $seconds . " second" . ($seconds != 1 ? "s" : "");
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You haven't completed any quizzes yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.table {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.9rem;
    padding: 0.5em 1em;
}

.badge.bg-info {
    background-color: #FF8C00 !important; /* Dark Orange instead of blue */
}

.btn-secondary {
    color: white;
}

.btn-secondary:hover {
    color: white;
    opacity: 0.9;
}
</style>

<?php require_once '../includes/footer.php'; ?>