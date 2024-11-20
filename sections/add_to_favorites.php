<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo 'User not logged in!';
    exit();
}

if (!isset($_POST['user_id']) || !isset($_POST['study_id'])) {
    echo 'Invalid request!';
    exit();
}

$user_id = $_POST['user_id'];
$study_id = $_POST['study_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "capstonedb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert into favoritestbl
$sql = "INSERT INTO favoritestbl (user_id, study_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $study_id);

if ($stmt->execute()) {
    echo 'Study added to favorites';
} else {
    echo 'Error adding to favorites';
}

$stmt->close();
$conn->close();
?>
