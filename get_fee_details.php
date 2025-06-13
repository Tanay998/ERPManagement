<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "schoolfeesys";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_POST['regNo'])) {
    $regNo = $_POST['regNo'];
    
    // Verify table/column names match your database
    $sql = "SELECT 
            `tuition_fee`,
            `tuitionFixed`,
            `studentFundFixed`,
            `personalityFixed`,
            `securityFixed`,
            `formProcessingFixed`,
            `student_fund`,
            `personality_dev`,
            `security_deposit`,
            `form_processing`,
            `total_fixed`,
            `total_paid`,
            `balance`
            FROM `record`
            WHERE `RegNo` = ?"; // Use backticks
    
    $stmt = $conn->prepare($sql);
    
    // Check if prepare() succeeded
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("s", $regNo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $feeDetails = '
        <div class="fee-details p-3 bg-light">
            <h5>Fee Details</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Fixed (₹)</th>
                        <th>Paid (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tuition Fee</td>
                        <td>'.number_format($row['tuitionFixed'], 2).'</td>
                        <td>'.number_format($row['tuition_fee'], 2).'</td>
                    </tr>
                    <tr>
                        <td>Student Fund</td>
                        <td>'.number_format($row['studentFundFixed'], 2).'</td>
                        <td>'.number_format($row['student_fund'], 2).'</td>
                    </tr>
                    <tr>
                        <td>Personality Development</td>
                        <td>'.number_format($row['personalityFixed'], 2).'</td>
                        <td>'.number_format($row['personality_dev'],).'</td>
                    </tr>
                    <tr>
                        <td>Security Deposit</td>
                        <td>'.number_format($row['securityFixed'], 2).'</td>
                        <td>'.number_format($row['security_deposit'], 2).'</td>
                    </tr>
                    <tr>
                        <td>Form Processing</td>
                        <td>'.number_format($row['formProcessingFixed'], 2).'</td>
                        <td>'.number_format($row['form_processing'], 2).'</td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>Total</strong></td>
                        <td><strong>'.number_format($row['total_fixed'], 2).'</strong></td>
                        <td><strong>'.number_format($row['total_paid'], 2).'</strong></td>
                    </tr>
                    <tr class="table-info">
                        <td colspan="2"><strong>Balance Amount:</strong></td>
                        <td><strong>'.number_format($row['balance'], 2).'</strong></td>
                    </tr>
                </tbody>
            </table>
           <div class="text-end mt-3">
            <a href="index.php?page=edit_fee_form.php&regNo='.$regNo.'" class="btn btn-warning me-2">Edit Details</a>
            <a href="index.php" class="btn btn-secondary">Back to Search</a>
        </div>
    </div>';
        
        echo $feeDetails;
    }
}
?>