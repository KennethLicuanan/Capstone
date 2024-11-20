<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['study_id'])) {
    echo 'Study ID not provided.';
    exit();
}

$study_id = $_POST['study_id'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "capstonedb";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_study'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $abstract = $_POST['abstract'];
    $keywords = $_POST['keywords'];
    $year = $_POST['year'];
    $cNumber = $_POST['cNumber'];

    $query = "UPDATE studytbl SET title = ?, author = ?, abstract = ?, keywords = ?, year = ?, cNumber = ? WHERE study_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $title, $author, $abstract, $keywords, $year, $cNumber, $study_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Study updated successfully.</div>';
        header("Refresh: 1; URL=manage.php");
        exit();
    } else {
        echo '<div class="alert alert-danger">Error updating study: ' . $conn->error . '</div>';
    }
}

$query = "SELECT * FROM studytbl WHERE study_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $study_id);
$stmt->execute();
$result = $stmt->get_result();
$study = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digi-Studies - Update Study</title>
    <link rel="stylesheet" href="../admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            background-color: #ffffff;
            margin-left: 250px; /* Leave space for the sidebar */
        }
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: darkblue;
            padding-top: 20px;
            font-weight: bold;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
        }
        .sidebar a:hover {
            background-color: black;
        }
        .sidebar .sidebar-brand {
            font-size: 24px;
            margin-bottom: 1rem;
            color: white;
            text-align: center;
        }
        .sidebar .sidebar-brand img {
            border-radius: 50%;
        }

        .form-control{
            border: 1px solid black;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <img src="imgs/logo.jpg" height="50" alt="Digi-Studies"> Digi - Studies
    </div>
    <a href="../admin.php"><i class="fas fa-home"></i> Home</a>
    <a href="IT.php"><i class="fas fa-laptop"></i> College of Computer Studies</a>
    <a href="BA.php"><i class="fas fa-briefcase"></i> Business Administration</a>
    <a href="TEP.php"><i class="fas fa-chalkboard-teacher"></i> Teachers Education Program</a>
    <a href="add.php"><i class="fas fa-plus"></i> Add Study</a>
    <a href="manage.php"><i class="fas fa-tasks"></i> Manage Studies</a>
    <a href="user.php"><i class="fas fa-users"></i> User Logs</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="container mt-5">
    <h2 class="mb-4">Update Study</h2>
    <form method="POST" class="card p-4 shadow-sm">
        <input type="hidden" name="study_id" value="<?= $study['study_id'] ?>">

        <div class="mb-3">
            <label class="form-label">Title:</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($study['title']) ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Author:</label>
            <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($study['author']) ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Abstract:</label>
            <textarea name="abstract" class="form-control" rows="4" required><?= htmlspecialchars($study['abstract']) ?></textarea>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Keywords:</label>
            <input type="text" name="keywords" class="form-control" value="<?= htmlspecialchars($study['keywords']) ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Year:</label>
            <input type="text" name="year" class="form-control" value="<?= htmlspecialchars($study['year']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Call Number:</label>
            <input type="text" name="cNumber" class="form-control" value="<?= htmlspecialchars($study['cNumber']) ?>" required>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" name="update_study" class="btn btn-primary">Update Study</button>
        </div>
    </form>
</div>

</body>
</html>
