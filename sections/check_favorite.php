<?php
session_start();

// Ensure user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    echo 'User not logged in.';
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

// Check if the study is already in the favorites
$sql = "SELECT * FROM favoritestbl WHERE user_id = ? AND study_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $study_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Study is already in favorites
    echo 'exists';
} else {
    // Study is not in favorites
    echo 'not_exists';
}

$stmt->close();
$conn->close();
?>
