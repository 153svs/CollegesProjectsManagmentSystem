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

// Handle cancel button click
if (isset($_POST['cancel_invite'])) {
    $receiver_name = $_POST['receiver_name'];

    // Delete the invitation from the database
    $sql = "DELETE FROM invitations WHERE sender_name = '$username' AND receiver_name = '$receiver_name' AND status = 'Pending'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Invitation canceled successfully');</script>";
    } else {
        echo "<script>alert('Error canceling invitation');</script>";
    }
}

// Fetch the sent invitations for the logged-in user
$sql = "SELECT receiver_name FROM invitations WHERE sender_name = '$username' AND status = 'Pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sent Invitations</title>
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Sent Invitations</h1>

    <!-- Display Sent Invitations in Table Format -->
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Is He Member</th>
                <th>Is He Admin</th>
                <th>Cancel</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($invite = $result->fetch_assoc()) {
                    $receiver_name = $invite['receiver_name'];

                    // Fetch Member and Admin statuses
                    $status_query = "SELECT member, admin FROM user_student WHERE name = '$receiver_name'";
                    $status_result = $conn->query($status_query);
                    $status_row = $status_result->fetch_assoc();

                    $is_member = $status_row['member'];
                    $is_admin = $status_row['admin'];

                    echo "<tr>
                        <td>$receiver_name</td>
                        <td>$is_member</td>
                        <td>$is_admin</td>
                        <td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='receiver_name' value='$receiver_name'>
                                <button type='submit' name='cancel_invite'>Cancel</button>
                            </form>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No sent invitations</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Link back to Dashboard -->
    <div style="text-align: center;">
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
