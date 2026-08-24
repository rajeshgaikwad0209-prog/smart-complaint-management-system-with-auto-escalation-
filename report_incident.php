<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report Incident | Smart Complaint Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="brand"><span class="dot"></span> Smart Complaint Desk</div>
  <div>
    <a href="user_dashboard.php">Dashboard</a>
    <a href="logout.php">Log Out</a>
  </div>
</div>

<div class="page">
  <div class="card">
    <h1>Report a Complaint</h1>
    <p class="subtitle">Tell us what's wrong. Complaints left unresolved for more than 3 days are automatically escalated so they don't get forgotten.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="submit.php" method="POST">
      <div class="field">
        <label for="category">Category</label>
        <select id="category" name="category" required>
          <option value="" disabled selected>Choose a category</option>
          <option value="Electricity">Electricity</option>
          <option value="Water">Water</option>
          <option value="Cleaning">Cleaning</option>
          <option value="Internet">Internet</option>
          <option value="Other">Other</option>
        </select>
      </div>

      <div class="field">
        <label for="description">Describe the issue</label>
        <textarea id="description" name="description" placeholder="Give as much detail as you can..." required></textarea>
      </div>

      <button type="submit" class="btn btn-primary">Submit Complaint</button>
    </form>
  </div>
</div>

</body>
</html>
