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
    echo "Course not found or you don't have permission to edit it";
    exit();
}

?>

<h2>Edit Course</h2>
<form id="editCourseForm" enctype="multipart/form-data" class="course-form">
    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
    <div class="form-group">
        <label for="name">Course Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($course['name']); ?>" required>
    </div>
    <div class="form-group">
        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($course['description']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="course_image">Course Image:</label>
        <input type="file" name="course_image" accept="image/*">
        <?php if (!empty($course['image'])): ?>
            <img src="../uploads/<?php echo htmlspecialchars($course['image']); ?>" alt="Current course image" style="max-width: 200px;">
        <?php endif; ?>
    </div>
    <div class="form-group">
        <button type="submit" class="btn save-btn" style="background-color: #4CAF50; color: white;">Update Course</button>
        <button type="button" class="btn cancel-btn" onclick="loadModule('manage-courses')" style="background-color: #f44336; color: white;">Cancel</button>
    </div>
</form>

<style>
    .course-form {
        max-width: 600px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
    }
    .form-group input[type="text"],
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .btn {
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }
    .btn:hover {
        opacity: 0.8;
    }
</style>

<script>
$(document).ready(function() {
    $('#editCourseForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'update_course.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    loadModule('manage-courses');
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while updating the course: ' + error);
            }
        });
    });
});

// Add this function to ensure loadModule is defined
function loadModule(module) {
    $('#mainContent').load(module + '.php');
}
</script>