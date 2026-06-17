<?php
require_once 'config.php';

$pdo = getDBConnection();

try {
    // Create rooms table
    $sqlRooms = "CREATE TABLE IF NOT EXISTS rooms (
        room_id INT AUTO_INCREMENT PRIMARY KEY,
        room_name VARCHAR(100) NOT NULL,
        capacity INT DEFAULT 0,
        status ENUM('Active', 'Maintenance') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sqlRooms);
    echo "Rooms table created (or already exists).<br>";

    // Insert active rooms if not exist
    $defaultRooms = ['Conference Room', 'Seminar Room', 'Auditorium', 'Senate Hall'];
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_name = ?");
    $stmtInsert = $pdo->prepare("INSERT INTO rooms (room_name) VALUES (?)");

    foreach ($defaultRooms as $room) {
        $stmtCheck->execute([$room]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsert->execute([$room]);
            echo "Inserted room: $room<br>";
        }
    }

    // Create bookings table
    $sqlBookings = "CREATE TABLE IF NOT EXISTS bookings (
        booking_id INT AUTO_INCREMENT PRIMARY KEY,
        room_id INT NOT NULL,
        user_id INT NOT NULL,
        booking_date DATE NOT NULL,
        time_slot_start TIME NOT NULL,
        time_slot_end TIME NOT NULL,
        title VARCHAR(255) NOT NULL,
        attendees INT DEFAULT 0,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES students(stu_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sqlBookings);
    echo "Bookings table created (or already exists).<br>";

    // Make sure 'is_focal_person' column exists in students table (it was in the provided SQL structure, but good to double check or ensure existing data is correct)
    // Check/Add 'is_focal_person' column to students table
    $colCheck = $pdo->query("SHOW COLUMNS FROM students LIKE 'is_focal_person'");
    if ($colCheck->rowCount() == 0) {
        $pdo->exec("ALTER TABLE students ADD COLUMN is_focal_person TINYINT(1) DEFAULT 0");
        echo "Added 'is_focal_person' column to students table.<br>";
    } else {
        echo "'is_focal_person' column already exists.<br>";
    }

    echo "Database setup completed successfully.";


} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
