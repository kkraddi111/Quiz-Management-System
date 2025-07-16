<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    exit('Unauthorized access');
}

$teacher_id = $_SESSION['user_id'];

// Fetch courses taught by this teacher
$courses_query = "SELECT id, name FROM courses WHERE teacher_id = ?";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses_result = $stmt->get_result();
?>

<div class="add-question-container">
    <div class="header-section mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-plus-circle"></i> Add New Question</h2>
            <button onclick="loadQuestions()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Questions
            </button>
        </div>
    </div>

    <form id="add-question-form" class="question-form">
        <div class="form-group mb-3">
            <label for="course_id">Select Course</label>
            <select class="form-control" id="course_id" name="course_id" required>
                <option value="">Choose a course...</option>
                <?php while ($course = $courses_result->fetch_assoc()): ?>
                    <option value="<?php echo $course['id']; ?>">
                        <?php echo htmlspecialchars($course['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="question_text">Question Text</label>
            <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="question_type">Question Type</label>
            <select class="form-control" id="question_type" name="question_type" required>
                <option value="multiple_choice">Multiple Choice</option>
                <option value="true_false">True/False</option>
            </select>
        </div>

        <div id="multiple-choice-options" class="mb-3">
            <label>Options</label>
            <?php for($i = 0; $i < 4; $i++): ?>
                <div class="option-group mb-2">
                    <div class="input-group">
                        <input type="text" class="form-control" name="options[]" 
                               placeholder="Option <?php echo $i + 1; ?>" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <input type="radio" name="correct_option" value="<?php echo $i; ?>" required>
                                <label class="ms-2 mb-0">Correct</label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <div id="true-false-options" class="mb-3" style="display:none;">
            <label>Correct Answer</label>
            <div class="form-check">
                <input type="radio" class="form-check-input" name="true_false_answer" value="true" id="true_option">
                <label class="form-check-label" for="true_option">True</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" name="true_false_answer" value="false" id="false_option">
                <label class="form-check-label" for="false_option">False</label>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="difficulty">Difficulty Level</label>
            <select class="form-control" id="difficulty" name="difficulty" required>
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="explanation">Explanation (Optional)</label>
            <textarea class="form-control" id="explanation" name="explanation" rows="3"></textarea>
            <small class="form-text text-muted">Provide an explanation for the correct answer</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Question
            </button>
        </div>
    </form>
</div>

<style>
.add-question-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.option-group {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
}

.input-group-text {
    background: #e9ecef;
    cursor: pointer;
}

.input-group-text input[type="radio"] {
    margin-right: 5px;
    cursor: pointer;
}

.form-actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

.btn i {
    margin-right: 5px;
}
</style>

<script>
$(document).ready(function() {
    // Handle question type change
    $('#question_type').change(function() {
        if (this.value === 'multiple_choice') {
            $('#multiple-choice-options').show();
            $('#true-false-options').hide();
            $('input[name="correct_option"]').prop('required', true);
            $('input[name="true_false_answer"]').prop('required', false);
        } else {
            $('#multiple-choice-options').hide();
            $('#true-false-options').show();
            $('input[name="correct_option"]').prop('required', false);
            $('input[name="true_false_answer"]').prop('required', true);
        }
    });

    // Handle form submission
    $('#add-question-form').on('submit', function(e) {
        e.preventDefault();
        
        // Prepare form data
        var formData = new FormData(this);
        
        // Add options data for multiple choice
        if ($('#question_type').val() === 'multiple_choice') {
            var options = [];
            $('input[name="options[]"]').each(function(index) {
                options.push({
                    text: $(this).val(),
                    is_correct: $('input[name="correct_option"]:checked').val() == index
                });
            });
            formData.append('options', JSON.stringify(options));
        }

        // Submit form
        $.ajax({
            url: 'add_question.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if (result.success) {
                        alert('Question added successfully!');
                        loadQuestions();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (e) {
                    alert('Error processing response');
                }
            },
            error: function() {
                alert('Error adding question. Please try again.');
            }
        });
    });
});

function loadQuestions() {
    $.ajax({
        url: 'manage-questions.php',
        type: 'GET',
        success: function(response) {
            $('#mainContent').html(response);
        },
        error: function() {
            alert('Error loading questions list.');
        }
    });
}
</script> 