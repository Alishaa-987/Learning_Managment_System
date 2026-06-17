<?php


require_once '../config.php';
requireFaculty(); 

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $action = $_POST['action'] ?? 'add';

    if ($type === 'news') {
        if ($action === 'add') {
            $title = sanitizeInput($_POST['title']);
            $content = sanitizeInput($_POST['content']);
            $stmt = $pdo->prepare("INSERT INTO news (title, content, created_by) VALUES (?, ?, ?)");
            $stmt->execute([$title, $content, $_SESSION['user_id']]);
            $success = 'News added successfully!';
        } elseif ($action === 'edit' && isset($_POST['news_id'])) {
            $title = sanitizeInput($_POST['title']);
            $content = sanitizeInput($_POST['content']);
            $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ? WHERE news_id = ? AND created_by = ?");
            $stmt->execute([$title, $content, $_POST['news_id'], $_SESSION['user_id']]);
            $success = 'News updated successfully!';
        } elseif ($action === 'delete' && isset($_POST['news_id'])) {
            $stmt = $pdo->prepare("DELETE FROM news WHERE news_id = ? AND created_by = ?");
            $stmt->execute([$_POST['news_id'], $_SESSION['user_id']]);
            $success = 'News deleted successfully!';
        }
        if ($success) {
            header("Location: dashboard.php?tab=news&success=" . urlencode($success));
            exit();
        }
    } elseif ($type === 'event') {
        if ($action === 'add') {
            $title = sanitizeInput($_POST['title']);
            $description = sanitizeInput($_POST['description']);
            $event_date = $_POST['event_date'];
            $event_time = $_POST['event_time'] ?: null;
            $location = sanitizeInput($_POST['location']);
            $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $event_date, $event_time, $location, $_SESSION['user_id']]);
            $success = 'Event added successfully!';
        } elseif ($action === 'edit' && isset($_POST['event_id'])) {
            $title = sanitizeInput($_POST['title']);
            $description = sanitizeInput($_POST['description']);
            $event_date = $_POST['event_date'];
            $event_time = $_POST['event_time'] ?: null;
            $location = sanitizeInput($_POST['location']);
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ? WHERE event_id = ? AND created_by = ?");
            $stmt->execute([$title, $description, $event_date, $event_time, $location, $_POST['event_id'], $_SESSION['user_id']]);
            $success = 'Event updated successfully!';
        } elseif ($action === 'delete' && isset($_POST['event_id'])) {
            $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ? AND created_by = ?");
            $stmt->execute([$_POST['event_id'], $_SESSION['user_id']]);
            $success = 'Event deleted successfully!';
        }
        if ($success) {
            header("Location: dashboard.php?tab=events&success=" . urlencode($success));
            exit();
        }
    } elseif ($type === 'notice') {
        $msg = '';
        if ($action === 'add') {
            $title = sanitizeInput($_POST['title']);
            $content = sanitizeInput($_POST['content']);
            $priority = $_POST['priority'];
            $expiry_date = $_POST['expiry_date'] ?: null;
            $stmt = $pdo->prepare("INSERT INTO notices (title, content, priority, expiry_date, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $content, $priority, $expiry_date, $_SESSION['user_id']]);
            $msg = 'Notice added successfully!';
        } elseif ($action === 'edit' && isset($_POST['notice_id'])) {
            $title = sanitizeInput($_POST['title']);
            $content = sanitizeInput($_POST['content']);
            $priority = $_POST['priority'];
            $expiry_date = $_POST['expiry_date'] ?: null;
            $stmt = $pdo->prepare("UPDATE notices SET title = ?, content = ?, priority = ?, expiry_date = ? WHERE id = ? AND created_by = ?");
            $stmt->execute([$title, $content, $priority, $expiry_date, $_POST['notice_id'], $_SESSION['user_id']]);
            $msg = 'Notice updated successfully!';
        } elseif ($action === 'delete' && isset($_POST['notice_id'])) {
            $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ? AND created_by = ?");
            $stmt->execute([$_POST['notice_id'], $_SESSION['user_id']]);
            $msg = 'Notice deleted successfully!';
        }
        if ($msg) {
            header("Location: dashboard.php?tab=notices&success=" . urlencode($msg));
            exit();
        }
    }
}

