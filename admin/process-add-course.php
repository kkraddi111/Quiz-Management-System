<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $teacher_ids = isset($_POST['teacher_ids']) ? $_POST['teacher_ids'] : [];

    // Handle image upload
    $image_path = '';
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
                $image_path = $new_filename;
            } else {
                echo "Error: There was a problem uploading your file. Please try again.";
            }
        } else {
            echo "Error: There was a problem uploading your file. Please try again.";
        }
    }

    // Insert course into database
    $stmt = $conn->prepare("INSERT INTO courses (name, description, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $course_name, $course_description, $image_path);

    if ($stmt->execute()) {
        $course_id = $stmt->insert_id;
        
        // Insert course-teacher relationships
        foreach ($teacher_ids as $teacher_id) {
            $conn->query("INSERT INTO course_teachers (course_id, teacher_id) VALUES ($course_id, $teacher_id)");
        }

        echo "Course added successfully!";
    } else {
        echo "Error adding course: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>