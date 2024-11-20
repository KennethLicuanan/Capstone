<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "capstonedb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archive_id'])) {
    $archive_id = $_POST['archive_id'];

    // Retrieve archived study data
    $query = "SELECT * FROM archivestbl WHERE archive_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $archive_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Insert data back into studytbl
        $insert_study = "INSERT INTO studytbl (title, author, abstract, keywords, year, cNumber) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert_study = $conn->prepare($insert_study);
        $stmt_insert_study->bind_param("ssssss", $row['title'], $row['author'], $row['abstract'], $row['keywords'], $row['year'], $row['cNumber']);
        $stmt_insert_study->execute();
        $new_study_id = $conn->insert_id; // Get the new study_id

        // Insert data back into categorytbl with the new study_id
        $insert_category = "INSERT INTO categorytbl (study_id, course, type) VALUES (?, ?, ?)";
        $stmt_insert_category = $conn->prepare($insert_category);
        $stmt_insert_category->bind_param("iss", $new_study_id, $row['course'], $row['type']);
        $stmt_insert_category->execute();
        $new_cat_id = $conn->insert_id; // Get the new cat_id for uploadtbl

        // Insert data back into uploadtbl if there's an associated file
        if (!empty($row['path'])) {
            $insert_upload = "INSERT INTO uploadtbl (cat_id, path) VALUES (?, ?)";
            $stmt_insert_upload = $conn->prepare($insert_upload);
            $stmt_insert_upload->bind_param("is", $new_cat_id, $row['path']);
            $stmt_insert_upload->execute();
        }

        // Remove the archived record after restoring
        $delete_archive = "DELETE FROM archivestbl WHERE archive_id = ?";
        $stmt_delete_archive = $conn->prepare($delete_archive);
        $stmt_delete_archive->bind_param("i", $archive_id);
        $stmt_delete_archive->execute();

        $_SESSION['message'] = "Study restored successfully!";
        header("Location: archives.php");
        exit();
    } else {
        $_SESSION['error'] = "Archived study not found.";
        header("Location: archives.php");
        exit();
    }
}

$conn->close();
?>
