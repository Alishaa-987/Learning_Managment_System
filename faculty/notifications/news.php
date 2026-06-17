<?php

require_once '../config.php';

$pdo = getDBConnection();
startSession();

$stmt = $pdo->query("SELECT n.*, s.name as author_name FROM news n LEFT JOIN students s ON n.created_by = s.stu_id ORDER BY n.created_at DESC");
$news_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>News - University</title>
    <style>
        .news-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
        }
        .news-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        .news-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(108, 64, 64, 0.15);
            overflow: hidden;
            transition: all 0.4s ease;
            border: 1px solid rgba(108, 64, 64, 0.1);
        }
        .news-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(108, 64, 64, 0.25);
        }
        .news-image {
            width: 100%;
            height: 140px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 2.5rem;
            position: relative;
            border-bottom: 1px solid #eee;
        }
        .news-image::before {
            display: none;
        }
        .news-content {
            padding: 2rem;
        }
        .news-title {
            color: #6c4040;
            margin-bottom: 0.75rem;
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.3;
        }
        .news-meta {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
            font-weight: 500;
        }
        .news-excerpt {
            color: #495057;
            line-height: 1.7;
            font-size: 1rem;
        }
        .no-news {
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
            <li><a href="./notices.php">Notices</a></li>
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

    <div class="news-container">
        <div class="news-header">
            <h1 style="font-family: 'Cinzel', serif; color: #6c4040;">University News</h1>
            <p>Stay updated with the latest news and announcements from our university</p>
        </div>

        <?php if (empty($news_list)): ?>
            <div class="no-news">
                <h2>No News Available</h2>
                <p>Check back later for updates.</p>
            </div>
        <?php else: ?>
            <div class="news-grid">
                <?php foreach ($news_list as $news): ?>
                    <div class="news-card">
                        <div class="news-image">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="news-content">
                            <h3 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                            <div class="news-meta">
                                By <?php echo htmlspecialchars($news['author_name'] ?? 'University Staff'); ?> |
                                <?php echo date('M d, Y', strtotime($news['created_at'])); ?>
                            </div>
                            <div class="news-excerpt">
                                <?php echo htmlspecialchars(substr($news['content'], 0, 150)); ?>...
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>