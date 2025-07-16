<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];
$num_questions = isset($_POST['num_questions']) ? (int)$_POST['num_questions'] : 5;
$difficulty = isset($_POST['difficulty']) ? $_POST['difficulty'] : 'medium';

// Validate number of questions
if (!in_array($num_questions, [5, 10, 15, 20, 25, 30])) {
    $num_questions = 5; // Default to 5 if invalid value
}

// Fetch course details first
$course_query = "SELECT * FROM courses WHERE id = ?";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: index.php");
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // First create a quiz record with proper title using course name
    $quiz_title = "Quiz Attempt - " . $course['name'];
    
    // Set difficulty to 'medium' when mixed is selected (as a default)
    $quiz_difficulty = $difficulty === 'mixed' ? 'medium' : $difficulty;
    
    $create_quiz = "INSERT INTO quizzes (title, course_id, created_by, duration, difficulty) 
                   VALUES (?, ?, ?, ?, ?)";
    if (!$stmt_quiz = $conn->prepare($create_quiz)) {
        throw new Exception("Error preparing quiz creation: " . $conn->error);
    }

    $duration = $num_questions * 60; // 1 minute per question in seconds
    $stmt_quiz->bind_param("siiis", $quiz_title, $course_id, $student_id, $duration, $quiz_difficulty);

    if (!$stmt_quiz->execute()) {
        throw new Exception("Error creating quiz: " . $stmt_quiz->error);
    }

    $quiz_id = $conn->insert_id;

    // Fetch questions based on difficulty
    if ($difficulty === 'mixed') {
        // For mixed difficulty, get random questions regardless of difficulty
        $questions_query = "SELECT * FROM questions 
                          WHERE course_id = ? 
                          ORDER BY RAND() 
                          LIMIT ?";
        if (!$stmt = $conn->prepare($questions_query)) {
            throw new Exception("Error preparing questions query: " . $conn->error);
        }
        $stmt->bind_param("ii", $course_id, $num_questions);
    } else {
        // For specific difficulty levels
        $questions_query = "SELECT * FROM questions 
                          WHERE course_id = ? 
                          AND difficulty = ? 
                          ORDER BY RAND() 
                          LIMIT ?";
        if (!$stmt = $conn->prepare($questions_query)) {
            throw new Exception("Error preparing questions query: " . $conn->error);
        }
        $stmt->bind_param("isi", $course_id, $difficulty, $num_questions);
    }

    if (!$stmt->execute()) {
        throw new Exception("Error fetching questions: " . $stmt->error);
    }
    $questions_result = $stmt->get_result();

    if ($questions_result->num_rows < $num_questions) {
        throw new Exception("Not enough questions available for this course");
    }

    // Create quiz attempt record
    $insert_attempt = "INSERT INTO quiz_attempts (user_id, quiz_id, started_at) 
                      VALUES (?, ?, NOW())";
    if (!$stmt = $conn->prepare($insert_attempt)) {
        throw new Exception("Error preparing attempt insert: " . $conn->error);
    }
    $stmt->bind_param("ii", $student_id, $quiz_id);
    if (!$stmt->execute()) {
        throw new Exception("Error creating quiz attempt: " . $stmt->error);
    }
    $attempt_id = $conn->insert_id;

    // Link questions to the quiz
    $insert_quiz_question = "INSERT INTO quiz_questions (quiz_id, question_id) VALUES (?, ?)";
    $stmt_link = $conn->prepare($insert_quiz_question);
    if (!$stmt_link) {
        throw new Exception("Error preparing quiz question link: " . $conn->error);
    }

    // Store questions in array for display
    $quiz_questions = [];
    while ($question = $questions_result->fetch_assoc()) {
        $quiz_questions[] = $question;
        // Link question to quiz
        $stmt_link->bind_param("ii", $quiz_id, $question['id']);
        if (!$stmt_link->execute()) {
            throw new Exception("Error linking question to quiz: " . $stmt_link->error);
        }
    }

    // Commit transaction
    $conn->commit();

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    error_log("Quiz error: " . $e->getMessage());
    header("Location: view-course.php?id=$course_id&error=" . urlencode($e->getMessage()));
    exit();
}

$page_title = "Quiz in Progress - " . $course['name'];
require_once '../includes/student_header.php';
?>

