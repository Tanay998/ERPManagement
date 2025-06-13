<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


// Get table type from URL parameter
$tableType = $_GET['type'] ?? '';
$tableName = '';
$columns = [];
$headers = [];

if ($tableType === 'polytechnic') {
    $tableName = 'polyregis';
    $columns = [
        'branch', 'applicantName', 'fatherName', 'state', 'cdistrict', 
        'dob', 'admissionType', 'course', 'RollNo', 'semester', 
        'RegistrationFee', 'TransactionID'
    ];
    $headers = [
        'Branch', 'Applicant Name', 'Father Name', 'State', 'District', 
        'DOB', 'Admission Type', 'Course', 'Roll No', 'Semester',
        'Registration Fee', 'Transaction ID'
    ];
} elseif ($tableType === 'non-polytechnic') {
    $tableName = 'estcregis';
    $columns = [
        'course_type', 'courseLevel', 'course_list', 'applicant_name', 
        'employment_status', 'photo_path', 'registration_fee', 'transaction_id'
    ];
    $headers = [
        'Course Type', 'Course Level', 'Course', 'Applicant Name', 
        'Employment Status', 'Photo Path', 'Registration Fee', 'Transaction ID'
    ];
} else {
    die("Invalid export type");
}

$columnList = implode(', ', $columns);
$sql = "SELECT $columnList FROM $tableName";
$result = $conn->query($sql);

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add headers
foreach ($headers as $index => $header) {
    $columnLetter = Coordinate::stringFromColumnIndex($index + 1);
    $sheet->setCellValue($columnLetter . '1', $header);
}

// Add data
$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    foreach ($columns as $colIndex => $colName) {
        $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
        $cell = $columnLetter . $rowNum;
        $sheet->setCellValue($cell, $row[$colName]);
    }
    $rowNum++;
}

// Send Excel file to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="student_export_'.date('Ymd').'.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;