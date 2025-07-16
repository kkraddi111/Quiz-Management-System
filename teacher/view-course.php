<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo "Unauthorized access";
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Check if course ID is provided
if (!isset($_GET['id'])) {
    echo "No course specified";
    exit();
}

$course_id = intval($_GET['id']);

// Fetch course data
$course_query = "SELECT c.* FROM courses c 
                 JOIN course_teachers ct ON c.id = ct.course_id 
                 WHERE c.id = ? AND ct.teacher_id = ?";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("ii", $course_id, $teacher_id);
$stmt->execute();
$course_result = $stmt->get_result();
$course = $course_result->fetch_assoc();

if (!$course) {
    echo "Course not found or you don't have permission to view it";
    exit();
}
?>

<h2><?php echo htmlspecialchars($course['name']); ?></h2>
<div class="course-details">
    <h3>Description:</h3>
    <div class="course-description"><?php echo nl2br(htmlspecialchars($course['description'])); ?></div>
    <?php if (!empty($course['image'])): ?>
        <h3>Course Image:</h3>
        <img src="../uploads/<?php echo htmlspecialchars($course['image']); ?>" alt="Course Image" style="max-width: 300px;">
    <?php endif; ?>
</div>
<button onclick="loadModule('manage-courses')" class="btn back-btn">Back to Courses</button>

<script>
function loadModule(module) {
    $('#mainContent').load(module + '.php');
}
</script>