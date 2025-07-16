<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$course = $conn->query("SELECT * FROM courses WHERE id = $course_id")->fetch_assoc();

if (!$course) {
    header("Location: course-catalog.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = intval($_POST['rating']);
    $review = $conn->real_escape_string($_POST['review']);
    $user_id = $_SESSION['user_id'];

    $conn->query("INSERT INTO reviews (user_id, course_id, rating, review) VALUES ($user_id, $course_id, $rating, '$review') ON DUPLICATE KEY UPDATE rating = $rating, review = '$review'");

    header("Location: course-preview.php?id=$course_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Course - <?php echo $course['name']; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="content">
        <div class="container">
            <h1>Rate Course: <?php echo $course['name']; ?></h1>
            <form action="" method="post">
                <div class="form-group">
                    <label for="rating">Rating:</label>
                    <select name="rating" id="rating" required>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="review">Review:</label>
                    <textarea name="review" id="review" required></textarea>
                </div>
                <button type="submit" class="btn">Submit Rating</button>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>