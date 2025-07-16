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

// Fetch teachers
$teachers_result = $conn->query("SELECT id, username, email, status FROM users WHERE role = 'teacher' ORDER BY status, username");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - Quiz Hub Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
    <link rel="stylesheet" href="../assets/css/manage-teachers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <div class="top-bar" id="topBar">
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
                <li><a href="manage-teachers.php" class="active"><i class="fas fa-chalkboard-teacher"></i> <span>Manage Teachers</span></a></li>
                <li><a href="manage-students.php"><i class="fas fa-user-graduate"></i> <span>Manage Students</span></a></li>
                <li><a href="manage-courses.php"><i class="fas fa-book"></i> <span>Manage Courses</span></a></li>
                <li><a href="manage-questions.php"><i class="fas fa-question-circle"></i> <span>Manage Questions</span></a></li>
                <li><a href="manage-history.php"><i class="fas fa-history"></i> <span>Quiz History</span></a></li>
                <li><a href="manage-feedback.php"><i class="fas fa-comments"></i> <span>Feedback</span></a></li>
            </ul>
        </div>
        <div class="main-content" id="mainContent">
            <h2>Manage Teachers</h2>
            <?php
            if (isset($_SESSION['success_message'])) {
                echo "<p class='success'>" . $_SESSION['success_message'] . "</p>";
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo "<p class='error'>" . $_SESSION['error_message'] . "</p>";
                unset($_SESSION['error_message']);
            }
            ?>
            <div class="teacher-list">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($teacher = $teachers_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                <td class="status-<?php echo $teacher['status']; ?>"><?php echo ucfirst($teacher['status']); ?></td>
                                <td class="action-buttons">
                                    <?php if ($teacher['status'] == 'pending'): ?>
                                        <a href="approve-teacher.php?id=<?php echo $teacher['id']; ?>" class="btn approve-btn">Approve</a>
                                        <a href="reject-teacher.php?id=<?php echo $teacher['id']; ?>" class="btn reject-btn">Reject</a>
                                    <?php endif; ?>
                                    <a href="edit-teacher.php?id=<?php echo $teacher['id']; ?>" class="btn edit-btn">Edit</a>
                                    <a href="delete-teacher.php?id=<?php echo $teacher['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this teacher?')">Delete</a>
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