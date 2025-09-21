<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['role'] != 'Admin') {
    die("Unauthorized access.");
}

date_default_timezone_set('Asia/Dhaka');

$currentDate = date("Y-m-d");
$currentTime = date("H:i:s");

// Fetch ongoing events
$query = "
    SELECT e.EventID, e.EventName, e.Date, e.StartTime, e.EndTime, l.City
    FROM Event e
    JOIN Location l ON e.LocationID = l.LocationID
    WHERE e.Date = ? AND e.StartTime <= ? AND e.EndTime >= ? AND e.Status = 'Active'
";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $currentDate, $currentTime, $currentTime);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are any ongoing events
if ($result->num_rows === 0) {
    echo "<h1>Ongoing Events</h1>";
    echo "<p>No ongoing events.</p>";
    exit();
}

// Fetch event details and participants
$events = [];
while ($row = $result->fetch_assoc()) {
    $eventID = $row['EventID'];

    // Fetch associated participants 
    $participantsQuery = "
        SELECT u.Name, u.Phone, r.RoleName 
        FROM User u
        JOIN (
            SELECT DonorID AS UserID, 'Donor' AS RoleName FROM EventDonors WHERE EventID = ?
            UNION
            SELECT VolunteerID AS UserID, 'Volunteer' AS RoleName FROM EventVolunteers WHERE EventID = ?
            UNION
            SELECT s.UserID, 'Supervisor' AS RoleName 
            FROM EventSupervisor es 
            JOIN Supervisor s ON es.SupervisorID = s.SupervisorID
            WHERE es.EventID = ?
        ) r ON u.UserID = r.UserID
    ";
    $participantsStmt = $conn->prepare($participantsQuery);
    $participantsStmt->bind_param("iii", $eventID, $eventID, $eventID);
    $participantsStmt->execute();
    $participantsResult = $participantsStmt->get_result();

    $participants = [];
    while ($participant = $participantsResult->fetch_assoc()) {
        $participants[] = $participant;
    }

    $events[] = [
        'details' => $row,
        'participants' => $participants,
    ];
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ongoing Events</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <h1>Ongoing Events</h1>

    <?php foreach ($events as $event) { ?>
        <div class="event">
            <h2>EventName: <?php echo htmlspecialchars($event['details']['EventName']); ?></h2>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($event['details']['Date']); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars($event['details']['StartTime']); ?> - <?php echo htmlspecialchars($event['details']['EndTime']); ?></p>
            <p><strong>City:</strong> <?php echo htmlspecialchars($event['details']['City']); ?></p>

            <?php if (!empty($event['participants'])) { ?>
                <h3>Participants:</h3>
                <ul>
                    <?php foreach ($event['participants'] as $participant) { ?>
                        <li>
                            <strong><?php echo htmlspecialchars($participant['RoleName']); ?>:</strong> 
                            <?php echo htmlspecialchars($participant['Name']); ?> 
                            (<?php echo htmlspecialchars($participant['Phone']); ?>)
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>No participants found for this event.</p>
            <?php } ?>
        </div>
    <?php } ?>
    <form method="GET" action="admin.php">
    <button type="submit">Back</button>
</form>
</body>
</html>