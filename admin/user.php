<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Ensure user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">User ID not found. Please log in again.</div>';
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "capstonedb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user data from usertbl excluding admin users
$userSql = "SELECT user_id, credentials, user_type FROM usertbl WHERE user_type != 'admin'";
$userResult = $conn->query($userSql);

// Fetch activity log data from activitylog_tbl excluding admin users
$activitySql = "SELECT a.user_id, a.activity, MAX(a.date) AS date 
                FROM activitylog_tbl a
                JOIN usertbl u ON a.user_id = u.user_id
                WHERE u.user_type != 'admin'
                GROUP BY a.user_id, a.activity";
$activityResult = $conn->query($activitySql);

// Delete user and associated activity records
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Prepare delete statements
    $deleteUser = "DELETE FROM usertbl WHERE user_id = ?";
    $deleteActivity = "DELETE FROM activitylog_tbl WHERE user_id = ?";
    
    $stmt1 = $conn->prepare($deleteUser);
    $stmt1->bind_param("i", $delete_id);
    $stmt1->execute();
    
    $stmt2 = $conn->prepare($deleteActivity);
    $stmt2->bind_param("i", $delete_id);
    $stmt2->execute();
    
    // Redirect after deletion
    header("Location: user.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digi-Studies</title>
    <link rel="stylesheet" href="../admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #ffffff; margin-left: 250px; }
        .sidebar { height: 100%; width: 250px; position: fixed; top: 0; left: 0; background-color: darkblue; font-weight: bold; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; }
        .sidebar a { padding: 10px 15px; text-decoration: none; font-size: 18px; color: white; display: block; }
        .sidebar a:hover { background-color: black; }
        .sidebar .sidebar-brand { font-size: 24px; margin-bottom: 1rem; color: white; text-align: center; }
        .sidebar .sidebar-brand img { border-radius: 50%; }

        body { background-color: #f5f5f5; margin-left: 250px; font-family: Arial, sans-serif; }
        .content { padding: 20px; margin-top: 20px; }
        h2 { color: #343a40; font-weight: 600; text-transform: uppercase; margin-bottom: 20px; text-align: center; font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; }
        .table { background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .table th { background-color: #007bff; color: #ffffff; font-weight: bold; text-align: center; }
        .table td, .table th { padding: 15px; text-align: center; }
        .table tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .alert { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; text-align: center; border-radius: 5px; margin: 10px auto; width: 80%; }
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
    <a href="manage.php"><i class="fas fa-tasks"></i> Manage Studies</a>
    <a href="user.php"><i class="fas fa-users"></i> User Logs</a>
    <a href="archives.php"><i class="fas fa-trash"></i> Archived Study</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h2>User Information</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Credentials</th>
                <th>User Type</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($userResult->num_rows > 0) {
                while ($user = $userResult->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $user["user_id"] . "</td>
                            <td>" . $user["credentials"] . "</td>
                            <td>" . $user["user_type"] . "</td>
                            <td>
                                <button class='btn btn-danger btn-sm delete-btn' data-id='" . $user["user_id"] . "'>Delete</button>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No users found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <h2>Activity Logs</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Activity</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($activityResult->num_rows > 0) {
                while ($activity = $activityResult->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $activity["user_id"] . "</td>
                            <td>" . $activity["activity"] . "</td>
                            <td>" . $activity["date"] . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No activity logs found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const userId = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `user.php?delete_id=${userId}`;
                    }
                });
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
