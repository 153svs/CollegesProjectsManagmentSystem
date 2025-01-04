<?php
// Include the database connection
include('db_connection.php');  // Make sure this file contains the correct DB connection details

// Function to update the count_of_team_member for each team
function updateTeamMemberCount($conn) {
    // Query to update the count_of_team_member for each team
    $sql = "UPDATE user_student u
            JOIN (
                SELECT team, COUNT(*) AS team_member_count
                FROM user_student
                GROUP BY team
            ) AS team_counts
            ON u.team = team_counts.team
            SET u.count_of_team_member = team_counts.team_member_count";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "Team member counts updated successfully.";
    } else {
        echo "Error updating team member counts: " . $conn->error;
    }
}

// Ensure the database connection is established
if ($conn) {
    // Call the function to update the counts
    updateTeamMemberCount($conn);
} else {
    echo "Database connection failed.";
}
?>
