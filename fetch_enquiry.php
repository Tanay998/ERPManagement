<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=schoolfeesys", DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM enquiry WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        header('Content-Type: application/json');
        echo json_encode($record);
    } else {
        echo json_encode([]); // Return empty if no record found
    }
    exit();
}
?>