<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "schoolfeesys";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid ID']);
        exit;
    }
    
    try {
        // Fetch applicant details
        $stmt = $conn->prepare("SELECT * FROM applicants WHERE applicant_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['error' => 'Applicant not found']);
            exit;
        }
        
        $applicant = $result->fetch_assoc();
        $stmt->close();
        
        // Fetch educational details
        $stmt = $conn->prepare("SELECT * FROM educational_details WHERE applicant_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $educational = [];
        while ($row = $result->fetch_assoc()) {
            $educational[] = $row;
        }
        $stmt->close();
        
        echo json_encode([
            'applicant' => $applicant,
            'educational' => $educational
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}