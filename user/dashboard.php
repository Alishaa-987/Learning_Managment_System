<?php
/**
 * Student Dashboard
 * Displays student profile information
 */

require_once '../config.php';
requireLogin(); // Ensure only students can access

$pdo = getDBConnection();

// Fetch student data
$stmt = $pdo->prepare("SELECT * FROM students WHERE stu_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Student Dashboard - University</title>
    <style>
        .dashboard-container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 2rem;
        }
        .profile-header {
            background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%);
            color: #fff;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #fff;
            object-fit: cover;
        }
        .profile-info h2 {
            margin-bottom: 0.5rem;
        }
        .profile-card {
            background: #fff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(108, 64, 64, 0.1);
            margin-bottom: 1.5rem;
        }
        .profile-card h3 {
            color: #6c4040;
            margin-bottom: 1.5rem;
            font-family: 'Cinzel', serif;
            border-bottom: 2px solid #6c4040;
            padding-bottom: 0.5rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-weight: 600;
            color: #6c4040;
            margin-bottom: 0.3rem;
        }
        .info-value {
            color: #333;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 1rem;
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
            <li><a href="../contact.html">Contact</a></li>
            <li><a href="booking.php">Book Room</a></li>
            <li><a href="logout.php">Logout</a></li>

        </ul>
    </div>

    <div class="dashboard-container">
        <div class="profile-header">
            <?php if ($student['profile_picture']): ?>
                <img src="../uploads/<?php echo htmlspecialchars($student['profile_picture']); ?>" alt="Profile" class="profile-picture">
            <?php else: ?>
                <div class="profile-picture" style="background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 3rem;"><?php echo strtoupper(substr($student['name'], 0, 1)); ?></div>
            <?php endif; ?>
            <div class="profile-info">
                <h2 style="margin: 0;"><?php echo htmlspecialchars($student['name']); ?></h2>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9;"><?php echo htmlspecialchars($student['email']); ?></p>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">AG No: <?php echo htmlspecialchars($student['ag_no']); ?></p>
            </div>
        </div>

        <div class="profile-card">
            <h3>Personal Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Father Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['father_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date of Birth</span>
                    <span class="info-value"><?php echo date('d M Y', strtotime($student['dob'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">CNIC</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['cnic']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Gender</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['gender']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['phone_no']); ?></span>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <h3>Academic Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">AG No / Registration No</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['ag_no']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Admission Date</span>
                    <span class="info-value"><?php echo date('d M Y', strtotime($student['adm_date'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Department</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['department']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Degree</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['degree']); ?></span>
                </div>
                <?php if ($student['class']): ?>
                <div class="info-item">
                    <span class="info-label">Class</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['class']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($student['section']): ?>
                <div class="info-item">
                    <span class="info-label">Section</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['section']); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['stu_status']); ?></span>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <h3>Contact Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['email']); ?></span>
                </div>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <span class="info-label">Address</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['address']); ?></span>
                </div>
            </div>
        </div>

        <a href="logout.php" class="btn btn-primary logout-btn">Logout</a>
    </div>
</body>
</html>

