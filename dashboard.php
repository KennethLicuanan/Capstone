<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digi-Studies</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #ffffff;
            margin-left: 250px;
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



            /* Styling the search input and button */
    .input-group .form-control {
        border-radius: 50px;
        padding-left: 20px;
        font-size: 14px;
    }

    .input-group button {
        border-radius: 50px;
        padding: 10px 20px;
        font-size: 14px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Navbar burger menu */
    .navbar-toggler {
        border: none;
        background-color: transparent;
    }

    .navbar-toggler-icon {
        background-color: #007bff;
    }

    /* Styling form select elements */
    .form-select {
        border-radius: 8px;
        font-size: 14px;
    }

    /* Make the navbar slightly elevated */
    .navbar {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Label styling */
    .form-label {
        font-weight: 600;
        font-size: 14px;
    }

    /* Adding smooth transition for dropdowns */
    .form-select, .navbar-toggler {
        transition: all 0.3s ease-in-out;
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
        </a>        
        <a href="help.php"><i class="fas fa-pencil"></i> Help</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="container mt-4">
    <!-- Search Box -->
    <div class="input-group mb-4">
        <input type="text" id="searchInput" class="form-control border-0 shadow-sm rounded-pill" placeholder="Search studies by title, author, or keywords">
        <button class="btn btn-primary shadow-sm rounded-pill" onclick="performSearch()">Search</button>
    </div>

    <!-- Burger Menu for Filters -->
    <nav class="navbar navbar-expand-md navbar-light bg-light rounded shadow-sm mb-3">
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarFilters" aria-controls="navbarFilters" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarFilters">
            <div class="row mb-3">
                <div class="col-md-6 mb-2 mb-md-0">
                    <label for="yearFilter" class="form-label text-muted">Filter by Year</label>
                    <select id="yearFilter" class="form-select shadow-sm rounded" onchange="applyFilters()">
                        <option value="">All Years</option>
                        <!-- Populate dynamically -->
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="courseFilter" class="form-label text-muted">Filter by Course</label>
                    <select id="courseFilter" class="form-select shadow-sm rounded" onchange="applyFilters()">
                        <option value="">All Courses</option>
                        <!-- Populate dynamically -->
                    </select>
                </div>
            </div>
        </div>
    </nav>
</div>


<!-- Search Results Modal -->
<div class="modal fade" id="searchResultsModal" tabindex="-1" aria-labelledby="searchResultsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchResultsLabel">Search Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul id="searchResultsList" class="list-group"></ul>
            </div>
        </div>
    </div>
</div>

<!-- Course Message Modal -->
 <style>
    /* Style for the modal content */
.modal-content {
    background-color: #f9f9f9; /* Light background */
    border: 2px solid #5cb85c; /* Green Archers theme color */
    border-radius: 12px; /* Smooth rounded corners */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
}

/* Style for the modal header */
.modal-header {
    background-color: darkblue; /* Theme green background */
    color: #fff; /* White text */
    border-bottom: 1px solid #4cae4c; /* Slightly darker border */
    padding: 15px;
    border-top-left-radius: 10px; /* Match content radius */
    border-top-right-radius: 10px;
}

/* Title styling */
.modal-title {
    font-size: 18px;
    font-weight: bold;
    text-transform: uppercase; /* Make title standout */
    margin: 0;
}

/* Style for close button */
.btn-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #fff;
    opacity: 0.7;
}

.btn-close:hover {
    opacity: 1;
}

/* Style for the modal body */
.modal-body {
    padding: 20px;
    font-size: 16px;
    color: #333; /* Neutral text for readability */
    line-height: 1.5;
}

/* Style for the modal footer */
.modal-footer {
    background-color: #f1f1f1; /* Light footer background */
    border-top: 1px solid #ddd; /* Subtle border */
    padding: 15px;
    display: flex;
    justify-content: space-between;
}

/* Button styles */
.btn-secondary {
    background-color: #6c757d;
    border: none;
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 6px;
    transition: background-color 0.3s ease;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

 </style>
<div class="modal fade" id="courseMessageModal" tabindex="-1" aria-labelledby="courseMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseMessageLabel">Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="courseMessageBody">
                <!-- Message will be injected dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    populateFilters(); // Populate filters on page load
});

function populateFilters() {
    fetch('filters.php')
        .then(response => response.json())
        .then(data => {
            const yearFilter = document.getElementById('yearFilter');
            const courseFilter = document.getElementById('courseFilter');

            // Populate years
            data.years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearFilter.appendChild(option);
            });

            // Populate courses
            data.courses.forEach(course => {
                const option = document.createElement('option');
                option.value = course;
                option.textContent = course;
                courseFilter.appendChild(option);
            });
        });
}

