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
        echo "Supervisor ID: " . $supervisorID . "<br>";  // Debugging supervisor ID output
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
            echo "<tr><th>Event Name</th><th>Date</th><th>Start Time</th><th>End Time</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['EventName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['StartTime']) . "</td>";
                echo "<td>" . htmlspecialchars($row['EndTime']) . "</td>";
                echo "</tr>";
                
                // Now fetch the donors and supervisors for each event
                $eventID = $row['EventID'];
                
                // Fetch Donors
                $donorsQuery = "
                    SELECT u.UserID, u.Name , u.Phone, l.City,l.State,l.Zipcode,l.RoadNo,l.HouseNo
                    FROM User u 
                    JOIN EventDonors ed ON u.UserID = ed.DonorID 
                    JOIN location l on l.LocationId = u.LocationID
                    WHERE ed.EventID = ?";
                $donorStmt = $conn->prepare($donorsQuery);
                $donorStmt->bind_param("i", $eventID);
                $donorStmt->execute();
                $donorResult = $donorStmt->get_result();

                // Fetch Supervisors
                $supervisorsQuery = "
                    SELECT u.UserID, u.Name ,u.Phone , l.City,l.State,l.Zipcode,l.RoadNo,l.HouseNo
                    FROM User u 
                    JOIN supervisor s on s.userID = u.userID
                    JOIN EventSupervisor es ON s.SupervisorID = es.SupervisorID 
                    JOIN location l on l.LocationId = u.LocationID
                    WHERE es.EventID = ?";
                $supervisorStmt = $conn->prepare($supervisorsQuery);
                $supervisorStmt->bind_param("i", $eventID);
                $supervisorStmt->execute();
                $supervisorResult = $supervisorStmt->get_result();

                // Display donors for this event
                echo "<h4>Donors:</h4>";
                if ($donorResult->num_rows > 0) {
                    while ($donor = $donorResult->fetch_assoc()) {
                        echo "<div><strong>Name:</strong> " . htmlspecialchars($donor['Name']) .
                         " <br> <strong>Phone:</strong> " . htmlspecialchars($donor['Phone']) .
                         " <br> <strong>City:</strong> " . htmlspecialchars($donor['City']) .
                         " <br> <strong>State:</strong> " . htmlspecialchars($donor['State']) .
                         " <br> <strong>RoadNo:</strong> " . htmlspecialchars($donor['RoadNo']) .
                         " <br> <strong>HouseNo:</strong> " . htmlspecialchars($donor['HouseNo']) .
                         
                         "</div>";
                    }
                } else {
                    echo "<p>No donors for this event.</p>";
                }

                // Display supervisors for this event
                echo "<h4>Supervisors:</h4>";
                if ($supervisorResult->num_rows > 0) {
                    while ($supervisor = $supervisorResult->fetch_assoc()) {
                         echo "<div><strong>Name:</strong> " . htmlspecialchars($supervisor['Name']) .
                          " <br> <strong>Phone:</strong> " . htmlspecialchars($supervisor['Phone']) .
                          " <br> <strong>City:</strong> " . htmlspecialchars($supervisor['City']) .
                          " <br> <strong>State:</strong> " . htmlspecialchars($supervisor['State']) .
                          " <br> <strong>RoadNo:</strong> " . htmlspecialchars($supervisor['RoadNo']) .
                          " <br> <strong>HouseNo:</strong> " . htmlspecialchars($supervisor['HouseNo']) .
                          
                          "</div>";
                    }
                } else {
                    echo "<p>No supervisors for this event.</p>";
                }
            }
            echo "</table>";
        } else {
            echo "<p>You have no events assigned.</p>";
        }
        ?>
    </div>
</body>
</html>