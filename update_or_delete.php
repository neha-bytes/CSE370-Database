<?php
session_start();
include 'connect.php';

if (!isset($conn)) {
    die("Database connection not established.");
}

if (!isset($_SESSION['UserID']) || $_SESSION['role'] != 'Admin') {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $userID = $_POST['UserID'] ?? '';
    $role = $_POST['Role'] ?? '';

    if (empty($userID) || empty($role)) {
        die("Invalid request.");
    }

    if ($action === 'delete') {
        // Handle delete action
        try {
            $conn->begin_transaction();

            // Delete user from specific role-related tables
            if ($role === 'Donor') {
                $conn->query("DELETE FROM donorstorage WHERE DonorID = $userID");
            } elseif ($role === 'Supervisor') {
                $conn->query("DELETE FROM Supervisor WHERE UserID = $userID");
            }

            // Delete user from User table
            $conn->query("DELETE FROM User WHERE UserID = $userID");

            $conn->commit();
            echo "User successfully deleted.";
        } catch (Exception $e) {
            $conn->rollback();
            die("Error deleting user: " . $e->getMessage());
        }

    } elseif ($action === 'update') {
        // Fetch user details for update
        $userQuery = $conn->prepare("SELECT * FROM User WHERE UserID = ?");
        $userQuery->bind_param("i", $userID);
        $userQuery->execute();
        $userResult = $userQuery->get_result();
        $userData = $userResult->fetch_assoc();

        if (!$userData) {
            die("User not found.");
        }

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Update User</title>
        </head>
        <body>
            <h1>Update User</h1>
            <form action="update_or_delete.php" method="POST">
                <input type="hidden" name="UserID" value="<?php echo htmlspecialchars($userID); ?>">
                <input type="hidden" name="Role" value="<?php echo htmlspecialchars($role); ?>">
                <label for="Name">Name:</label>
                <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($userData['Name']); ?>" required><br>

                <label for="Email">Email:</label>
                <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($userData['Email']); ?>" required><br>

                <label for="Phone">Phone:</label>
                <input type="text" id="Phone" name="Phone" value="<?php echo htmlspecialchars($userData['Phone']); ?>" required><br>

                <button type="submit" name="action" value="save_update">Save</button>
            </form>
        </body>
        </html>
        <?php
    } elseif ($action === 'save_update') {
        // Save updated user details
        $userID = $_POST['UserID'];
        $name = $_POST['Name'];
        $email = $_POST['Email'];
        $phone = $_POST['Phone'];

        $updateQuery = $conn->prepare("UPDATE User SET Name = ?, Email = ?, Phone = ? WHERE UserID = ?");
        $updateQuery->bind_param("sssi", $name, $email, $phone, $userID);

        if ($updateQuery->execute()) {
            echo "User successfully updated.";
        } else {
            die("Error updating user: " . $conn->error);
        }
    } else {
        die("Invalid action.");
    }
} else {
    die("Invalid request method.");
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
    <form method="GET" action="user_info.php">
    <button type="submit">Back</button>
</body>
</html>