<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

// Include the database connection
include('db_connection.php');

// Include the script to update team member count
include('team_member_count.php'); // Include this to ensure count is updated after leaving the group

// Get the username of the logged-in user
$username = $_SESSION['username'];

// Get the team and admin status of the logged-in user
$sql = "SELECT team, admin FROM user_student WHERE name = '$username'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$team = $row['team'];
$admin = $row['admin']; // 'YES' if admin, 'NO' otherwise

// Fetch students whose team matches the logged-in user's team
$sql = "SELECT name, team FROM user_student WHERE team = '$team'";
$result = $conn->query($sql);

// Handle leave group functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_group'])) {
    if ($admin === 'YES') {
        // If admin leaves the group
        $updateAdmin = "UPDATE user_student SET member = 'NO', admin = 'NO' WHERE name = '$username'";
        $conn->query($updateAdmin);

        // Kick out all other members
        $kickMembers = "UPDATE user_student SET team = name, member = 'NO' WHERE team = '$team' AND name != '$username'";
        $conn->query($kickMembers);

    } else {
        // If a member leaves the group
        $updateMember = "UPDATE user_student SET team = name, member = 'NO' WHERE name = '$username'";
        $conn->query($updateMember);
    }

    // After a member leaves, check if the admin is the only remaining member
    resetAdminIfOnlyMember($conn);

    // Call the function to update team member counts after the user leaves
    updateTeamMemberCount($conn);

    // Redirect to avoid form resubmission
    header("Location: group.php");
    exit();
}

// Function to reset the admin's status if they are the only member in their team
function resetAdminIfOnlyMember($conn) {
    // Step 1: Find all users with ADMIN = YES
    $sql = "SELECT name, team FROM user_student WHERE admin = 'YES'";
    $result = $conn->query($sql);

    // Step 2: Loop through each user with ADMIN = YES
    while ($row = $result->fetch_assoc()) {
        $adminName = $row['name'];
        $adminTeam = $row['team'];

        // Step 3: Check how many users are in the same team
        $countSql = "SELECT COUNT(*) AS member_count FROM user_student WHERE team = '$adminTeam' AND member = 'YES'";
        $countResult = $conn->query($countSql);
        $countRow = $countResult->fetch_assoc();

        // Step 4: If only one member in the team (i.e., the admin is the only one left)
        if ($countRow['member_count'] == 1) {
            // Step 5: Update the admin's MEMBER and ADMIN attributes to NO
            $updateSql = "UPDATE user_student SET member = 'NO', admin = 'NO' WHERE name = '$adminName'";
            $conn->query($updateSql);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Members</title>
</head>
<body>
    <h1>Group Members</h1>
    <p><strong>Team: </strong><?php echo $team; ?></p>

    <!-- List of group members -->
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Team</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($student = $result->fetch_assoc()) {
                echo "<tr><td>" . $student['name'] . "</td><td>" . $student['team'] . "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No group members found</td></tr>";
        }
        ?>
    </table>

    <!-- Leave Group Button -->
    <form method="POST" action="" style="margin-top: 20px;">
        <button type="submit" name="leave_group">Leave Group</button>
    </form>

    <!-- Link back to Dashboard -->
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
