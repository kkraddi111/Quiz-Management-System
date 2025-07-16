<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch admin name
$admin_id = $_SESSION['user_id'];
$admin_result = $conn->query("SELECT username FROM users WHERE id = '$admin_id'");
$admin_name = $admin_result->fetch_assoc()['username'];

// Check if course ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-courses.php");
    exit();
}

$course_id = intval($_GET['id']);

// Fetch course data
$course_result = $conn->query("SELECT id, name, description, image FROM courses WHERE id = $course_id");
$course = $course_result->fetch_assoc();

// Fetch assigned teachers
$assigned_teachers_result = $conn->query("SELECT teacher_id FROM course_teachers WHERE course_id = $course_id");
$assigned_teachers = [];
while ($row = $assigned_teachers_result->fetch_assoc()) {
    $assigned_teachers[] = $row['teacher_id'];
}

// Fetch all teachers
$teachers_query = "SELECT id, username FROM users WHERE role = 'teacher' AND status = 'approved'";
$teachers_result = $conn->query($teachers_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $teacher_ids = isset($_POST['teacher_ids']) ? $_POST['teacher_ids'] : [];

    // Handle image upload
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] == 0) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["course_image"]["name"];
        $filetype = $_FILES["course_image"]["type"];
        $filesize = $_FILES["course_image"]["size"];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) die("Error: File size is larger than the allowed limit.");

        // Verify MIME type of the file
        if (in_array($filetype, $allowed)) {
            // Check whether file exists before uploading it
            $new_filename = uniqid() . "." . $ext;
            if (move_uploaded_file($_FILES["course_image"]["tmp_name"], $upload_dir . $new_filename)) {
                // Delete old image if exists
                if (!empty($course['image']) && file_exists($upload_dir . $course['image'])) {
                    unlink($upload_dir . $course['image']);
                }
                $image_path = $new_filename;
                
                // Update image in database
                $update_image_sql = "UPDATE courses SET image = ? WHERE id = ?";
                $update_image_stmt = $conn->prepare($update_image_sql);
                $update_image_stmt->bind_param("si", $image_path, $course_id);
                $update_image_stmt->execute();
            } else {
                echo "Error: There was a problem uploading your file. Please try again.";
            }
        } else {
            echo "Error: There was a problem uploading your file. Please try again.";
        }
    }

    $update_sql = "UPDATE courses SET name = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $name, $description, $course_id);
    if ($stmt->execute()) {
        // Update course teachers
        $conn->query("DELETE FROM course_teachers WHERE course_id = $course_id");
        foreach ($teacher_ids as $teacher_id) {
            $conn->query("INSERT INTO course_teachers (course_id, teacher_id) VALUES ($course_id, $teacher_id)");
        }

        $_SESSION['success_message'] = "Course updated successfully.";
        header("Location: manage-courses.php");
        exit();
    } else {
        $error_message = "Error updating course: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Quiz Hub Admin</title>
    <link rel="stylesheet" href="../assets/css/admin-styles.css">
    <link rel="stylesheet" href="../assets/css/manage-courses.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Include top bar and sidebar here -->
        <div class="main-content" id="mainContent">
            <h2>Edit Course</h2>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data" class="course-form">
                <div class="form-group">
                    <label for="name">Course Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($course['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="teacher_ids">Assign Teachers:</label>
                    <select name="teacher_ids[]" multiple required>
                        <?php 
                        $teachers_result->data_seek(0); // Reset the result pointer
                        while ($teacher = $teachers_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $teacher['id']; ?>" <?php echo in_array($teacher['id'], $assigned_teachers) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($teacher['username']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="course_image">Course Image:</label>
                    <input type="file" name="course_image" accept="image/*">
                    <?php if (!empty($course['image'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($course['image']); ?>" alt="Current course image" style="max-width: 200px;">
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn save-btn">Update Course</button>
                    <a href="manage-courses.php" class="btn cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>