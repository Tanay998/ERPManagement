<?php
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate CSRF token if you have one
if (isset($_SESSION['csrf_token']) && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
    $_SESSION['error'] = "Invalid CSRF token";
    header("Location: index.php?page=studentList.php");
    exit();
}

// Validate parameters
if (!isset($_POST['table']) || !isset($_POST['id'])) {
    $_SESSION['error'] = "Invalid parameters";
    header("Location: index.php?page=studentList.php");
    exit();
}

$table = $_POST['table'];
$id = $_POST['id'];
print_r($table);
print_r($id);

try {
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
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Student record not found";
        header("Location: index.php?page=studentList.php");
        exit();
    }
    
    $student = $result->fetch_assoc();
    
    // Split applicant name into parts
    $nameParts = explode(' ', $student[$table === 'polyregis' ? 'applicantName' : 'applicant_name']);
    $firstName = $nameParts[0] ?? '';
    $middleName = count($nameParts) > 1 ? $nameParts[1] : '';
    $lastName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 2)) : '';
    
    // Prepare data for transfer
    $transferData = [
        'firstName' => $firstName,
        'middleName' => $middleName,
        'lastName' => $lastName,
        'original_id' => $id,
        'source_table' => $table
    ];
    
    // Add other fields based on table type
    if ($table === 'polyregis') {
        $transferData += [
            'branch' => $student['branch'],
            'fatherName' => $student['fatherName'],
            'state' => $student['state'],
            'district' => $student['cdistrict'],
            'dob' => $student['dob'],
            'admissionType' => $student['admissionType'],
            'course' => $student['course'],
            'rollNo' => $student['RollNo'],
            'semester' => $student['semester'],
            'registrationFee' => $student['RegistrationFee'],
            'transactionId' => $student['TransactionID']
        ];
    } else {
        $transferData += [
            'courseType' => $student['course_type'],
            'courseLevel' => $student['courseLevel'],
            'courseList' => $student['course_list'],
            'employmentStatus' => $student['employment_status'],
            'photoPath' => $student['photo_path'],
            'registrationFee' => $student['registration_fee'],
            'transactionId' => $student['transaction_id']
        ];
    }
    
    // Store data in session for the transfer
    $_SESSION['transfer_data'] = $transferData;
    
    // Redirect to new admission form
    header("Location: index.php?page=new_admission1.php");
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "Error transferring student: " . $e->getMessage();
    header("Location: index.php?page=studentList.php");
    exit();
}