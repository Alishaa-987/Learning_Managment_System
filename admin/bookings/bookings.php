<?php
require_once '../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure Admin
// Ensure Admin
requireAdmin();

// Double check role from DB for security (Optional but good)
// $pdo is not set yet, requireAdmin initializes nothing variables wise but checks session.
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT role FROM students WHERE stu_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$db_role = $stmt->fetchColumn();

if ($db_role !== 'admin') {
    die("Access Denied: You are not an Admin. Role found: " . htmlspecialchars($db_role));
}



// Handle Actions
if (isset($_POST['action']) && isset($_POST['booking_id'])) {
    $action = $_POST['action'];
    $booking_id = $_POST['booking_id'];
    
    if ($action === 'approve') {
        $status = 'Approved';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
    }
    
    if (isset($status)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->execute([$status, $booking_id]);
    }
    
    // Redirect to self to prevent resubmit
    header("Location: bookings.php");
    exit();
}

// Fetch requests
// "ju admin ka booking page he usme wu request dekh sakta he ju usko focal person ki taraf se aein hen with department name"
// Join bookings with students to get name/dept, and rooms for room name.
$sql = "
    SELECT b.*, s.name as focal_person, s.department, r.room_name 
    FROM bookings b
    JOIN students s ON b.user_id = s.stu_id
    JOIN rooms r ON b.room_id = r.room_id
    ORDER BY b.created_at DESC
";
try {
    $stmt = $pdo->query($sql);
    $bookings = $stmt->fetchAll() ?: [];
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Manage Bookings - Admin</title>
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f9f9f9;
            color: #6c4040;
            font-family: 'Cinzel', serif;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .status-pending { background: #ffe0b2; color: #e65100; }
        .status-approved { background: #c8e6c9; color: #2e7d32; }
        .status-rejected { background: #ffcdd2; color: #c62828; }
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-right: 5px;
        }
        .btn-approve { background: #2e7d32; color: #fff; }
        .btn-reject { background: #c62828; color: #fff; }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="../ChatGPT Image Oct 1, 2025, 10_03_37 AM.png" class="logo" alt="University Logo">
        <ul>
            <li><a href="../index.html">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="departments.php">Departments</a></li>
            <li><a href="faculty.php">Faculty</a></li>
            <li><a href="bookings.php">Manage Bookings</a></li>
            <li><a href="add_student.php">Add User</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>


    <div class="admin-container">
        <h2 style="color: #6c4040; font-family: 'Cinzel', serif; margin-bottom: 1.5rem;">Booking Requests</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Focal Person</th>
                    <th>Dept</th>
                    <th>Room</th>
                    <th>Date & Time</th>
                    <th>Title / Persons</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?php echo $b['booking_id']; ?></td>
                    <td><?php echo htmlspecialchars($b['focal_person']); ?></td>
                    <td><?php echo htmlspecialchars($b['department']); ?></td>
                    <td><?php echo htmlspecialchars($b['room_name']); ?></td>
                    <td>
                        <?php echo date('M d, Y', strtotime($b['booking_date'])); ?><br>
                        <small><?php echo date('g:ia', strtotime($b['time_slot_start'])) . ' - ' . date('g:ia', strtotime($b['time_slot_end'])); ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($b['title']); ?></strong><br>
                        <small><?php echo $b['attendees']; ?> persons</small>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($b['status']); ?>">
                            <?php echo $b['status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($b['status'] === 'Pending'): ?>
                        <div style="display: flex; gap: 8px;">
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="action-btn btn-approve">Grant</button>
                            </form>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="action-btn btn-reject">Deny</button>
                            </form>
                        </div>
                        <?php else: ?>
                            <!-- Maybe Allow revert? Request implied just view and action on new ones? -->
                            <!-- "reject ... it doesnt mean ke wu delete na hu balke likha show hu ke ye reject hua he" -->
                            <!-- So showing status is enough. -->
                            <!-- If Admin wants to change mind? Let's allow edit for flexibility -->
                            <?php if ($b['status'] === 'Approved'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="action-btn btn-reject">Revoke</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
