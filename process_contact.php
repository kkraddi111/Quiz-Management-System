<?php
session_start();
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['contact_error'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = "Invalid email format.";
    } else {
        // Insert the message into the database
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['contact_success'] = "Your message has been sent successfully.";
            header("Location: contact-successful.php");
            exit();
        } else {
            $_SESSION['contact_error'] = "Error: " . $conn->error;
        }
    }

    // If there was an error, redirect back to the contact form
    if (isset($_SESSION['contact_error'])) {
        header("Location: contact.php");
        exit();
    }
}
?>