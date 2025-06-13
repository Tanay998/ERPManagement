<?php
require_once 'db_connection.php';

if (!isset($_GET['table']) || !isset($_GET['id'])) {
    header("Location: studentList.php");
    exit();
}
$page = 'edit_student.php';
$table = $_GET['table'];
$id = $_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($table === 'polyregis') {
        $sql = "UPDATE polyregis SET 
                branch = ?, applicantName = ?, fatherName = ?, state = ?, 
                cdistrict = ?, dob = ?, admissionType = ?, course = ?, 
                semester = ?, RegistrationFee = ?, TransactionID = ?, RollNo = ? 
                WHERE RollNo = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssss", 
            $_POST['branch'], $_POST['applicantName'], $_POST['fatherName'], 
            $_POST['state'], $_POST['district'], $_POST['dob'], 
            $_POST['admissionType'], $_POST['course'], $_POST['semester'], 
            $_POST['RegistrationFee'], $_POST['TransactionID'], $_POST['RollNo'],$id);
    } elseif ($table === 'estcregis') {
        $sql = "UPDATE estcregis SET 
                course_type = ?, courseLevel = ?, course_list = ?, 
                applicant_name = ?, employment_status = ?, registration_fee = ?, 
                transaction_id = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", 
            $_POST['course_type'], $_POST['courseLevel'], $_POST['course_list'], 
            $_POST['applicant_name'], $_POST['employment_status'], 
            $_POST['registration_fee'], $_POST['transaction_id'], $id);
    }
    
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $message = "Record updated successfully";
    } else {
        $message = "Error updating record";
    }
    
    header("Location: studentList.php?message=" . urlencode($message));
    exit();
}

// Fetch the record to edit
if ($table === 'polyregis') {
    $sql = "SELECT * FROM polyregis WHERE RollNo = ?";
    $primaryKey = 'RollNo';
} elseif ($table === 'estcregis') {
    $sql = "SELECT * FROM estcregis WHERE id = ?";
    $primaryKey = 'id';
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();

if (!$record) {
    header("Location: studentList.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student Record</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Student Record</h1>
        <form method="post" class="shadow p-3 mb-5 bg-white rounded">
        <a href="index.php?page=studentList.php" class="btn btn-danger float-end">BACK</a><br /><br />
            <?php if ($table === 'polyregis'): ?>
                <div class="form-group">
                    <label for="branch" class="form-label">Branch:</label>
                    <input type="text" id="branch" name="branch" value="<?php echo htmlspecialchars($record['branch']); ?>" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="applicantName" class="form-label">Applicant Name:</label>
                    <input type="text" id="applicantName" name="applicantName" value="<?php echo htmlspecialchars($record['applicantName']); ?>" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="fatherName" class="form-label">Father Name:</label>
                    <input type="text" id="fatherName" name="fatherName" value="<?php echo htmlspecialchars($record['fatherName']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="state" class="form-label">State:</label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($record['state']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="district" class="form-label">District:</label>
                    <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($record['cdistrict']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="dob" class="form-label">Date of Birth:</label>
                    <input type="text" id="dob" name="dob" value="<?php echo htmlspecialchars($record['dob']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="admissionType" class="form-label">Admission Type:</label>
                    <input type="text" id="admissionType" name="admissionType" value="<?php echo htmlspecialchars($record['admissionType']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="course" class="form-label">Course:</label>
                    <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($record['course']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="RollNo" class="form-label">Roll No:</label>
                    <input type="text" id="RollNo" name="RollNo" value="<?php echo htmlspecialchars($record['RollNo']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="semester" class="form-label">Semester:</label>
                    <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($record['semester']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="RegistrationFee" class="form-label">Registration Fee:</label>
                    <input type="text" id="RegistrationFee" name="RegistrationFee" value="<?php echo htmlspecialchars($record['RegistrationFee']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="TransactionID" class="form-label">Transaction ID:</label>
                    <input type="text" id="TransactionID" name="TransactionID" value="<?php echo htmlspecialchars($record['TransactionID']); ?>" class="form-control" required>
                </div>
                
            <?php elseif ($table === 'estcregis'): ?>
                <div class="form-group">
                    <label for="course_type" class="form-label">Course Type:</label>
                    <input type="text" id="course_type" name="course_type" value="<?php echo htmlspecialchars($record['course_type']); ?>" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="courseLevel" class="form-label">Course Level:</label>
                    <input type="text" id="courseLevel" name="courseLevel" value="<?php echo htmlspecialchars($record['courseLevel']); ?>" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="course_list" class="form-label">Course List:</label>
                    <input type="text" id="course_list" name="course_list" value="<?php echo htmlspecialchars($record['course_list']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="applicant_name" class="form-label">Course List:</label>
                    <input type="text" id="applicant_name" name="applicant_name" value="<?php echo htmlspecialchars($record['applicant_name']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="employment_status" class="form-label">Employment Status:</label>
                    <input type="text" id="employment_status" name="employment_status" value="<?php echo htmlspecialchars($record['employment_status']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="photo_path" class="form-label">Photo</label>
                    <input type="file" name="photo_path" id="photo_path" class="form-control">
                    <img src="<?php echo htmlspecialchars($record['photo_path']); ?>" alt="">
                </div>

                <div class="form-group">
                    <label for="registration_fee" class="form-label">Registration Fee:</label>
                    <input type="text" id="registration_fee" name="registration_fee" value="<?php echo htmlspecialchars($record['registration_fee']); ?>" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="transaction_id" class="form-label">Transaction ID:</label>
                    <input type="text" id="transaction_id" name="transaction_id" value="<?php echo htmlspecialchars($record['transaction_id']); ?>" class="form-control" required>
                </div>
                
            <?php endif; ?>
            
            <button type="submit" class="btn btn-warning"><i class="fa-solid fa-pen-to-square me-1"></i>Update Record</button>
            <a href="index.php?page=studentList.php"><button type="button" class="btn btn-danger"><i class="fa-solid fa-power-off me-1"></i>Cancel</button></a>
        </form>
    </div>
</body>
</html>