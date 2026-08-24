<?php
// Database connection settings for XAMPP (default local setup)
$host = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "complaint_system";

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
