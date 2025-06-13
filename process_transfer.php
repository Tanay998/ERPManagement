<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the student ID from the URL
$studentId = $_GET['gid'] ?? '';
$table = $_GET['table'] ?? '';

if (empty($studentId) || empty($table)) {
    $_SESSION['error'] = "Invalid transfer request";
    header("Location: index.php?page=studentList.php");
    exit();
}

// Fetch student data based on table
if ($table === 'polyregis') {
    $sql = "SELECT * FROM polyregis WHERE RollNo = ?";
} elseif ($table === 'estcregis') {
    $sql = "SELECT * FROM estcregis WHERE id = ?";
} else {
    $_SESSION['error'] = "Invalid table specified";
    header("Location: index.php?page=studentList.php");
    exit();
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Student not found";
    header("Location: studentList.php");
    exit();
}

// Store transfer data in session
$_SESSION['transfer_data'] = $result->fetch_assoc();

// Redirect to the admission form
header("Location: index.php?page=new_admission1.php");
exit();
?>