function applyFilters() {
    const year = document.getElementById('yearFilter').value;
    const course = document.getElementById('courseFilter').value;

    fetch(`search.php?year=${encodeURIComponent(year)}&course=${encodeURIComponent(course)}`)
        .then(response => response.json())
        .then(data => {
            const resultsList = document.getElementById('searchResultsList');
            resultsList.innerHTML = '';

            if (data.length > 0) {
                data.forEach(item => {
                    const listItem = document.createElement('li');
                    listItem.className = 'list-group-item';
                    listItem.innerHTML = `
                        <strong>Title:</strong> ${item.title}<br>
                        <strong>Author:</strong> ${item.author}<br>
                        <strong>Keywords:</strong> ${item.keywords}<br>
                        <strong>Year:</strong> ${item.year}<br>
                        <strong>Course:</strong> ${item.course}
                    `;

                    // Attach click event
                    listItem.addEventListener('click', () => {
                        handleStudyClick(item.course);
                    });

                    resultsList.appendChild(listItem);
                });
            } else {
                resultsList.innerHTML = '<li class="list-group-item">No results found.</li>';
            }

            // Show the modal
            new bootstrap.Modal(document.getElementById('searchResultsModal')).show();
        });
}

function performSearch() {
    const query = document.getElementById('searchInput').value.trim();
    const year = document.getElementById('yearFilter').value;
    const course = document.getElementById('courseFilter').value;

    if (query === '') {
        alert('Please enter a search query.');
        return;
    }

    fetch(`search.php?q=${encodeURIComponent(query)}&year=${encodeURIComponent(year)}&course=${encodeURIComponent(course)}`)
        .then(response => response.json())
        .then(data => {
            const resultsList = document.getElementById('searchResultsList');
            resultsList.innerHTML = '';

            if (data.length > 0) {
                data.forEach(item => {
                    const listItem = document.createElement('li');
                    listItem.className = 'list-group-item';
                    listItem.innerHTML = `
                        <strong>Title:</strong> ${item.title}<br>
                        <strong>Author:</strong> ${item.author}<br>
                        <strong>Keywords:</strong> ${item.keywords}<br>
                        <strong>Year:</strong> ${item.year}<br>
                        <strong>Course:</strong> ${item.course}
                    `;

                    // Attach click event
                    listItem.addEventListener('click', () => {
                        handleStudyClick(item.course);
                    });

                    resultsList.appendChild(listItem);
                });
            } else {
                resultsList.innerHTML = '<li class="list-group-item">No results found.</li>';
            }

            // Show the modal
            new bootstrap.Modal(document.getElementById('searchResultsModal')).show();
        });
}

function handleStudyClick(course) {
    const message = `If you want to view the full abstract of this study, please direct to the ${course} section for more specific details.`;
    const courseMessageBody = document.getElementById('courseMessageBody');
    courseMessageBody.textContent = message;

    // Show the modal
    const courseMessageModal = new bootstrap.Modal(document.getElementById('courseMessageModal'));
    courseMessageModal.show();
}

</script>


    <div class="content">
    <style>
        .content {
            display: flex;
            flex-direction: column; /* Align images in a vertical list */
            align-items: center; /* Center-align the images */
            gap: 20px; /* Add spacing between the images */
            padding: 20px;
            background-color: #f9f9f9; /* Light, modern background */
        }

        .content img {
            width: 90%; /* Responsive width relative to the container */
            max-width: 800px; /* Limit the max size for large screens */
            height: auto; /* Maintain aspect ratio */
            object-fit: cover; /* Ensure images are cropped proportionally */
            border-radius: 12px; /* Add rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .content img:hover {
            transform: scale(1.02); /* Slight zoom on hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
        }

        .content h1,
.content p {
    font-family: 'Poppins', Arial, sans-serif; /* Modern sans-serif font */
    font-size: 24px; /* Uniform font size */
    font-weight: 600; /* Semi-bold for better emphasis */
    color: #2c3e50; /* Sleek dark blue-gray for modern aesthetics */
    letter-spacing: 0.5px; /* Slight letter spacing for clarity */
    line-height: 1.8; /* Improved line height for readability */
    margin: 15px 0; /* Balanced margins for spacing */
    text-align: left; /* Align text to the left for a clean layout */
}

.content h1 {
    font-size: 32px; /* Slightly larger size for the heading */
    font-weight: 700; /* Bolder heading for hierarchy */
    color: #34495e; /* Darker shade for contrast */
    text-transform: uppercase; /* Make heading more prominent */
    border-bottom: 2px solid #3498db; /* Modern underline effect */
    padding-bottom: 5px; /* Spacing for the underline */
    margin-bottom: 20px; /* Extra margin to separate from other text */
}

.content p {
    font-size: 18px; /* Slightly smaller size for paragraph */
    color: #555; /* Softer text color for readability */
    text-align: justify; /* Justify text for a clean block alignment */
}

    </style>
<br>
    <h1>Welcome to Digi-Studies</h1>
    <p>Your Guide in Research Finding</p>
    <a href="./sections/IT.php"><img class="ccs" src="imgs/ccs.jpg" alt="CCS">
    </a>
    <a href="./sections/BA.php"><img class="bsba" src="imgs/bsba.jpg" alt="BSBA">
    </a>
    <a href="./sections/TEP.php"><img class="tep" src="imgs/tep.jpg" alt="TEP">
    </a>
</div>

<!-- Add Bootstrap JS for the burger menu toggle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
