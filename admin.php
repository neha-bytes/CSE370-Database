<?php
session_start();
include 'connect.php';

if (!isset($conn)) {
    die("Database connection not established.");
}

if (!isset($_SESSION['UserID']) || $_SESSION['role'] != 'Admin') {
    die("Unauthorized access.");
}

$admin_id = $_SESSION['UserID'];

$adminQuery = "SELECT * FROM User WHERE UserID = ?";

$adminStmt = $conn->prepare($adminQuery);
$adminStmt->bind_param("i", $admin_id);
$adminStmt->execute();
$adminResult = $adminStmt->get_result();
$adminData = $adminResult->fetch_assoc();

// Event Creation Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $eventName = $_POST['event_name'];
    $cityName = $_POST['city_name'];
    $date = $_POST['date'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];

    $locationQuery = "SELECT LocationID FROM Location WHERE City = ?";
    $stmt = $conn->prepare($locationQuery);
    $stmt->bind_param("s", $cityName);
    $stmt->execute();
    $locationResult = $stmt->get_result();

    if ($locationResult->num_rows > 0) {
        $location = $locationResult->fetch_assoc();
        $locationID = $location['LocationID'];

        $createEventQuery = "INSERT INTO Event (EventName, LocationID, Date, StartTime, EndTime, CreatedBy) 
                             VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($createEventQuery);
        $stmt->bind_param("sisssi", $eventName, $locationID, $date, $startTime, $endTime, $admin_id);

        if ($stmt->execute()) {
            $eventID = $stmt->insert_id;
            echo "<p>Event created successfully with ID: $eventID</p>";
        } else {
            echo "<p>Error creating event: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Invalid city name.</p>";
    }
}


// From here participants Assign on event work starts 
$participants = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_participants'])) {
    $cityName = $_POST['city_name'];

    $fetchQuery = "
        SELECT u.UserID, u.Name, u.Role 
        FROM User u
        JOIN Location l ON u.LocationID = l.LocationID
        WHERE l.City = ?";
    $stmt = $conn->prepare($fetchQuery);
    $stmt->bind_param("s", $cityName);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $participants[] = $row;
    }
    if (empty($participants)) {
        echo "<p>No participants found in $cityName.</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_participants'])) {
    $eventID = $_POST['eventID']; 
    $donors = $_POST['donors'] ?? []; 
    $volunteers = $_POST['volunteers'] ?? [];
    $supervisors = $_POST['supervisor'] ?? []; 

    foreach ($donors as $donorID) {
        $pickupTime = $_POST['pickup_time_' . $donorID];
        $addDonorQuery = "INSERT INTO EventDonors (EventID, DonorID, PickupTime) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($addDonorQuery);
        $stmt->bind_param("iis", $eventID, $donorID, $pickupTime);
        if (!$stmt->execute()) {
            echo "<p>Error adding donor $donorID: " . $stmt->error . "</p>";
        }
    }

    foreach ($supervisors as $userID) {
        $checkSupervisorQuery = "SELECT SupervisorID FROM Supervisor WHERE UserID = ?";
        $checkStmt = $conn->prepare($checkSupervisorQuery);
        $checkStmt->bind_param("i", $userID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
    
        if ($result->num_rows > 0) {
            $supervisor = $result->fetch_assoc();
            $supervisorID = $supervisor['SupervisorID'];
            $deliveryTime = $_POST['delivery_time_' . $userID];
            $addSupervisorQuery = "INSERT INTO EventSupervisor (EventID, SupervisorID, DeliveryTime) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($addSupervisorQuery);
            $stmt->bind_param("iis", $eventID, $supervisorID, $deliveryTime);
    
            if (!$stmt->execute()) {
                echo "<p>Error adding supervisor $userID: " . $stmt->error . "</p>";
            } else {
                echo "<p>Supervisor $userID added successfully to Event $eventID.</p>";
            }
        } else {
            echo "<p>No supervisor found for UserID $userID. Skipping...</p>";
        }
    }
    
    foreach ($volunteers as $volunteerID) {
        $addVolunteerQuery = "INSERT INTO EventVolunteers (EventID, VolunteerID) VALUES (?, ?)";
        $stmt = $conn->prepare($addVolunteerQuery);
        $stmt->bind_param("ii", $eventID, $volunteerID);
        if (!$stmt->execute()) {
            echo "<p>Error adding volunteer $volunteerID: " . $stmt->error . "</p>";
        }
    }

    echo "<p>Participants added successfully to Event ID: $eventID.</p>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style3.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <h2>Admin Information</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($adminData['Name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($adminData['Email']); ?></p>

    <h2>User Information</h2>
    <form method="POST" action="user_info.php">
        <button type="submit" name="fetch_participants">User Informations</button>
    </form>


    <h2>Create Event</h2>
    <form method="POST" action="">
        <label for="event_name">Event Name:</label>
        <input type="text" name="event_name" id="event_name" required><br><br>

        <label for="city_name">City:</label>
        <input type="text" name="city_name" id="city_name" required><br><br>

        <label for="date">Event Date:</label>
        <input type="date" name="date" id="date" required><br><br>

        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" id="start_time" required><br><br>

        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" id="end_time" required><br><br>

        <button type="submit" name="create_event">Create Event</button>
    </form>

    <h2>Fetch Participants</h2>
    <form method="POST" action="">
        <label for="city_name">City:</label>
        <input type="text" name="city_name" id="city_name" required><br><br>
        <button type="submit" name="fetch_participants">Fetch Participants</button>
    </form>

    <?php if (!empty($participants)) { ?>
        <h3>Participants in Selected City</h3>
        <form method="POST" action="">
            <label for="eventID">Event ID:</label>
            <input type="number" name="eventID" id="eventID" required><br><br>

            <h4>Donors</h4>
            <?php foreach ($participants as $participant) {
                if ($participant['Role'] === 'Donor') { ?>
                    <input type="checkbox" name="donors[]" value="<?php echo $participant['UserID']; ?>">
                    <?php echo htmlspecialchars($participant['Name']); ?><br>
                    <label for="pickup_time_<?php echo $participant['UserID']; ?>">Pickup Time:</label>
                    <input type="time" name="pickup_time_<?php echo $participant['UserID']; ?>"><br><br>
            <?php }} ?>

            <h4>Supervisors</h4>
            <?php foreach ($participants as $participant) {
                if ($participant['Role'] === 'Supervisor') { ?>
                    <input type="checkbox" name="supervisor[]" value="<?php echo $participant['UserID']; ?>">
                    <?php echo htmlspecialchars($participant['Name']); ?><br>
                    <label for="delivery_time_<?php echo $participant['UserID']; ?>">Delivery Time:</label>
                    <input type="time" name="delivery_time_<?php echo $participant['UserID']; ?>"><br><br>
            <?php }} ?>


            <h4>Volunteers</h4>
            <?php foreach ($participants as $participant) {
                if ($participant['Role'] === 'Volunteer') { ?>
                    <input type="checkbox" name="volunteers[]" value="<?php echo $participant['UserID']; ?>">
                    <?php echo htmlspecialchars($participant['Name']); ?><br><br>
            <?php }} ?>

            <button type="submit" name="add_participants">Add Participants</button>
        </form>
        
    <?php } ?>
    <form method="GET" action="ongoing_events.php">
        <button type="submit">Ongoing Events</button>
    </form>

    <form method="GET" action="drop_event.php">
        <button type="submit">Check Expired Events</button>
    </form>
    <form method="GET" action="index.php">
        <button type="submit">Logout</button>
    </form>


</body>
</html>