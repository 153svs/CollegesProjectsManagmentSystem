<?php
$servername = "localhost"; // Replace with your MySQL server
$username = "sam"; // MySQL username
$password = "sam153"; // MySQL password
$dbname = "trial1"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
