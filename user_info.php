<?php 
session_start();
include 'connect.php';

if (!isset($conn)) {
    die("Database connection not established.");
}

if (!isset($_SESSION['UserID']) || $_SESSION['role'] != 'Admin') {
    die("Unauthorized access.");
}

$donorQuery = "SELECT u.UserID, u.Name, u.Email, u.Phone, d.Quantity, l.City, l.State, l.ZipCode
                FROM User u
                JOIN donorstorage d ON u.UserID = d.DonorID
                JOIN Location l ON u.LocationID = l.LocationID
                WHERE u.Role = 'Donor'";
$donorResult = $conn->query($donorQuery);

$volunteerQuery = "SELECT u.UserID, u.Name, u.Email, u.Phone, l.City, l.State, l.ZipCode
                    FROM User u
                    JOIN Location l ON u.LocationID = l.LocationID
                    WHERE u.Role = 'Volunteer'";
$volunteerResult = $conn->query($volunteerQuery);

$supervisorQuery = "SELECT u.UserID, u.Name, u.Email, u.Phone, o.ORGName AS OrganizationName, s.TotalPeople, s.RequiredStorageCapacity, l.City, l.State, l.ZipCode
                     FROM User u
                     JOIN Supervisor s ON u.UserID = s.UserID
                     JOIN Organization o ON s.OrganizationID = o.OrganizationID
                     JOIN Location l ON u.LocationID = l.LocationID
                     WHERE u.Role = 'Supervisor'";
$supervisorResult = $conn->query($supervisorQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="container">
        <h1>Registered User Informations</h1>

        <h2>Donors</h2>
        <table border="1">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Donation Quantity</th>
                <th>City</th>
                <th>State</th>
                <th>ZipCode</th>
                <th>Actions</th>
            </tr>
            <?php while ($donor = $donorResult->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($donor['Name']); ?></td>
                    <td><?php echo htmlspecialchars($donor['Email']); ?></td>
                    <td><?php echo htmlspecialchars($donor['Phone']); ?></td>
                    <td><?php echo htmlspecialchars($donor['Quantity']); ?></td>
                    <td><?php echo htmlspecialchars($donor['City']); ?></td>
                    <td><?php echo htmlspecialchars($donor['State']); ?></td>
                    <td><?php echo htmlspecialchars($donor['ZipCode']); ?></td>
                    <td>
                        <form action="update_or_delete.php" method="POST" style="display:inline;">
                            <input type="hidden" name="UserID" value="<?php echo $donor['UserID']; ?>">
                            <input type="hidden" name="Role" value="Donor">
                            <button type="submit" name="action" value="update">Update</button>
                            <button type="submit" name="action" value="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <h2>Volunteers</h2>
        <table border="1">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>City</th>
                <th>State</th>
                <th>ZipCode</th>
                <th>Actions</th>
            </tr>
            <?php while ($volunteer = $volunteerResult->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($volunteer['Name']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['Email']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['Phone']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['City']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['State']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['ZipCode']); ?></td>
                    <td>
                        <form action="update_or_delete.php" method="POST" style="display:inline;">
                            <input type="hidden" name="UserID" value="<?php echo $volunteer['UserID']; ?>">
                            <input type="hidden" name="Role" value="Volunteer">
                            <button type="submit" name="action" value="update">Update</button>
                            <button type="submit" name="action" value="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <h2>Supervisors</h2>
        <table border="1">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Organization Name</th>
                <th>Total People</th>
                <th>Required Storage Capacity</th>
                <th>City</th>
                <th>State</th>
                <th>ZipCode</th>
                <th>Actions</th>
            </tr>
            <?php while ($supervisor = $supervisorResult->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($supervisor['Name']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['Email']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['Phone']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['OrganizationName']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['TotalPeople']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['RequiredStorageCapacity']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['City']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['State']); ?></td>
                    <td><?php echo htmlspecialchars($supervisor['ZipCode']); ?></td>
                    <td>
                        <form action="update_or_delete.php" method="POST" style="display:inline;">
                            <input type="hidden" name="UserID" value="<?php echo $supervisor['UserID']; ?>">
                            <input type="hidden" name="Role" value="Supervisor">
                            <button type="submit" name="action" value="update">Update</button>
                            <button type="submit" name="action" value="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

    </div>
</body>
</html>