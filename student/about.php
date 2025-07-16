<?php
session_start();
require_once '../config/db.php';

$page_title = "About Us";
require_once '../includes/student_header.php';
?>

<div class="container">
    <h1>About QuizHub</h1>
    <p>QuizHub is an innovative online learning platform designed to help students enhance their knowledge through interactive quizzes and courses.</p>
    
    <h2>Our Mission</h2>
    <p>Our mission is to make learning engaging, accessible, and effective for students of all backgrounds.</p>
    
    <h2>What We Offer</h2>
    <ul>
        <li>A wide range of courses across various subjects</li>
        <li>Interactive quizzes to test and reinforce your knowledge</li>
        <li>Progress tracking to help you monitor your learning journey</li>
        <li>A supportive community of learners and educators</li>
    </ul>
    
    <h2>Our Team</h2>
    <p>QuizHub is brought to you by a dedicated team of educators, developers, and learning enthusiasts who are passionate about transforming education through technology.</p>
</div>

<?php require_once '../includes/footer.php'; ?>