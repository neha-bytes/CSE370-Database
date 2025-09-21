<?php
date_default_timezone_set('Asia/Dhaka');
include 'connect.php';


$currentDateTime = date("H:i:s");  // 24-hour format

echo "Current Time: $currentDateTime<br>";
 
$expiredEventsQuery = "
    SELECT EventID, EndTime, Status 
    FROM Event 
    WHERE EndTime < ? AND Status != 'Closed'
";

$stmt = $conn->prepare($expiredEventsQuery);
$stmt->bind_param("s", $currentDateTime);
$stmt->execute();
$result = $stmt->get_result();



$expiredEventIDs = [];  
while ($row = $result->fetch_assoc()) { // expired value gula ber korar jonno 
    echo "EventID: {$row['EventID']}, EndTime: {$row['EndTime']}, Status: {$row['Status']}<br>";
    $expiredEventIDs[] = $row['EventID'];
}

if (empty($expiredEventIDs)) {
    echo "No expired events found.<br>";
} else {
    echo "Expired Event IDs: " . implode(", ", $expiredEventIDs) . "<br>";
}

if (!empty($expiredEventIDs)) {
    $eventIDs = implode(",", $expiredEventIDs);

    $updateStatusQuery = "
        UPDATE Event 
        SET Status = 'Closed' 
        WHERE EventID IN ($eventIDs)
    ";

    if ($conn->query($updateStatusQuery)) {
        echo "Updated event status successfully.<br>";
    } else {
        echo "Error updating event status: " . $conn->error . "<br>";
    }

    // donor, supervisor , volunteer remove korte
    $queries = [
        "DELETE FROM EventVolunteers WHERE EventID IN ($eventIDs)",
        "DELETE FROM EventDonors WHERE EventID IN ($eventIDs)",
        "DELETE FROM EventSupervisor WHERE EventID IN ($eventIDs)"
    ];

    foreach ($queries as $query) {
        if ($conn->query($query)) {
            echo "Executed: $query<br>";
        } else {
            echo "Error executing query: $query - " . $conn->error . "<br>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="GET" action="admin.php">
    <button type="submit">Back</button>
</body>
</html>