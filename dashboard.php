<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

// Include the database connection
include('db_connection.php');

// Get the username of the logged-in user
$username = $_SESSION['username'];

// Get the division of the logged-in user
$sql = "SELECT division FROM user_student WHERE name = '$username'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$division = $row['division'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Dashboard</title>
</head>
<body>
    <h1>Welcome to your Profile Dashboard</h1>
    <p><strong>Name:</strong> <?php echo $username; ?></p>
    <p><strong>Division:</strong> <?php echo $division; ?></p>
    
    <!-- Log Out Button -->
    <form action="logout.php" method="POST">
        <button type="submit" name="logout">Log Out</button>
    </form>
    
    <!-- Buttons to navigate to Sent and Received Invitations -->
    <h3>Manage Invitations</h3>
    <a href="sent_invitations.php">View Sent Invitations</a><br>
    <a href="received_invitations.php">View Received Invitations</a><br>

    <!-- Link back to Student List -->
    <a href="list.php">View Student List</a><br>

    <!-- Group Button (Placeholder for functionality) -->
    <h3>Group Management</h3>
    <a href="group.php"><button type="button">Your Group</button></a>
</body>
</html>
