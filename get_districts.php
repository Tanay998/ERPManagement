<?php
// Database connection
$host = 'localhost';
$dbname = 'schoolfeesys';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (isset($_POST['state_id'])) {
    $stateId = $_POST['state_id'];
    $stmt = $pdo->prepare("SELECT * FROM districts WHERE state_id = ?");
    $stmt->execute([$stateId]);
    $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($districts);
}