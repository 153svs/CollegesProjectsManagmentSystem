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

// Handle reject button click
if (isset($_POST['reject_invite'])) {
    $sender_name = $_POST['sender_name'];
    $sql = "DELETE FROM invitations WHERE sender_name = '$sender_name' AND receiver_name = '$username' AND status = 'Pending'";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Invitation rejected and removed.');</script>";
    } else {
        echo "<script>alert('Error rejecting invitation.');</script>";
    }
}

// Handle accept button click
if (isset($_POST['accept_invite'])) {
    $sender_name = $_POST['sender_name'];

    // Check if the receiver is already a member of any team
    $check_member_sql = "SELECT member FROM user_student WHERE name = '$username'";
    $member_result = $conn->query($check_member_sql);
    $member_status = $member_result->fetch_assoc();

    if ($member_status['member'] == 'YES') {
        echo "<script>alert('You are already a member of a team and cannot accept invitations.');</script>";
    } else {
        // Update member status and assign the team
        $update_receiver_sql = "UPDATE user_student SET member = 'YES', team = '$sender_name' WHERE name = '$username'";
        $update_sender_sql = "UPDATE user_student SET member = 'YES', admin = 'YES' WHERE name = '$sender_name'";

        if ($conn->query($update_receiver_sql) === TRUE && $conn->query($update_sender_sql) === TRUE) {
            echo "<script>alert('Accepted invitation from $sender_name. Team and status updated.');</script>";

            // Mark the invitation as accepted
            $update_invitation_sql = "UPDATE invitations SET status = 'Accepted' WHERE sender_name = '$sender_name' AND receiver_name = '$username'";
            $conn->query($update_invitation_sql);

            // Now, delete the accepted invitation from the invitations table
            $delete_accepted_invitation_sql = "DELETE FROM invitations WHERE sender_name = '$sender_name' AND receiver_name = '$username' AND status = 'Accepted'";
            $conn->query($delete_accepted_invitation_sql); // Delete accepted invitation from table

            // Delete all sent invitations from the current user (this deletes invitations that were sent by the logged-in user)
            $delete_sent_sql = "DELETE FROM invitations WHERE sender_name = '$username' AND status = 'Pending'";
            $conn->query($delete_sent_sql);  // Deletes all sent invitations

            // Run the 'team_member_count.php' to update the team member count
            // Include and run the team_member_count.php script here
            include('team_member_count.php');  // This will run the script to update the team counts
        } else {
            echo "<script>alert('Error updating team and status.');</script>";
        }
    }
}

// Fetch received invitations for the logged-in user
$sql = "SELECT sender_name FROM invitations WHERE receiver_name = '$username' AND status = 'Pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Received Invitations</title>
    <style>
        table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .action-button {
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Received Invitations</h1>

    <!-- Table for Received Invitations -->
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Is He a Member?</th>
                <th>Is He Admin?</th>
                <th>Accept</th>
                <th>Reject</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($invite = $result->fetch_assoc()) {
                    $sender_name = $invite['sender_name'];

                    // Fetch Member and Admin status of the sender (X or Z)
                    $status_query = "SELECT member, admin FROM user_student WHERE name = '$sender_name'";
                    $status_result = $conn->query($status_query);
                    $status = $status_result->fetch_assoc();

                    $member_status = $status['member'] === 'YES' ? 'Yes' : 'No';
                    $admin_status = $status['admin'] === 'YES' ? 'Yes' : 'No';

                    // Check if the current user (Y) is already a member
                    $check_user_sql = "SELECT member FROM user_student WHERE name = '$username'";
                    $user_result = $conn->query($check_user_sql);
                    $user_data = $user_result->fetch_assoc();
                    $user_member_status = $user_data['member'] == 'YES' ? 'Yes' : 'No';

                    // Now, display the invitation rows without the "Disable Accept Button" condition
                    echo "<tr>
                        <td>$sender_name</td>
                        <td>$member_status</td>
                        <td>$admin_status</td>
                        <td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='sender_name' value='$sender_name'>
                                <button type='submit' name='accept_invite' class='action-button'>Accept</button>
                            </form>
                        </td>
                        <td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='sender_name' value='$sender_name'>
                                <button type='submit' name='reject_invite' class='action-button'>Reject</button>
                            </form>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr>
                    <td colspan='5'>No received invitations</td>
                </tr>";
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
