<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "capstonedb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['study_id'])) {
    $study_id = $_POST['study_id'];

    $archive_query = "INSERT INTO archivestbl (study_id, title, author, abstract, keywords, year, cNumber, course, type, file_path)
                      SELECT s.study_id, s.title, s.author, s.abstract, s.keywords, s.year, s.cNumber, 
                             c.course, c.type, u.path
                      FROM studytbl s
                      LEFT JOIN categorytbl c ON s.study_id = c.study_id
                      LEFT JOIN uploadtbl u ON c.cat_id = u.cat_id
                      WHERE s.study_id = ?";

    $stmt = $conn->prepare($archive_query);
    $stmt->bind_param("i", $study_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $delete_upload_query = "DELETE FROM uploadtbl WHERE cat_id IN (SELECT cat_id FROM categorytbl WHERE study_id = ?)";
        $stmt = $conn->prepare($delete_upload_query);
        $stmt->bind_param("i", $study_id);
        $stmt->execute();

        $delete_category_query = "DELETE FROM categorytbl WHERE study_id = ?";
        $stmt = $conn->prepare($delete_category_query);
        $stmt->bind_param("i", $study_id);
        $stmt->execute();

        $delete_study_query = "DELETE FROM studytbl WHERE study_id = ?";
        $stmt = $conn->prepare($delete_study_query);
        $stmt->bind_param("i", $study_id);
        $stmt->execute();

        $_SESSION['message'] = "<div class='alert alert-success'>Study archived successfully.</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger'>Failed to archive the study.</div>";
    }

    $stmt->close();
}

$conn->close();

// Redirect back to the same page to display the message
header("Location: manage.php");
exit();
