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

// Fetch archived studies
$query = "SELECT * FROM archivestbl ORDER BY deleted_at DESC";
$result = $conn->query($query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Studies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            margin-bottom: 20px;
        }
        .study-card .card {
            border: 1px solid #e1e5ee;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .study-card .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        }


        .modal-content {
        border-radius: 10px;
        font-weight: bold;
        font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .modal-header {
            background-color: darkblue;
            color: white;
            font-weight: bold;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .modal-footer .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .modal-footer .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
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

<div class="container mt-5">
    <h2>Archived Studies</h2>
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Abstract</th>
                    <th>Keywords</th>
                    <th>Year</th>
                    <th>Call Number</th>
                    <th>Course</th>
                    <th>Type</th>
                    <th>Archived Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['author']) ?></td>
                        <td>
                            <?php
                            $abstract = htmlspecialchars($row['abstract']);
                            if (strlen($abstract) > 100): ?>
                                <span class="short-abstract"><?= substr($abstract, 0, 100) ?>...</span>
                                <span class="full-abstract d-none"><?= $abstract ?></span>
                                <a href="#" class="see-more">See More</a>
                            <?php else: ?>
                                <?= $abstract ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['keywords']) ?></td>
                        <td><?= htmlspecialchars($row['year']) ?></td>
                        <td><?= htmlspecialchars(string: $row['cNumber']) ?></td>
                        <td><?= htmlspecialchars($row['course']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><?= htmlspecialchars($row['deleted_at']) ?></td>
                        <td>
                        <form action="restore.php" method="POST" class="restore-form">
                            <input type="hidden" name="archive_id" value="<?= $row['archive_id'] ?>">
                            <button type="button" class="btn btn-success restore-btn">Restore</button>
                        </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No archived studies found.</p>
    <?php endif; ?>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreModalLabel">Confirm Restore</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to restore this study?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <form id="restoreForm" action="restore.php" method="POST">
                    <input type="hidden" name="archive_id" id="archiveId">
                    <button type="submit" class="btn btn-success">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".see-more").forEach(function(link) {
            link.addEventListener("click", function(event) {
                event.preventDefault();
                const shortAbstract = this.previousElementSibling.previousElementSibling;
                const fullAbstract = this.previousElementSibling;

                if (fullAbstract.classList.contains("d-none")) {
                    shortAbstract.classList.add("d-none");
                    fullAbstract.classList.remove("d-none");
                    this.textContent = "See Less";
                } else {
                    shortAbstract.classList.remove("d-none");
                    fullAbstract.classList.add("d-none");
                    this.textContent = "See More";
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".restore-btn").forEach(function(button) {
            button.addEventListener("click", function(event) {
                const form = this.closest("form");
                
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to restore this study?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, Restore it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit the form if confirmed
                    }
                });
            });
        });
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
