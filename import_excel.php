<?php
require 'config.php';
require 'vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

$studentType = $_POST['table'] ?? '';
$file = $_FILES['excelFile']['tmp_name'];
if (!is_uploaded_file($file)) {
    die("Failed to upload file.");
}

print_r($file);

if (!$file || !$studentType) {
    die("Invalid file or student type.");
}

$spreadsheet = IOFactory::load($file);
$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

// Skip the header row
unset($sheetData[1]);

try {
    if ($studentType === 'polytechnic') {
        $stmt = $db->prepare("INSERT INTO polyregis (branch, applicantName, fatherName, state, cdistrict, dob, admissionType, course, RollNo, semester, RegistrationFee, TransactionID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($sheetData as $row) {
            $stmt->bind_param(
                "ssssssssssss",
                $row['A'], $row['B'], $row['C'], $row['D'], $row['E'], $row['F'],
                $row['G'], $row['H'], $row['I'], $row['J'], $row['K'], $row['L']
            );
            $stmt->execute();
        }
    } elseif ($studentType === 'non-polytechnic') {
        $stmt = $db->prepare("INSERT INTO estcregis (id, course_type, courseLevel, course_list, applicant_name, employment_status, registration_fee, transaction_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($sheetData as $row) {
            $stmt->bind_param(
                "isssssss",
                $row['A'], $row['B'], $row['C'], $row['D'], $row['E'],
                $row['F'], $row['G'], $row['H']
            );
            $stmt->execute();
        }
    }
    header("Location: index.php?page=studentList.php&message=" . urlencode("Excel data imported successfully"));

} catch (Exception $e) {
    header("Location: index.php?page=studentList.php?error=" . urlencode("Import failed: " . $e->getMessage()));
}
exit;
?>
