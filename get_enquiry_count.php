<?php
require_once 'config.php';

$conn = new mysqli('localhost', DB_USER, DB_PASS, 'schoolfeesys');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$session = isset($_GET['session']) ? trim($_GET['session']) : '';

$count = 0;
if (!empty($session)) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM enquiry WHERE Session = ?");
    $stmt->bind_param("s", $session);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $count = $row['count'];
    }
    $stmt->close();
}

echo $count;
$conn->close();
?>