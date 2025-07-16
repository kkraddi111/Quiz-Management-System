<?php
// Database configuration
$servername = "localhost";
$username = "root";  // Default username for XAMPP
$password = "shiva";      // Default password for XAMPP is blank
$dbname = "quizhub-schema"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define base URL
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project/";

// Make sure this variable is available to other scripts
if (!defined('BASE_URL')) {
    define('BASE_URL', $base_url);
}
?>