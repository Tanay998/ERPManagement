<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Unauthorized access'];
    header("Location: index.php?page=studentList.php");
    exit;
}

if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $tableType = $_POST['table'] ?? '';
    $tmpName = $_FILES['excelFile']['tmp_name'];
    
    try {
        $spreadsheet = IOFactory::load($tmpName);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        if ($tableType === 'polytechnic') {
            $tableName = 'polyregis';
            $columns = [
                'branch' => 'A',
                'applicantName' => 'B',
                'fatherName' => 'C',
                'state' => 'D',
                'cdistrict' => 'E',
                'category' => 'F',
                'dob' => 'G',
                'admissionType' => 'H',
                'course' => 'I',
                'RollNo' => 'J',
                'semester' => 'K',
                'RegistrationFee' => 'L',
                'TransactionID' => 'M'
            ];
        } elseif ($tableType === 'non-polytechnic') {
            $tableName = 'estcregis';
            $columns = [
                'course_type' => 'B',
                'courseLevel' => 'C',
                'course_list' => 'D',
                'applicant_name' => 'E',
                'employment_status' => 'F',
                'registration_fee' => 'G',
                'transaction_id' => 'H'
            ];
        } else {
            throw new Exception("Invalid table type specified");
        }

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];

            foreach ($columns as $field => $col) {
                $cellValue = $sheet->getCell($col.$row)->getValue();
                
                // Convert null values to empty string
                $cellValue = ($cellValue === null) ? '' : $cellValue;

                if ($field === 'dob' && $tableType === 'polytechnic' && !empty($cellValue)) {
                    if (is_numeric($cellValue)) {
                        $cellValue = Date::excelToDateTimeObject($cellValue)->format('Y-m-d');
                    } else {
                        $cellValue = date('Y-m-d', strtotime($cellValue));
                    }
                }

                $rowData[$field] = $cellValue;
            }

            try {
                $fields = implode(', ', array_keys($rowData));
                $placeholders = implode(', ', array_fill(0, count($rowData), '?'));
                $sql = "INSERT INTO $tableName ($fields) VALUES ($placeholders)";

                $stmt = $db->prepare($sql);
                $types = str_repeat('s', count($rowData));
                $values = array_values($rowData);
                $stmt->bind_param($types, ...$values);

                if ($stmt->execute()) {
                    $successCount++;
                } else {
                    throw new Exception($stmt->error);
                }
            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "Row $row: " . $e->getMessage();
            }
        }

        $msg = "Import completed: $successCount records imported, $errorCount errors.";
        if ($errorCount > 0) {
            $msg .= " First error: " . ($errors[0] ?? '');
            error_log("Import errors:\n" . implode("\n", $errors));
            $_SESSION['alert'] = ['type' => 'warning', 'message' => $msg];
        } else {
            $_SESSION['alert'] = ['type' => 'success', 'message' => $msg];
        }

        header("Location: index.php?page=studentList.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Error: ' . $e->getMessage()];
        header("Location: index.php?page=studentList.php");
        exit;
    }
} else {
    $uploadError = $_FILES['excelFile']['error'] ?? 0;
    $errorMsg = match($uploadError) {
        1 => 'File exceeds upload_max_filesize',
        2 => 'File exceeds MAX_FILE_SIZE',
        3 => 'Partial upload',
        4 => 'No file uploaded',
        6 => 'Missing temporary folder',
        7 => 'Disk write failed',
        8 => 'PHP extension stopped upload',
        default => 'Unknown error'
    };
    $_SESSION['alert'] = ['type' => 'danger', 'message' => "File upload error: $errorMsg"];
    header("Location: index.php?page=studentList.php");
    exit;
}