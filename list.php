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

// Get the division, membership status, admin status, and team of the logged-in user
$sql = "SELECT division, member, admin, team FROM user_student WHERE name = '$username'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$division = $row['division'];
$membership_status = $row['member']; // "YES" or "NO"
$is_admin = $row['admin']; // "YES" or "NO"
$team = $row['team']; // Team name of the logged-in user

// Fetch the count of members in the logged-in user's team (including the user themselves)
$team_member_count_sql = "SELECT count_of_team_member FROM user_student WHERE name = '$username'";
$team_member_count_result = $conn->query($team_member_count_sql);
$team_member_count_row = $team_member_count_result->fetch_assoc();
$team_member_count = $team_member_count_row['count_of_team_member'];

// Fetch all students from the same division along with their membership status
$sql = "SELECT name, member FROM user_student WHERE division = '$division'";
$students = $conn->query($sql);

// Variable to store error message
$error_message = "";

// Handle invite button click
if (isset($_POST['invite'])) {
    $receiver_name = $_POST['receiver_name'];

    // Check if the user has already sent an invitation
    $check_sent = "SELECT * FROM invitations WHERE sender_name = '$username' AND status = 'Pending'";
    $check_sent_result = $conn->query($check_sent);

    // If the team already has 3 members, prevent sending more invitations
    if ($team_member_count < 3 && $check_sent_result->num_rows < (3 - $team_member_count)) {
        // Check if an invitation has already been sent to the receiver
        $check_invite = "SELECT * FROM invitations WHERE sender_name = '$username' AND receiver_name = '$receiver_name' AND status = 'Pending'";
        $check_result = $conn->query($check_invite);

        if ($check_result->num_rows == 0) {
            // Insert invitation into the database
            $sql = "INSERT INTO invitations (sender_name, receiver_name, status) VALUES ('$username', '$receiver_name', 'Pending')";
            
            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Invitation sent to $receiver_name');</script>";
            } else {
                echo "<script>alert('Error sending invitation.');</script>";
            }
        } else {
            echo "<script>alert('You have already sent an invitation to $receiver_name.');</script>";
        }
    } else {
        // Set the error message for the maximum limit reached
        $error_message = "You can only send a maximum of " . (3 - $team_member_count) . " invitations.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <style>
        .error-message {
            color: red;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .status-row {
            font-weight: bold;
        }
        .disabled-button {
            background-color: grey;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Display the number of members in the logged-in user's group -->
    <div class="status-row">
        <p>Logged-in User: <?php echo $username; ?> | Membership Status: <?php echo $membership_status; ?> | Members in Your Team: <?php echo $team_member_count; ?></p>
    </div>

    <!-- Display error message if any -->
    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <h1>Student List (Division: <?php echo $division; ?>)</h1>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Member</th>
                <th>Invite</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Loop through the students and display their names with "Member" status and "Invite" button
            while ($student = $students->fetch_assoc()) {
                $student_name = $student['name'];
                $member_status = $student['member'];
                
                // Only show the "Invite" button if it's not the logged-in user
                if ($student_name != $username) {
                    // Check if an invitation has already been sent or received
                    $invited = false;
                    
                    // Check if the logged-in user has already sent an invitation to this student
                    $check_invite_sent = "SELECT * FROM invitations WHERE sender_name = '$username' AND receiver_name = '$student_name' AND status = 'Pending'";
                    $check_invite_received = "SELECT * FROM invitations WHERE sender_name = '$student_name' AND receiver_name = '$username' AND status = 'Pending'";
                    
                    // If an invitation has been sent or received, do not show the invite button
                    $check_sent_result = $conn->query($check_invite_sent);
                    $check_received_result = $conn->query($check_invite_received);

                    if ($check_sent_result->num_rows > 0 || $check_received_result->num_rows > 0) {
                        $invited = true;
                    }

                    // Logic to enable/disable the "Invite" button based on user status
                    if ($membership_status == 'YES' && $is_admin == 'NO') {
                        // Disable the button if the user is just a member
                        echo "<tr>
                            <td>$student_name</td>
                            <td>$member_status</td>
                            <td>
                                <button class='disabled-button' disabled>Invite</button>
                            </td>
                        </tr>";
                    } else {
                        // Enable the button for admin or non-member users
                        if ($team_member_count < 3 && !$invited) {
                            echo "<tr>
                                <td>$student_name</td>
                                <td>$member_status</td>
                                <td>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='receiver_name' value='$student_name'>
                                        <button type='submit' name='invite'>Invite</button>
                                    </form>
                                </td>
                            </tr>";
                        } else {
                            echo "<tr>
                                <td>$student_name</td>
                                <td>$member_status</td>
                                <td>
                                    <button class='disabled-button' disabled>Invite</button>
                                </td>
                            </tr>";
                        }
                    }
                }
            }
            ?>
        </tbody>
    </table>
    
    <!-- Link back to Profile Dashboard -->
    <a href="dashboard.php">Back to Profile Dashboard</a>
</body>
</html>
