<?php
include 'config.php';

$regNo = $_GET['regNo'] ?? '';

// Fetch transferred data
$sql = "SELECT * FROM examinations WHERE reg_no = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $regNo);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Examination Records</title>
    <!-- Include your CSS here -->
</head>
<body>
    <div class="container">
        <h1>Examination Record</h1>
        <?php if($data): ?>
            <table class="table">
                <tr><th>Reg No</th><td><?= $data['reg_no'] ?></td></tr>
                <tr><th>Admission No</th><td><?= $data['admission_no'] ?></td></tr>
                <tr><th>Student Name</th><td><?= $data['student_name'] ?></td></tr>
                <tr><th>Course</th><td><?= $data['course'] ?></td></tr>
                <tr><th>Amount Paid</th><td>₹<?= number_format($data['amount'], 2) ?></td></tr>
                <tr><th>Transaction ID</th><td><?= $data['transaction_id'] ?></td></tr>
            </table>
        <?php else: ?>
            <p>No record found</p>
        <?php endif; ?>
    </div>
</body>
</html>