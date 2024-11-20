<?php
session_start();

// Database connection
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "capstonedb";

// Create a connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$login_error = ""; // Initialize an empty error message
$signup_error = ""; // Initialize an empty signup error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Handle login
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Query to check user credentials and user_type
        $stmt = $conn->prepare("SELECT user_id, user_type FROM usertbl WHERE credentials = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['user_id'];
            $user_type = $row['user_type']; // Retrieve the user type

            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = $user_type; // Store user type in session

            // Log user login activity
            $activity = "User logged in";
            $stmt = $conn->prepare("INSERT INTO activitylog_tbl (user_id, activity, date) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $user_id, $activity);
            $stmt->execute();

            // Redirect based on user type
            if ($user_type === 'admin') {
                echo "<script>alert('Login successful! Welcome, Admin.'); window.location.href = 'admin.php';</script>";
            } else {
                echo "<script>alert('Login successful! Welcome to DIGIBOOKS...'); window.location.href = 'dashboard.php';</script>";
            }
            exit();
        } else {
            $login_error = "Invalid username or password.";
        }

        $stmt->close();
    } elseif (isset($_POST['signup'])) {
        // Handle signup
        $username = $_POST['signup_username'];
        $password = $_POST['signup_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $signup_error = "Passwords do not match.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM usertbl WHERE credentials = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $signup_error = "Username already exists.";
            } else {
                // Default user type is 'user'
                $stmt = $conn->prepare("INSERT INTO usertbl (credentials, password, user_type) VALUES (?, ?, 'user')");
                $stmt->bind_param("ss", $username, $password);
                if ($stmt->execute()) {
                    echo "<script>alert('Signup successful! You can now login.');</script>";
                } else {
                    $signup_error = "Error signing up. Please try again.";
                }
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login & Signup</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
        .modal-content {
            margin-top: 50px;
            padding: 20px;
        }
        
        .navbar.custom-color .navbar-brand,
        .navbar.custom-color .nav-link {
            color: #ffffff !important; /* Set text color to white */
        }
        .custom-color{
            background-color: darkblue;
        }
        .navbar-brand img{
            border-radius: 50%;
        }
        .modal-title img{
            margin-left: 110%;
        }

        .nbsc img{
            margin-left: 5%;
            margin-top: 5%;
            width: 95%;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light custom-color">
        <a class="navbar-brand" href="login.php"> <img src="imgs/logo.jpg" height="80" alt="">  Digi - Studies</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#loginModal">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#signupModal">Signup</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel"> <img src="imgs/logo.jpg" height="100" alt="">Login</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="username">Institutional Identification</label>
                            <input type="number" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="login">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signupModalLabel"> <img src="imgs/logo.jpg" height="100" alt="">Signup</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="signup_username">Institutional Identification</label>
                            <input type="number" class="form-control" id="signup_username" name="signup_username" required>
                        </div>
                        <div class="form-group">
                            <label for="signup_password">Password</label>
                            <input type="password" class="form-control" id="signup_password" name="signup_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="signup">Signup</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    if (!empty($login_error)) {
        echo "<script>alert('$login_error');</script>";
    }
    if (!empty($signup_error)) {
        echo "<script>alert('$signup_error');</script>";
    }
    ?>
    <div class="nbsc">
        <img src="imgs/nbsc.jpg" alt="">
    </div>
</body>
</html>
