<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "schoolfeesys";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$sql = "SELECT name, fee_amount, fee_amount1 FROM courses";
$result = $conn->query($sql);

$courses = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

echo json_encode($courses);
$conn->close();
?>