<?php $title = $title ?? 'Smart Complaint Desk'; $admin = !empty($_SESSION['admin_id']); ?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Smart Complaint Management System with automatic escalation.">
<title><?= e($title) ?></title><link rel="stylesheet" href="<?= $admin ? '../' : '' ?>assets/css/style.css">
</head><body><a class="skip-link" href="#main-content">Skip to content</a>
<header class="topbar"><a class="brand" href="<?= $admin ? 'dashboard.php' : 'user_dashboard.php' ?>"><span class="brand-mark">SC</span><span>Smart Complaint Desk</span></a>
<nav><?php if ($admin): ?><span class="signed"><?= e($_SESSION['admin_name'] ?? 'Admin') ?></span><a href="logout.php">Log out</a><?php else: ?><a href="user_dashboard.php">Dashboard</a><a href="report_incident.php">Report</a><a href="logout.php">Log out</a><?php endif; ?></nav></header>
<main id="main-content" class="page-wide">
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
