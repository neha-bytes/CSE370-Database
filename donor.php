<?php
session_start();
include 'connect.php';

if (!isset($conn)) {
    die("Database connection not established.");
}

if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'Donor') {
    die("Unauthorized access.");
}

$donor_id = $_SESSION['UserID'];

$DonorQuery = "SELECT u.Name, u.Email, u.Phone, l.City, l.State, l.ZipCode, d.StorageID, d.Quantity
                   FROM User u
                   JOIN Location l ON u.LocationID = l.LocationID
                   JOIN donorstorage d ON u.UserID = d.DonorID
                   WHERE u.UserID = ?";

$stmt = $conn->prepare($DonorQuery);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$DonorResult = $stmt->get_result();

if ($DonorResult->num_rows > 0) {
    $DonorData = $DonorResult->fetch_assoc();
} else {
    die("Donor details not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Donor Dashboard</h1>
        
        <h2>Your Information</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($DonorData['Name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($DonorData['Email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($DonorData['Phone']); ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($DonorData['City']); ?></p>
        <p><strong>State:</strong> <?php echo htmlspecialchars($DonorData['State']); ?></p>
        <p><strong>Zip Code:</strong> <?php echo htmlspecialchars($DonorData['ZipCode']); ?></p>
        <p><strong>StorageID:</strong> <?php echo htmlspecialchars($DonorData['StorageID']); ?></p>
        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($DonorData['Quantity']); ?></p>

        <br>
        <h3>Your Events</h3>
        <p>Click below to view the events you are involved in:</p>
        <a href="donor_supervisor_events.php">View My Events</a>
        <br><br>



        <a href="index.php">Logout</a>
    </div>
</body>
</html>