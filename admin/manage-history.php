<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch admin name
$admin_id = $_SESSION['user_id'];
$admin_result = $conn->query("SELECT username FROM users WHERE id = '$admin_id'");
$admin_name = $admin_result->fetch_assoc()['username'];

// Handle deletion
if (isset($_POST['delete_attempt'])) {
    $attempt_id = intval($_POST['attempt_id']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete user answers
        $delete_answers = "DELETE FROM user_answers WHERE attempt_id = ?";
        $stmt = $conn->prepare($delete_answers);
        $stmt->bind_param("i", $attempt_id);
        $stmt->execute();
        
        // Delete quiz attempt
        $delete_attempt = "DELETE FROM quiz_attempts WHERE id = ?";
        $stmt = $conn->prepare($delete_attempt);
        $stmt->bind_param("i", $attempt_id);
        $stmt->execute();
        
        $conn->commit();
        $success_message = "Quiz attempt deleted successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error deleting quiz attempt.";
    }
}

// Fetch students with quiz attempts
$students_query = "SELECT DISTINCT 
                    u.id,
                    u.username,
                    COUNT(qa.id) as total_attempts,
                    AVG(qa.score) as avg_score,
                    MAX(qa.completed_at) as last_attempt
                  FROM users u
                  JOIN quiz_attempts qa ON u.id = qa.user_id
                  WHERE u.role = 'student' AND qa.completed_at IS NOT NULL
                  GROUP BY u.id
                  ORDER BY u.username";
$students_result = $conn->query($students_query);

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
                      WHERE qa.user_id = ? AND qa.completed_at IS NOT NULL
                      ORDER BY qa.completed_at DESC";
    $stmt = $conn->prepare($attempts_query);
    $stmt->bind_param("i", $selected_student);
    $stmt->execute();
    $attempts_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz History - QuizHub Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
</head>
<body>
    <div class="dashboard">
        <div class="top-bar">
            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>
            <div class="quiz-hub-header">
                <i class="fas fa-graduation-cap"></i> Quiz<span class="highlight">Hub</span>
            </div>
            <div class="user-actions">
                <div class="profile-dropdown">
                    <div class="profile-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span><?php echo htmlspecialchars($admin_name); ?></span>
                    <i class="fas fa-caret-down"></i>
                    <div class="profile-dropdown-content">
                        <a href="edit-profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="manage-teachers.php"><i class="fas fa-chalkboard-teacher"></i> <span>Manage Teachers</span></a></li>
                <li><a href="manage-students.php"><i class="fas fa-user-graduate"></i> <span>Manage Students</span></a></li>
                <li><a href="manage-courses.php"><i class="fas fa-book"></i> <span>Manage Courses</span></a></li>
                <li><a href="manage-questions.php"><i class="fas fa-question-circle"></i> <span>Manage Questions</span></a></li>
                <li><a href="manage-history.php" class="active"><i class="fas fa-history"></i> <span>Quiz History</span></a></li>
                <li><a href="manage-feedback.php"><i class="fas fa-comments"></i> <span>Feedback</span></a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h2>Quiz History</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!$selected_student): ?>
                <!-- Show list of students -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Total Attempts</th>
                                <th>Average Score</th>
                                <th>Last Attempt</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $students_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                                    <td><?php echo $student['total_attempts']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $student['avg_score'] >= 70 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo number_format($student['avg_score'], 1); ?>%
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y, g:i A', strtotime($student['last_attempt'])); ?></td>
                                    <td>
                                        <a href="?student_id=<?php echo $student['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View History
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Show selected student's quiz attempts -->
                <div class="mb-3">
                    <a href="manage-history.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Students List
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Quiz</th>
                                <th>Course</th>
                                <th>Questions</th>
                                <th>Level</th>
                                <th>Score</th>
                                <th>Completion Date</th>
                                <th>Time Taken</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($attempt = $attempts_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                    <td><?php echo htmlspecialchars($attempt['course_name']); ?></td>
                                    <td><?php echo $attempt['question_count']; ?> Questions</td>
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
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this attempt?');">
                                            <input type="hidden" name="attempt_id" value="<?php echo $attempt['id']; ?>">
                                            <button type="submit" name="delete_attempt" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 500;
        color: white;
    }

    .bg-success {
        background-color: #28a745;
    }

    .bg-danger {
        background-color: #dc3545;
    }

    .bg-info {
        background-color: #FF8C00;
    }

    .btn {
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        border: none;
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-primary {
        background-color: #007bff;
    }

    .btn-secondary {
        background-color: #6c757d;
    }

    .btn-danger {
        background-color: #dc3545;
    }

    .btn:hover {
        opacity: 0.9;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>

    <script src="../assets/js/admin-script.js"></script>
</body>
</html> 