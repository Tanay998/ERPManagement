<?php
require 'config.php';
require 'vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$studentType = $_POST['student_type'] ?? 'polytechnic';
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

if ($studentType === 'polytechnic') {
    $sql = "SELECT branch, applicantName, fatherName, state, cdistrict, dob, admissionType, course, RollNo, semester, RegistrationFee, TransactionID FROM polyregis";
    $headers = ['Branch', 'Applicant Name', 'Father Name', 'State', 'District', 'DOB', 'Admission Type', 'Course', 'Roll No', 'Semester', 'Reg Fee', 'Transaction ID'];
} else {
    $sql = "SELECT id, course_type, courseLevel, course_list, applicant_name, employment_status, registration_fee, transaction_id FROM estcregis";
    $headers = ['ID', 'Course Type', 'Course Level', 'Course', 'Applicant Name', 'Employment Status', 'Reg Fee', 'Transaction ID'];
}

$result = $db->query($sql);

// Set headers
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Fill rows
$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $col = 'A';
    foreach ($row as $value) {
        $sheet->setCellValue($col . $rowNum, $value);
        $col++;
    }
    $rowNum++;
}

// Download file
$filename = $studentType . "_students.xlsx";
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
?>
