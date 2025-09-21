<?php
session_start();
include 'connect.php';

if (!isset($conn)) {
    die("Database connection not established.");
}
echo password_hash('1234', PASSWORD_DEFAULT);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['signIn'])) {

        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];

        $query = "SELECT * FROM User WHERE Email = '$email'";
        $result = $conn->query($query);

        if ($result->num_rows == 0) {
            echo "<script>alert('Email does not exist. Please check your credentials or Sign Up.');</script>";
        } else {
            $user = $result->fetch_assoc();
            $query2 = $user['Role'];
            
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];

        if ( $query2 == 'Admin' ){
            if (password_verify($password, $user['Password'])) {
                echo "<script>alert('Login successful!');</script>";
                header("location: admin.php");
                exit;
            } else {
                echo "<script>alert('Password does not match. Please try again.');</script>";
            }
        }
            if (password_verify($password, $user['Password'])) {
                echo "<script>alert('Login successful!');</script>";
                if ($query2 == 'Supervisor'){
                    header("location: supervisor.php");
                    exit;
                } elseif ( $query2 == 'Donor'){
                    header("location: donor.php");
                    exit;
                } elseif ( $query2 == 'Volunteer'){
                    header("location: volunteer.php");
                    exit;
                }

            } else {
                echo "<script>alert('Password does not match. Please try again.');</script>";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['signup'])) {

        $fname = $conn->real_escape_string($_POST['fname']); 
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $city = $conn->real_escape_string($_POST['city']);
        $state = $conn->real_escape_string($_POST['state']);
        $zipcode = $conn->real_escape_string($_POST['Zipcode']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
        var_dump($password);
        $userType = $conn->real_escape_string($_POST['userType']);
        $house = $conn->real_escape_string($_POST['houseNumber']);
        $road = $conn->real_escape_string($_POST['roadNo']);

        $locationQuery = "SELECT LocationID FROM Location WHERE City='$city' AND State='$state' AND ZipCode='$zipcode' AND RoadNo = '$road' AND HouseNO = '$house' ";
        $locationResult = $conn->query($locationQuery);

        if ($locationResult->num_rows > 0) {
            $locationID = $locationResult->fetch_assoc()['LocationID'];
        } else {
            $insertLocationQuery = "INSERT INTO Location (City, State, ZipCode,RoadNo,HouseNo) VALUES ('$city', '$state', '$zipcode','$road','$house')";
            if ($conn->query($insertLocationQuery)) {
                $locationID = $conn->insert_id;
            } else {
                die("Error inserting location: " . $conn->error);
            }
        }

        $userQuery = "INSERT INTO User (Name, Email, Phone, Role, Password, LocationID) 
                      VALUES ('$fname', '$email', '$phone', '$userType', '$password', $locationID)";
        if ($conn->query($userQuery)) {
            echo "Signup successful!";
        } else {
            echo "Error: " . $conn->error;
        }

        $userID = $conn->insert_id; 
        if ($userType == 'donor') {
            $donationAmount = $conn->real_escape_string($_POST['donationAmount']);
            $donorQuery = "INSERT INTO donorstorage (LocationID, DonorID, Quantity) 
                           VALUES ($locationID, $userID , $donationAmount)";
            if (!$conn->query($donorQuery)) {
                die("Error in Donor Query: " . $conn->error);
            } else {
                echo "Donor data inserted successfully.";
            }
        } elseif ($userType == 'supervisor') {
            $orgName = $conn->real_escape_string($_POST['orgName']);
            $totalPeople = $conn->real_escape_string($_POST['total']);
            $requiredStorage = $conn->real_escape_string($_POST['st']);
        
            $organizationQuery = "INSERT INTO Organization (ORGName, LocationID) 
                                  VALUES ('$orgName', $locationID)";
            if ($conn->query($organizationQuery)) {
                $organizationID = $conn->insert_id;
        
                $supervisorQuery = "INSERT INTO Supervisor (UserID, OrganizationID, TotalPeople, RequiredStorageCapacity) 
                                    VALUES ($userID, $organizationID, $totalPeople, $requiredStorage)";
                if (!$conn->query($supervisorQuery)) {
                    die("Error in Supervisor Query: " . $conn->error);
                } else {
                    echo "Supervisor data inserted successfully.";
                }
            } else {
                die("Error in Organization Query: " . $conn->error);
            }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" id="signup" style="display: none;">
        <h1 class="form-title">Register</h1>
        <form method="post" action="">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fname" id="fname" placeholder="Full Name" required>
                <label for="fname">Full Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phone" id="phone" placeholder="Phone Number" pattern="[0-9]{11}" title="Please enter a 10-digit phone number" required>
                <label for="phone">Phone Number</label>
            </div>
            
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-city"></i>
                <input type="text" name="city" id="city" placeholder="City Name" required>
                <label for="city">City Name</label>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-location-pin"></i>
                <input type="text" name="state" id="state" placeholder="State Name" required>
                <label for="state">State Name</label>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-address-book"></i>
                <input type="text" name="Zipcode" id="Zipcode" placeholder="Zipcode" required>
                <label for="Zipcode">Zipcode</label>
            </div>
            <div class="input-group">
                    <i class="fa-solid fa-house"></i>
                    <input type="text" name="houseNumber" id="houseNumber" placeholder="House number" required>
                    <label for="houseNumber">House number</label>
            </div>
            <div class="input-group">
                    <i class="fa-solid fa-road"></i>
                    <input type="text" name="roadNo" id="roadNo" placeholder="Road number" required>
                    <label for="roadNo">Road number</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            
            <div class="input-group">
                <label for="userType"></label>
                <select id="userType" name="userType" onchange="toggleUserSpecificFields(this.value)" required>
                    <option value="">Choose your designation</option>
                    <option value="donor">Donor</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="volunteer">Volunteer</option>
                </select>
            </div>

            <div id="donorFields" class="user-type-specific">
                <div class="input-group">
                    <i class="fas fa-donate"></i>
                    <input type="number" name="donationAmount" id="donationAmount" placeholder="Donation Amount" min="1" step="1" required oninput="validateMinimumValue(this)">
                    <label for="donationAmount">Donation Amount</label>
                </div>
            </div> 

            <div id="supervisorFields" class="user-type-specific">
                <div class="input-group">
                    <i class="fas fa-project-diagram"></i>
                    <input type="text" name="orgName" id="orgName" placeholder="Organization Name" required>
                    <label for="orgName">Organization Name</label>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-person"></i>
                    <input type="number" name="total" id="total" placeholder="Total people in Organization"  min="1" step="1" required oninput="validateMinimumValue(this)">
                    <label for="total">Total people in Organization</label>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-warehouse"></i>
                    <input type="number" name="st" id="st" placeholder="Required storage Capacity" required>
                    <label for="st">Required storage Capacity</label>
                </div>
            </div>

            <div id="volunteerFields" class="user-type-specific">
                <div class="input-group">
                    <i class="fas fa-tools"></i>
                    <input type="text" name="skills" id="skills" placeholder="Your Skills" required>
                    <label for="skills">Skills</label>
                </div>
            </div>

            <input type="submit" class="btn" value="Sign Up" name="signup">
        </form>
        <div class="links">
            <p>Already have an Account?</p>    
            <button id = 'signInButton'>Sign In</button>
        </div>        
    </div>
    <div class="container" id="signIn">
        <h1 class="form-title">Sign In</h1>
        <form method="post" action="">
          <div class="input-group">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" id="email" placeholder="Email" required>
              <label for="email">Email</label>
          </div>
          <div class="input-group">
              <i class="fas fa-lock"></i>
              <input type="password" name="password" id="password" placeholder="Password" required>
              <label for="password">Password</label>
          </div>
         <input type="submit" class="btn" value="Sign In" name="signIn">
        </form>
        <div class="links">
            <p>Don't have an Account?</p>    
            <button id = 'signUpButton'>Sign Up</button>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>