$edit_news = null;
$edit_event = null;
$edit_notice = null;

if (isset($_GET['edit_news'])) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE news_id = ? AND created_by = ?");
    $stmt->execute([$_GET['edit_news'], $_SESSION['user_id']]);
    $edit_news = $stmt->fetch();
}

if (isset($_GET['edit_event'])) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? AND created_by = ?");
    $stmt->execute([$_GET['edit_event'], $_SESSION['user_id']]);
    $edit_event = $stmt->fetch();
}

if (isset($_GET['edit_notice'])) {
    $stmt = $pdo->prepare("SELECT * FROM notices WHERE id = ? AND created_by = ?");
    $stmt->execute([$_GET['edit_notice'], $_SESSION['user_id']]);
    $edit_notice = $stmt->fetch();
}

$news = $pdo->prepare("SELECT * FROM news WHERE created_by = ? ORDER BY created_at DESC");
$news->execute([$_SESSION['user_id']]);
$news_list = $news->fetchAll();

$events = $pdo->prepare("SELECT * FROM events WHERE created_by = ? ORDER BY event_date DESC");
$events->execute([$_SESSION['user_id']]);
$events_list = $events->fetchAll();

$notices = $pdo->prepare("SELECT * FROM notices WHERE created_by = ? ORDER BY created_at DESC");
$notices->execute([$_SESSION['user_id']]);
$notices_list = $notices->fetchAll();

