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

$signup_error = ""; // Initialize an empty error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $signup_error = "Passwords do not match.";
    } else {
        // Check if the username already exists
        $stmt = $conn->prepare("SELECT * FROM usertbl WHERE credentials = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $signup_error = "Username already exists.";
        } else {
            // Insert new user into the database
            $stmt = $conn->prepare("INSERT INTO usertbl (credentials, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);
            if ($stmt->execute()) {
                echo "<script>alert('Signup successful! You can now login.'); window.location.href = 'login.php';</script>";
            } else {
                $signup_error = "Error signing up. Please try again.";
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
    <style>
        body { 
        font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; 
        background-image: url('./imgs/background.jpg'); 
        background-size: cover; 
        background-repeat: no-repeat; 
        background-position: center;
        }
        .signup-container { 
            width: 300px; 
            margin: auto; 
            margin-top: 25px; 
            padding: 20px;
            box-shadow: 0 12px 16px 0 rgba(0, 0, 0, 20);
            border-radius: 8px;
            background-color: white;
        }
        input[type=text], input[type=password] { 
            width: 90%; 
            padding: 10px; 
            margin: 5px 0;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
        input[type=submit] { 
            width: 50%; 
            padding: 10px; 
            background-color: green; 
            color: white; 
            border: none; 
            margin-left: 27%;
        }
        .signup-container img {
            margin-left: 27%;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }
    </style>
    <script>
        function showError(message) {
            alert(message);
        }
    </script>
</head>
<body>
    <?php
    if (!empty($signup_error)) {
        echo "<script>showError('$signup_error');</script>";
    }
    ?>
    <div class="signup-container">
        <img src="imgs/book.png" height="150" alt=""><br>
        <h2>Signup</h2>
        <form action="signup.php" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required><br><br>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required><br><br>
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>
            <input type="submit" value="Signup">
        </form>
    </div>
</body>
</html>
