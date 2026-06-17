<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['room_id']) || !isset($_GET['date'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

$room_id = $_GET['room_id'];
$date = $_GET['date'];
$pdo = getDBConnection();

// Define all possible slots (8 AM to 6 PM)
$allSlots = [];
$startHour = 8;
$endHour = 18; // 6 PM

for ($h = $startHour; $h < $endHour; $h++) {
    $start = sprintf('%02d:00:00', $h);
    $end = sprintf('%02d:00:00', $h + 1);
    $label = sprintf('%02d:00 - %02d:00', $h, $h + 1);
    
    // 12 PM format fix if needed, but 13:00 is fine for logic
    // For label, nice to have AM/PM
    $startDisplay = date('g A', strtotime($start));
    $endDisplay = date('g A', strtotime($end));
    
    $allSlots[] = [
        'start' => $start,
        'end' => $end,
        'label' => "$startDisplay - $endDisplay",
        'status' => 'free'
    ];
}

// Fetch bookings for this room and date
// We include Pending and Approved to BLOCK slots.
// We include Rejected just to track if we need to show it? 
// User said "Deny with red". If a slot was denied, maybe we show it as red but clickable? 
// Or maybe just show it as available?
// "rejected bookings clearly marked as such rather than deleted"
// "deny with red".
// I will mark Rejected slots as 'denied' (Red). If it's denied, it means "This specific attempt was denied".
// Does it block others? Probably not.
// BUT "jab koi aur focal parson ... dekhe ... ye room nai selct kar sakta iski already request gai hui he" -> Applies to Pending.
// If Rejected -> It should be available for others.
// So, if there is an overlapping REJECTED booking, I will NOT mark the slot as blocked. I will leave it 'free'.
// UNLESS the prompt implies the slot is permanently disabled? Unlikely.
// So:
// Approved -> 'booked'
// Pending -> 'pending'
// Rejected -> Ignore (so it remains 'free' unless another booking exists).

$sql = "SELECT * FROM bookings WHERE room_id = ? AND booking_date = ? AND status IN ('Approved', 'Pending')";
$stmt = $pdo->prepare($sql);
$stmt->execute([$room_id, $date]);
$bookings = $stmt->fetchAll();

foreach ($bookings as $booking) {
    // Check overlap with slots
    // Booking has start_time and end_time (time_slot_start, time_slot_end)
    // Actually column names are time_slot_start, time_slot_end.
    
    $bStart = $booking['time_slot_start'];
    $bEnd = $booking['time_slot_end'];
    $bStatus = $booking['status'];
    
    foreach ($allSlots as &$slot) {
        // Simple overlap check: (StartA < EndB) and (EndA > StartB)
        // Here slots are hourly aligned.
        if ($slot['start'] == $bStart) { // Exact match or subset
             // Map status
             if ($bStatus == 'Approved') {
                 $slot['status'] = 'booked';
             } elseif ($bStatus == 'Pending') {
                 $slot['status'] = 'pending';
             }
        }
    }
}

// Logic for Rejected?
// If we want to show Rejected slots as Red, we need to fetch them.
// But if they are free to book, they should be selectable.
// If I mark them 'denied' (red), and the CSS makes them line-through/red, can user select?
// "available with yellow". 
// If I follow strictly: 
// The CALENDAR view (Days) shows Red if there is a rejection.
// The SLOTS view? 
// I will keep slots available (Yellow/White) if Rejected, so they can be booked again.
// The "Red" indication is on the Daily Calendar view (which I handled in booking.php).

echo json_encode(['slots' => $allSlots]);
?>
