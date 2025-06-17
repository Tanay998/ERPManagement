<?php
session_start();
require_once 'config.php';

// Database connection
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'schoolfeesys');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $new_semester = $_POST['new_semester'];
    
    // Determine target table based on semester
    if (in_array($new_semester, ['Sem 1', 'Sem 2'])) {
        $target_table = 'record1';
    } elseif (in_array($new_semester, ['Sem 3', 'Sem 4'])) {
        $target_table = 'record2';
    } elseif (in_array($new_semester, ['Sem 5', 'Sem 6'])) {
        $target_table = 'record3';
    } else {
        $_SESSION['error'] = "Invalid semester selected";
        header("Location: student_records.php");
        exit();
    }
    
    try {
        // Get all columns except ID
        $columns = [];
        $res = $conn->query("SHOW COLUMNS FROM record");
        while ($row = $res->fetch_assoc()) {
            if ($row['Field'] !== 'id') {
                $columns[] = $row['Field'];
            }
        }
        $columns_str = implode(', ', $columns);
        
        // Prepare transfer
        $conn->begin_transaction();
        
        // Copy to target table
        $copy_sql = "INSERT INTO $target_table ($columns_str, Semester)
                     SELECT $columns_str, ? FROM record WHERE id = ?";
        $stmt = $conn->prepare($copy_sql);
        $stmt->bind_param("si", $new_semester, $id);
        $stmt->execute();
        
        // Delete original record
        $delete_sql = "DELETE FROM record WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['message'] = "Record transferred successfully to $target_table";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Transfer failed: " . $e->getMessage();
    }
}

$conn->close();
header("Location: student_records.php");
exit();
?>