<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Fetch user details
$user = $conn->query("SELECT * FROM users WHERE id = '$user_id'")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $update_sql = "UPDATE users SET username = '$username', email = '$email'";
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql .= ", password = '$hashed_password'";
        }
        $update_sql .= " WHERE id = '$user_id'";

        if ($conn->query($update_sql) === TRUE) {
            $success = "Profile updated successfully.";
            // Refresh user data
            $user = $conn->query("SELECT * FROM users WHERE id = '$user_id'")->fetch_assoc();
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    }
}
?>

<h2>Profile Management</h2>

<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<form action="" method="post">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
    </div>
    <div class="form-group">
        <label for="new_password">New Password (leave blank to keep current):</label>
        <input type="password" id="new_password" name="new_password">
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password">
    </div>
    <button type="submit" name="update_profile">Update Profile</button>
</form>

<?php require_once 'includes/footer.php'; ?>