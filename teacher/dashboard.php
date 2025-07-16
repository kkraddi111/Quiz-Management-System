<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Fetch teacher data
$teacher_result = $conn->query("SELECT username, email FROM users WHERE id = '$teacher_id'");
$teacher_data = $teacher_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $conn->real_escape_string($_POST['username']);
    $new_email = $conn->real_escape_string($_POST['email']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        $update_sql = "UPDATE users SET username = '$new_username', email = '$new_email'";
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql .= ", password = '$hashed_password'";
        }

        $update_sql .= " WHERE id = '$teacher_id'";

        if ($conn->query($update_sql)) {
            $_SESSION['success_message'] = "Profile updated successfully.";
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }
}

// Fetch teacher name
$teacher_result = $conn->query("SELECT username FROM users WHERE id = '$teacher_id'");
if (!$teacher_result) {
    die("Error fetching teacher name: " . $conn->error);
}
$teacher_name = $teacher_result->fetch_assoc()['username'];

// Fetch courses taught by this teacher
$courses_query = "SELECT c.id, c.name, c.image FROM courses c 
                  JOIN course_teachers ct ON c.id = ct.course_id 
                  WHERE ct.teacher_id = '$teacher_id'";
$courses_result = $conn->query($courses_query);
if (!$courses_result) {
    die("Error fetching courses: " . $conn->error);
}

// Count assigned courses
$assigned_courses_count = $courses_result->num_rows;

// Count total questions for the courses taught by this teacher
$total_questions_query = "SELECT COUNT(*) as total FROM questions q 
                          JOIN course_teachers ct ON q.course_id = ct.course_id 
                          WHERE ct.teacher_id = '$teacher_id'";
$total_questions_result = $conn->query($total_questions_query);
$total_questions_count = $total_questions_result->fetch_assoc()['total'];

// Fetch recent questions for courses taught by this teacher
$questions_query = "SELECT q.id, q.question_text, c.name AS course_name 
                    FROM questions q 
                    JOIN courses c ON q.course_id = c.id 
                    JOIN course_teachers ct ON c.id = ct.course_id
                    WHERE ct.teacher_id = '$teacher_id' 
                    ORDER BY q.created_at DESC LIMIT 5";
$questions_result = $conn->query($questions_query);
if (!$questions_result) {
    die("Error fetching questions: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Quiz Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/teacher-dashboard.css">
    <style>
        .dashboard-content {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .stat-card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 200px;
        }
        .stat-card h3 {
            margin-top: 0;
            color: #333;
            font-size: 18px;
        }
        .stat-card .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #e8491d;
        }
        .recent-questions {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex-basis: 100%;
        }
        .recent-questions h3 {
            margin-top: 0;
            color: #333;
            font-size: 18px;
            border-bottom: 2px solid #e8491d;
            padding-bottom: 10px;
        }
        .recent-questions table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .recent-questions th {
            background-color: #e8491d;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .recent-questions td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .recent-questions tr:last-child td {
            border-bottom: none;
        }
        .course-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .course-card {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            display: flex;
            flex-direction: column;
        }
        .course-image-container {
            height: 150px;
            overflow: hidden;
        }
        .course-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .course-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .course-title-box {
            background-color: #e8491d;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
            margin-top: auto;
        }
        .course-title {
            margin: 0;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="top-bar" id="topBar">
            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>
            <div class="quiz-hub-header">
                <i class="fas fa-graduation-cap"></i> <span class="highlight">Quiz</span> Hub
            </div>
            <div class="user-actions">
                <div class="profile-dropdown">
                    <div class="profile-icon-container">
                        <div class="profile-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <span class="username-display"><?php echo htmlspecialchars($teacher_name); ?></span>
                    </div>
                    <div class="profile-dropdown-content">
                        <div class="user-name">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span id="userName"><?php echo htmlspecialchars($teacher_name); ?></span>
                        </div>
                        <a href="#" onclick="loadEditProfileForm()">
                            <i class="fas fa-user-edit edit-profile-icon"></i>
                            Edit Profile
                        </a>
                        <a href="../logout.php" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#" data-module="dashboard"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="#" data-module="manage-courses"><i class="fas fa-book"></i> <span>Manage Courses</span></a></li>
                <li><a href="#" data-module="manage-questions"><i class="fas fa-question-circle"></i> <span>Manage Questions</span></a></li>
                <li><a href="#" data-module="reports"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
            </ul>
        </div>
        <div class="main-content" id="mainContent">
            <!-- Dashboard content will be loaded here -->
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Load dashboard content by default
        loadModule('dashboard');

        // Handle sidebar menu clicks
        $('.sidebar-menu a').on('click', function(e) {
            e.preventDefault();
            var module = $(this).data('module');
            loadModule(module);
        });

        function loadModule(module) {
            var url;
            switch(module) {
                case 'dashboard':
                    url = 'dashboard-content.php';
                    break;
                case 'manage-courses':
                    url = 'manage-courses.php';
                    break;
                case 'manage-questions':
                    url = 'manage-questions.php';
                    break;
                case 'reports':
                    url = 'reports.php';
                    break;
                default:
                    url = 'dashboard-content.php';
            }
            
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#mainContent').html(response);
                    // Update the URL without reloading the page
                    history.pushState({module: module}, "", "dashboard.php?module=" + module);
                },
                error: function() {
                    alert('Error loading module.');
                }
            });
        }

        // Handle browser back/forward buttons
        $(window).on('popstate', function(event) {
            var state = event.originalEvent.state;
            if (state && state.module) {
                loadModule(state.module);
            } else {
                loadModule('dashboard');
            }
        });

        // Handle "Edit Profile" link click
        $('a[onclick="loadEditProfileForm()"]').on('click', function(e) {
            e.preventDefault();
            loadEditProfileForm();
        });

        function loadEditProfileForm() {
            $.ajax({
                url: 'edit-profile.php',
                type: 'GET',
                success: function(response) {
                    $('#mainContent').html(response);
                },
                error: function() {
                    alert('Error loading edit profile form.');
                }
            });
        };

        $(document).on('submit', '#editProfileForm', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'edit-profile.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#userName, .username-display').text(response.username);
                        loadDashboardContent(); // Return to dashboard after successful update
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating the profile.');
                }
            });
        });
    });
    </script>
    <script src="../assets/js/teacher-script.js"></script>
</body>
</html>