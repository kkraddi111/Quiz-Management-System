<?php
session_start();
require_once 'config/db.php';

$page_title = "Available Courses";
require_once 'includes/student_header.php';

// Fetch available courses
$courses_query = "SELECT * FROM courses ORDER BY name ASC";
$courses_result = $conn->query($courses_query);

// Function to get the correct image URL
function getImageUrl($course) {
    if (isset($course['image']) && !empty($course['image'])) {
        $image_path = $course['image'];
        
        // Check if the path is already a full URL
        if (filter_var($image_path, FILTER_VALIDATE_URL)) {
            return $image_path;
        }
        
        // Check if the path is absolute
        if (strpos($image_path, '/') === 0) {
            return $image_path;
        }
        
        // If it's a relative path, prepend the uploads directory
        return 'uploads/' . $image_path;
    }
    
    // Return a default placeholder image if no image is set
    return 'assets/images/course-placeholder.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - QuizHub</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>student/css/student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1>Available Courses</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo "<p class='error'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<p class='success'>" . $_SESSION['success'] . "</p>";
            unset($_SESSION['success']);
        }
        ?>
        <div class="courses-grid">
            <?php
            if ($courses_result->num_rows > 0) {
                while ($course = $courses_result->fetch_assoc()) {
                    $image_url = getImageUrl($course);
                    echo "<div class='course-card'>";
                    echo "<div class='course-image' style='background-image: url(\"" . htmlspecialchars($image_url) . "\");'></div>";
                    echo "<div class='course-content'>";
                    echo "<h2 class='course-title'>" . htmlspecialchars($course['name']) . "</h2>";
                    echo "<p class='course-description'>" . htmlspecialchars(substr($course['description'], 0, 100)) . "...</p>";
                    echo "<a href='enroll-course.php?course_id=" . $course['id'] . "' class='enroll-btn'>Enroll Now</a>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>No courses available at the moment.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        // Add this script to create a staggered animation effect
        document.addEventListener('DOMContentLoaded', (event) => {
            const cards = document.querySelectorAll('.course-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>