if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Faculty Dashboard - University</title>
    <style>
        .dashboard-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 2rem;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%);
            color: #fff;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .tabs {
            display: flex;
            margin-bottom: 2rem;
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .tab-button {
            background: transparent;
            border: none;
            padding: 1rem 2.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            margin: 0 0.25rem;
            transition: all 0.3s ease;
            color: #6c4040;
        }
        .tab-button:hover {
            background: rgba(108, 64, 64, 0.1);
        }
        .tab-button.active {
            background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(108, 64, 64, 0.3);
        }
        .tab-content {
            display: none;
            background: #fff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(108, 64, 64, 0.15);
            border: 1px solid rgba(108, 64, 64, 0.1);
        }
        .tab-content.active {
            display: block;
        }
        .form-section {
            margin-bottom: 2.5rem;
            padding: 2rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 12px;
            border: 1px solid rgba(108, 64, 64, 0.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .form-section h3 {
            color: #6c4040;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            color: #6c4040;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #6c4040;
            box-shadow: 0 0 0 3px rgba(108, 64, 64, 0.1);
            outline: none;
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .item-list {
            margin-top: 2.5rem;
        }
        .item-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(108, 64, 64, 0.1);
            transition: all 0.3s ease;
        }
        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .item-card h4 {
            color: #6c4040;
            margin-bottom: 0.75rem;
            font-size: 1.2rem;
            font-weight: 700;
        }
        .item-card p {
            margin-bottom: 0.75rem;
            line-height: 1.6;
            color: #495057;
        }
        .event-details {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .event-details strong {
            color: #6c4040;
        }
        .notice-priority {
            display: inline-block;
            padding: 0.35rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-left: 0.8rem;
            letter-spacing: 1px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            vertical-align: middle;
        }
        .notice-priority.high {
            background: linear-gradient(135deg, #ef5350 0%, #c62828 100%);
            color: white;
        }
        .notice-priority.medium {
             background: linear-gradient(135deg, #34312dff 0%, #f57c00 100%);
            color: white;
        }
        .notice-priority.low {
            background: linear-gradient(135deg, #66bb6a 0%, #2e7d32 100%);
            color: white;
        }
        .item-meta {
            color: #6c757d;
            font-size: 0.9rem;
            font-style: italic;
        }
        .item-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .btn-small {
            padding: 14px 32px !important;
            font-size: 1.1rem !important;
            font-weight: 600;
            border-radius: 50px !important;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Source Sans Pro', sans-serif !important;
            box-shadow: 0 4px 15px rgba(108, 64, 64, 0.3);
        }
        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .btn-edit {
            background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%);
            color: #fff;
        }
        .btn-edit:hover {
            background: linear-gradient(135deg, #502d2d 0%, #6c4040 100%);
        }
        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: #fff;
        }
        .btn-delete:hover {
            background: linear-gradient(135deg, #c82333 0%, #e8680d 100%);
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
        }
        .alert-error {
            background: #fee;
            color: #c33;
        }
        .form-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="../ChatGPT Image Oct 1, 2025, 10_03_37 AM.png" class="logo" alt="University Logo">
        <ul>
            <li><a href="../index.html">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="notices.php">Notices</a></li>
            <li><a href="./news.php">News</a></li>
            <li><a href="../events.php">Events</a></li>
            <li><a href="../user/booking.php">Book Room</a></li>
            <li><a href="logout.php">Logout</a></li>


        </ul>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 style="margin: 0;">Faculty Dashboard</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-button active" onclick="showTab('news')">News</button>
            <button class="tab-button" onclick="showTab('events')">Events</button>
            <button class="tab-button" onclick="showTab('notices')">Notices</button>
        </div>

        <div id="news" class="tab-content active">
            <div class="form-section">
                <h3><?php echo $edit_news ? 'Edit News' : 'Add New News'; ?></h3>
                <form method="POST">
                    <input type="hidden" name="type" value="news">
                    <input type="hidden" name="action" value="<?php echo $edit_news ? 'edit' : 'add'; ?>">
                    <?php if ($edit_news): ?>
                        <input type="hidden" name="news_id" value="<?php echo $edit_news['news_id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo $edit_news ? htmlspecialchars($edit_news['title']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" required><?php echo $edit_news ? htmlspecialchars($edit_news['content']) : ''; ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 600; border-radius: 8px; background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%); border: none; color: white; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(108, 64, 64, 0.3);"><?php echo $edit_news ? 'Update News' : 'Add News'; ?></button>
                        <?php if ($edit_news): ?>
                            <a href="dashboard.php?tab=news" class="btn btn-secondary" style="padding: 0.75rem 2rem; font-weight: 600; border-radius: 8px; background: #6c757d; border: none; color: white; text-decoration: none; display: inline-block; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="item-list">
                <h3>Your News</h3>
                <?php if (empty($news_list)): ?>
                    <p>No news added yet.</p>
                <?php else: ?>
                    <?php foreach ($news_list as $item): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($item['content'] ?? '', 0, 200)); ?>...</p>
                            <div class="item-meta">Published: <?php echo htmlspecialchars(substr($item['created_at'] ?? '', 0, 16)); ?></div>
                            <div class="item-actions">
                                <a href="?edit_news=<?php echo $item['news_id']; ?>" class="btn-small btn-edit">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="type" value="news">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="news_id" value="<?php echo $item['news_id']; ?>">
                                    <button type="submit" class="btn-small btn-delete" onclick="return confirm('Delete this news?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="events" class="tab-content">
            <div class="form-section">
                <h3><?php echo $edit_event ? 'Edit Event' : 'Add New Event'; ?></h3>
                <form method="POST">
                    <input type="hidden" name="type" value="event">
                    <input type="hidden" name="action" value="<?php echo $edit_event ? 'edit' : 'add'; ?>">
                    <?php if ($edit_event): ?>
                        <input type="hidden" name="event_id" value="<?php echo $edit_event['event_id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo $edit_event ? htmlspecialchars($edit_event['title']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" required><?php echo $edit_event ? htmlspecialchars($edit_event['description']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Event Date</label>
                        <input type="date" name="event_date" value="<?php echo $edit_event ? $edit_event['event_date'] : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Event Time</label>
                        <input type="time" name="event_time" value="<?php echo $edit_event ? $edit_event['event_time'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" value="<?php echo $edit_event ? htmlspecialchars($edit_event['location']) : ''; ?>">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 600; border-radius: 8px; background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%); border: none; color: white; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(108, 64, 64, 0.3);"><?php echo $edit_event ? 'Update Event' : 'Add Event'; ?></button>
                        <?php if ($edit_event): ?>
                            <a href="dashboard.php?tab=events" class="btn btn-secondary" style="padding: 0.75rem 2rem; font-weight: 600; border-radius: 8px; background: #6c757d; border: none; color: white; text-decoration: none; display: inline-block; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="item-list">
                <h3>Your Events</h3>
                <?php if (empty($events_list)): ?>
                    <p>No events added yet.</p>
                <?php else: ?>
                    <?php foreach ($events_list as $item): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($item['description'], 0, 200)); ?>...</p>
                            <div class="event-details">
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($item['event_date'] ?? 'TBD'); ?> <?php echo !empty($item['event_time']) ? 'at ' . htmlspecialchars($item['event_time']) : ''; ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location'] ?: 'TBD'); ?></p>
                            </div>
                            <div class="item-meta">Created: <?php echo htmlspecialchars(substr($item['created_at'] ?? '', 0, 10)); ?></div>
                            <div class="item-actions">
                                <a href="?edit_event=<?php echo $item['event_id']; ?>" class="btn-small btn-edit">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="type" value="event">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="event_id" value="<?php echo $item['event_id']; ?>">
                                    <button type="submit" class="btn-small btn-delete" onclick="return confirm('Delete this event?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="notices" class="tab-content">
            <div class="form-section">
                <h3><?php echo $edit_notice ? 'Edit Notice' : 'Add New Notice'; ?></h3>
                <form method="POST">
                    <input type="hidden" name="type" value="notice">
                    <input type="hidden" name="action" value="<?php echo $edit_notice ? 'edit' : 'add'; ?>">
                    <?php if ($edit_notice): ?>
                        <input type="hidden" name="notice_id" value="<?php echo $edit_notice['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?php echo $edit_notice ? htmlspecialchars($edit_notice['title']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" required><?php echo $edit_notice ? htmlspecialchars($edit_notice['content']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority">
                            <option value="low" <?php echo $edit_notice && $edit_notice['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo $edit_notice && $edit_notice['priority'] === 'medium' ? 'selected' : 'selected'; ?>>Medium</option>
                            <option value="high" <?php echo $edit_notice && $edit_notice['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" value="<?php echo $edit_notice ? $edit_notice['expiry_date'] : ''; ?>">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 600; border-radius: 8px; background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%); border: none; color: white; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(108, 64, 64, 0.3);"><?php echo $edit_notice ? 'Update Notice' : 'Add Notice'; ?></button>
                        <?php if ($edit_notice): ?>
                            <a href="dashboard.php?tab=notices" class="btn btn-secondary" style="padding: 0.75rem 2rem; font-weight: 600; border-radius: 8px; background: #6c757d; border: none; color: white; text-decoration: none; display: inline-block; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="item-list">
                <h3>Your Notices</h3>
                <?php if (empty($notices_list)): ?>
                    <p>No notices added yet.</p>
                <?php else: ?>
                    <?php foreach ($notices_list as $item): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($item['title'] ?? 'Untitled'); ?> <span class="notice-priority <?php echo $item['priority'] ?? 'low'; ?>"><?php echo $item['priority'] ?? 'low'; ?></span></h4>
                            <p><?php echo htmlspecialchars(substr($item['content'], 0, 200)); ?>...</p>
                            <?php if (!empty($item['expiry_date'])): ?>
                                <div style="background: #fff3cd; padding: 0.5rem; border-radius: 6px; margin: 0.5rem 0;">
                                    <strong>Expires:</strong> <?php echo htmlspecialchars($item['expiry_date']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="item-meta">Created: <?php echo htmlspecialchars(substr($item['created_at'] ?? '', 0, 10)); ?></div>
                            <div class="item-actions">
                                <a href="?edit_notice=<?php echo $item['id'] ?? 0; ?>" class="btn-small btn-edit">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="type" value="notice">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="notice_id" value="<?php echo $item['id'] ?? 0; ?>">
                                    <button type="submit" class="btn-small btn-delete" onclick="return confirm('Delete this notice?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => button.classList.remove('active'));

            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($edit_news): ?>
                showTabByName('news');
            <?php elseif ($edit_event): ?>
                showTabByName('events');
            <?php elseif ($edit_notice): ?>
                showTabByName('notices');
            <?php else: ?>
                // Check URL for tab
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab');
                if (tab) {
                    showTabByName(tab);
                } else {
                    showTabByName('news'); // Default
                }
            <?php endif; ?>
        });

        function showTabByName(tabName) {
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => button.classList.remove('active'));

            document.getElementById(tabName).classList.add('active');
            const button = document.querySelector(`[onclick="showTab('${tabName}')"]`);
            if (button) button.classList.add('active');
        }
    </script>
</body>
</html>