<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch courses for dropdown
$courses_query = "SELECT id, name FROM courses";
$courses_result = $conn->query($courses_query);
?>

<h3>Add New Question</h3>
<form id="addQuestionForm" method="POST" action="process-add-question.php">
    <label for="course_id">Course:</label>
    <select name="course_id" required>
        <?php while ($course = $courses_result->fetch_assoc()): ?>
            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
        <?php endwhile; ?>
    </select>

    <label for="question_text">Question:</label>
    <textarea name="question_text" required></textarea>

    <label for="question_type">Question Type:</label>
    <select name="question_type" id="question_type" required>
        <option value="multiple_choice">Multiple Choice</option>
        <option value="true_false">True/False</option>
    </select>

    <label for="difficulty">Difficulty:</label>
    <select name="difficulty" required>
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
    </select>

    <div id="multiple_choice_options">
        <div class="option-group">
            <label for="option1">Option 1:</label>
            <input type="text" name="options[]" required>
            <label class="radio-label">
                <input type="radio" name="correct_option" value="0" checked> Correct Answer
            </label>
        </div>
        <div class="option-group">
            <label for="option2">Option 2:</label>
            <input type="text" name="options[]" required>
            <label class="radio-label">
                <input type="radio" name="correct_option" value="1"> Correct Answer
            </label>
        </div>
        <div class="option-group">
            <label for="option3">Option 3:</label>
            <input type="text" name="options[]" required>
            <label class="radio-label">
                <input type="radio" name="correct_option" value="2"> Correct Answer
            </label>
        </div>
        <div class="option-group">
            <label for="option4">Option 4:</label>
            <input type="text" name="options[]" required>
            <label class="radio-label">
                <input type="radio" name="correct_option" value="3"> Correct Answer
            </label>
        </div>
    </div>

    <div id="true_false_options" style="display:none;">
        <label for="correct_answer">Correct Answer:</label>
        <select name="correct_answer">
            <option value="true">True</option>
            <option value="false">False</option>
        </select>
    </div>

    <label for="explanation">Explanation:</label>
    <textarea name="explanation" required></textarea>

    <input type="submit" value="Add Question">
</form>

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