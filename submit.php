<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: report_incident.php');
    exit;
}

$userId = $_SESSION['user_id'];
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');

$validCategories = ['Electricity', 'Water', 'Cleaning', 'Internet', 'Other'];

if ($description === '' || !in_array($category, $validCategories)) {
    $_SESSION['error'] = "Please fill in all fields correctly.";
    header('Location: report_incident.php');
    exit;
}

$stmt = $conn->prepare("INSERT INTO complaints (user_id, category, description) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $userId, $category, $description);

if ($stmt->execute()) {
    $_SESSION['success'] = "Your complaint has been submitted. We'll get back to you soon.";
} else {
    $_SESSION['error'] = "Something went wrong. Please try again.";
}

$stmt->close();
$conn->close();

header('Location: user_dashboard.php');
exit;
?>
