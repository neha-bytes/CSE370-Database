<?php
session_start();
include 'connect.php';

if (!isset($conn)) {
    die("Database connection not established.");
}

// Ensure the user is logged in and is a Supervisor
if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'Supervisor') {
    die("Unauthorized access.");
}

$Supervisor_id = $_SESSION['UserID'];

// Fetch Supervisor details
$SupervisorQuery = "SELECT u.Name, u.Email, u.Phone, l.City, l.State, l.ZipCode, o.OrganizationID, S.TotalPeople, s.RequiredStorageCapacity, o.ORGName
                   FROM User u
                   JOIN Location l ON u.LocationID = l.LocationID
                   JOIN organization o on u.LocationID = o.LocationID
                   JOIN supervisor s on s.UserID = u.UserID
                   WHERE u.UserID = $Supervisor_id";
$SupervisorResult = $conn->query($SupervisorQuery);

if ($SupervisorResult->num_rows > 0) {
    $SupervisorData = $SupervisorResult->fetch_assoc();
} else {
    die("Supervisor details not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="container">
        <h1>Supervisor Dashboard</h1>
        
        <h2>Your Information</h2>
        <p><strong>Name:</strong> <?php echo $SupervisorData['Name']; ?></p>
        <p><strong>Email:</strong> <?php echo $SupervisorData['Email']; ?></p>
        <p><strong>Phone:</strong> <?php echo $SupervisorData['Phone']; ?></p>
        <p><strong>City:</strong> <?php echo $SupervisorData['City']; ?></p>
        <p><strong>State:</strong> <?php echo $SupervisorData['State']; ?></p>
        <p><strong>Zip Code:</strong> <?php echo $SupervisorData['ZipCode']; ?></p>
        <p><strong>OrganizationID:</strong> <?php echo $SupervisorData['OrganizationID']; ?></p>
        <p><strong>TotalPeople:</strong> <?php echo $SupervisorData['TotalPeople']; ?></p>
        <p><strong>RequiredStorageCapacity:</strong> <?php echo $SupervisorData['RequiredStorageCapacity']; ?></p>
        <p><strong>OrganizationName:</strong> <?php echo $SupervisorData['ORGName']; ?></p>
        
        <br>
        <h3>Your Events</h3>
        <p>Click below to view the events you are involved in:</p>
        <a href="donor_supervisor_events.php">View My Events</a>
        <br><br>

        <a href="index.php">Logout</a>
    </div>
</body>
</html>