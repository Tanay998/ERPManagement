<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "schoolfeesys");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Student ID']);
    exit;
}

$sql = "DELETE FROM record WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Delete failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
