<?php
require_once '../config.php';
define('FPDF_FONTPATH', '../includes/fpdf/font/');
require_once '../includes/fpdf/fpdf.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: booking.php');
    exit();
}

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];
$room_id = $_POST['room_id'];
$booking_date = $_POST['booking_date'];
$slotsJson = $_POST['slots'];
$title = $_POST['title'];
$attendees = $_POST['attendees'];

$slots = json_decode($slotsJson, true);

if (empty($slots) || empty($room_id) || empty($booking_date)) {
    die("Invalid data");
}

// Fetch Room Name
$stmtRoom = $pdo->prepare("SELECT room_name FROM rooms WHERE room_id = ?");
$stmtRoom->execute([$room_id]);
$roomName = $stmtRoom->fetchColumn();

// Fetch User Info for PDF
$stmtUser = $pdo->prepare("SELECT name, department, ag_no FROM students WHERE stu_id = ?");
$stmtUser->execute([$user_id]);
$userInfo = $stmtUser->fetch();

// 1. Process Slots: Merge contiguous slots
// Sort slots by start time
usort($slots, function($a, $b) {
    return strcmp($a['start'], $b['start']);
});

$mergedBookings = [];
if (count($slots) > 0) {
    $currentStart = $slots[0]['start'];
    $currentEnd = $slots[0]['end'];
    
    for ($i = 1; $i < count($slots); $i++) {
        if ($slots[$i]['start'] == $currentEnd) {
            // Contiguous
            $currentEnd = $slots[$i]['end'];
        } else {
            // Gap, push current and start new
            $mergedBookings[] = ['start' => $currentStart, 'end' => $currentEnd];
            $currentStart = $slots[$i]['start'];
            $currentEnd = $slots[$i]['end'];
        }
    }
    $mergedBookings[] = ['start' => $currentStart, 'end' => $currentEnd];
}

// 2. Insert Bookings and Generate PDF(s)
// "submit karne ke bad focal person ke pass uski pdf generate hu"
// Usually one request = one PDF. If they selected disjoint slots, technically multiple bookings in DB?
// User requirement: "submit ... pdf generate hu". Singular.
// Even if multiple slots, I'll generate one Receipt PDF summarizing them.

// We will insert multiple rows but generate ONE receipt ID/File.
// Let's create a transaction to ensure atomicity.
$pdo->beginTransaction();

try {
    $bookingIds = [];
    foreach ($mergedBookings as $booking) {
        $stmt = $pdo->prepare("
            INSERT INTO bookings (room_id, user_id, booking_date, time_slot_start, time_slot_end, title, attendees, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->execute([
            $room_id, 
            $user_id, 
            $booking_date, 
            $booking['start'], 
            $booking['end'], 
            $title, 
            $attendees
        ]);
        $bookingIds[] = $pdo->lastInsertId();
    }
    
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error saving booking: " . $e->getMessage());
}

// 3. Generate PDF
class PDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('../logo.png',10,6,30);
        $this->SetFont('Arial','',15);
        $this->Cell(80);
        $this->Cell(30,10,'Booking Receipt',0,0,'C');
        $this->Ln(20);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','',8);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

$pdf->Cell(0, 10, 'Reference No: ' . implode('-', $bookingIds), 0, 1);
$pdf->Cell(0, 10, 'Date Generated: ' . date('Y-m-d H:i:s'), 0, 1);
$pdf->Ln(10);

$pdf->SetFont('Arial','',12);
$pdf->Cell(50, 10, 'Focal Person:', 0, 0);
$pdf->Cell(0, 10, $userInfo['name'] . ' (' . $userInfo['ag_no'] . ')', 0, 1);

$pdf->Cell(50, 10, 'Department:', 0, 0);
$pdf->Cell(0, 10, $userInfo['department'], 0, 1);

$pdf->Cell(50, 10, 'Room:', 0, 0);
$pdf->Cell(0, 10, $roomName, 0, 1);

$pdf->Cell(50, 10, 'Event Title:', 0, 0);
$pdf->Cell(0, 10, $title, 0, 1);

$pdf->Cell(50, 10, 'Attendees:', 0, 0);
$pdf->Cell(0, 10, $attendees, 0, 1);

$pdf->Cell(50, 10, 'Date:', 0, 0);
$pdf->Cell(0, 10, $booking_date, 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0, 10, 'Requested Slots:', 0, 1);
$pdf->SetFont('Arial','',11);

foreach ($mergedBookings as $booking) {
    $timeStr = date('g:i A', strtotime($booking['start'])) . ' - ' . date('g:i A', strtotime($booking['end']));
    $pdf->Cell(0, 10, '- ' . $timeStr, 0, 1);
}

$pdf->Ln(10);
$pdf->SetTextColor(200, 100, 0);
$pdf->Cell(0, 10, 'Status: Pending Approval', 0, 1);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 10, "Your request has been sent to the Admin. Please check your dashboard for updates.");

// Save PDF
$saveDir = '../uploads/bookings/';
if (!is_dir($saveDir)) {
    mkdir($saveDir, 0777, true);
}
$filename = 'booking_' . $bookingIds[0] . '.pdf';
$filepath = $saveDir . $filename;
$pdf->Output('F', $filepath);

// Redirect to success
// Redirect to success
// header("Location: booking_success.php?file=$filename");
// exit();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=booking_success.php?file=<?php echo urlencode($filename); ?>">
    <title>Redirecting...</title>
    <style>body { font-family: sans-serif; text-align: center; padding: 50px; }</style>
</head>
<body>
    <h3>Booking Successful!</h3>
    <p>Redirecting you to the receipt page...</p>
    <p><a href="booking_success.php?file=<?php echo urlencode($filename); ?>">Click here if you are not redirected automatically.</a></p>
</body>
</html>
<?php
exit();

?>
