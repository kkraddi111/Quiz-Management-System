<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Check if course_id is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$course_id = $_GET['id'];
$student_id = $_SESSION['user_id'];

// Fetch course details
$course_query = "SELECT c.*, u.username AS teacher_name FROM courses c 
                 JOIN users u ON c.teacher_id = u.id 
                 WHERE c.id = ?";
$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$course = $course_result->fetch_assoc();

// Fetch reviews for this course
$reviews_query = "SELECT cr.*, u.username FROM course_reviews cr 
                  JOIN users u ON cr.user_id = u.id 
                  WHERE cr.course_id = ? 
                  ORDER BY cr.created_at DESC";
$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param("i", $course_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Fetch the user's review if it exists
$user_review_query = "SELECT * FROM course_reviews WHERE course_id = ? AND user_id = ?";
$user_review_stmt = $conn->prepare($user_review_query);
$user_review_stmt->bind_param("ii", $course_id, $student_id);
$user_review_stmt->execute();
$user_review_result = $user_review_stmt->get_result();
$user_review = $user_review_result->fetch_assoc();
$has_reviewed = $user_review_result->num_rows > 0;

$page_title = $course['name'];
require_once '../includes/student_header.php';
?>

<style>
.course-image-container {
    position: relative;
    width: 100%;
    height: 200px; /* Fixed height */
    overflow: hidden;
    background-color: #f8f9fa;
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
}

.course-image {
    width: 100%;
    height: 100%;
    object-fit: cover; /* This will maintain aspect ratio */
    object-position: center;
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e9ecef;
    color: #6c757d;
    font-size: 1rem;
}

/* Update card styles */
.card {
    margin-bottom: 1.5rem;
    border: 1px solid rgba(0,0,0,.125);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card-body {
    position: relative;
    z-index: 1; /* Ensure content stays above the image */
    background-color: white;
}

/* Ensure proper spacing */
.quiz-section {
    padding: 1rem 0;
}

/* Make sure the content below doesn't overlap */
.col-md-4 {
    margin-bottom: 2rem;
}
</style>

<div class="container mt-4">
    <h1 class="course-title"><?php echo htmlspecialchars($course['name']); ?></h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="course-image-container">
                    <?php if (!empty($course['image'])): ?>
                        <?php
                        $image_path = "../uploads/" . $course['image'];
                        if (file_exists($image_path)):
                        ?>
                            <img src="<?php echo $image_path; ?>" 
                                 alt="<?php echo htmlspecialchars($course['name']); ?>" 
                                 class="course-image">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image me-2"></i>
                                Image not found
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image me-2"></i>
                            No image available
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Quiz Section -->
                <div class="card-body">
                    <div class="quiz-section">
                        <h3 class="card-title">Course Challenge</h3>
                        <?php 
                        $questions_query = "SELECT COUNT(*) as count FROM questions WHERE course_id = $course_id";
                        $questions_result = $conn->query($questions_query);
                        $question_count = $questions_result->fetch_assoc()['count'];
                        
                        if ($question_count > 0): 
                        ?>
                            <p class="card-text">Test your knowledge with a quiz!</p>
                            <form action="start-quiz.php" method="GET" class="text-center">
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play-circle"></i> Start Challenge
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                No questions available for this course yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Course Details</h2>
                    <p><strong>Teacher:</strong> <?php echo htmlspecialchars($course['teacher_name']); ?></p>
                    <p><strong>Description:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Reviews</h2>
                    <?php if ($reviews_result->num_rows > 0): ?>
                        <?php while ($review = $reviews_result->fetch_assoc()): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($review['username']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">Rating: <?php echo $review['rating']; ?>/5</h6>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                    <small class="text-muted">Posted on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No reviews yet. Be the first to review this course!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Review Form -->
            <div class="card">
                <div class="card-body">
                    <?php if (!$has_reviewed): ?>
                        <h2 class="card-title">Add Your Review</h2>
                        <form id="review-form" action="submit_review.php" method="post">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="">Select a rating</option>
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="3">3 - Good</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="1">1 - Poor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="review_text" class="form-label">Your Review</label>
                                <textarea class="form-control" id="review_text" name="review_text" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <h2 class="card-title">Edit Your Review</h2>
                        <form id="edit-review-form" action="update_review.php" method="post">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            <input type="hidden" name="review_id" value="<?php echo $user_review['id']; ?>">
                            <div class="mb-3">
                                <label for="edit_rating" class="form-label">Rating</label>
                                <select class="form-select" id="edit_rating" name="rating" required>
                                    <option value="5" <?php echo $user_review['rating'] == 5 ? 'selected' : ''; ?>>5 - Excellent</option>
                                    <option value="4" <?php echo $user_review['rating'] == 4 ? 'selected' : ''; ?>>4 - Very Good</option>
                                    <option value="3" <?php echo $user_review['rating'] == 3 ? 'selected' : ''; ?>>3 - Good</option>
                                    <option value="2" <?php echo $user_review['rating'] == 2 ? 'selected' : ''; ?>>2 - Fair</option>
                                    <option value="1" <?php echo $user_review['rating'] == 1 ? 'selected' : ''; ?>>1 - Poor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_review_text" class="form-label">Your Review</label>
                                <textarea class="form-control" id="edit_review_text" name="review_text" rows="5" required><?php echo htmlspecialchars($user_review['review_text']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Review</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#review-form, #edit-review-form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
