// reject_invitation.php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('db_connection.php');

$sender = $_POST['sender']; // user who sent the invitation
$receiver = $_SESSION['username']; // logged-in user

// Update invitation status to "Rejected"
$sql = "UPDATE invitations SET status = 'Rejected' WHERE sender_name = '$sender' AND receiver_name = '$receiver' AND status = 'Pending'";
$conn->query($sql);

// Re-add the receiver to the list of students for sender
$sql = "SELECT * FROM user_student WHERE name = '$receiver'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

header("Location: received_invitations.php"); // Redirect back to received invitations page
exit();
