<?php
session_start();
require_once '../config/db.php';

$page_title = "Student Dashboard";
require_once '../includes/student_header.php';

$student_id = $_SESSION['user_id'];

// Fetch student information
$student_query = "SELECT * FROM users WHERE id = $student_id AND role = 'student'";
$student_result = $conn->query($student_query);
$student = $student_result->fetch_assoc();

// Fetch enrolled courses
$courses_query = "SELECT c.* FROM courses c
                  JOIN enrollments e ON c.id = e.course_id
                  WHERE e.user_id = $student_id
                  LIMIT 3";
$courses_result = $conn->query($courses_query);

// Fetch all completed quiz attempts across all courses
$recent_attempts_query = "SELECT qa.*, q.title AS quiz_title, c.name AS course_name, c.id AS course_id 
                         FROM quiz_attempts qa
                         JOIN quizzes q ON qa.quiz_id = q.id
                         JOIN courses c ON q.course_id = c.id
                         WHERE qa.user_id = ? 
                         AND qa.completed_at IS NOT NULL
                         ORDER BY qa.completed_at DESC";
$attempts_stmt = $conn->prepare($recent_attempts_query);
$attempts_stmt->bind_param("i", $student_id);
$attempts_stmt->execute();
$recent_attempts_result = $attempts_stmt->get_result();
?>

<div class="dashboard-header">
    <h1>Welcome, <?php echo htmlspecialchars($student['username']); ?>!</h1>
    <?php if (isset($student['last_login']) && $student['last_login'] !== null): ?>
        <p class="last-login">Last login: <?php echo date('M d, Y H:i', strtotime($student['last_login'])); ?></p>
    <?php else: ?>
        <p class="last-login">This is your first login!</p>
    <?php endif; ?>
</div>

<div class="dashboard-grid">
    <section class="dashboard-section">
        <h2><i class="fas fa-graduation-cap"></i> Available Courses</h2>
        <p>Explore new courses and expand your knowledge!</p>
        <a href="../available-courses.php" class="btn btn-primary">View Available Courses</a>
    </section>

    <section class="dashboard-section">
        <h2><i class="fas fa-book"></i> Your Courses</h2>
        <?php if ($courses_result && $courses_result->num_rows > 0): ?>
            <ul class="course-list">
                <?php while ($course = $courses_result->fetch_assoc()): ?>
                    <li>
                        <a href="view-course.php?id=<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>You are not enrolled in any courses yet.</p>
        <?php endif; ?>
        <a href="view-mycourses.php" class="btn btn-primary">View All My Courses</a>
    </section>

    <section class="dashboard-section">
        <h2><i class="fas fa-chart-bar"></i> Recent Quiz Attempts</h2>
        <p>View your quiz performance and track your progress.</p>
        <a href="quiz-history.php" class="btn btn-primary">
            <i class="fas fa-history"></i> View Quiz History
        </a>
    </section>
</div>

<style>
.quiz-attempts-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.attempt-card {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.attempt-header {
    margin-bottom: 0.5rem;
}

.quiz-title {
    font-size: 1.1rem;
    margin: 0;
    color: #2c3e50;
}

.course-name {
    font-size: 0.9rem;
    color: #6c757d;
}

.attempt-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.score-badge {
    font-weight: bold;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 1.1rem;
}

.score-badge.passing {
    background: #d4edda;
    color: #155724;
}

.score-badge.failing {
    background: #f8d7da;
    color: #721c24;
}

.attempt-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
}

.attempt-date {
    font-size: 0.9rem;
    color: #6c757d;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
    text-decoration: none;
}

.btn-info:hover {
    background-color: #138496;
}

.mt-3 {
    margin-top: 1rem;
}
</style>

<?php require_once '../includes/footer.php'; ?>