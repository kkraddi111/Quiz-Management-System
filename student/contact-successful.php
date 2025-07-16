<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Sent Successfully - QuizHub</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="content">
        <div class="container">
            <section class="hero">
                <h1><i class="fas fa-check-circle"></i> Message Sent Successfully</h1>
                <p>Thank you for contacting QuizHub. We have received your message.</p>
            </section>

            <section class="success-message">
                <?php
                if (isset($_SESSION['contact_success'])) {
                    echo "<p class='success'>" . $_SESSION['contact_success'] . "</p>";
                    unset($_SESSION['contact_success']);
                }
                ?>
                <p>We appreciate your interest in QuizHub. Our team will review your message and get back to you as soon as possible.</p>
                <p>In the meantime, feel free to explore our courses and quizzes.</p>
                <div class="button-group">
                    <a href="index.php" class="btn"><i class="fas fa-home"></i> Return to Homepage</a>
                    <a href="course-catalog.php" class="btn"><i class="fas fa-book"></i> Explore Courses</a>
                </div>
            </section>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>