<?php
require_once 'config.php';
$pdo = getDBConnection();
// Ensure the default faculty user is a focal person
$stmt = $pdo->prepare("UPDATE students SET is_focal_person = 1, password = ? WHERE email = 'faculty@university.edu'");
$stmt->execute([password_hash('faculty123', PASSWORD_DEFAULT)]);
echo "Successfully updated 'faculty@university.edu' to be a Focal Person with password 'faculty123'.<br>";
echo "You can now log in as this user to test booking.";
?>
