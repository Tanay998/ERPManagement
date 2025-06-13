<?php
session_start();
include 'config.php';

// Check if required parameters are present
if (!isset($_POST['payment_id'], $_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

$payment_id = trim($_POST['payment_id']);
$user_id = (int)$_SESSION['user_id'];

// Validate payment ID format
if (empty($payment_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payment ID']);
    exit();
}

// Check if payment already exists
$check_sql = "SELECT id FROM payment WHERE payment_id = ?";
$stmt = $db->prepare($check_sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $db->error]);
    exit();
}

$stmt->bind_param("s", $payment_id);
$stmt->execute();
$result = $stmt->get_result();

// Insert only if payment doesn't exist
if ($result->num_rows === 0) {
    // Flexible insert query that works with or without amount column
    $insert_sql = "INSERT INTO payment (payment_id, user_id, status";
    $values = "VALUES (?, ?, 'success'";
    $params = ["si", $payment_id, $user_id];
    // Add amount if column exists (optional)
    if (isset($_POST['amount'])) {
        $insert_sql .= ", amount";
        $values .= ", ?";
        $params[0] .= "i";
        $params[] = (int)$_POST['amount'];
    }
    
    $insert_sql .= ") " . $values . ")";

    $insert_stmt = $db->prepare($insert_sql);
    
    if ($insert_stmt === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $db->error]);
        exit();
    }
    
    // Dynamic parameter binding
    $insert_stmt->bind_param(...$params);
    $success = $insert_stmt->execute();
    
    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Payment record saved']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save payment record'.$db->error]);
    }
} else {
    // Payment already exists - return success
    echo json_encode(['status' => 'success', 'message' => 'Payment already recorded']);
}