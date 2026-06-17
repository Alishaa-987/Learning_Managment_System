<?php

require_once 'config.php';

$pdo = getDBConnection();
startSession();

// Get all upcoming events ordered by date
$stmt = $pdo->query("SELECT e.*, s.name as author_name FROM events e LEFT JOIN students s ON e.created_by = s.stu_id WHERE e.event_date >= CURDATE() ORDER BY e.event_date ASC, e.event_time ASC");
$events_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Events - University</title>
    <style>
        .events-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
        }
        .events-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        .event-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(108, 64, 64, 0.15);
            overflow: hidden;
            transition: all 0.4s ease;
            border: 1px solid rgba(108, 64, 64, 0.1);
            position: relative;
        }
        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(108, 64, 64, 0.25);
        }
        .event-date {
            background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%);
            color: #fff;
            padding: 1.5rem 1rem;
            text-align: center;
            position: relative;
        }
        .event-date::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px 15px 0 0;
        }
        .event-date .day {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
            position: relative;
            z-index: 1;
        }
        .event-date .month {
            font-size: 1.1rem;
            opacity: 0.95;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        .event-content {
            padding: 2rem;
        }
        .event-title {
            color: #6c4040;
            margin-bottom: 0.75rem;
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.3;
        }
        .event-meta {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
            font-weight: 500;
        }
        .event-description {
            color: #495057;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }
        .event-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.95rem;
            color: #6c4040;
            font-weight: 600;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .event-details strong {
            color: #6c4040;
        }
        .no-events {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="./ChatGPT Image Oct 1, 2025, 10_03_37 AM.png" class="logo" alt="University Logo">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="program.html">Programs</a></li>
            <li><a href="faculty/news.php">News</a></li>
            <li><a href="events.php">Events</a></li>
            <li><a href="faculty/notices.php">Notices</a></li>
            <?php if (isFaculty()): ?>
                <li><a href="faculty/dashboard.php">Dashboard</a></li>
                <li><a href="faculty/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Signup</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="events-container">
        <div class="events-header">
            <h1 style="font-family: 'Cinzel', serif; color: #6c4040;">University Events</h1>
            <p>Discover upcoming events and activities at our university</p>
        </div>

        <?php if (empty($events_list)): ?>
            <div class="no-events">
                <h2>No Upcoming Events</h2>
                <p>Check back later for upcoming events.</p>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events_list as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                            <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                        </div>
                        <div class="event-content">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <div class="event-meta">
                                Organized by <?php echo htmlspecialchars($event['author_name'] ?? 'University Staff'); ?>
                            </div>
                            <div class="event-description">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>...
                            </div>
                            <div class="event-details">
                                <span><strong>Location:</strong> <?php echo htmlspecialchars($event['location'] ?: 'TBD'); ?></span>
                                <?php if ($event['event_time']): ?>
                                    <span><strong>Time:</strong> <?php echo date('h:i A', strtotime($event['event_time'])); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>