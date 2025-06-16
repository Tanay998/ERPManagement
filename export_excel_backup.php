<?php
// export_excel_backup.php
require_once 'config.php';

$type = $_GET['type'] ?? 'polytechnic';

if ($type === 'polytechnic') {
    $table = 'polyregis_backup';
    $filename = 'transferred_polytechnic_students_' . date('Ymd') . '.xls';
} else {
    $table = 'estcregis_backup';
    $filename = 'transferred_non_polytechnic_students_' . date('Ymd') . '.xls';
}

// Fetch data
$sql = "SELECT * FROM $table";
$result = $db->query($sql);

// Excel headers
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

$flag = false;
while ($row = $result->fetch_assoc()) {
    if (!$flag) {
        // Display column names as first row
        echo implode("\t", array_keys($row)) . "\n";
        $flag = true;
    }
    // Escape double quotes
    array_walk($row, function(&$str) {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
    });
    echo implode("\t", array_values($row)) . "\n";
}
exit;
?>