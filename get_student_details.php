<?php
// get_student_details.php

// Turn off all error reporting in production
// error_reporting(0);

// For debugging, you can enable this:
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user

// Database connection - REPLACE WITH YOUR CREDENTIALS
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "schoolfeesys";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if (!isset($_GET['id'])) {
        throw new Exception("Student ID not provided");
    }

    $id = intval($_GET['id']);
    if ($id <= 0) {
        throw new Exception("Invalid Student ID");
    }

    $sql = "SELECT *, `Profile Picture` FROM record WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo json_encode($student);
    } else {
        throw new Exception("Student not found");
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>