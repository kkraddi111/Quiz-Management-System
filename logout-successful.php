<?php
session_start();
$page_title = "Logout Successful";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Successfully Logged Out - Quiz Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }
        .container {
            text-align: center;
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .icon-3d {
            font-size: 80px;
            color: #e8491d;
            animation: rotate3d 3s infinite linear;
            transform-style: preserve-3d;
        }
        @keyframes rotate3d {
            0% { transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); }
        }
        p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e8491d;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
        }
        .btn:hover {
            background-color: #c73e1d;
            transform: scale(1.05);
        }
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .particle {
            position: absolute;
            background-color: #e8491d;
            border-radius: 50%;
            animation: particleFall 3s infinite linear;
        }
        @keyframes particleFall {
            0% { transform: translateY(-10vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
        }
        .top-bar {
            background-color: #f4f4f4;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1000;
            height: 50px;
            border-bottom: 1px solid #ddd;
            border-radius: 10px;
        }
        .quiz-hub-header {
            font-size: 24px;
            font-weight: bold;
        }
        .quiz-hub-header .highlight {
            color: #e8491d;
        }
        .user-actions {
            display: flex;
            align-items: center;
        }
        .home-button {
            background-color: #e8491d;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .home-button:hover {
            background-color: #c73e1d;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="quiz-hub-header">
            <i class="fas fa-graduation-cap"></i> <span class="highlight">Quiz</span> Hub
        </div>
    </div>
    <div class="particles" id="particles"></div>
    <div class="container">
        <div class="icon-3d">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h1>Successfully Logged Out</h1>
        <p>Thank you for using Quiz Hub. We hope to see you again soon!</p>
        <a href="login.php" class="btn">Back to Login</a>
    </div>

    <script>
        function createParticle() {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            particle.style.left = Math.random() * 100 + 'vw';
            particle.style.animationDuration = Math.random() * 2 + 1 + 's';
            particle.style.width = particle.style.height = Math.random() * 10 + 5 + 'px';
            document.getElementById('particles').appendChild(particle);
            setTimeout(() => {
                particle.remove();
            }, 3000);
        }

        setInterval(createParticle, 100);
    </script>
</body>
</html>