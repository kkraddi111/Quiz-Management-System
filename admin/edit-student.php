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

// Check if student ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-students.php");
    exit();
}

$student_id = intval($_GET['id']);

// Fetch student data
$student_result = $conn->query("SELECT id, username, email FROM users WHERE id = $student_id AND role = 'student'");
$student = $student_result->fetch_assoc();

if (!$student) {
    header("Location: manage-students.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $conn->real_escape_string($_POST['username']);
    $new_email = $conn->real_escape_string($_POST['email']);
    $new_password = $_POST['password'];

    $update_sql = "UPDATE users SET username = '$new_username', email = '$new_email'";
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql .= ", password = '$hashed_password'";
    }
    $update_sql .= " WHERE id = $student_id";

    if ($conn->query($update_sql)) {
        $_SESSION['success_message'] = "Student updated successfully.";
        header("Location: manage-students.php");
        exit();
    } else {
        $error_message = "Error updating student: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Quiz Hub Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
    <link rel="stylesheet" href="../assets/css/manage-students.css">
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
                    <div class="profile-dropdown-content">
                        <div class="user-name">
                            <i class="fas fa-user-shield"></i>
                            <span id="userName"><?php echo htmlspecialchars($admin_name); ?></span>
                        </div>
                        <a href="#" onclick="editProfile()">
                            <i class="fas fa-user-edit edit-profile-icon"></i>
                            Edit Profile
                        </a>
                    </div>
                </div>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        <div class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="manage-teachers.php"><i class="fas fa-chalkboard-teacher"></i> <span>Manage Teachers</span></a></li>
                <li><a href="manage-students.php" class="active"><i class="fas fa-user-graduate"></i> <span>Manage Students</span></a></li>
                <li><a href="manage-courses.php"><i class="fas fa-book"></i> <span>Manage Courses</span></a></li>
                <li><a href="manage-questions.php"><i class="fas fa-question-circle"></i> <span>Manage Questions</span></a></li>
            </ul>
        </div>
        <div class="main-content" id="mainContent">
            <h2>Edit Student</h2>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form action="" method="post" class="edit-form">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($student['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current):</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn save-btn">Save Changes</button>
                    <a href="manage-students.php" class="btn cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div id="editProfileModal" class="edit-profile-modal">
        <!-- Add edit profile modal content here -->
    </div>

    <script src="../assets/js/admin-script.js"></script>
</body>
</html>