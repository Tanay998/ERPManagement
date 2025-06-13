<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "schoolfeesys");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

// Validate input data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$amount = floatval($_POST['amount']);
$payfee = floatval($_POST['payfee']);
$payfee1 = floatval($_POST['payfee1']);
$balance = $amount - ($payfee + $payfee1);
$firstName = isset($_POST['firstName']) ? $conn->real_escape_string($_POST['firstName']) : '';
$middleName = isset($_POST['middleName']) ? $conn->real_escape_string($_POST['middleName']) : '';
$lastName = isset($_POST['lastName']) ? $conn->real_escape_string($_POST['lastName']) : '';
$fatherName = isset($_POST['fatherName']) ? $conn->real_escape_string($_POST['fatherName']) : '';
$motherName = isset($_POST['motherName']) ? $conn->real_escape_string($_POST['motherName']) : '';
$course = isset($_POST['course']) ? $conn->real_escape_string($_POST['course']) : '';
$admissionDate = isset($_POST['admissionDate']) ? $conn->real_escape_string($_POST['admissionDate']) : '';
$payfee = isset($_POST['payfee']) ? intval($_POST['payfee']) : 0;
$payfee1 = isset($_POST['payfee1']) ? intval($_POST['payfee1']) : 0;
$balance = isset($_POST['balance']) ? intval($_POST['balance']) : 0;

// Handle file upload
$profilePicturePath = null;
if (!empty($_FILES['profilePicture']['name'])) {
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES['profilePicture']['name']);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES['profilePicture']['tmp_name']);
    if ($check === false) {
        echo json_encode(['success' => false, 'error' => 'File is not an image.']);
        exit;
    }
    
    // Check file size (5MB max)
    if ($_FILES['profilePicture']['size'] > 5000000) {
        echo json_encode(['success' => false, 'error' => 'Sorry, your file is too large.']);
        exit;
    }
    
    // Allow certain file formats
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Only JPG, JPEG, PNG & GIF files are allowed.']);
        exit;
    }
    
    // Generate unique filename
    $newFilename = uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFilename;
    
    if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $targetFile)) {
        $profilePicturePath = $targetFile;
    }
}

// Check if ID is valid
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Student ID.']);
    exit;
}

// Ensure required fields are not empty
if (empty($firstName) || empty($lastName) || empty($fatherName) || empty($course)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
    exit;
}


// Update query
$sql = "UPDATE record SET 
        `Amount` = ?,
        `payfee` = ?,
        `payfee1` = ?,
        `balance` = ?,
        " . ($profilePicturePath ? "`Profile Picture` = ?," : "") . "
        `First Name` = ?, 
        `Middle Name` = ?, 
        `Last Name` = ?, 
        `Father Name` = ?,
        `Mother Name` = ?,
        `Course` = ?,
        `AdmissionDate` = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// Bind parameters based on whether we have a new profile picture
if ($profilePicturePath) {
    $stmt->bind_param("ddddssssssssi", 
        $amount,
        $payfee,
        $payfee1,
        $balance,
        $profilePicturePath,
        $_POST['firstName'],
        $_POST['middleName'],
        $_POST['lastName'],
        $_POST['fatherName'],
        $_POST['motherName'],
        $_POST['course'],
        $_POST['admissionDate'],
        $id
    );
} else {
    $stmt->bind_param("dddssssssssi", 
        $amount,
        $payfee,
        $payfee1,
        $balance,
        $_POST['firstName'],
        $_POST['middleName'],
        $_POST['lastName'],
        $_POST['fatherName'],
        $_POST['motherName'],
        $_POST['course'],
        $_POST['admissionDate'],
        $id
    );
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student updated successfully.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>