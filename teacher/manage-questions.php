<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Pagination settings
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Filter settings
$difficulty_filter = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';

// Fetch questions created by this teacher with pagination and filter
$questions_query = "SELECT q.*, c.name AS course_name 
                    FROM questions q 
                    JOIN courses c ON q.course_id = c.id 
                    WHERE c.teacher_id = ?";

if ($difficulty_filter) {
    $questions_query .= " AND q.difficulty = ?";
}

$questions_query .= " ORDER BY q.id DESC LIMIT ?, ?";

$stmt = $conn->prepare($questions_query);

if ($difficulty_filter) {
    $stmt->bind_param("isii", $teacher_id, $difficulty_filter, $start_from, $results_per_page);
} else {
    $stmt->bind_param("iii", $teacher_id, $start_from, $results_per_page);
}

$stmt->execute();
$questions_result = $stmt->get_result();

// Get total number of questions for pagination
$count_query = "SELECT COUNT(*) as total FROM questions q JOIN courses c ON q.course_id = c.id WHERE c.teacher_id = ?";
if ($difficulty_filter) {
    $count_query .= " AND q.difficulty = ?";
}
$count_stmt = $conn->prepare($count_query);
if ($difficulty_filter) {
    $count_stmt->bind_param("is", $teacher_id, $difficulty_filter);
} else {
    $count_stmt->bind_param("i", $teacher_id);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_questions = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_questions / $results_per_page);

$page_title = "Manage Questions";
include '../includes/teacher_header.php';

// Update this line to match the database difficulty levels
$difficulty_options = ['easy', 'medium', 'hard'];

?>

<div class="container">
    <h1>Manage Questions</h1>
    <div class="mb-3">
        <button id="add-question-btn" class="btn btn-primary">Add New Question</button>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="btn-group" role="group" aria-label="Difficulty filter">
                <button class="btn btn-outline-secondary active" data-difficulty="">All Difficulties</button>
                <?php foreach ($difficulty_options as $option): ?>
                    <button class="btn btn-outline-secondary" data-difficulty="<?php echo $option; ?>"><?php echo ucfirst($option); ?></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="questions-container">
        <?php if ($questions_result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php while ($question = $questions_result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars(substr($question['question_text'], 0, 50)) . '...'; ?></h5>
                                <p class="card-text">
                                    <strong>Course:</strong> <?php echo htmlspecialchars($question['course_name']); ?><br>
                                    <strong>Type:</strong> <?php echo htmlspecialchars($question['question_type']); ?><br>
                                    <strong>Difficulty:</strong> <?php echo htmlspecialchars($question['difficulty']); ?>
                                </p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-sm btn-primary btn-edit" onclick="editQuestion(<?php echo $question['id']; ?>)">Edit</button>
                                <button onclick="deleteQuestion(<?php echo $question['id']; ?>)" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="pagination mt-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&difficulty=<?php echo $difficulty_filter; ?>" class="btn btn-outline-primary">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&difficulty=<?php echo $difficulty_filter; ?>" class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&difficulty=<?php echo $difficulty_filter; ?>" class="btn btn-outline-primary">Next</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                No questions found. <a href="add_question.php" class="alert-link">Add a new question</a> to get started.
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function loadQuestions(difficulty = '') {
        $.ajax({
            url: 'load_questions.php',
            method: 'GET',
            data: { difficulty: difficulty },
            success: function(response) {
                $('#questions-container').html(response);
            },
            error: function() {
                $('#questions-container').html('<p>Error loading questions. Please try again.</p>');
            }
        });
    }

    // Load all questions initially
    loadQuestions();

    // Handle difficulty button clicks
    $('.btn-group button').click(function() {
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
        loadQuestions($(this).data('difficulty'));
    });

    // Handle Edit button clicks
    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        var questionId = $(this).data('id');
        $.ajax({
            url: 'edit_question.php', // Change this line
            method: 'GET',
            data: { id: questionId },
            success: function(response) {
                $('#questions-container').html(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); // Add this line for debugging
                $('#questions-container').html('<p>Error loading edit form. Please try again.</p>');
            }
        });
    });

    // Handle Add New Question button click
    $('#add-question-btn').click(function() {
        $.ajax({
            url: 'add-question-form.php',
            method: 'GET',
            success: function(response) {
                $('#questions-container').html(response);
            },
            error: function() {
                $('#questions-container').html('<p>Error loading add question form. Please try again.</p>');
            }
        });
    });

    // Handle add question form submission
    $(document).on('submit', '#add-question-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'add_question.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    loadQuestions(); // Reload the questions list
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Error adding question. Please try again.');
            }
        });
    });

    // Handle delete button clicks
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var questionId = $(this).data('id');
        if (confirm('Are you sure you want to delete this question?')) {
            $.ajax({
                url: 'delete_question.php',
                method: 'POST',
                data: { id: questionId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        loadQuestions(); // Reload the questions list
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('Error deleting question. Please try again.');
                }
            });
        }
    });

    function editQuestion(questionId) {
        $.ajax({
            url: 'edit_question.php',
            method: 'GET',
            data: { id: questionId },
            success: function(response) {
                $('#questions-container').html(response);
            },
            error: function() {
                alert('Error loading edit form. Please try again.');
            }
        });
    }

    function deleteQuestion(questionId) {
        if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
            $.ajax({
                url: 'delete_question.php',
                type: 'POST',
                data: { id: questionId },
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.success) {
                            alert(result.message);
                            loadQuestions(); // Reload the questions list
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (e) {
                        alert('Error processing response');
                    }
                },
                error: function() {
                    alert('Error deleting question. Please try again.');
                }
            });
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
