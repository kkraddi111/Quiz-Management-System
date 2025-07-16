<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - QuizHub</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <!-- Include your header content here -->
    </header>

    <div class="content-wrapper">
        <section id="courses">
            <div class="container">
                <h1>Available Courses</h1>
                <!-- Add your course listing here -->
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 QuizHub. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>