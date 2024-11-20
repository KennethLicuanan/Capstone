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

// Fetch total studies for each course
$courseCounts = [
    'IT' => 0,
    'TEP' => 0,
    'BA' => 0
];

$courses = array_keys($courseCounts);
foreach ($courses as $course) {
    $query = "SELECT COUNT(*) as total FROM categorytbl WHERE course = '$course'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $courseCounts[$course] = $row['total'];
    }
}

// Fetch IT studies by type
$studyTypes = ['IoT', 'Web-Based', 'Web-Application', 'Mobile Application'];
$typeCounts = [];
foreach ($studyTypes as $type) {
    $query = "SELECT COUNT(*) as total FROM categorytbl WHERE course = 'IT' AND type = '$type'";
    $result = $conn->query($query);
    $typeCounts[$type] = $result->num_rows > 0 ? $result->fetch_assoc()['total'] : 0;
}

// Fetch BA studies by type
$baTypes = ['Financial Management', 'Marketing Management', 'Operations Management'];
$baTypeCounts = [];
foreach ($baTypes as $type) {
    $query = "SELECT COUNT(*) as total FROM categorytbl WHERE course = 'BA' AND type = '$type'";
    $result = $conn->query($query);
    $baTypeCounts[$type] = $result->num_rows > 0 ? $result->fetch_assoc()['total'] : 0;
}

// Fetch TEP studies by type
$tepTypes = ['Early Childhood', 'Elementary Education', 'Secondary Education'];
$tepTypeCounts = [];
foreach ($tepTypes as $type) {
    $query = "SELECT COUNT(*) as total FROM categorytbl WHERE course = 'TEP' AND type = '$type'";
    $result = $conn->query($query);
    $tepTypeCounts[$type] = $result->num_rows > 0 ? $result->fetch_assoc()['total'] : 0;
}

