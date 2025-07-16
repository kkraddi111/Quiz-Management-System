<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$question_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch question details
$question_query = "SELECT q.*, c.name AS course_name 
                  FROM questions q 
                  JOIN courses c ON q.course_id = c.id 
                  WHERE q.id = ? AND c.teacher_id = ?";
$stmt = $conn->prepare($question_query);
$stmt->bind_param("ii", $question_id, $teacher_id);
$stmt->execute();
$question = $stmt->get_result()->fetch_assoc();

// Fetch options if it's a multiple choice question
$options = [];
if ($question['question_type'] === 'multiple_choice') {
    $options_query = "SELECT * FROM options WHERE question_id = ? ORDER BY id";
    $stmt = $conn->prepare($options_query);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $options_result = $stmt->get_result();
    while ($option = $options_result->fetch_assoc()) {
        $options[] = $option;
    }
}

if (!$question) {
    echo "Question not found or unauthorized access.";
    exit();
}
?>

<div class="edit-question-form">
    <h3>Edit Question</h3>
    <form id="edit-question-form">
        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
        
        <div class="form-group">
            <label for="question_text">Question Text</label>
            <textarea class="form-control" id="question_text" name="question_text" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="question_type">Question Type</label>
            <select class="form-control" id="question_type" name="question_type" required>
                <option value="multiple_choice" <?php echo $question['question_type'] == 'multiple_choice' ? 'selected' : ''; ?>>Multiple Choice</option>
                <option value="true_false" <?php echo $question['question_type'] == 'true_false' ? 'selected' : ''; ?>>True/False</option>
            </select>
        </div>

        <div id="options-container" <?php echo $question['question_type'] != 'multiple_choice' ? 'style="display:none;"' : ''; ?>>
            <div class="form-group">
                <label>Options (Select the correct answer)</label>
                <?php 
                // If no options exist, create default 4 empty options
                if (empty($options)) {
                    $options = array_fill(0, 4, ['option_text' => '', 'is_correct' => 0]);
                }
                foreach ($options as $index => $option): 
                ?>
                    <div class="option-group">
                        <div class="option-input-group">
                            <input type="text" 
                                   class="form-control" 
                                   name="options[]" 
                                   value="<?php echo htmlspecialchars($option['option_text']); ?>" 
                                   placeholder="Enter option <?php echo $index + 1; ?>"
                                   required>
                            <div class="option-radio">
                                <input type="radio" 
                                       name="correct_option" 
                                       id="option_<?php echo $index; ?>" 
                                       value="<?php echo $index; ?>" 
                                       <?php echo $option['is_correct'] ? 'checked' : ''; ?> 
                                       required>
                                <label for="option_<?php echo $index; ?>">Correct</label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="true-false-container" <?php echo $question['question_type'] != 'true_false' ? 'style="display:none;"' : ''; ?>>
            <div class="form-group">
                <label>Correct Answer</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="true_false_answer" value="true" 
                           <?php echo $question['correct_answer'] === 'true' ? 'checked' : ''; ?>>
                    <label class="form-check-label">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="true_false_answer" value="false" 
                           <?php echo $question['correct_answer'] === 'false' ? 'checked' : ''; ?>>
                    <label class="form-check-label">False</label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="difficulty">Difficulty</label>
            <select class="form-control" id="difficulty" name="difficulty" required>
                <option value="easy" <?php echo $question['difficulty'] == 'easy' ? 'selected' : ''; ?>>Easy</option>
                <option value="medium" <?php echo $question['difficulty'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="hard" <?php echo $question['difficulty'] == 'hard' ? 'selected' : ''; ?>>Hard</option>
            </select>
        </div>

        <div class="form-group">
            <label for="explanation">Explanation</label>
            <textarea class="form-control" id="explanation" name="explanation"><?php echo htmlspecialchars($question['explanation']); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Question</button>
            <button type="button" class="btn btn-secondary" onclick="loadQuestions()">Cancel</button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Handle question type change
    $('#question_type').change(function() {
        if (this.value === 'multiple_choice') {
            $('#options-container').show();
            $('#true-false-container').hide();
        } else {
            $('#options-container').hide();
            $('#true-false-container').show();
        }
    });

    $('#edit-question-form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        // Add correct answer based on question type
        if ($('#question_type').val() === 'multiple_choice') {
            var correctOption = $('input[name="correct_option"]:checked').val();
            formData.append('correct_answer', $('input[name="options[]"]').eq(correctOption).val());
        } else {
            formData.append('correct_answer', $('input[name="true_false_answer"]:checked').val());
        }

        $.ajax({
            url: 'update_question.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response === 'success') {
                    alert('Question updated successfully!');
                    loadQuestions();
                } else {
                    alert('Error updating question: ' + response);
                }
            },
            error: function() {
                alert('Error updating question. Please try again.');
            }
        });
    });
});
</script>

<style>
.edit-question-form {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.option-group {
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid transparent;
}

.option-input-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.option-radio {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
    padding: 0 0.5rem;
}

.option-radio input[type="radio"] {
    margin: 0;
    cursor: pointer;
}

.option-radio label {
    margin: 0;
    cursor: pointer;
    font-weight: normal;
    font-size: 0.9rem;
}

.form-control {
    flex: 1;
}

/* Style for selected correct answer */
.option-group:has(input[type="radio"]:checked) {
    border-left-color: #007bff;
    background-color: #f0f7ff;
}

.option-group:has(input[type="radio"]:checked) .option-radio label {
    color: #007bff;
    font-weight: bold;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 1.5rem;
}
</style>
