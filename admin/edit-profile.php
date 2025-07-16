<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch admin data
$admin_result = $conn->query("SELECT username, email FROM users WHERE id = '$admin_id'");
$admin_data = $admin_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $conn->real_escape_string($_POST['username']);
    $new_email = $conn->real_escape_string($_POST['email']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        $update_sql = "UPDATE users SET username = '$new_username', email = '$new_email'";
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql .= ", password = '$hashed_password'";
        }

        $update_sql .= " WHERE id = '$admin_id'";

        if ($conn->query($update_sql)) {
            $_SESSION['success_message'] = "Profile updated successfully.";
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar" id="sidebar">
            <h2>Admin Dashboard</h2>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="manage-teachers.php"><i class="fas fa-chalkboard-teacher"></i> <span>Manage Teachers</span></a></li>
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
                        <span><?php echo htmlspecialchars($admin_data['username']); ?></span>
                        <i class="fas fa-caret-down"></i>
                        <div class="profile-dropdown-content">
                            <a href="edit-profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
                            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dashboard-content">
                <h2>Edit Profile</h2>
                <?php if (isset($error_message)): ?>
                    <p class="error"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <form action="" method="post" class="edit-form">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin_data['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">New Password (leave blank to keep current):</label>
                        <input type="password" id="password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn save-btn">Update Profile</button>
                        <a href="dashboard.php" class="btn cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>