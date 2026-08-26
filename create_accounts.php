<?php require_once 'config.php';
$adminPassword=password_hash('rajesh123',PASSWORD_DEFAULT);$userPassword=password_hash('yash123',PASSWORD_DEFAULT);
$a=$conn->prepare("INSERT INTO admin(name,username,password) VALUES('Rajesh Gaikwad','rajesh',?) ON DUPLICATE KEY UPDATE name=VALUES(name),password=VALUES(password)");$a->bind_param('s',$adminPassword);$a->execute();
$u=$conn->prepare("INSERT INTO users(name,email,password) VALUES('Demo User','yash@example.com',?) ON DUPLICATE KEY UPDATE name=VALUES(name),password=VALUES(password)");$u->bind_param('s',$userPassword);$u->execute();echo 'Demo accounts are ready. Delete this file after setup.';
