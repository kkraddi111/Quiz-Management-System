<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle feedback deletion
if (isset($_POST['delete_feedback'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $delete_query = "DELETE FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $feedback_id);
    
    if ($stmt->execute()) {
        $success_message = "Feedback deleted successfully.";
    } else {
        $error_message = "Error deleting feedback.";
    }
}

// Fetch all feedback messages
$feedback_query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$feedback_result = $conn->query($feedback_query);

// Fetch admin name
$admin_id = $_SESSION['user_id'];
$admin_result = $conn->query("SELECT username FROM users WHERE id = '$admin_id'");
$admin_name = $admin_result->fetch_assoc()['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - QuizHub Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/top-bar.php'; ?>
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <h2><i class="fas fa-comments"></i> Manage Feedback</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if ($feedback_result->num_rows > 0): ?>
                <div class="feedback-grid">
                    <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                        <div class="feedback-card">
                            <div class="feedback-header">
                                <div class="user-info">
                                    <h3><?php echo htmlspecialchars($feedback['name']); ?></h3>
                                    <span class="email"><?php echo htmlspecialchars($feedback['email']); ?></span>
                                </div>
                                <span class="date">
                                    <?php echo date('M d, Y, g:i A', strtotime($feedback['created_at'])); ?>
                                </span>
                            </div>
                            
                            <div class="feedback-content">
                                <?php if (isset($feedback['subject']) && !empty($feedback['subject'])): ?>
                                    <h4><?php echo htmlspecialchars($feedback['subject']); ?></h4>
                                <?php endif; ?>
                                <p><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                            </div>
                            
                            <div class="feedback-actions">
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                    <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                    <button type="submit" name="delete_feedback" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-feedback">
                    <i class="fas fa-inbox"></i>
                    <p>No feedback messages found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .feedback-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .feedback-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
    }

    .feedback-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .user-info h3 {
        margin: 0;
        color: #333;
        font-size: 1.1rem;
    }

    .email {
        color: #666;
        font-size: 0.9rem;
    }

    .date {
        color: #888;
        font-size: 0.8rem;
    }

    .feedback-content h4 {
        color: #444;
        margin-bottom: 10px;
    }

    .feedback-content p {
        color: #666;
        line-height: 1.5;
    }

    .feedback-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .no-feedback {
        text-align: center;
        padding: 40px;
        color: #666;
    }

    .no-feedback i {
        font-size: 48px;
        margin-bottom: 15px;
        color: #ddd;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>

    <script src="../assets/js/admin-script.js"></script>
</body>
</html> 