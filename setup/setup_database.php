<?php
/**
 * Database Setup Script
 * Execute the database.sql file to create tables and insert sample data
 */

require_once 'config.php';

echo "=== Database Setup ===\n\n";

try {
    $pdo = getDBConnection();

    // Read the SQL file
    $sql = file_get_contents('database.sql');

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $pdo->beginTransaction();

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $pdo->exec($statement);
        }
    }

    $pdo->commit();

    echo "✓ Database tables created successfully!\n";
    echo "✓ Sample data inserted!\n";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Setup Complete ===\n";
echo "You can now access the admin panel and use the new features.\n";