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

// Check if question ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-questions.php");
    exit();
}

$question_id = $_GET['id'];

// Fetch question details
$question_query = "SELECT q.*, c.name AS course_name FROM questions q 
                   JOIN courses c ON q.course_id = c.id 
                   WHERE q.id = ?";
$stmt = $conn->prepare($question_query);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$question_result = $stmt->get_result();
$question = $question_result->fetch_assoc();

if (!$question) {
    header("Location: manage-questions.php");
    exit();
}

// Fetch options for multiple-choice questions
$options = [];
if ($question['question_type'] === 'multiple_choice') {
    $options_query = "SELECT * FROM options WHERE question_id = ? ORDER BY is_correct DESC";
    $stmt = $conn->prepare($options_query);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $options_result = $stmt->get_result();
    while ($option = $options_result->fetch_assoc()) {
        $options[] = $option;
    }
}

// Fetch courses for dropdown
$courses_query = "SELECT id, name FROM courses";
$courses_result = $conn->query($courses_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type'];
    $difficulty = $_POST['difficulty'];
    $explanation = $_POST['explanation'];

    // Update question
    $update_query = "UPDATE questions SET course_id = ?, question_text = ?, question_type = ?, difficulty = ?, explanation = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("issssi", $course_id, $question_text, $question_type, $difficulty, $explanation, $question_id);
    $stmt->execute();

    // Handle options
    if ($question_type === 'multiple_choice') {
        // Delete existing options
        $delete_options_query = "DELETE FROM options WHERE question_id = ?";
        $stmt = $conn->prepare($delete_options_query);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();

        // Insert new options
        $options = $_POST['options'];
        $correct_option = $_POST['correct_option'];
        $option_sql = "INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
        $option_stmt = $conn->prepare($option_sql);
        
        foreach ($options as $index => $option) {
            $is_correct = ($index == $correct_option) ? 1 : 0;
            $option_stmt->bind_param("isi", $question_id, $option, $is_correct);
            $option_stmt->execute();
        }
    } elseif ($question_type === 'true_false') {
        // Update correct answer for true/false questions
        $correct_answer = $_POST['correct_answer'];
        $update_tf_query = "UPDATE options SET is_correct = CASE WHEN option_text = ? THEN 1 ELSE 0 END WHERE question_id = ?";
        $stmt = $conn->prepare($update_tf_query);
        $stmt->bind_param("si", $correct_answer, $question_id);
        $stmt->execute();
    }

    header("Location: manage-questions.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question - Quiz Hub Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .edit-question-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .edit-question-form label {
            display: block;
            margin-top: 10px;
        }
        .edit-question-form input[type="text"],
        .edit-question-form select,
        .edit-question-form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .edit-question-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .edit-question-form input[type="submit"]:hover {
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
            </ul>
        </div>
        <div class="main-content" id="mainContent">
            <h2>Edit Question</h2>
            <form method="POST" action="" class="edit-question-form">
                <label for="course_id">Course:</label>
                <select name="course_id" required>
                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo ($course['id'] == $question['course_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="question_text">Question:</label>
                <textarea name="question_text" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>

                <label for="question_type">Question Type:</label>
                <select name="question_type" id="question_type" required>
                    <option value="multiple_choice" <?php echo ($question['question_type'] == 'multiple_choice') ? 'selected' : ''; ?>>Multiple Choice</option>
                    <option value="true_false" <?php echo ($question['question_type'] == 'true_false') ? 'selected' : ''; ?>>True/False</option>
                </select>

                <label for="difficulty">Difficulty:</label>
                <select name="difficulty" required>
                    <option value="easy" <?php echo ($question['difficulty'] == 'easy') ? 'selected' : ''; ?>>Easy</option>
                    <option value="medium" <?php echo ($question['difficulty'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="hard" <?php echo ($question['difficulty'] == 'hard') ? 'selected' : ''; ?>>Hard</option>
                </select>

                <div id="multiple_choice_options" <?php echo ($question['question_type'] != 'multiple_choice') ? 'style="display:none;"' : ''; ?>>
                    <?php foreach ($options as $index => $option): ?>
                        <div class="option-group">
                            <label for="option<?php echo $index + 1; ?>">Option <?php echo $index + 1; ?>:</label>
                            <input type="text" name="options[]" value="<?php echo htmlspecialchars($option['option_text']); ?>" required>
                            <label class="radio-label">
                                <input type="radio" name="correct_option" value="<?php echo $index; ?>" <?php echo ($option['is_correct'] == 1) ? 'checked' : ''; ?>> Correct Answer
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="true_false_options" <?php echo ($question['question_type'] != 'true_false') ? 'style="display:none;"' : ''; ?>>
                    <label for="correct_answer">Correct Answer:</label>
                    <select name="correct_answer">
                        <option value="true" <?php echo ($question['correct_answer'] == 'true') ? 'selected' : ''; ?>>True</option>
                        <option value="false" <?php echo ($question['correct_answer'] == 'false') ? 'selected' : ''; ?>>False</option>
                    </select>
                </div>

                <label for="explanation">Explanation:</label>
                <textarea name="explanation" required><?php echo htmlspecialchars($question['explanation']); ?></textarea>

                <input type="submit" value="Update Question">
            </form>
            <a href="manage-questions.php" class="btn back-btn"><i class="fas fa-arrow-left"></i> Back to Manage Questions</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    document.getElementById('question_type').addEventListener('change', function() {
        var multipleChoiceOptions = document.getElementById('multiple_choice_options');
        var trueFalseOptions = document.getElementById('true_false_options');
        
        if (this.value === 'multiple_choice') {
            multipleChoiceOptions.style.display = 'block';
            trueFalseOptions.style.display = 'none';
        } else {
            multipleChoiceOptions.style.display = 'none';
            trueFalseOptions.style.display = 'block';
        }
    });
    </script>
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>