<?php
require_once 'config.php';

// Create database connection
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'schoolfeesys');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['session'])) {
    $session = $_GET['session'];
    $sessionYear = explode('-', $session)[0];
    
    $sql = "SELECT COUNT(*) as count FROM record 
            WHERE AdmissionNo1 LIKE ? OR AdmissionDate LIKE ?";
    $stmt = $conn->prepare($sql);
    
    $admissionPattern = $sessionYear . '%';
    $datePattern = $sessionYear . '%';
    
    $stmt->bind_param("ss", $admissionPattern, $datePattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo $row['count'] ?? 0;
} else {
    echo '0';
}

$conn->close();
?>