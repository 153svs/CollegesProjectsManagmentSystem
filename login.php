<?php
// Include the database connection
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SQL query to check credentials
    $sql = "SELECT * FROM user_student WHERE name = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Login successful, start session
        session_start();
        $_SESSION['username'] = $username;

        // Redirect to the dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Invalid username or password.";
    }
}
?>

<!-- HTML Form for Login -->
<form method="POST" action="login.php">
    <label for="username">Username:</label>
    <input type="text" name="username" required><br>

    <label for="password">Password:</label>
    <input type="password" name="password" required><br>

    <input type="submit" value="Login">
</form>
