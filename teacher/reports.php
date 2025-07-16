<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Fetch students who have attempted quizzes in teacher's courses
$students_query = "SELECT DISTINCT 
                    u.id,
                    u.username,
                    COUNT(DISTINCT qa.id) as total_attempts,
                    AVG(qa.score) as avg_score,
                    MAX(qa.completed_at) as last_attempt
                  FROM users u
                  JOIN quiz_attempts qa ON u.id = qa.user_id
                  JOIN quizzes q ON qa.quiz_id = q.id
                  JOIN courses c ON q.course_id = c.id
                  WHERE u.role = 'student' 
                  AND c.teacher_id = ? 
                  AND qa.completed_at IS NOT NULL
                  GROUP BY u.id
                  ORDER BY u.username";

$stmt = $conn->prepare($students_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$students_result = $stmt->get_result();

// If a specific student is selected, fetch their attempts
$selected_student = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;

if ($selected_student) {
    $attempts_query = "SELECT qa.*, 
                             u.username as student_name,
                             q.title as quiz_title,
                             c.name as course_name,
                             q.difficulty,
                             (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                      FROM quiz_attempts qa
                      JOIN users u ON qa.user_id = u.id
                      JOIN quizzes q ON qa.quiz_id = q.id
                      JOIN courses c ON q.course_id = c.id
                      WHERE qa.user_id = ? 
                      AND c.teacher_id = ?
                      AND qa.completed_at IS NOT NULL
                      ORDER BY qa.completed_at DESC";
    $stmt = $conn->prepare($attempts_query);
    $stmt->bind_param("ii", $selected_student, $teacher_id);
    $stmt->execute();
    $attempts_result = $stmt->get_result();
    
    // Fetch student name
    $student_name_query = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($student_name_query);
    $stmt->bind_param("i", $selected_student);
    $stmt->execute();
    $student_name = $stmt->get_result()->fetch_assoc()['username'];
}
?>

<div class="reports-container">
    <?php if (!isset($_GET['student_id'])): ?>
        <div class="reports-header">
            <h2><i class="fas fa-chart-bar"></i> Student Performance Reports</h2>
            <p>View detailed quiz performance for each student</p>
        </div>

        <div class="students-grid">
            <?php if ($students_result->num_rows > 0): ?>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                    <div class="student-card">
                        <div class="student-info">
                            <div class="student-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($student['username']); ?></h3>
                        </div>
                        <div class="student-stats">
                            <div class="stat">
                                <span class="stat-label">Total Attempts</span>
                                <span class="stat-value"><?php echo $student['total_attempts']; ?></span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">Average Score</span>
                                <span class="stat-value <?php echo $student['avg_score'] >= 70 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($student['avg_score'], 1); ?>%
                                </span>
                            </div>
                        </div>
                        <div class="student-footer">
                            <span class="last-attempt">Last attempt: <?php echo date('M d, Y', strtotime($student['last_attempt'])); ?></span>
                            <button onclick="loadStudentDetails(<?php echo $student['id']; ?>)" class="view-details-btn">
                                View Details <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-info-circle"></i>
                    <p>No students have attempted any quizzes yet.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="reports-header">
            <div class="header-left">
                <button onclick="loadReports()" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Students
                </button>
                <h2>Quiz History for <?php echo htmlspecialchars($student_name); ?></h2>
            </div>
        </div>

        <?php if ($attempts_result->num_rows > 0): ?>
            <div class="attempts-table">
                <table>
                    <thead>
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
                                <td><?php echo htmlspecialchars($attempt['course_name']); ?></td>
                                <td><?php echo $attempt['question_count']; ?> Questions</td>
                                <td>
                                    <span class="badge <?php echo 'badge-' . strtolower($attempt['difficulty']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($attempt['difficulty'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="score-badge <?php echo $attempt['score'] >= 70 ? 'score-pass' : 'score-fail'; ?>">
                                        <?php echo number_format($attempt['score'], 1); ?>%
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y, g:i A', strtotime($attempt['completed_at'])); ?></td>
                                <td>
                                    <?php
                                    $start_time = strtotime($attempt['started_at']);
                                    $end_time = strtotime($attempt['completed_at']);
                                    $time_taken = $end_time - $start_time;
                                    
                                    if ($time_taken < 60) {
                                        echo $time_taken . " seconds";
                                    } else {
                                        $minutes = floor($time_taken / 60);
                                        $seconds = $time_taken % 60;
                                        echo $minutes . " min " . $seconds . " sec";
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-info-circle"></i>
                <p>No quiz attempts found for this student.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.reports-container {
    padding: 20px;
}

.reports-header {
    margin-bottom: 30px;
}

.reports-header h2 {
    color: #333;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.reports-header p {
    color: #666;
}

.students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.student-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.student-card:hover {
    transform: translateY(-5px);
}

.student-info {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.student-icon {
    width: 50px;
    height: 50px;
    background: #f0f0f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #666;
}

.student-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.stat {
    text-align: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-label {
    display: block;
    font-size: 0.9em;
    color: #666;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 1.2em;
    font-weight: bold;
    color: #333;
}

.text-success {
    color: #28a745;
}

.text-danger {
    color: #dc3545;
}

.student-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.last-attempt {
    font-size: 0.9em;
    color: #666;
}

.view-details-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    background: #007bff;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    transition: background 0.2s;
}

.view-details-btn:hover {
    background: #0056b3;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    background: #6c757d;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    margin-bottom: 20px;
}

.attempts-table {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    background: #f8f9fa;
    font-weight: 600;
}

.badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85em;
}

.badge-easy {
    background: #28a745;
    color: white;
}

.badge-medium {
    background: #ffc107;
    color: #000;
}

.badge-hard {
    background: #dc3545;
    color: white;
}

.score-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: 500;
}

.score-pass {
    background: #d4edda;
    color: #155724;
}

.score-fail {
    background: #f8d7da;
    color: #721c24;
}

.no-data {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.no-data i {
    font-size: 48px;
    color: #6c757d;
    margin-bottom: 15px;
}

.no-data p {
    color: #666;
    margin: 0;
}

.header-left {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
</style>

<script>
function loadStudentDetails(studentId) {
    $.ajax({
        url: 'reports.php',
        type: 'GET',
        data: { student_id: studentId },
        success: function(response) {
            $('#mainContent').html(response);
        },
        error: function() {
            alert('Error loading student details.');
        }
    });
}

function loadReports() {
    $.ajax({
        url: 'reports.php',
        type: 'GET',
        success: function(response) {
            $('#mainContent').html(response);
        },
        error: function() {
            alert('Error loading reports.');
        }
    });
}
</script>