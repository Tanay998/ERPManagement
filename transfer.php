<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'regNo' => $_POST['regNo'],
        'admissionNo' => $_POST['admissionNo'],
        'fullName' => $_POST['fullName'],
        'course' => $_POST['course'],
        'amount' => $_POST['amount'],
        'transactionId' => $_POST['transactionId']
    ];

    // Insert into examinations table
    $sql = "INSERT INTO examinations (reg_no, admission_no, student_name, course, amount, transaction_id)
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssds",
        $data['regNo'],
        $data['admissionNo'],
        $data['fullName'],
        $data['course'],
        $data['amount'],
        $data['transactionId']
    );

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }
    exit;
}

// Handle GET request to examinations.php
header("Location: examinations.php");
exit;
?>