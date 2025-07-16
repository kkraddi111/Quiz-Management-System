<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';

$student_id = $_SESSION['user_id'];
$success = $error = '';

// Handle course enrollment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll'])) {
    $course_id = $conn->real_escape_string($_POST['course_id']);
    
    // Check if already enrolled
    $check_enrollment = $conn->query("SELECT * FROM enrollments WHERE student_id = '$student_id' AND course_id = '$course_id'");
    if ($check_enrollment->num_rows > 0) {
        $error = "You are already enrolled in this course.";
    } else {
        $sql = "INSERT INTO enrollments (student_id, course_id) VALUES ('$student_id', '$course_id')";
        if ($conn->query($sql) === TRUE) {
            $success = "Successfully enrolled in the course.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Fetch available courses (not enrolled)
$courses = $conn->query("
    SELECT c.* FROM courses c
    WHERE c.id NOT IN (
        SELECT course_id FROM enrollments WHERE student_id = '$student_id'
    )
    ORDER BY c.name
");

?>

<h2>Enroll in Courses</h2>

<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Course Name</th>
            <th>Description</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($course = $courses->fetch_assoc()): ?>
            <tr>
                <td><?php echo $course['name']; ?></td>
                <td><?php echo $course['description']; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                        <button type="submit" name="enroll">Enroll</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="index.php">Back to Dashboard</a></p>

<?php require_once '../includes/footer.php'; ?>