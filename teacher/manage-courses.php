<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Fetch courses taught by this teacher
$courses_query = "SELECT c.id, c.name, c.image, c.description FROM courses c 
                  JOIN course_teachers ct ON c.id = ct.course_id 
                  WHERE ct.teacher_id = ?";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses_result = $stmt->get_result();

?>

<h2>Manage Courses</h2>
<div class="course-list">
    <table>
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Description</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($courses_result->num_rows > 0): ?>
                <?php while ($course = $courses_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['name']); ?></td>
                        <td>
                            <div class="course-description">
                                <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($course['image'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($course['image']); ?>" alt="Course Image" style="max-width: 100px; max-height: 100px;">
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </td>
                        <td class="action-buttons">
                            <button onclick="loadEditCourse(<?php echo $course['id']; ?>)" class="btn edit-btn">Edit</button>
                            <button onclick="loadViewCourse(<?php echo $course['id']; ?>)" class="btn view-btn">View</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">You are not assigned to any courses yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function loadEditCourse(courseId) {
    $('#mainContent').load('edit-course.php?id=' + courseId);
}

function loadViewCourse(courseId) {
    $('#mainContent').load('view-course.php?id=' + courseId);
}
</script>