<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();

    echo "Notices table structure:\n";
    $stmt = $pdo->query("DESCRIBE notices");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . " - " . ($row['Key'] ? $row['Key'] : ' ') . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>