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

// Get parameters
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$course = isset($_GET['course']) ? trim($_GET['course']) : '';

// Build the query
$sql = "SELECT s.title, s.author, s.keywords, s.year, c.course 
        FROM studytbl s 
        JOIN categorytbl c ON s.study_id = c.study_id 
        WHERE 1=1";

if ($query !== '') {
    $likeQuery = '%' . $query . '%';
    $sql .= " AND (s.title LIKE '$likeQuery' OR s.author LIKE '$likeQuery' OR s.keywords LIKE '$likeQuery')";
}
if ($year !== '') {
    $sql .= " AND s.year = '$year'";
}
if ($course !== '') {
    $sql .= " AND c.course = '$course'";
}

$result = $conn->query($sql);

// Fetch results and output as JSON
$studies = [];
while ($row = $result->fetch_assoc()) {
    $studies[] = $row;
}

echo json_encode($studies);

$conn->close();
?>
