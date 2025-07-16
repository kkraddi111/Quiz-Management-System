<?php
// Ensure the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Define the base URL
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project/";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>student/css/student.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>student/css/view-course.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><i class="fas fa-graduation-cap"></i> <span class="highlight">Quiz</span>Hub</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo $base_url; ?>student/index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="<?php echo $base_url; ?>student/about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="<?php echo $base_url; ?>student/contact.php"><i class="fas fa-envelope"></i> Contact Us</a></li>
                    <li><a href="<?php echo $base_url; ?>student/edit-profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="<?php echo $base_url; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
        <!-- Removed the user-greeting div from here -->
