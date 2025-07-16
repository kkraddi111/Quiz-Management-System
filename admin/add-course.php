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

// Fetch teachers for dropdown
$teachers_query = "SELECT id, username FROM users WHERE role = 'teacher' AND status = 'approved'";
$teachers_result = $conn->query($teachers_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Quiz Hub Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
    <link rel="stylesheet" href="../assets/css/manage-courses.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <div class="top-bar" id="topBar">
            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>
            <div class="quiz-hub-header">
                <i class="fas fa-graduation-cap"></i> <span class="highlight">Quiz</span> Hub
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
                <li><a href="manage-courses.php" class="active"><i class="fas fa-book"></i> <span>Manage Courses</span></a></li>
                <li><a href="manage-questions.php"><i class="fas fa-question-circle"></i> <span>Manage Questions</span></a></li>
            </ul>
        </div>
        <div class="main-content" id="mainContent">
            <h2>Add New Course</h2>
            <form method="POST" action="process-add-course.php" enctype="multipart/form-data" class="course-form">
                <div class="form-group">
                    <label for="course_name">Course Name:</label>
                    <input type="text" id="course_name" name="course_name" required>
                </div>
                <div class="form-group">
                    <label for="course_description">Description:</label>
                    <textarea id="course_description" name="course_description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="teacher_ids">Assign Teachers:</label>
                    <select name="teacher_ids[]" multiple required>
                        <?php while ($teacher = $teachers_result->fetch_assoc()): ?>
                            <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['username']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="course_image">Course Image:</label>
                    <input type="file" name="course_image" accept="image/*">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn save-btn">Add Course</button>
                    <a href="manage-courses.php" class="btn cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>