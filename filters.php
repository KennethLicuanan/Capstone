<?php
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

// Fetch distinct years and courses
$years = $conn->query("SELECT DISTINCT year FROM studytbl ORDER BY year DESC")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT DISTINCT course FROM categorytbl ORDER BY course ASC")->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'years' => array_column($years, 'year'),
    'courses' => array_column($courses, 'course'),
]);

$conn->close();
?>
