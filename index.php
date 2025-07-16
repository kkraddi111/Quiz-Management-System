<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'student':
            header("Location: student/dashboard.php");
            exit();
        case 'teacher':
            header("Location: teacher/dashboard.php");
            exit();
        case 'admin':
            header("Location: admin/dashboard.php");
            exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to QuizHub - Your Online Learning Platform</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section id="hero">
        <div class="container">
            <h1>Empower Your Learning Journey with QuizHub</h1>
            <p>Discover interactive quizzes, track your progress, and achieve your educational goals.</p>
            <a href="signup.php" class="cta-button">Get Started <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

    <section id="features">
        <div class="container">
            <h2>Why Choose QuizHub?</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <i class="fas fa-brain fa-3x"></i>
                    <h3>Adaptive Learning</h3>
                    <p>Our quizzes adapt to your skill level, ensuring you're always challenged.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-line fa-3x"></i>
                    <h3>Track Progress</h3>
                    <p>Monitor your improvement with detailed performance analytics.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-users fa-3x"></i>
                    <h3>Community</h3>
                    <p>Join a community of learners and share your knowledge.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-mobile-alt fa-3x"></i>
                    <h3>Learn Anywhere</h3>
                    <p>Access your quizzes on any device, anytime, anywhere.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials">
        <div class="container">
            <h2>What Our Users Say</h2>
            <div class="testimonial-grid">
                <div class="testimonial-item">
                    <p>"QuizHub has revolutionized the way I study. The adaptive quizzes have helped me improve my weak areas significantly."</p>
                    <p class="testimonial-author">- Sarah J., Student</p>
                </div>
                <div class="testimonial-item">
                    <p>"As a teacher, I find QuizHub an invaluable tool for assessing my students' progress and identifying areas that need more focus."</p>
                    <p class="testimonial-author">- Mark T., Teacher</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>