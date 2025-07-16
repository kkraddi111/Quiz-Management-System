<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$student_id = $_SESSION['user_id'];

// Verify course exists and student is enrolled
$course_query = "SELECT c.* FROM courses c
                JOIN enrollments e ON c.id = e.course_id
                WHERE c.id = ? AND e.user_id = ?";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("ii", $course_id, $student_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: view-course.php?id=$course_id&error=not_enrolled");
    exit();
}

$page_title = "Start Quiz - " . $course['name'];
require_once '../includes/student_header.php';
?>

<div class="quiz-setup-container">
    <h2>Quiz Setup - <?php echo htmlspecialchars($course['name']); ?></h2>
    
    <div class="quiz-instructions">
        <h3><i class="fas fa-info-circle"></i> Instructions</h3>
        <ul>
            <li>Once you start the quiz, the timer will begin counting down</li>
            <li>You can navigate between questions freely</li>
            <li>Your answers are automatically saved as you proceed</li>
            <li>Each question is worth equal marks</li>
            <li>Time allocation is 1 minute per question</li>
            <li>Results will be displayed immediately after completion</li>
        </ul>
    </div>

    <form action="quiz.php" method="POST" class="quiz-setup-form">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        
        <div class="form-group">
            <label for="num_questions">Number of Questions:</label>
            <select name="num_questions" id="num_questions" required>
                <option value="5">5 Questions (5 minutes)</option>
                <option value="10">10 Questions (10 minutes)</option>
                <option value="15">15 Questions (15 minutes)</option>
                <option value="20">20 Questions (20 minutes)</option>
                <option value="25">25 Questions (25 minutes)</option>
                <option value="30">30 Questions (30 minutes)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="difficulty">Difficulty Level:</label>
            <select name="difficulty" id="difficulty" required>
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
            </select>
        </div>

        <div class="time-allocation">
            <p><strong>Time Allocation:</strong></p>
            <p>Each question is allocated 1 minute:</p>
            <ul>
                <li>5 questions = 5 minutes</li>
                <li>10 questions = 10 minutes</li>
                <li>15 questions = 15 minutes</li>
                <li>20 questions = 20 minutes</li>
                <li>25 questions = 25 minutes</li>
                <li>30 questions = 30 minutes</li>
            </ul>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Start Quiz</button>
            <a href="view-course.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.quiz-setup-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quiz-instructions {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 6px;
    margin-bottom: 2rem;
}

.quiz-instructions h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quiz-instructions ul {
    list-style-type: none;
    padding-left: 0;
}

.quiz-instructions li {
    margin-bottom: 0.8rem;
    padding-left: 1.5rem;
    position: relative;
}

.quiz-instructions li:before {
    content: "•";
    color: #007bff;
    position: absolute;
    left: 0;
}

.quiz-setup-form {
    display: grid;
    gap: 1.5rem;
}

.form-group {
    display: grid;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 500;
    color: #2c3e50;
}

.form-group select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.time-allocation {
    background: #e9ecef;
    padding: 1rem;
    border-radius: 6px;
    margin: 1rem 0;
}

.time-allocation p {
    margin: 0.5rem 0;
}

.time-allocation ul {
    list-style-type: none;
    padding-left: 0;
    margin: 0.5rem 0;
}

.time-allocation li {
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.btn {
    padding: 0.5rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn:hover {
    opacity: 0.9;
}
</style>

<?php require_once '../includes/footer.php'; ?> 