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

// Check if teacher ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-teachers.php");
    exit();
}

$teacher_id = intval($_GET['id']);

// Fetch teacher data
$teacher_result = $conn->query("SELECT id, username, email, status FROM users WHERE id = $teacher_id AND role = 'teacher'");
$teacher = $teacher_result->fetch_assoc();

if (!$teacher) {
    header("Location: manage-teachers.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        // Delete the teacher
        $delete_sql = "DELETE FROM users WHERE id = $teacher_id AND role = 'teacher'";
        if ($conn->query($delete_sql)) {
            $_SESSION['success_message'] = "Teacher deleted successfully.";
            header("Location: manage-teachers.php");
            exit();
        } else {
            $error_message = "Error deleting teacher: " . $conn->error;
        }
    } else {
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $status = $conn->real_escape_string($_POST['status']);

        $update_sql = "UPDATE users SET username = '$username', email = '$email', status = '$status' WHERE id = $teacher_id";
        if ($conn->query($update_sql)) {
            $_SESSION['success_message'] = "Teacher updated successfully.";
            header("Location: manage-teachers.php");
            exit();
        } else {
            $error_message = "Error updating teacher: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher - Quiz Hub Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
    <link rel="stylesheet" href="../assets/css/manage-teachers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar" id="sidebar">
            <h2>Admin Dashboard</h2>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="manage-teachers.php" class="active"><i class="fas fa-chalkboard-teacher"></i> <span>Manage Teachers</span></a></li>
                <li><a href="manage-students.php"><i class="fas fa-user-graduate"></i> <span>Manage Students</span></a></li>
                <li><a href="manage-courses.php"><i class="fas fa-book"></i> <span>Manage Courses</span></a></li>
                <li><a href="manage-questions.php"><i class="fas fa-question-circle"></i> <span>Manage Questions</span></a></li>
            </ul>
        </div>
        <div class="main-content">
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
            <div class="dashboard-content">
                <h2>Edit Teacher</h2>
                <?php if (isset($error_message)): ?>
                    <p class="error"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <form action="" method="post" class="edit-form">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($teacher['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?php echo $teacher['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $teacher['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $teacher['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn save-btn">Update Teacher</button>
                        <button type="submit" name="delete" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this teacher?')">Delete Teacher</button>
                        <a href="manage-teachers.php" class="btn cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>