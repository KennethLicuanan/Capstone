<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}



// Function to fetch new studies added within the past week and delete those not in the database
function getWeeklyNotifications($conn) {
    $notifications = [];

    // Query to find studies added within the last 7 days
    $query = "
        SELECT s.study_id, s.title, c.course
        FROM studytbl s
        JOIN categorytbl c ON s.study_id = c.study_id
        WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Check if the study is still in the database
            $checkQuery = "SELECT COUNT(*) as count FROM studytbl WHERE study_id = " . $row['study_id'];
            $checkResult = $conn->query($checkQuery);
            $checkRow = $checkResult->fetch_assoc();

            if ($checkRow['count'] == 0) {
                // Study not found in the database, delete it from notifications
                $deleteQuery = "DELETE FROM studytbl WHERE study_id = " . $row['study_id'];
                $conn->query($deleteQuery);
            } else {
                // Add valid study to notifications
                $notifications[] = [
                    'title' => $row['title'],
                    'course' => $row['course']
                ];
            }
        }
    }

    return $notifications;
}

// Fetch notifications// Fetch notifications and count
$notifications = getWeeklyNotifications($conn);
$notificationCount = count($notifications); // Get the count of notifications

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digi-Books</title>
    <link rel="stylesheet" href="dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f4f7fc;
        }
        
        /* General instructions container style */
        .instructions {
            margin: 30px auto;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 10px;
            max-width: 800px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: #333;
        }

        /* Title styling */
        .instructions h3 {
            color: #2c3e50;
            font-weight: bold;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid #d1d5db;
            padding-bottom: 10px;
        }

        /* List styling */
        .instructions ul {
            list-style-type: none;
            padding: 0;
        }

        /* List items styling */
        .instructions ul li {
            display: flex;
            align-items: flex-start;
            margin: 15px 0;
            font-size: 18px;
            line-height: 1.6;
            color: #555;
            font-weight: 500;
            padding-left: 35px;
            position: relative;
        }

        /* Icon for each list item */
        .instructions ul li::before {
            content: '\f05a'; /* Font Awesome info-circle icon */
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: #3498db;
            position: absolute;
            left: 0;
            top: 0;
        }

        /* Sidebar styling */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: darkblue;
            color: white;
            font-weight: bold;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            padding-top: 20px;
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
    </style>
</head>
<body>

<div class="sidebar">
        <div class="sidebar-brand">
            <img src="imgs/logo.jpg" height="50" alt="Digi-Studies"> Digi - Studies
        </div>
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="./sections/IT.php"><i class="fas fa-laptop"></i> College of Computer Studies</a>
        <a href="./sections/BA.php"><i class="fas fa-briefcase"></i> Business Administration</a>
        <a href="./sections/TEP.php"><i class="fas fa-chalkboard-teacher"></i> Teachers Education Program</a>
        <a href="analytics.php"><i class="fas fa-blackboard"></i> Studies Analysis</a>
        <a href="add_favorite.php"><i class="fas fa-star"></i> Favorites</a>
        <a href="notification.php">
            <i class="fas fa-bell"></i> Notifications 
            <?php if ($notificationCount > 0): ?>
                <span class="badge bg-danger"><?php echo $notificationCount; ?></span>
            <?php endif; ?>
        </a>        <a href="help.php"><i class="fas fa-pencil"></i> Help</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="content">
        <div class="container instructions">
            <h3>How to Use Digi-Books</h3>
            <ul>
                <li><span>Access "Courses" to view studies by course.</span></li>
                <li><span>Each course is organized by year.</span></li>
                <li><span>Use the filtering feature for a more specific search.</span></li>
                <li><span>Search using keywords for quick results.</span></li>
                <li><span>Studies are available from 2019 to 2024.</span></li>
                <li><span>Add studies to your favorites to view them easily in the favorites section.</span></li>
                <li><span>Click "Digi-Books" to return to the dashboard.</span></li>
            </ul>
        </div>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</html>
