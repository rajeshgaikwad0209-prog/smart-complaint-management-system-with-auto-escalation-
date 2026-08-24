<?php
// RUN THIS FILE ONCE in your browser after importing database.sql:
// http://localhost/complaint-box/includes/create_accounts.php
// Creates the admin (Rajesh Gaikwad) and a demo user (Yash Haldankar).
// DELETE this file after running it once, for security.

require_once 'config.php';

echo "<h2>Account Setup</h2>";

// ---------- Create Admin: Rajesh Gaikwad ----------
$adminName = "Rajesh Gaikwad";
$adminUsername = "rajesh";
$adminPassword = password_hash("rajesh123", PASSWORD_DEFAULT);

$check = $conn->prepare("SELECT id FROM admin WHERE username = ?");
$check->bind_param("s", $adminUsername);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "<p>Admin account already exists.</p>";
} else {
    $stmt = $conn->prepare("INSERT INTO admin (name, username, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $adminName, $adminUsername, $adminPassword);
    if ($stmt->execute()) {
        echo "<p><strong>Admin created:</strong> Rajesh Gaikwad — username: <code>rajesh</code> / password: <code>rajesh123</code></p>";
    } else {
        echo "<p>Error creating admin: " . $conn->error . "</p>";
    }
    $stmt->close();
}
$check->close();

// ---------- Create demo User: Yash Haldankar ----------
$userName = "Yash Haldankar";
$userEmail = "yash@example.com";
$userPassword = password_hash("yash123", PASSWORD_DEFAULT);

$check2 = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check2->bind_param("s", $userEmail);
$check2->execute();
$check2->store_result();

if ($check2->num_rows > 0) {
    echo "<p>User account already exists.</p>";
} else {
    $stmt2 = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt2->bind_param("sss", $userName, $userEmail, $userPassword);
    if ($stmt2->execute()) {
        echo "<p><strong>User created:</strong> Yash Haldankar — email: <code>yash@example.com</code> / password: <code>yash123</code></p>";
    } else {
        echo "<p>Error creating user: " . $conn->error . "</p>";
    }
    $stmt2->close();
}
$check2->close();

echo "<hr><p><strong>Important:</strong> Delete this file (create_accounts.php) now for security.</p>";

$conn->close();
?>
