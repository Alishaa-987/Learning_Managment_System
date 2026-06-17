<?php
/**
 * Notices Management
 * Admin can add/edit/delete notices
 */

require_once '../config.php';
requireAdmin();

$pdo = getDBConnection();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notice_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
        $stmt->execute([$notice_id]);
        $success = 'Notice deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to delete notice!';
    }
}

// Handle add/edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $notice_id = isset($_POST['notice_id']) ? (int)$_POST['notice_id'] : null;

    // Validation
    if (empty($title) || empty($description)) {
        $error = 'Title and description are required!';
    } else {
        try {
            if ($notice_id) {
                // Update existing notice
                $stmt = $pdo->prepare("UPDATE notices SET title = ?, description = ? WHERE id = ?");
                $stmt->execute([$title, $description, $notice_id]);
                $success = 'Notice updated successfully!';
            } else {
                // Add new notice
                $stmt = $pdo->prepare("INSERT INTO notices (title, description) VALUES (?, ?)");
                $stmt->execute([$title, $description]);
                $success = 'Notice added successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all notices
$stmt = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC");
$notices = $stmt->fetchAll();

// Get notice for editing (if edit parameter is set)
$edit_notice = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM notices WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_notice = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../style.css">
<title>Notices Management - Admin</title>
<style>
.dashboard-container { max-width:1400px; margin:3rem auto; padding:2rem; }
.dashboard-header { background:linear-gradient(135deg,#6c4040 0%,#8b5555 100%); color:#fff; padding:2rem; border-radius:15px; margin-bottom:2rem; }
.notices-table { background:#fff; border-radius:10px; box-shadow:0 5px 20px rgba(108,64,64,0.1); overflow:hidden; margin-bottom:2rem; }
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
.form-group input, .form-group textarea { width:100%; padding:0.8rem; border:2px solid #6c4040; border-radius:8px; font-size:1rem; font-family:'Source Sans Pro', sans-serif; }
.form-group textarea { resize:vertical; min-height:150px; }
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
        <h1 style="margin:0;">Notices Management</h1>
        <p style="margin:0.5rem 0 0 0; opacity:0.9;">Manage university notices</p>
    </div>

    <?php if(isset($success)): ?><div class="alert alert-success"><?=htmlspecialchars($success)?></div><?php endif; ?>
    <?php if(isset($error)): ?><div class="alert alert-error"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <div class="form-container">
        <h2 style="font-family:'Cinzel', serif; color:#6c4040; margin-bottom:1.5rem;"><?= $edit_notice ? 'Edit Notice' : 'Add New Notice'; ?></h2>
        <form method="POST">
            <?php if($edit_notice): ?><input type="hidden" name="notice_id" value="<?=$edit_notice['id']?>"><?php endif; ?>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required value="<?=htmlspecialchars($edit_notice['title'] ?? '')?>">
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" required><?=htmlspecialchars($edit_notice['description'] ?? '')?></textarea>
            </div>
            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;"><?= $edit_notice?'Update Notice':'Add Notice';?></button>
                <?php if($edit_notice): ?><a href="notices.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancel</a><?php endif;?>
            </div>
        </form>
    </div>

    <div class="notices-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Title</th><th>Description</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($notices)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:2rem;">No notices found.</td></tr>
                <?php else: ?>
                    <?php foreach($notices as $notice): ?>
                        <tr>
                            <td><?=htmlspecialchars($notice['id'])?></td>
                            <td><?=htmlspecialchars($notice['title'])?></td>
                            <td><?=htmlspecialchars(substr($notice['description'],0,100))?><?=strlen($notice['description'])>100?'...':''?></td>
                            <td><?=date('M d, Y', strtotime($notice['created_at']))?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?=$notice['id']?>" class="btn-small btn-edit">Edit</a>
                                    <a href="?delete=<?=$notice['id']?>" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this notice?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach;?>
                <?php endif;?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
