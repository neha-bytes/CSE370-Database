<?php
session_start();
include 'connect.php';

if (!isset($conn)) {
    die("Database connection not established.");
}

// Ensure the user is logged in and is a volunteer
if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'Volunteer') {
    die("Unauthorized access.");
}

$volunteer_id = $_SESSION['UserID'];

// Fetch volunteer details
$volunteerQuery = "SELECT u.Name, u.Email, u.Phone, l.City, l.State, l.ZipCode
                   FROM User u
                   JOIN Location l ON u.LocationID = l.LocationID
                   WHERE u.UserID = $volunteer_id";
$volunteerResult = $conn->query($volunteerQuery);

if ($volunteerResult->num_rows > 0) {
    $volunteerData = $volunteerResult->fetch_assoc();
} else {
    die("Volunteer details not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Volunteer Dashboard</h1>
        
        <h2>Your Information</h2>
        <p><strong>Name:</strong> <?php echo $volunteerData['Name']; ?></p>
        <p><strong>Email:</strong> <?php echo $volunteerData['Email']; ?></p>
        <p><strong>Phone:</strong> <?php echo $volunteerData['Phone']; ?></p>
        <p><strong>City:</strong> <?php echo $volunteerData['City']; ?></p>
        <p><strong>State:</strong> <?php echo $volunteerData['State']; ?></p>
        <p><strong>Zip Code:</strong> <?php echo $volunteerData['ZipCode']; ?></p>

        <br>
        <h3>Your Events</h3>
        <p>Click below to view the events you are involved in:</p>
        <a href="volunteer_events.php">View My Events</a>
        <br><br>

        <a href="index.php">Logout</a>
    </div>
</body>
</html>