<?php
require_once '../config.php';
requireLogin();

$pdo = getDBConnection();

// Check if user is focal person
$stmt = $pdo->prepare("SELECT is_focal_person FROM students WHERE stu_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_focal_person'] != 1) {
    // Redirect or show error if not focal person
    // For now, let's assume if they are here, we let them proceed or show message
    // But per requirements "focal person ... booking page", implies restriction.
    // I will show a message if not authorized, but won't hard block for this demo unless crucial.
    // Actually, let's just alert them.
    $isFocal = false;
} else {
    $isFocal = true;
}

// Fetch Rooms
$stmtRooms = $pdo->query("SELECT * FROM rooms WHERE status = 'Active'");
$rooms = $stmtRooms->fetchAll();

$selectedRoomId = $_GET['room_id'] ?? ($rooms[0]['room_id'] ?? null);
$selectedMonth = $_GET['month'] ?? date('m');
$selectedYear = $_GET['year'] ?? date('Y');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Room Booking - University</title>
</head>
<body>
    <div class="navbar">
        <img src="../logo.webp" class="logo" alt="University Logo">
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
            <li><a href="booking.php" class="active">Book Room</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>

    </div>

    <?php if (!$isFocal): ?>
    <div style="max-width:800px; margin: 5rem auto; text-align:center; color: #6c4040;">
        <h2>Access Restricted</h2>
        <p>You are not registered as a Focal Person.</p>
        <a href="<?php echo $dashboardLink; ?>" class="btn btn-primary" style="margin-top:1rem;">Go Back</a>

    </div>
    <?php else: ?>

    <div class="booking-container">
        <!-- Calendar Section -->
        <div class="calendar-card">
            <div class="form-group">
                <label for="room_select">Select Room:</label>
                <select id="room_select" onchange="window.location.href='booking.php?room_id='+this.value">
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['room_id']; ?>" <?php echo $selectedRoomId == $room['room_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($room['room_name']); ?> (Cap: <?php echo $room['capacity']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="calendar-header">
                <h3><?php echo date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)); ?></h3>
                <div>
                   <?php 
                        $prevMonth = $selectedMonth - 1;
                        $prevYear = $selectedYear;
                        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                        
                        $nextMonth = $selectedMonth + 1;
                        $nextYear = $selectedYear;
                        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                   ?>
                   <a href="?room_id=<?php echo $selectedRoomId; ?>&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn-secondary" style="padding: 5px 10px; font-size: 0.8rem;">&lt; Prev</a>
                   <a href="?room_id=<?php echo $selectedRoomId; ?>&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn-secondary" style="padding: 5px 10px; font-size: 0.8rem;">Next &gt;</a>
                </div>
            </div>

            <div class="calendar-grid">
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>

                <?php
                // Generate Calendar Days
                $firstDayOfMonth = mktime(0, 0, 0, $selectedMonth, 1, $selectedYear);
                $numberDays = date('t', $firstDayOfMonth);
                $dateComponents = getdate($firstDayOfMonth);
                $dayOfWeek = $dateComponents['wday'];

                // Empty slots before first day
                for ($i = 0; $i < $dayOfWeek; $i++) {
                    echo "<div class='calendar-day empty'></div>";
                }

                // Days
                for ($currentDay = 1; $currentDay <= $numberDays; $currentDay++) {
                    $dateStr = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $currentDay);
                    
                    // Determine Class based on overall availability
                    // Need to check specific room bookings
                    // Logic: 
                    // Green: Fully Booked (or just "Booked" status exists?) - User said "Booked with Green". 
                    // Yellow: Available.
                    // Red: Denied.
                    
                    // I will fetch bookings for this day and room
                    $stmtStatus = $pdo->prepare("
                        SELECT status, count(*) as cnt FROM bookings 
                        WHERE room_id = ? AND booking_date = ? 
                        GROUP BY status
                    ");
                    $stmtStatus->execute([$selectedRoomId, $dateStr]);
                    $statuses = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR); // 'Approved' => 2, 'Pending' => 1
                    
                    // Default
                    $dayClass = "status-available";
                    
                    // Priority Logic (Simplified for visual calendar)
                    // If ANY Rejected -> Red (User said "deny with red")
                    // If ANY Approved -> Green (User said "booked with green")
                    // If ALL Available -> Yellow
                    
                    // "booked with green colour deny with red colour available with yellow colour"
                    // If there are mixed? e.g. 8-9 Approved, 9-10 Available.
                    // The day isn't fully booked. But showing Green indicates "There is a booking".
                    // Let's show:
                    // - Red if there is a rejection (Alert!)
                    // - Green if there is an approved booking.
                    // - Yellow if free.
                    // But waiting? "Request gai hui he" (Request sent). User didn't specify color for Pending.
                    // I'll make Pending show as "Partial" or just Green-ish/Orange.
                    // Let's stick to user request: Green, Red, Yellow.
                    
                    if (isset($statuses['Rejected'])) {
                        $dayClass = "status-denied";
                    } elseif (isset($statuses['Approved'])) {
                        $dayClass = "status-booked";
                    } elseif (isset($statuses['Pending'])) {
                         // Pending requests could be considered "Booked" for the purpose of "Request gai hui he"
                         // blocking others from requesting same slot? 
                         // "jab koi aur focal parson ... dekhe ... ye room nai selct kar sakta iski already request gai hui he"
                         // So Pending blocks selection. Treat as Green/Booked visually or maybe Orange to differentiate.
                         $dayClass = "status-booked"; // Or make a new class 'status-pending' that looks like booked
                    }
                    
                    echo "<div class='calendar-day $dayClass' onclick=\"selectDate('$dateStr', this)\">$currentDay</div>";

                    if (($dayOfWeek + $currentDay) % 7 == 0) {
                        // End of row
                    }
                }
                
                // Empty slots after last day
                 // (Optional, grid handles it)
                ?>
            </div>
            
            <div class="legend">
                <div class="legend-item"><div class="legend-color status-available" style="background:#fff9c4;"></div> Available</div>
                <div class="legend-item"><div class="legend-color status-booked" style="background:#c8e6c9;"></div> Booked/Pending</div>
                <div class="legend-item"><div class="legend-color status-denied" style="background:#ffcdd2;"></div> Denied</div>
            </div>
        </div>

        <!-- Booking Form Section -->
        <div class="booking-form-card">
            <h3 style="color:#6c4040; margin-bottom:1rem; font-family:'Cinzel', serif;">Book a Slot</h3>
            <p id="selected-date-display" style="color:#666; margin-bottom:1rem; font-style:italic;">Select a date from the calendar</p>

            <form id="booking-form" action="process_booking.php" method="POST" style="display:none;">
                <input type="hidden" name="room_id" value="<?php echo $selectedRoomId; ?>">
                <input type="hidden" name="booking_date" id="form_booking_date">
                <input type="hidden" name="slots" id="form_slots">

                <div class="form-group">
                    <label>Select Time Slots (8 AM - 6 PM)</label>
                    <div id="time-slots-container" class="time-slots-grid">
                        <!-- Slots injected via JS -->
                    </div>
                </div>

                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" name="title" id="title" required placeholder="e.g. Department Sync">
                </div>

                <div class="form-group">
                    <label for="attendees">Number of Persons</label>
                    <input type="number" name="attendees" id="attendees" required min="1" placeholder="e.g. 20">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Submit Request</button>
            </form>
            
            <div id="empty-state">
                <p>Click on a date to view available time slots.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function selectDate(dateStr, element) {
            // Visual feedback
            document.querySelectorAll('.calendar-day').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');

            document.getElementById('selected-date-display').innerText = "Selected Date: " + dateStr;
            document.getElementById('form_booking_date').value = dateStr;
            document.getElementById('booking-form').style.display = 'block';
            document.getElementById('empty-state').style.display = 'none';

            // Fetch slots
            fetchSlots(dateStr);
        }

        function fetchSlots(date) {
            const roomId = <?php echo $selectedRoomId ?? 0; ?>;
            const container = document.getElementById('time-slots-container');
            container.innerHTML = '<p>Loading slots...</p>';

            fetch(`get_slots.php?room_id=${roomId}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    container.innerHTML = '';
                    if (data.error) {
                        container.innerHTML = '<p style="color:red">'+data.error+'</p>';
                        return;
                    }
                    
                    data.slots.forEach(slot => {
                         const div = document.createElement('div');
                         div.className = `time-slot ${slot.status == 'free' ? '' : slot.status.toLowerCase()}`;
                         div.innerText = slot.label;
                         div.dataset.start = slot.start;
                         div.dataset.end = slot.end;
                         
                         // Interactive if free
                         if (slot.status === 'free') {
                             div.onclick = function() {
                                 this.classList.toggle('selected');
                                 updateSelectedSlots();
                             }
                         } else {
                             div.title = "Not available";
                         }
                         
                         container.appendChild(div);
                    });
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = 'Error loading slots.';
                });
        }

        function updateSelectedSlots() {
            const selected = document.querySelectorAll('.time-slot.selected');
            const slots = [];
            selected.forEach(el => {
                slots.push({
                    start: el.dataset.start,
                    end: el.dataset.end
                });
            });
            document.getElementById('form_slots').value = JSON.stringify(slots);
        }
        
        // Form Validation on submit
        document.getElementById('booking-form').onsubmit = function(e) {
            const slots = document.getElementById('form_slots').value;
             if (!slots || slots === '[]') {
                 alert("Please select at least one time slot.");
                 e.preventDefault();
             }
        };
    </script>
</body>
</html>
