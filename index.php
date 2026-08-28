<?php require_once 'includes/config.php'; redirect(!empty($_SESSION['user_id']) ? 'user_dashboard.php' : 'login.php');
