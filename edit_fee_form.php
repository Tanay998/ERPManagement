<?php
// Include your database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "schoolfeesys";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_GET['regNo'])) {
    $regNo = $_GET['regNo'];
    
    // Fetch current fee details
    $sql = "SELECT * FROM `record` WHERE `RegNo` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $regNo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Fee Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Fee Details for Registration No: <?php echo htmlspecialchars($regNo); ?></h2>
        <form action="update_fee_details.php" method="post">
            <input type="hidden" name="regNo" value="<?php echo htmlspecialchars($regNo); ?>">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <h4>Fixed Fees</h4>
                    <div class="mb-3">
                        <label class="form-label">Tuition Fee Fixed</label>
                        <input type="number" step="0.01" class="form-control" name="tuitionFixed" value="<?php echo $row['tuitionFixed']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student Fund Fixed</label>
                        <input type="number" step="0.01" class="form-control" name="studentFundFixed" value="<?php echo $row['studentFundFixed']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Personality Development Fixed</label>
                        <input type="number" step="0.01" class="form-control" name="personalityFixed" value="<?php echo $row['personalityFixed']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Security Deposit Fixed</label>
                        <input type="number" step="0.01" class="form-control" name="securityFixed" value="<?php echo $row['securityFixed']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Form Processing Fixed</label>
                        <input type="number" step="0.01" class="form-control" name="formProcessingFixed" value="<?php echo $row['formProcessingFixed']; ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4>Paid Fees</h4>
                    <div class="mb-3">
                        <label class="form-label">Tuition Fee Paid</label>
                        <input type="number" step="0.01" class="form-control" name="tuition_fee" value="<?php echo $row['tuition_fee']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student Fund Paid</label>
                        <input type="number" step="0.01" class="form-control" name="student_fund" value="<?php echo $row['student_fund']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Personality Development Paid</label>
                        <input type="number" step="0.01" class="form-control" name="personality_dev" value="<?php echo $row['personality_dev']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Security Deposit Paid</label>
                        <input type="number" step="0.01" class="form-control" name="security_deposit" value="<?php echo $row['security_deposit']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Form Processing Paid</label>
                        <input type="number" step="0.01" class="form-control" name="form_processing" value="<?php echo $row['form_processing']; ?>">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Fee Details</button>
            <a href="view_student.php?regNo=<?php echo htmlspecialchars($regNo); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php
    } else {
        echo "No record found for this registration number.";
    }
} else {
    echo "No registration number provided.";
}
?>