// Fetch studies per year
$yearlyCounts = [];
$query = "SELECT year, COUNT(*) as total FROM studytbl GROUP BY year";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $yearlyCounts[$row['year']] = $row['total'];
}


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
        h3{
            margin-top: 15px;
            text-align: center;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            font-weight: bold;
        }
        h2 {
            margin-top: 15px;
            text-align: center;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            font-weight: bold;
        }
        .flex-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 5%;
            flex-wrap: wrap;
        }
        .chart-container {
            width: 30%;
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
        <a href="add_favorite.php"><i class="fas fa-star"></i> Favorites</a>
        <a href="notification.php"><i class="fas fa-bell"></i> Notifications</a>
        <a href="help.php"><i class="fas fa-pencil"></i> Help</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

<h2>Data Analytics</h2>

<div class="flex-container">
    <div class="chart-container">
        <h3>Overall Studies</h3>
        <canvas id="studiesChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Information Technology Studies</h3>
        <canvas id="itTypesChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Business Administration Studies</h3>
        <canvas id="baTypesChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Teachers Education Program Studies</h3>
        <canvas id="tepTypesChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Studies Per Year</h3>
        <canvas id="yearlyChart" width="400" height="400"></canvas>
    </div>

</div>

<script>
    // Pass PHP data to JavaScript
    const courseData = {
        IT: <?php echo $courseCounts['IT']; ?>,
        TEP: <?php echo $courseCounts['TEP']; ?>,
        BA: <?php echo $courseCounts['BA']; ?>
    };

    const itTypeData = {
        IoT: <?php echo $typeCounts['IoT']; ?>,
        "Web-Based": <?php echo $typeCounts['Web-Based']; ?>,
        "Web-Application": <?php echo $typeCounts['Web-Application']; ?>,
        "Mobile Application": <?php echo $typeCounts['Mobile Application']; ?>
    };

    const baTypeData = {
        "Financial Management": <?php echo $baTypeCounts['Financial Management']; ?>,
        "Marketing Management": <?php echo $baTypeCounts['Marketing Management']; ?>,
        "Operations Management": <?php echo $baTypeCounts['Operations Management']; ?>
    };

    const tepTypeData = {
        "Early Childhood": <?php echo $tepTypeCounts['Early Childhood']; ?>,
        "Elementary Education": <?php echo $tepTypeCounts['Elementary Education']; ?>,
        "Secondary Education": <?php echo $tepTypeCounts['Secondary Education']; ?>
    };

    // Pass studies per year data to JavaScript
    const yearlyData = <?php echo json_encode($yearlyCounts); ?>;


   // Render Total Studies by Course Chart
   const ctxCourse = document.getElementById('studiesChart').getContext('2d');
    new Chart(ctxCourse, {
        type: 'pie',
        data: {
            labels: ['Information Technology', 'Teachers Education Program', 'Business Administration'],
            datasets: [{
                label: 'Total Studies by Course',
                data: [courseData.IT, courseData.TEP, courseData.BA],
                backgroundColor: ['#2A9D8F', '#E9C46A', '#F4A261'],
                hoverBackgroundColor: ['#2A9D8F99', '#E9C46A99', '#F4A26199'],
                borderColor: 'transparent',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14,
                            family: 'Arial, sans-serif',
                        },
                        color: '#333',
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(2);
                            return `${label}: ${value} studies (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Render IT Studies by Type Chart
    const ctxITTypes = document.getElementById('itTypesChart').getContext('2d');
    new Chart(ctxITTypes, {
        type: 'pie',
        data: {
            labels: Object.keys(itTypeData),
            datasets: [{
                label: 'IT Studies by Type',
                data: Object.values(itTypeData),
                backgroundColor: ['#264653', '#2A9D8F', '#E76F51', '#F4A261'],
                hoverBackgroundColor: ['#26465399', '#2A9D8F99', '#E76F5199', '#F4A26199'],
                borderColor: 'transparent',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14,
                            family: 'Arial, sans-serif',
                        },
                        color: '#333',
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                }
            }
        }
    });

    // Render BA Studies by Type Chart
    const ctxBATypes = document.getElementById('baTypesChart').getContext('2d');
    new Chart(ctxBATypes, {
        type: 'pie',
        data: {
            labels: Object.keys(baTypeData),
            datasets: [{
                label: 'BA Studies by Type',
                data: Object.values(baTypeData),
                backgroundColor: ['#264653', '#2A9D8F', '#E76F51', '#F4A261'],
                hoverBackgroundColor: ['#26465399', '#2A9D8F99', '#E76F5199', '#F4A26199'],
                borderColor: 'transparent',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14,
                            family: 'Arial, sans-serif',
                        },
                        color: '#333',
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                }
            }
        }
    });

    // Render TEP Studies by Type Chart
    const ctxTEPTypes = document.getElementById('tepTypesChart').getContext('2d');
    new Chart(ctxTEPTypes, {
        type: 'pie',
        data: {
            labels: Object.keys(tepTypeData),
            datasets: [{
                label: 'TEP Studies by Type',
                data: Object.values(tepTypeData),
                backgroundColor: ['#264653', '#2A9D8F', '#E76F51', '#F4A261'],
                hoverBackgroundColor: ['#26465399', '#2A9D8F99', '#E76F5199', '#F4A26199'],
                borderColor: 'transparent',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14,
                            family: 'Arial, sans-serif',
                        },
                        color: '#333',
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                }
            }
        }
    });

        // Render Studies Per Year Chart
        const ctxYearly = document.getElementById('yearlyChart').getContext('2d');
    new Chart(ctxYearly, {
        type: 'bar',
        data: {
            labels: Object.keys(yearlyData),
            datasets: [{
                label: 'Number of Studies',
                data: Object.values(yearlyData),
                backgroundColor: ['#2A9D8F99'],
                borderColor: '#2A9D8F',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14,
                            family: 'Arial, sans-serif',
                        },
                        color: '#333',
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    title: {
                        display: true,
                        text: 'Year',
                        font: { size: 14 },
                        color: '#555',
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#ddd' },
                    title: {
                        display: true,
                        text: 'Number of Studies',
                        font: { size: 14 },
                        color: '#555',
                    }
                }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
