<?php
/**
 * Database Update Script
 * Updates the database schema to include faculty role
 * Run once: php update_database.php
 */

require_once 'config.php';

echo "=== Database Update for Faculty Role ===\n\n";

try {
    $pdo = getDBConnection();

    // Add the role column
    echo "Adding role column...\n";
    $pdo->exec("ALTER TABLE students ADD COLUMN role ENUM('student', 'admin', 'faculty') DEFAULT 'student'");

    echo "✓ Role column added successfully!\n\n";

    // Create news table
    echo "Creating news table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS news (
        news_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES students(stu_id) ON DELETE CASCADE,
        INDEX idx_created_by (created_by),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create events table
    echo "Creating events table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        event_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        event_date DATE NOT NULL,
        event_time TIME,
        location VARCHAR(255),
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES students(stu_id) ON DELETE CASCADE,
        INDEX idx_created_by (created_by),
        INDEX idx_event_date (event_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create notices table
    echo "Creating notices table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS notices (
        notice_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        expiry_date DATE,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES students(stu_id) ON DELETE CASCADE,
        INDEX idx_created_by (created_by),
        INDEX idx_priority (priority),
        INDEX idx_expiry_date (expiry_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "✓ Tables created successfully!\n";

    echo "\n=== Database Update Complete ===\n";
    echo "Role-based access removed. All users can login and manage content!\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>