<div class="container mt-4">
    <div class="quiz-container">
        <div class="quiz-header">
            <div class="timer" id="timer" data-time="<?php echo $duration; ?>">
                Time Remaining: <span id="time-display"><?php echo $num_questions; ?>:00</span>
            </div>
            <div class="progress">
                Question <span id="current-question">1</span> of <?php echo $questions_result->num_rows; ?>
            </div>
        </div>

        <form id="quiz-form" method="POST" action="submit-quiz.php">
            <input type="hidden" name="attempt_id" value="<?php echo $attempt_id; ?>">
            <div class="questions-navigation">
                <?php
                $question_num = 1;
                while ($question = $questions_result->fetch_assoc()):
                ?>
                    <button type="button" class="question-nav-btn" data-question="<?php echo $question_num; ?>">
                        <?php echo $question_num; ?>
                    </button>
                <?php
                    $question_num++;
                endwhile;
                $questions_result->data_seek(0); // Reset the result pointer
                ?>
            </div>

            <?php
            $question_num = 1;
            while ($question = $questions_result->fetch_assoc()):
            ?>
                <div class="question-container" id="question-<?php echo $question_num; ?>" 
                     style="<?php echo $question_num === 1 ? '' : 'display: none;'; ?>">
                    <h3>Question <?php echo $question_num; ?></h3>
                    <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                    
                    <input type="hidden" name="question_ids[]" value="<?php echo $question['id']; ?>">
                    
                    <?php if ($question['question_type'] === 'multiple_choice'): ?>
                        <?php
                        $options_query = "SELECT * FROM options WHERE question_id = ? ORDER BY RAND()";
                        $stmt = $conn->prepare($options_query);
                        $stmt->bind_param("i", $question['id']);
                        $stmt->execute();
                        $options = $stmt->get_result();
                        ?>
                        <div class="options">
                            <?php while ($option = $options->fetch_assoc()): ?>
                                <label class="option">
                                    <input type="radio" name="answers[<?php echo $question['id']; ?>]" 
                                           value="<?php echo $option['id']; ?>">
                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    <?php elseif ($question['question_type'] === 'true_false'): ?>
                        <div class="options">
                            <label class="option">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="true">
                                True
                            </label>
                            <label class="option">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="false">
                                False
                            </label>
                        </div>
                    <?php else: ?>
                        <div class="short-answer">
                            <textarea name="answers[<?php echo $question['id']; ?>]" rows="3" 
                                      placeholder="Type your answer here..."></textarea>
                        </div>
                    <?php endif; ?>

                    <div class="question-actions">
                        <?php if ($question_num > 1): ?>
                            <button type="button" class="btn btn-secondary prev-question">Previous</button>
                        <?php endif; ?>
                        
                        <?php if ($question_num < $questions_result->num_rows): ?>
                            <button type="button" class="btn btn-primary next-question">Next</button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-success">Submit Quiz</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
            $question_num++;
            endwhile; 
            ?>
        </form>
    </div>
</div>

<style>
.quiz-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quiz-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.timer {
    font-size: 1.2rem;
    font-weight: 500;
    color: #dc3545;
}

.questions-navigation {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
}

.question-nav-btn {
    width: 35px;
    height: 35px;
    border: 1px solid #ddd;
    border-radius: 50%;
    background: white;
    cursor: pointer;
}

.question-nav-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.question-nav-btn.answered {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.question-container {
    margin-bottom: 2rem;
}

.options {
    display: grid;
    gap: 1rem;
    margin: 1.5rem 0;
}

.option {
    display: block;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.option:hover {
    background-color: #f8f9fa;
}

.question-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
}

.btn {
    padding: 0.5rem 1.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerElement = document.getElementById('timer');
    const timeDisplay = document.getElementById('time-display');
    const currentQuestionDisplay = document.getElementById('current-question');
    const questions = document.querySelectorAll('.question-container');
    const navButtons = document.querySelectorAll('.question-nav-btn');
    let timeLeft = parseInt(timerElement.dataset.time);

    // Timer functionality
    const timer = setInterval(function() {
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timeDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

        if (timeLeft <= 0) {
            clearInterval(timer);
            document.getElementById('quiz-form').submit();
        }
    }, 1000);

    // Question navigation function
    function showQuestion(questionNum) {
        questions.forEach(q => q.style.display = 'none');
        document.getElementById(`question-${questionNum}`).style.display = 'block';
        currentQuestionDisplay.textContent = questionNum;
        
        // Update navigation buttons
        navButtons.forEach(btn => {
            btn.classList.remove('active');
            if (parseInt(btn.dataset.question) === questionNum) {
                btn.classList.add('active');
            }
        });
    }

    // Navigation button click handlers
    navButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const questionNum = parseInt(this.dataset.question);
            showQuestion(questionNum);
        });
    });

    // Next button click handlers
    document.querySelectorAll('.next-question').forEach(button => {
        button.addEventListener('click', function() {
            const currentQuestion = this.closest('.question-container');
            const currentNum = parseInt(currentQuestion.id.split('-')[1]);
            showQuestion(currentNum + 1);
        });
    });

    // Previous button click handlers
    document.querySelectorAll('.prev-question').forEach(button => {
        button.addEventListener('click', function() {
            const currentQuestion = this.closest('.question-container');
            const currentNum = parseInt(currentQuestion.id.split('-')[1]);
            showQuestion(currentNum - 1);
        });
    });

    // Mark questions as answered
    document.querySelectorAll('input[type="radio"], textarea').forEach(input => {
        input.addEventListener('change', function() {
            const questionContainer = this.closest('.question-container');
            const questionNum = parseInt(questionContainer.id.split('-')[1]);
            navButtons[questionNum - 1].classList.add('answered');
        });
    });

    // Show first question and mark its navigation button as active
    showQuestion(1);
    navButtons[0].classList.add('active');
});
</script>

<?php require_once '../includes/footer.php'; ?> 