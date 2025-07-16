<?php
session_start();
require_once 'config/db.php';

// Define the base URL correctly
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project/";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QuizHub</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section id="login">
        <div class="container">
            <div class="form-container">
                <h2><i class="fas fa-sign-in-alt"></i> Login to QuizHub</h2>
                <?php
                if (isset($_SESSION['error'])) {
                    echo "<p class='error'>" . $_SESSION['error'] . "</p>";
                    unset($_SESSION['error']);
                }
                ?>
                <form action="" method="post" id="loginForm">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="role"><i class="fas fa-user-tag"></i> Role:</label>
                        <select id="role" name="role" required>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Login</button>
                </form>
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var role = document.getElementById('role').value;
            var form = this;
            
            switch(role) {
                case 'student':
                    form.action = '<?php echo $base_url; ?>student/process_login.php';
                    break;
                case 'teacher':
                case 'admin':
                    form.action = '<?php echo $base_url; ?>process_login.php';
                    break;
            }
            
            form.submit();
        });
    </script>
</body>
</html>