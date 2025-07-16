<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch enrolled courses for the student
$courses_query = "SELECT c.* FROM courses c 
                  JOIN enrollments e ON c.id = e.course_id 
                  WHERE e.user_id = ?";
$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param("i", $student_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

$page_title = "My Courses";
require_once '../includes/student_header.php';

?>

<style>
.card {
    transition: transform 0.3s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: #ffffff;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    color: #ffffff;
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}
</style>

<div class="container mt-4">
    <h1>My Courses</h1>
    
    <?php if ($courses_result->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php while ($course = $courses_result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if (!empty($course['image'])): ?>
                            <?php
                            $image_path = "../uploads/" . $course['image'];
                            if (file_exists($image_path)):
                            ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['name']); ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-light text-center py-5">Image not found</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="card-img-top bg-light text-center py-5">No image available</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                        </div>
                        <div class="card-footer">
                            <a href="view-course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100">View Course</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>You are not enrolled in any courses yet.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
