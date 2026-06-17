<?php

require_once '../config.php';

$pdo = getDBConnection();
startSession();

$stmt = $pdo->query("SELECT n.*, s.name as author_name FROM notices n LEFT JOIN students s ON n.created_by = s.stu_id WHERE (n.expiry_date IS NULL OR n.expiry_date >= CURDATE()) ORDER BY CASE n.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END, n.created_at DESC");
$notices_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Notices - University</title>
    <style>
        .notices-container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 2rem;
        }
        .notices-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .notices-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .notice-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(108, 64, 64, 0.15);
            padding: 2.5rem;
            border-left: 6px solid #6c4040;
            transition: all 0.4s ease;
            border: 1px solid rgba(108, 64, 64, 0.1);
            position: relative;
        }
        .notice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(108, 64, 64, 0.25);
        }
        .notice-card.high {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%);
        }
        .notice-card.medium {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, #fffbf0 0%, #fefce8 100%);
        }
        .notice-card.low {
            border-left-color: #28a745;
            background: linear-gradient(135deg, #f0f9f0 0%, #f0fdf4 100%);
        }
        .notice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .notice-title {
            color: #6c4040;
            margin: 0;
            font-size: 1.3rem;
        }
        .notice-priority {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .notice-priority.high {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
        }
        .notice-priority.medium {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: #212529;
        }
        .notice-priority.low {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .notice-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .notice-content {
            color: #f5f0f0ff;
            line-height: 1.6;
        }
        .notice-expiry {
            margin-top: 1rem;
            padding: 0.5rem;
            background: #f9f9f9;
            border-radius: 5px;
            font-size: 0.9rem;
            color: #666;
        }
        .no-notices {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="../ChatGPT Image Oct 1, 2025, 10_03_37 AM.png" class="logo" alt="University Logo">
        <ul>
            <li><a href="../index.html">Home</a></li>
            <li><a href="../about.html">About</a></li>
            <li><a href="../program.html">Programs</a></li>
            <li><a href="news.php">News</a></li>
            <li><a href="../events.php">Events</a></li>
            <li><a href="notices.php">Notices</a></li>
            <?php if (isFaculty()): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="../contact.html">Contact</a></li>
                <li><a href="../login.php">Login</a></li>
                <li><a href="../signup.php">Signup</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="notices-container">
        <div class="notices-header">
            <h1 style="font-family: 'Cinzel', serif; color: #6c4040;">University Notices</h1>
            <p>Important announcements and notices for students and faculty</p>
        </div>

        <?php if (empty($notices_list)): ?>
            <div class="no-notices">
                <h2>No Active Notices</h2>
                <p>All notices have expired or there are no current announcements.</p>
            </div>
        <?php else: ?>
            <div class="notices-list">
                <?php foreach ($notices_list as $notice): ?>
                    <div class="notice-card <?php echo $notice['priority']; ?>">
                        <div class="notice-header">
                            <h3 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h3>
                            <span class="notice-priority <?php echo $notice['priority']; ?>"><?php echo $notice['priority']; ?></span>
                        </div>
                        <div class="notice-meta">
                            Posted by <?php echo htmlspecialchars($notice['author_name'] ?? 'University Staff'); ?> |
                            <?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
                        </div>
                        <div class="notice-content">
                            <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                        </div>
                        <?php if ($notice['expiry_date']): ?>
                            <div class="notice-expiry">
                                <strong>Expires:</strong> <?php echo date('M d, Y', strtotime($notice['expiry_date'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>