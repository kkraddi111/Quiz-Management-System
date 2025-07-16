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

// Fetch attempt details with quiz and course information
$attempt_query = "SELECT qa.*, q.title as quiz_title, q.course_id, c.name as course_name,
                        q.duration, COUNT(qq.question_id) as total_questions
                 FROM quiz_attempts qa
                 JOIN quizzes q ON qa.quiz_id = q.id
                 JOIN courses c ON q.course_id = c.id
                 LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id
                 WHERE qa.id = ? AND qa.user_id = ?
                 GROUP BY qa.id";

$stmt = $conn->prepare($attempt_query);
$stmt->bind_param("ii", $attempt_id, $student_id);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();

if (!$attempt) {
    header("Location: index.php");
    exit();
}

// Fetch questions and answers with explanations
$questions_query = "SELECT 
                    q.*, 
                    ua.user_answer,
                    ua.is_correct,
                    o.option_text as selected_option_text,
                    co.option_text as correct_option_text,
                    q.explanation
                   FROM questions q
                   JOIN quiz_questions qq ON q.id = qq.question_id
                   JOIN user_answers ua ON q.id = ua.question_id
                   LEFT JOIN options o ON ua.user_answer = o.id
                   LEFT JOIN options co ON co.question_id = q.id AND co.is_correct = 1
                   WHERE ua.attempt_id = ?
                   ORDER BY qq.id";

$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $attempt_id);
$stmt->execute();
$questions = $stmt->get_result();

$page_title = "Quiz Results";
require_once '../includes/student_header.php';
?>

<!-- First show the quiz summary -->
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Quiz Results - <?php echo htmlspecialchars($attempt['course_name']); ?></h2>
        </div>
        <div class="card-body">
            <!-- Quiz summary section -->
            <div class="row mb-4">
                <div class="col-md-4 text-center">
                    <div class="score-circle <?php echo $attempt['score'] >= 70 ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo number_format($attempt['score'], 1); ?>%
                    </div>
                    <h4 class="mt-2"><?php echo $attempt['score'] >= 70 ? 'Passed!' : 'Need Improvement'; ?></h4>
                </div>
                <div class="col-md-8">
                    <div class="quiz-details">
                        <p><strong>Quiz:</strong> <?php echo htmlspecialchars($attempt['quiz_title']); ?></p>
                        <p><strong>Time Taken:</strong> 
                            <?php
                            $start_time = strtotime($attempt['started_at']);
                            $end_time = strtotime($attempt['completed_at']);
                            $time_taken = $end_time - $start_time;
                            
                            $minutes = floor($time_taken / 60);
                            $seconds = $time_taken % 60;
                            echo $minutes . " minutes " . $seconds . " seconds";
                            ?>
                        </p>
                        <p><strong>Completed:</strong> <?php echo date('F j, Y, g:i a', strtotime($attempt['completed_at'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Explanations for wrong answers -->
            <div class="wrong-answers mt-4">
                <h3 class="text-danger mb-3">Explanations for Wrong Answers</h3>
                <?php 
                $questions->data_seek(0);
                $found_wrong = false;
                while ($question = $questions->fetch_assoc()):
                    if (!$question['is_correct']):
                        $found_wrong = true;
                ?>
                    <div class="wrong-answer-card mb-4">
                        <div class="question-text">
                            <h4>Question: <?php echo htmlspecialchars($question['question_text']); ?></h4>
                        </div>
                        <div class="answers mt-2">
                            <p class="text-danger">
                                <strong>Your Answer:</strong> 
                                <?php 
                                if ($question['question_type'] === 'multiple_choice') {
                                    echo htmlspecialchars($question['selected_option_text'] ?? 'No answer provided');
                                } else {
                                    echo htmlspecialchars($question['user_answer'] ?? 'No answer provided');
                                }
                                ?>
                            </p>
                            <p class="text-success">
                                <strong>Correct Answer:</strong> 
                                <?php 
                                if ($question['question_type'] === 'multiple_choice') {
                                    echo htmlspecialchars($question['correct_option_text']);
                                } else {
                                    echo htmlspecialchars($question['correct_answer']);
                                }
                                ?>
                            </p>
                        </div>
                        <?php if (!empty($question['explanation'])): ?>
                            <div class="explanation mt-3">
                                <h5><i class="fas fa-lightbulb text-warning"></i> Explanation:</h5>
                                <p><?php echo nl2br(htmlspecialchars($question['explanation'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php 
                    endif;
                endwhile;
                if (!$found_wrong):
                ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Congratulations! You got all questions correct!
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4">
                <a href="view-course.php?id=<?php echo $attempt['course_id']; ?>" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Back to Course
                </a>
                <a href="start-quiz.php?course_id=<?php echo $attempt['course_id']; ?>" class="btn btn-primary">
                    <i class="fas fa-redo"></i> Try Again
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.score-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0 auto;
}

.wrong-answer-card {
    background-color: #f8f9fa;
    border-left: 4px solid #dc3545;
    padding: 1.5rem;
    border-radius: 4px;
}

.explanation {
    background-color: #fff3cd;
    padding: 1rem;
    border-radius: 4px;
    border-left: 4px solid #ffc107;
}

.explanation h5 {
    color: #856404;
    margin-bottom: 0.5rem;
}

.explanation p {
    color: #333;
    margin-bottom: 0;
}

.btn i {
    margin-right: 0.5rem;
}
</style>

<?php require_once '../includes/footer.php'; ?>