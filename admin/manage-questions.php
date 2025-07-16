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

// Fetch questions
$questions_result = $conn->query("SELECT q.id, q.question_text, c.name AS course_name, q.difficulty FROM questions q JOIN courses c ON q.course_id = c.id ORDER BY c.name, q.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - Quiz Hub Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
    <link rel="stylesheet" href="../assets/css/manage-questions.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        #addQuestionFormContainer {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        #addQuestionForm label {
            display: block;
            margin-top: 10px;
        }
        #addQuestionForm input[type="text"],
        #addQuestionForm select,
        #addQuestionForm textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #addQuestionForm input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        #addQuestionForm input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
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
                <li><a href="manage-teachers.php"><i class="fas fa-chalkboard-teacher"></i> <span>Manage Teachers</span></a></li>
                <li><a href="manage-students.php"><i class="fas fa-user-graduate"></i> <span>Manage Students</span></a></li>
                <li><a href="manage-courses.php"><i class="fas fa-book"></i> <span>Manage Courses</span></a></li>
                <li><a href="manage-questions.php" class="active"><i class="fas fa-question-circle"></i> <span>Manage Questions</span></a></li>
                <li><a href="manage-history.php"><i class="fas fa-history"></i> <span>Quiz History</span></a></li>
                <li><a href="manage-feedback.php"><i class="fas fa-comments"></i> <span>Feedback</span></a></li>
            </ul>
        </div>
        <div class="main-content" id="mainContent">
            <h2>Manage Questions</h2>
            
            <!-- Add New Question Button -->
            <button id="showAddQuestionForm" class="btn add-btn">Add New Question</button>
            
            <!-- Add New Question Form Container -->
            <div id="addQuestionFormContainer" style="display: none;"></div>

            <!-- Existing Questions List -->
            <div class="question-list">
                <table>
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Course</th>
                            <th>Difficulty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($question = $questions_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                <td><?php echo htmlspecialchars($question['course_name']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($question['difficulty'])); ?></td>
                                <td class="action-buttons">
                                    <a href="edit-question.php?id=<?php echo $question['id']; ?>" class="btn edit-btn">Edit</a>
                                    <a href="delete-question.php?id=<?php echo $question['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#showAddQuestionForm').click(function() {
            var container = $('#addQuestionFormContainer');
            if (container.is(':empty')) {
                container.load('add-question.php', function() {
                    container.show();
                    $('#showAddQuestionForm').hide(); // Hide the button after loading the form
                });
            } else {
                container.toggle();
                $('#showAddQuestionForm').toggle();
            }
        });

        // Handle form submission
        $(document).on('submit', '#addQuestionForm', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'process-add-question.php',
                data: $(this).serialize(),
                success: function(response) {
                    alert(response); // You can replace this with a more user-friendly notification
                    location.reload(); // Reload the page to show the new question
                }
            });
        });
    });
    </script>

    <script src="../assets/js/admin-script.js"></script>
</body>
</html>