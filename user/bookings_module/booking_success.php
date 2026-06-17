<?php
require_once '../config.php';
requireLogin();

$file = $_GET['file'] ?? '';

if (!$file || !file_exists('../uploads/bookings/' . $file)) {
    die("Invalid request");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Booking Success</title>
</head>
<body>
    <div class="navbar">
        <img src="../logo.png" class="logo" alt="University Logo">
        <ul>
            <li><a href="../index.html">Home</a></li>
            <?php 
                $dashboardLink = 'dashboard.php';
                if (isset($_SESSION['role'])) {
                    if ($_SESSION['role'] === 'faculty') $dashboardLink = '../faculty/dashboard.php';
                    elseif ($_SESSION['role'] === 'admin') $dashboardLink = '../admin/dashboard.php';
                }
            ?>
            <li><a href="<?php echo $dashboardLink; ?>">Dashboard</a></li>
            <li><a href="booking.php">Book Room</a></li>
        </ul>
    </div>

    <div style="max-width: 600px; margin: 5rem auto; text-align: center; background: #fff; padding: 3rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(108,64,64,0.1);">
        <h2 style="color: #2e7d32; font-family: 'Cinzel', serif; margin-bottom: 1rem;">Request Sent Successfully!</h2>
        <p style="color: #555; margin-bottom: 2rem;">Your room booking request has been submitted to the administration.</p>
        
        <div style="margin-bottom: 2rem;">
            <img src="../includes/pdf_icon_placeholder.png" alt="" style="width: 50px; opacity: 0.5; display:block; margin: 0 auto 10px;"> 
            <!-- Just a visual placeholder or skip img -->
            
            <a href="../uploads/bookings/<?php echo htmlspecialchars($file); ?>" target="_blank" class="btn btn-primary">
                View/Download Receipt (PDF)
            </a>
        </div>
        
        <p style="font-size: 0.9rem; color: #888;">Click the button above to view your booking receipt. The admin will review your request shortly.</p>
        
        <div style="margin-top: 2rem;">
            <a href="booking.php" class="btn btn-secondary" style="color:#6c4040; border-color:#6c4040;">Book Another Room</a>
            <a href="<?php echo $dashboardLink; ?>" class="btn btn-secondary" style="margin-left: 10px; color:#6c4040; border-color:#6c4040;">Go to Dashboard</a>
        </div>
    </div>

</body>
</html>
