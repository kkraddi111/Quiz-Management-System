<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    exit('Unauthorized access');
}

$teacher_id = $_SESSION['user_id'];
$difficulty_filter = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';

// Fetch questions created by this teacher with filter
$questions_query = "SELECT q.*, c.name AS course_name 
                    FROM questions q 
                    JOIN courses c ON q.course_id = c.id 
                    WHERE c.teacher_id = ?";

if ($difficulty_filter) {
    $questions_query .= " AND q.difficulty = ?";
}

$questions_query .= " ORDER BY q.id DESC";

$stmt = $conn->prepare($questions_query);

if ($difficulty_filter) {
    $stmt->bind_param("is", $teacher_id, $difficulty_filter);
} else {
    $stmt->bind_param("i", $teacher_id);
}

$stmt->execute();
$questions_result = $stmt->get_result();

if ($questions_result->num_rows > 0):
?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($question = $questions_result->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars(substr($question['question_text'], 0, 50)) . '...'; ?></h5>
                        <p class="card-text">
                            <strong>Course:</strong> <?php echo htmlspecialchars($question['course_name']); ?><br>
                            <strong>Type:</strong> <?php echo htmlspecialchars($question['question_type']); ?><br>
                            <strong>Difficulty:</strong> <?php echo ucfirst(htmlspecialchars($question['difficulty'])); ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-sm btn-primary btn-edit" data-id="<?php echo $question['id']; ?>">Edit</a>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $question['id']; ?>">Delete</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        No questions found for this difficulty level. <a href="add_question.php" class="alert-link">Add a new question</a> to get started.
    </div>
<?php endif; ?>
