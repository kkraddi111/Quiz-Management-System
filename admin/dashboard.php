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

// Fetch various statistics
$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];
$total_teachers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetch_row()[0];
$total_courses = $conn->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
$total_questions = $conn->query("SELECT COUNT(*) FROM questions")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QuizHub</title>
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="manage-teachers.php"><i class="fas fa-chalkboard-teacher"></i> <span>Manage Teachers</span></a></li>
                <li><a href="manage-students.php"><i class="fas fa-user-graduate"></i> <span>Manage Students</span></a></li>
                <li><a href="manage-courses.php"><i class="fas fa-book"></i> <span>Manage Courses</span></a></li>
                <li><a href="manage-questions.php"><i class="fas fa-question-circle"></i> <span>Manage Questions</span></a></li>
                <li><a href="manage-history.php"><i class="fas fa-history"></i> <span>Quiz History</span></a></li>
                <li><a href="manage-feedback.php"><i class="fas fa-comments"></i> <span>Feedback</span></a></li>
            </ul>
        </div>
        <div class="main-content" id="mainContent">
            <h2>Dashboard Overview</h2>
            <div class="overview-stats">
                <div class="stat-card">
                    <a href="manage-students.php">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Total Students</h3>
                    <p><?php echo $total_students; ?></p></a>
                </div>
                <div class="stat-card">
                    <a href="manage-teachers.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3>Total Teachers</h3>
                    <p><?php echo $total_teachers; ?></p></a>
                </div>
                <div class="stat-card">
                    <a href="manage-courses.php">
                    <i class="fas fa-book"></i>
                    <h3>Total Courses</h3>
                    <p><?php echo $total_courses; ?></p></a>
                </div>
                <div class="stat-card">
                    <a href="manage-questions.php">
                    <i class="fas fa-question-circle"></i>
                    <h3>Total Questions</h3>
                    <p><?php echo $total_questions; ?></p></a>
                </div>
            </div>
        </div>
    </div>

    <div id="editProfileModal" class="edit-profile-modal">
        <!-- Add edit profile modal content here -->
    </div>

    <script src="../assets/js/admin-script.js"></script>
</body>
</html>