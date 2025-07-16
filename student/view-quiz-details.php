<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

// Fetch attempt details
$attempt_query = "SELECT qa.*, q.title AS quiz_title, 
                        c.name AS course_name, q.difficulty,
                        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                 FROM quiz_attempts qa
                 JOIN quizzes q ON qa.quiz_id = q.id
                 JOIN courses c ON q.course_id = c.id
                 WHERE qa.id = ? AND qa.user_id = ?";
$stmt = $conn->prepare($attempt_query);
$stmt->bind_param("ii", $attempt_id, $student_id);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();

if (!$attempt) {
    header("Location: quiz-history.php");
    exit();
}

// Fetch questions with answers and explanations
$questions_query = "SELECT q.*, ua.user_answer, ua.is_correct, 
                          o.option_text as selected_option,
                          co.option_text as correct_option
                   FROM questions q
                   JOIN quiz_questions qq ON q.id = qq.question_id
                   JOIN user_answers ua ON q.id = ua.question_id
                   LEFT JOIN options o ON ua.user_answer = o.id
                   LEFT JOIN options co ON co.question_id = q.id AND co.is_correct = 1
                   WHERE ua.attempt_id = ?";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $attempt_id);
$stmt->execute();
$questions = $stmt->get_result();

$page_title = "Quiz Details";
require_once '../includes/student_header.php';
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Quiz Details</h2>
            <a href="quiz-history.php" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Back to History
            </a>
        </div>
        <div class="card-body">
            <div class="quiz-summary mb-4">
                <h3><?php echo htmlspecialchars($attempt['quiz_title']); ?></h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($attempt['course_name']); ?></p>
                        <p><strong>Level:</strong> <?php echo ucfirst($attempt['difficulty']); ?></p>
                        <p><strong>Questions:</strong> <?php echo $attempt['question_count']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Score:</strong> 
                            <span class="badge <?php echo $attempt['score'] >= 70 ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo number_format($attempt['score'], 1); ?>%
                            </span>
                        </p>
                        <p><strong>Completed:</strong> <?php echo date('F j, Y, g:i A', strtotime($attempt['completed_at'])); ?></p>
                    </div>
                </div>
            </div>

            <h4>Questions Review</h4>
            <?php while ($question = $questions->fetch_assoc()): ?>
                <div class="question-card mb-3 <?php echo $question['is_correct'] ? 'border-success' : 'border-danger'; ?>">
                    <div class="question-header">
                        <span class="badge <?php echo $question['is_correct'] ? 'bg-success' : 'bg-danger'; ?> float-end">
                            <?php echo $question['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                        </span>
                        <h5><?php echo htmlspecialchars($question['question_text']); ?></h5>
                    </div>
                    <div class="question-body">
                        <p>
                            <strong>Your Answer:</strong> 
                            <span class="<?php echo $question['is_correct'] ? 'text-success' : 'text-danger'; ?>">
                                <?php echo htmlspecialchars($question['selected_option'] ?? $question['user_answer']); ?>
                            </span>
                        </p>
                        <?php if (!$question['is_correct']): ?>
                            <p>
                                <strong>Correct Answer:</strong> 
                                <span class="text-success">
                                    <?php echo htmlspecialchars($question['correct_option'] ?? $question['correct_answer']); ?>
                                </span>
                            </p>
                            <?php if (!empty($question['explanation'])): ?>
                                <div class="explanation">
                                    <h6><i class="fas fa-lightbulb"></i> Explanation:</h6>
                                    <p><?php echo nl2br(htmlspecialchars($question['explanation'])); ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<style>
.question-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid;
}

.question-header {
    margin-bottom: 15px;
}

.question-body {
    background: white;
    padding: 15px;
    border-radius: 4px;
}

.explanation {
    background: #fff3cd;
    padding: 15px;
    border-radius: 4px;
    margin-top: 15px;
}

.explanation h6 {
    color: #856404;
    margin-bottom: 10px;
}

.explanation i {
    margin-right: 5px;
}
</style>

<?php require_once '../includes/footer.php'; ?> 