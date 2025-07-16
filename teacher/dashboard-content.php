<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo "Unauthorized access";
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Fetch teacher name
$teacher_result = $conn->query("SELECT username FROM users WHERE id = '$teacher_id'");
$teacher_name = $teacher_result->fetch_assoc()['username'];

// Fetch courses taught by this teacher
$courses_query = "SELECT c.id, c.name, c.image FROM courses c 
                  JOIN course_teachers ct ON c.id = ct.course_id 
                  WHERE ct.teacher_id = '$teacher_id'";
$courses_result = $conn->query($courses_query);

// Count assigned courses
$assigned_courses_count = $courses_result->num_rows;

// Count total questions for the courses taught by this teacher
$total_questions_query = "SELECT COUNT(*) as total FROM questions q 
                          JOIN course_teachers ct ON q.course_id = ct.course_id 
                          WHERE ct.teacher_id = '$teacher_id'";
$total_questions_result = $conn->query($total_questions_query);
$total_questions_count = $total_questions_result->fetch_assoc()['total'];

// Fetch recent questions
$questions_query = "SELECT q.id, q.question_text, c.name AS course_name 
                    FROM questions q 
                    JOIN courses c ON q.course_id = c.id 
                    JOIN course_teachers ct ON c.id = ct.course_id
                    WHERE ct.teacher_id = '$teacher_id' 
                    ORDER BY q.created_at DESC LIMIT 5";
$questions_result = $conn->query($questions_query);
?>

<h2>Welcome, <?php echo htmlspecialchars($teacher_name); ?>!</h2>

<div class="dashboard-content">
    <div class="stat-card">
        <h3>Assigned Courses</h3>
        <div class="stat-number"><?php echo $assigned_courses_count; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Questions</h3>
        <div class="stat-number"><?php echo $total_questions_count; ?></div>
    </div>
</div>

<h3>Your Courses</h3>
<div class="course-cards">
    <?php if ($courses_result->num_rows > 0): ?>
        <?php while ($course = $courses_result->fetch_assoc()): ?>
            <div class="course-card">
                <div class="course-image-container">
                    <?php if (!empty($course['image'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['name']); ?>" class="course-image">
                    <?php else: ?>
                        <div class="course-image-placeholder">No Image</div>
                    <?php endif; ?>
                </div>
                <div class="course-info">
                    <div class="course-title-box">
                        <h4 class="course-title"><?php echo htmlspecialchars($course['name']); ?></h4>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You are not assigned to any courses yet.</p>
    <?php endif; ?>
</div>

<div class="recent-questions">
    <h3>Recent Questions</h3>
    <?php if ($questions_result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Question</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($question = $questions_result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($question['course_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No recent questions found.</p>
    <?php endif; ?>
</div>