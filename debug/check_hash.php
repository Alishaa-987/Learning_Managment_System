<?php
require_once 'config.php';

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT password FROM students WHERE email = ?");
$stmt->execute(['admin@university.edu']); // exact email from DB
$hash = $stmt->fetchColumn();

if (!$hash) {
    echo "Admin not found";
    exit;
}

if (password_verify('YourPlainPassword', $hash)) {
    echo "Password matches!";
} else {
    echo "Password does NOT match!";
}