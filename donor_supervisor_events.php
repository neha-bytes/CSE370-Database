<?php 
session_start();
include 'connect.php';

if (!isset($_SESSION['UserID'])) {
    die("Unauthorized access.");
}

$userID = $_SESSION['UserID'];
$supervisorID = null;

// If the user is a Supervisor, get the SupervisorID
if ($_SESSION['role'] == 'Supervisor') {
    // Fetch the SupervisorID using the logged-in UserID
    $supervisorQuery = "SELECT SupervisorID FROM Supervisor WHERE UserID = ?";
    $stmt = $conn->prepare($supervisorQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $supervisorData = $result->fetch_assoc();
        $supervisorID = $supervisorData['SupervisorID'];
    } else {
        die("Supervisor details not found.");
    }
}

// Query to fetch events the user has access to (Donor, Volunteer, or Supervisor)
$userEventsQuery = "
    SELECT DISTINCT e.EventID, e.EventName, e.Date, e.StartTime, e.EndTime 
    FROM Event e
    LEFT JOIN EventVolunteers ev ON e.EventID = ev.EventID
    LEFT JOIN EventSupervisor es ON e.EventID = es.EventID
    LEFT JOIN EventDonors ed ON e.EventID = ed.EventID
    WHERE 
        ev.VolunteerID = ? OR
        es.SupervisorID = ? OR
        ed.DonorID = ?
";

$stmt = $conn->prepare($userEventsQuery);

// Bind parameters based on the user role
if ($_SESSION['role'] == 'Supervisor') {
    $stmt->bind_param("iii", $userID, $supervisorID, $userID); // For supervisor, use supervisorID for events
} else {
    $stmt->bind_param("iii", $userID, $userID, $userID); // For other roles (donor/volunteer), use userID
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events</title>
    <link rel="stylesheet" href="style2.css">

</head>
<body>
    <div class="container">
        <h1>My Events</h1>
        
        <?php
        if ($result->num_rows > 0) {
            // Display the events the user has access to
            echo "<table>";
            echo "<tr><th>Event Name</th><th>Date</th><th>Start Time</th><th>End Time</th><th>Volunteers</th></tr>";
            while ($row = $result->fetch_assoc()) {
                $eventID = $row['EventID'];
                
                // Fetch volunteers for the current event
                $volunteersQuery = "
                    SELECT u.Name, u.Phone
                    FROM User u
                    JOIN EventVolunteers ev ON u.UserID = ev.VolunteerID
                    WHERE ev.EventID = ?
                ";
                $volunteerStmt = $conn->prepare($volunteersQuery);
                $volunteerStmt->bind_param("i", $eventID);
                $volunteerStmt->execute();
                $volunteerResult = $volunteerStmt->get_result();

                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['EventName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['StartTime']) . "</td>";
                echo "<td>" . htmlspecialchars($row['EndTime']) . "</td>";

                // Display the list of volunteers for this event
                echo "<td class='volunteer-list'>";
                if ($volunteerResult->num_rows > 0) {
                    while ($volunteer = $volunteerResult->fetch_assoc()) {
                        echo "<div class='volunteer'><strong>Name:</strong> " . htmlspecialchars($volunteer['Name']) . " <br> <strong>Phone:</strong> " . htmlspecialchars($volunteer['Phone']) . "</div>";
                    }
                } else {
                    echo "<p>No volunteers assigned yet.</p>";
                }
                echo "</td>";

                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No events found for your access.</p>";
        }
        ?>
    </div>
</body>
</html>