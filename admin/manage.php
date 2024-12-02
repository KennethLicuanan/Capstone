<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Display any session message
if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}

// Ensure user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">User ID not found. Please log in again.</div>';
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Use your database username
$password = ""; // Use your database password
$dbname = "capstonedb"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search results to null to clear previous results
$searchResults = null;

// Fetch studies based on search input (course or type)
function fetchStudiesBySearch($conn, $searchTerm) {
    $query = "SELECT studytbl.study_id, studytbl.title, studytbl.author, studytbl.abstract, studytbl.keywords, studytbl.year, studytbl.cNumber, categorytbl.course, categorytbl.type 
              FROM studytbl 
              INNER JOIN categorytbl ON studytbl.study_id = categorytbl.study_id 
              WHERE categorytbl.course LIKE '%$searchTerm%' OR categorytbl.type LIKE '%$searchTerm%'";
    $result = $conn->query($query);
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];
    $searchResults = fetchStudiesBySearch($conn, $searchTerm);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digi-Studies</title>
    <link rel="stylesheet" href="../admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .content {
            padding: 20px;
        }
        .content h2 {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 20px;
        }
        .course-section input[type="text"] {
            margin-bottom: 15px;
            border-radius: 5px;
            padding: 10px;
        }
        
.study-card {
    border: none;
    border-radius: 12px;
    background-color: #ffffff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.study-card:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.study-card .card-title {
    font-size: 1.2rem;
    font-weight: bold;
    color: #007bff;
}

.study-card .card-text {
    font-size: 0.9rem;
    color: #6c757d;
}

.study-card .btn {
    border-radius: 25px;
    padding: 10px 20px;
    font-size: 0.9rem;
}

.list-group-item {
    border: none; /* Remove default borders */
    padding: 20px; /* Add padding for better spacing */
    margin-bottom: 10px; /* Add space between list items */
    background-color: #f9f9f9; /* Subtle background color */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth hover effect */
}

.list-group-item:hover {
    transform: translateY(-2px); /* Slight upward movement on hover */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); /* Enhanced shadow on hover */
}

.list-group-item h6 {
    color: #007bff; /* Modern blue for titles */
    font-weight: bold;
}

.see-more {
    cursor: pointer;
    text-decoration: underline;
}



    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
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
    <a href="manage.php"><i class="fas fa-tasks"></i> Manage Studies</a>
    <a href="user.php"><i class="fas fa-users"></i> User Logs</a>
    <a href="archives.php"><i class="fas fa-trash"></i> Archived Study</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content"><br>
    <h2>Welcome to Managing Digi-Studies</h2>
<br>
    <div class="row">
<!-- Search Card -->
<div class="col-md-6 study-card mb-4">
    <div class="card text-center">
        <div class="card-body">
            <h5 class="card-title">Search Studies</h5>
            <!-- Search Form inside Card -->
            <form method="post">
                <div class="form-group">
                    <input type="text" name="search_term" class="form-control" placeholder="Search Course or Type here">
                </div>
                <button type="submit" name="search" class="btn btn-primary mt-3">Search</button>
            </form>
        </div>
    </div>
</div>

    <!-- Studies Card -->
    <div class="col-md-6 study-card mb-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Add Studies</h5>
                <p class="card-text">Access Adding Study.</p>
                <a href="add.php" class="btn btn-warning">Add?</a>
            </div>
        </div>
    </div>
</div>


    
    <!-- Search Results -->
    <?php if (isset($searchResults) && $searchResults->num_rows > 0): ?>
    <div class="mt-4">
        <h5>Search Results</h5>
        <ul class="list-group">
            <?php while ($row = $searchResults->fetch_assoc()): ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1"><strong><?= htmlspecialchars($row['title']) ?></strong></h6>
                            <p class="mb-1 text-muted"><small>Author: <?= htmlspecialchars($row['author']) ?> | Year: <?= htmlspecialchars($row['year']) ?></small></p>
                            <p class="mb-1"><strong>Abstract:</strong> 
                                <span class="abstract-content" id="abstract-<?= $row['study_id'] ?>">
                                    <?= htmlspecialchars(substr($row['abstract'], 0, 100)) ?>...
                                </span>
                                <span class="see-more text-primary" onclick="toggleAbstract(<?= $row['study_id'] ?>)">See More</span>
                                <span class="full-abstract" id="full-abstract-<?= $row['study_id'] ?>" style="display: none;">
                                    <?= htmlspecialchars($row['abstract']) ?>
                                    <span class="see-more text-primary" onclick="toggleAbstract(<?= $row['study_id'] ?>)">See Less</span>
                                </span>
                            </p>
                            <p class="mb-0"><strong>Keywords:</strong> <?= htmlspecialchars($row['keywords']) ?></p>
                            <p class="mb-0"><strong>Course:</strong> <?= htmlspecialchars($row['course']) ?> | <strong>Type:</strong> <?= htmlspecialchars($row['type']) ?></p>
                        </div>
                        <div class="d-flex flex-column">
                            <form action="update_study.php" method="POST" class="mb-2">
                                <input type="hidden" name="study_id" value="<?= $row['study_id'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </form>
                            <form action="delete_study.php" method="POST" onsubmit="return confirmDeletion(this)">
                                <input type="hidden" name="study_id" value="<?= $row['study_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])): ?>
    <div class="alert alert-info mt-4">No results found.</div>
<?php endif; ?>

</div>

<script>
        // Toggle between truncated and full abstract
        function toggleAbstract(id) {
        const abstractContent = document.getElementById(`abstract-${id}`);
        const fullAbstract = document.getElementById(`full-abstract-${id}`);

        if (fullAbstract.style.display === "none") {
            abstractContent.style.display = "none";
            fullAbstract.style.display = "inline";
        } else {
            abstractContent.style.display = "inline";
            fullAbstract.style.display = "none";
        }
    }

       // SweetAlert Confirmation Alert
       function confirmDeletion(form) {
        event.preventDefault(); // Prevent form submission
        Swal.fire({
            title: 'Are you sure?',
            text: "This will be saved on Archives",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Submit the form if confirmed
            }
        });
        return false; // Prevent default form submission
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

<?php $conn->close(); ?>
