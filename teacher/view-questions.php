<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';

$teacher_id = $_SESSION['user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch course details
$course = $conn->query("SELECT * FROM courses WHERE id = '$course_id' AND teacher_id = '$teacher_id'")->fetch_assoc();

if (!$course) {
    echo "Course not found or you don't have permission to view it.";
    exit();
}

// Fetch questions for this course
$questions = $conn->query("SELECT * FROM questions WHERE course_id = '$course_id' ORDER BY difficulty, id");

?>

<h2>Questions for: <?php echo $course['name']; ?></h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Question</th>
            <th>Type</th>
            <th>Difficulty</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($question = $questions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $question['id']; ?></td>
                <td><?php echo $question['question_text']; ?></td>
                <td><?php echo $question['question_type']; ?></td>
                <td><?php echo $question['difficulty']; ?></td>
                <td>
                    <a href="edit-question.php?id=<?php echo $question['id']; ?>">Edit</a>
                    <a href="delete-question.php?id=<?php echo $question['id']; ?>" onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<a href="manage-questions.php?course_id=<?php echo $course_id; ?>">Add New Question</a>
<a href="index.php">Back to Dashboard</a>

<?php require_once '../includes/footer.php'; ?>