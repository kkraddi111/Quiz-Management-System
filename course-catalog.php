<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Catalog - QuizHub</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="content">
        <div class="container">
            <section class="hero">
                <h1>Course Catalog</h1>
                <p>Explore our wide range of courses and start learning today!</p>
            </section>

            <section class="course-list">
                <?php
                $courses = $conn->query("SELECT * FROM courses ORDER BY name");
                if ($courses->num_rows > 0):
                ?>
                    <div class="course-grid">
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <div class="course-card">
                                <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo $course['name']; ?>" class="course-image">
                                <h3><?php echo $course['name']; ?></h3>
                                <p><?php echo substr($course['description'], 0, 100); ?>...</p>
                                <a href="course-preview.php?id=<?php echo $course['id']; ?>" class="btn">View Course</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No courses available at the moment.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>