<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - QuizHub</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section id="about">
        <div class="container">
            <div class="content-container">
                <h2><i class="fas fa-info-circle"></i> About QuizHub</h2>
                
                <div class="about-section">
                    <h3><i class="fas fa-bullseye"></i> Our Mission</h3>
                    <p>At QuizHub, our mission is to make learning engaging, accessible, and effective for students of all ages. We strive to create a platform that not only tests knowledge but also enhances understanding and retention.</p>
                </div>

                <div class="about-section">
                    <h3><i class="fas fa-history"></i> Our Story</h3>
                    <p>Founded in 2023, QuizHub was born out of a passion for education and technology. Our team of educators and developers came together with a shared vision of transforming the way people learn and assess their knowledge.</p>
                </div>

                <div class="about-section">
                    <h3><i class="fas fa-gift"></i> What We Offer</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> A wide range of quizzes across various subjects</li>
                        <li><i class="fas fa-check"></i> Personalized learning paths based on individual performance</li>
                        <li><i class="fas fa-check"></i> Instant feedback and detailed explanations</li>
                        <li><i class="fas fa-check"></i> Progress tracking and performance analytics</li>
                        <li><i class="fas fa-check"></i> A platform for teachers to create and manage quizzes</li>
                    </ul>
                </div>

                <div class="about-section">
                    <h3><i class="fas fa-users"></i> Our Team</h3>
                    <p>QuizHub is powered by a diverse team of passionate educators, developers, and designers. We're committed to creating the best possible learning experience for our users.</p>
                </div>

                <div class="about-section">
                    <h3><i class="fas fa-handshake"></i> Join Us</h3>
                    <p>Whether you're a student looking to enhance your learning or a teacher wanting to create engaging quizzes, QuizHub is here for you. Join our community today and take your learning to the next level!</p>
                </div>

                <div class="signup-cta">
                    <a href="signup.php" class="btn btn-large">Sign Up Now <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>