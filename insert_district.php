<?php
// Database connection details
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "schoolfeesys";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$state_id = $_POST['state_id'];
$names = explode(',', $_POST['name']); // Split the comma-separated names into an array

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO districts (state_id, name) VALUES (?, ?)");
$stmt->bind_param("is", $state_id, $name);

// Insert each district name
foreach ($names as $name) {
    $name = trim($name); // Remove any extra spaces
    if (!empty($name)) {
        if ($stmt->execute()) {
            echo "District '$name' inserted successfully.<br>";
            echo "<script>
                setTimeout(3000,function(){
                    location.href='index.html'
                });
            </script>";
        } else {
            echo "Error inserting district '$name': " . $stmt->error . "<br>";
        }
    }
}

// Close statement and connection
$stmt->close();
$conn->close();
?>