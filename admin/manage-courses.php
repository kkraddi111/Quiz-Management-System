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

// Fetch courses with teacher information
$courses_result = $conn->query("
    SELECT c.id, c.name, c.description, c.image, 
           GROUP_CONCAT(u.username SEPARATOR ', ') AS teacher_names
    FROM courses c 
    LEFT JOIN course_teachers ct ON c.id = ct.course_id
    LEFT JOIN users u ON ct.teacher_id = u.id 
    GROUP BY c.id
    ORDER BY c.name
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Quiz Hub Admin</title>
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
                <li><a href="manage-history.php"><i class="fas fa-history"></i> <span>Quiz History</span></a></li>
                <li><a href="manage-feedback.php"><i class="fas fa-comments"></i> <span>Feedback</span></a></li>
            </ul>
        </div>
        <div class="main-content" id="mainContent">
            <h2>Manage Courses</h2>
            <a href="add-course.php" class="btn add-btn">Add New Course</a>
            <div class="course-list">
                <table>
                    <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Assigned Teacher</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($course = $courses_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['name']); ?></td>
                                <td><?php echo htmlspecialchars($course['description']); ?></td>
                                <td>
                                    <?php if (!empty($course['image'])): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($course['image']); ?>" alt="Course Image" style="max-width: 100px; max-height: 100px;">
                                    <?php else: ?>
                                        No image
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($course['teacher_names'] ?? 'Not assigned'); ?></td>
                                <td class="action-buttons">
                                    <a href="edit-course.php?id=<?php echo $course['id']; ?>" class="btn edit-btn">Edit</a>
                                    <a href="delete-course.php?id=<?php echo $course['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="editProfileModal" class="edit-profile-modal">
        <!-- Add edit profile modal content here -->
    </div>

    <script src="../assets/js/admin-script.js"></script>
</body>
</html>