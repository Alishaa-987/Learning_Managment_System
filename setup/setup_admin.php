<?php
/**
 * Admin Setup Script
 * Use this to create or update admin user
 * Run once: php setup_admin.php
 */

require_once 'config.php';

echo "=== Admin User Setup ===\n\n";

// Get admin details
$email = readline("Enter admin email (default: admin@university.edu): ");
if (empty($email)) {
    $email = 'admin@university.edu';
}

$password = readline("Enter admin password (default: admin123): ");
if (empty($password)) {
    $password = 'admin';
}

$name = readline("Enter admin name (default: Admin User): ");
if (empty($name)) {
    $name = 'Admin User';
}

try {
    $pdo = getDBConnection();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT stu_id FROM students WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE students SET name = ?, password = ? WHERE email = ? AND role = 'admin'");
        $stmt->execute([$name, $hashed_password, $email]);
        echo "✓ Admin user updated successfully!\n";
    } else {
        // Create new admin
        $stmt = $pdo->prepare("INSERT INTO students (name, father_name, dob, cnic, gender, email, adm_date, ag_no, department, degree, phone_no, address, password, role) VALUES (?, 'Admin', '1990-01-01', '12345-1234567-1', 'Male', ?, '2020-01-01', 'ADMIN001', 'Administration', 'Administration', '03001234567', 'University Campus', ?, 'admin')");
        $stmt->execute([$name, $email, $hashed_password]);
        echo "✓ Admin user created successfully!\n";
    }
    
    echo "\nAdmin Credentials:\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "\n⚠️  Please save these credentials securely!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Setup Complete ===\n";

