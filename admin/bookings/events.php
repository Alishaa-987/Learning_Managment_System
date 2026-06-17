<?php
require_once '../config.php';
requireAdmin();

$pdo = getDBConnection();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $event_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        header("Location: events.php?success=Event+deleted+successfully!");
        exit;
    } catch (PDOException $e) {
        header("Location: events.php?error=Failed+to+delete+event!");
        exit;
    }
}

// Handle add/edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $department_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;

    if (empty($title) || empty($description) || empty($event_date)) {
        $error = 'Title, description, and date are required!';
    } else {
        try {
            if ($event_id) {
                $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, department_id = ? WHERE id = ?");
                $stmt->execute([$title, $description, $event_date, $department_id, $event_id]);
                $success = 'Event updated successfully!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, department_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $description, $event_date, $department_id]);
                $success = 'Event added successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all events with department names
$query = "SELECT e.*, d.dept_name FROM events e LEFT JOIN departments d ON e.department_id = d.dept_id ORDER BY e.event_date DESC";
$stmt = $pdo->query($query);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get departments for dropdown
$dept_stmt = $pdo->query("SELECT dept_id, dept_name FROM departments ORDER BY dept_name");
$departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get event for editing
$edit_event = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../style.css">
<title>Events Management - Admin</title>
<style>
.dashboard-container { max-width: 1400px; margin:3rem auto; padding:2rem; }
.dashboard-header { background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%); color:#fff; padding:2rem; border-radius:15px; margin-bottom:2rem; }
.events-table { background:#fff; border-radius:10px; box-shadow:0 5px 20px rgba(108,64,64,0.1); overflow:hidden; margin-bottom:2rem; }
table { width:100%; border-collapse:collapse; }
th { background:#6c4040; color:#fff; padding:1rem; text-align:left; }
td { padding:1rem; border-bottom:1px solid #eee; }
tr:hover { background:#f9f9f9; }
.action-buttons { display:flex; gap:0.5rem; }
.btn-small { padding:0.5rem 1rem; font-size:0.9rem; border-radius:5px; text-decoration:none; display:inline-block; }
.btn-edit { background:#6c4040; color:#fff; }
.btn-delete { background:#c33; color:#fff; }
.form-container { background:#fff; padding:2rem; border-radius:10px; box-shadow:0 5px 20px rgba(108,64,64,0.1); margin-bottom:2rem; }
.form-group { margin-bottom:1.5rem; }
.form-group label { display:block; margin-bottom:0.5rem; color:#6c4040; font-weight:600; }
.form-group input, .form-group select, .form-group textarea { width:100%; padding:0.8rem; border:2px solid #6c4040; border-radius:8px; font-size:1rem; font-family:'Source Sans Pro', sans-serif; }
.form-group textarea { resize:vertical; min-height:100px; }
.alert { padding:1rem; border-radius:8px; margin-bottom:1.5rem; }
.alert-success { background:#efe; color:#3c3; }
.alert-error { background:#fee; color:#c33; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
@media (max-width:768px) { .form-row { grid-template-columns:1fr; } }
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
        <li><a href="events.php">Events</a></li>
        <li><a href="notices.php">Notices</a></li>
        <li><a href="news.php">News</a></li>
        <li><a href="add_student.php">Add Student</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 style="margin:0;">Events Management</h1>
        <p style="margin:0.5rem 0 0 0; opacity:0.9;">Manage university events</p>
    </div>

    <?php if(isset($success)): ?><div class="alert alert-success"><?=htmlspecialchars($success)?></div><?php endif; ?>
    <?php if(isset($error)): ?><div class="alert alert-error"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <div class="form-container">
        <h2 style="font-family:'Cinzel', serif; color:#6c4040; margin-bottom:1.5rem;"><?= $edit_event ? 'Edit Event' : 'Add New Event'; ?></h2>
        <form method="POST">
            <?php if(isset($edit_event['id'])): ?><input type="hidden" name="event_id" value="<?=htmlspecialchars($edit_event['id'])?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required value="<?=htmlspecialchars($edit_event['title'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label>Event Date *</label>
                    <input type="date" name="event_date" required value="<?=htmlspecialchars($edit_event['event_date'] ?? '')?>">
                </div>
            </div>

            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" required><?=htmlspecialchars($edit_event['description'] ?? '')?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Department (Optional)</label>
                    <select name="department_id">
                        <option value="">All Departments</option>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?=htmlspecialchars($dept['dept_id'])?>" <?= (isset($edit_event['department_id']) && $edit_event['department_id']==$dept['dept_id'])?'selected':''?>><?=htmlspecialchars($dept['dept_name'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;"><?= $edit_event?'Update Event':'Add Event';?></button>
                <?php if($edit_event): ?><a href="events.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="events-table">
        <table>
            <thead>
                <tr>
                    <th>Event ID</th><th>Title</th><th>Date</th><th>Department</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($events)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:2rem;">No events found.</td></tr>
                <?php else: ?>
                    <?php foreach($events as $event): ?>
                        <tr>
                            <td><?=htmlspecialchars($event['id'] ?? '')?></td>
                            <td><?=htmlspecialchars($event['title'] ?? '')?></td>
                            <td><?=isset($event['event_date']) ? date('M d, Y', strtotime($event['event_date'])) : ''?></td>
                            <td><?=htmlspecialchars($event['dept_name'] ?? 'All Departments')?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?=htmlspecialchars($event['id'] ?? '')?>" class="btn-small btn-edit">Edit</a>
                                    <a href="?delete=<?=htmlspecialchars($event['id'] ?? '')?>" class="btn-small btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
