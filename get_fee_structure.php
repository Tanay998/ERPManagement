<?php
include 'config.php';

$category = $_POST['category'] ?? 'GENERAL_OBC';
$year = $_POST['year'] ?? '1';
$installment = $_POST['installment'] ?? null;

// Map form category to database category types
$categoryMap = [
    'GENERAL' => 'GENERAL_OBC',
    'OBC' => 'GENERAL_OBC',
    'SC' => 'SC_ST_TFW',
    'ST' => 'SC_ST_TFW',
    'TFW' => 'SC_ST_TFW',
    'EWS' => 'GENERAL_OBC'
];

$dbCategory = $categoryMap[$category] ?? 'GENERAL_OBC';

// Calculate fees based on the new structure
$tuitionFee = ($dbCategory === 'GENERAL_OBC') ? 25000 : 0;
$studentFundFee = 7000;
$securityAmount = ($year == 1) ? 2000 : 0;
$personalityDevCharges = ($year == 1) ? 2000 : 0;

// Adjust for installments (only for first year)
if ($year == 1 && $installment) {
    if ($dbCategory === 'GENERAL_OBC') {
        // Split tuition fee into two installments
        $tuitionFee = ($installment == 1) ? 12500 : 12500;
    }
    
    // Student fund fee is paid in full in first installment
    $studentFundFee = ($installment == 1) ? 7000 : 0;
    
    // Security and personality dev charges only in first installment
    $securityAmount = ($installment == 1) ? 2000 : 0;
    $personalityDevCharges = ($installment == 1) ? 2000 : 0;
}

$response = [
    'tuition_fee' => $tuitionFee,
    'student_fund_fee' => $studentFundFee,
    'security_amount' => $securityAmount,
    'personality_dev_charges' => $personalityDevCharges,
    'total_amount' => $tuitionFee + $studentFundFee + $securityAmount + $personalityDevCharges,
    'year' => $year,
    'installment' => $installment,
    'category' => $dbCategory
];

echo json_encode($response);
?>