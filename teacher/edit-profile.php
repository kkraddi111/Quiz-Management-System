<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $conn->real_escape_string($_POST['username']);
    $new_email = $conn->real_escape_string($_POST['email']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit();
    }

    $update_sql = "UPDATE users SET username = '$new_username', email = '$new_email'";
    
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql .= ", password = '$hashed_password'";
    }

    $update_sql .= " WHERE id = '$teacher_id'";

    if ($conn->query($update_sql)) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.', 'username' => $new_username]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $conn->error]);
    }
    exit();
}

// Fetch teacher data
$teacher_result = $conn->query("SELECT username, email FROM users WHERE id = '$teacher_id'");
$teacher_data = $teacher_result->fetch_assoc();
?>

<style>
    .edit-profile-form {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 400px;
        margin: 0 auto;
    }
    .edit-profile-form h2 {
        color: #e8491d;
        margin-bottom: 20px;
        text-align: center;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: bold;
    }
    .form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    .form-group button {
        background-color: #e8491d;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
    }
    .form-group button:hover {
        background-color: #c73e1d;
    }
</style>

<div class="edit-profile-form">
    <h2>Edit Profile</h2>
    <form id="editProfileForm">
        <div class="form-group">
            <label for="editUsername">Username:</label>
            <input type="text" id="editUsername" name="username" value="<?php echo htmlspecialchars($teacher_data['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="editEmail">Email:</label>
            <input type="email" id="editEmail" name="email" value="<?php echo htmlspecialchars($teacher_data['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="editPassword">New Password (leave blank to keep current):</label>
            <input type="password" id="editPassword" name="password">
        </div>
        <div class="form-group">
            <label for="editConfirmPassword">Confirm New Password:</label>
            <input type="password" id="editConfirmPassword" name="confirm_password">
        </div>
        <div class="form-group">
            <button type="submit">Save Changes</button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $('#editProfileForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'edit-profile.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#userName, .username-display').text(response.username);
                    closeEditProfileModal();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('An error occurred while updating the profile.');
            }
        });
    });
});
</script>