<?php
session_start();
require_once 'config/db.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$course = $conn->query("SELECT * FROM courses WHERE id = $course_id")->fetch_assoc();

if (!$course) {
    header("Location: course-catalog.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $course['name']; ?> - QuizHub</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="content">
        <div class="container">
            <section class="course-preview">
                <h1><?php echo $course['name']; ?></h1>
                <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo $course['name']; ?>" class="course-image">
                <p><?php echo $course['description']; ?></p>
                <h3>Requirements</h3>
                <p><?php echo $course['requirements']; ?></p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="take-quiz.php?course_id=<?php echo $course_id; ?>" class="btn">Take Quiz</a>
                <?php else: ?>
                    <p>Please <a href="login.php">log in</a> to take the quiz for this course.</p>
                <?php endif; ?>
            </section>

            <section class="course-ratings">
                <h2>Course Ratings</h2>
                <?php
                $ratings = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM reviews WHERE course_id = $course_id");
                $rating_data = $ratings->fetch_assoc();
                ?>
                <p>Average Rating: <?php echo number_format($rating_data['avg_rating'], 1); ?> / 5 (<?php echo $rating_data['total_ratings']; ?> ratings)</p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="rate-course.php?course_id=<?php echo $course_id; ?>" class="btn">Rate This Course</a>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>