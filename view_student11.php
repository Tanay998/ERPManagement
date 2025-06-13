<?php
// Start session and check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'config.php';
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'schoolfeesys');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student ID from URL parameter
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch student record
$sql = "SELECT * FROM record WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Student record not found";
    echo "<script>
        window.location.href='index.php?page=student_records.php'
    </script>";
    // header("Location: student_records.php");
    exit();
}

$student = $result->fetch_assoc();

// Format full name
$fullName = $student['First Name'] . ' ' . $student['Middle Name'] . ' ' . $student['Last Name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Record</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .card-header {
            font-weight: bold;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }
        .section-title {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 5px;
            margin-bottom: 20px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Student Details</h3>
                <div>
                    <a href="index.php?page=student_records.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Records
                    </a>
                    <a href="index.php?page=edit_student11.php&id=<?= $student['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-md-2 text-center">
                        <?php if (!empty($student['Profile Picture'])): ?>
                            <img src="<?= htmlspecialchars($student['Profile Picture']) ?>" 
                                 alt="Student Photo" class="profile-img rounded-circle mb-2">
                        <?php else: ?>
                            <div class="profile-img rounded-circle bg-light d-flex align-items-center justify-content-center mb-2">
                                <i class="fas fa-user-graduate fa-4x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-10">
                        <h2><?= htmlspecialchars($fullName) ?></h2>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><span class="info-label">Admission No:</span> <?= htmlspecialchars($student['AdmissionNo1']) ?>/<span class="info-label ms-2"><?= htmlspecialchars($student['AdmissionNo2']) ?></span>/<span class="info-label ms-2"><?= htmlspecialchars($student['RegNo']) ?></span></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><span class="info-label">Course:</span> <?= htmlspecialchars($student['Course']) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><span class="info-label">Semester:</span> <?= htmlspecialchars($student['Semester']) ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><span class="info-label">Admission Date:</span> <?= htmlspecialchars($student['AdmissionDate']) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><span class="info-label">Type of Entry:</span> <?= htmlspecialchars($student['TypeofEntry']) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><span class="info-label">Gender:</span> <?= htmlspecialchars($student['Gender']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Personal Information -->
                    <div class="col-md-6 mb-4">
                        <h4 class="section-title">Personal Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><span class="info-label">Date of Birth:</span> <?= htmlspecialchars($student['Date Of Birth']) ?></p>
                                <p><span class="info-label">Father's Name:</span> <?= htmlspecialchars($student['Father Name']) ?></p>
                                <p><span class="info-label">Mother's Name:</span> <?= htmlspecialchars($student['Mother Name']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><span class="info-label">Category:</span> <?= htmlspecialchars($student['Category']) ?></p>
                                <p><span class="info-label">Blood Group:</span> <?= htmlspecialchars($student['Blood Group']) ?></p>
                                <p><span class="info-label">Aadhaar No:</span> <?= htmlspecialchars($student['Aadhaar No']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="col-md-6 mb-4">
                        <h4 class="section-title">Contact Information</h4>
                        <p><span class="info-label">Email:</span> <?= htmlspecialchars($student['Email']) ?></p>
                        <p><span class="info-label">Mobile:</span> <?= htmlspecialchars($student['Parents Mobile No']) ?></p>
                        <p><span class="info-label">Landline:</span> <?= htmlspecialchars($student['Land Line No']) ?></p>
                        
                        <h5 class="mt-3">Correspondence Address</h5>
                        <p><?= htmlspecialchars($student['Correspondence Street']) ?><br>
                        <?= htmlspecialchars($student['Correspondence City']) ?>, 
                        <?= htmlspecialchars($student['Correspondence District']) ?><br>
                        <?= htmlspecialchars($student['Correspondence State']) ?> - 
                        <?= htmlspecialchars($student['Correspondence pincode']) ?></p>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Academic Information -->
                    <div class="col-md-6 mb-4">
                        <h4 class="section-title">Academic Information</h4>
                        <p><span class="info-label">Last School:</span> <?= htmlspecialchars($student['School Name']) ?></p>
                        <p><span class="info-label">Board:</span> <?= htmlspecialchars($student['Board']) ?></p>
                        <p><span class="info-label">Year of Passing:</span> <?= htmlspecialchars($student['Year of Passing']) ?></p>
                        <p><span class="info-label">Percentage:</span> <?= htmlspecialchars($student['Percentage']) ?>%</p>
                    </div>
                    
                    <!-- Financial Information -->
                    <div class="col-md-6 mb-4">
                        <h4 class="section-title">Financial Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><span class="info-label">Tuition Fee:</span> ₹<?= number_format($student['tuition_fee'] ?? 0, 2) ?></p>
                                <p><span class="info-label">Student Fund:</span> ₹<?= number_format($student['student_fund'] ?? 0, 2) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><span class="info-label">Security Deposit:</span> ₹<?= number_format($student['security_deposit'] ?? 0, 2) ?></p>
                                <p><span class="info-label">Total Paid:</span> ₹<?= number_format($student['total_paid'] ?? 0, 2) ?></p>
                            </div>
                        </div>
                        <p class="mt-2"><span class="info-label">Balance:</span> 
                            <span class="<?= ($student['balance'] > 0) ? 'text-danger' : 'text-success' ?>">
                                ₹<?= number_format($student['balance'] ?? 0, 2) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card-footer text-end">
                <a href="print_student.php&id=<?= $student['id'] ?>" class="btn btn-secondary" target="_blank">
                    <i class="fas fa-print"></i> Print Record
                </a>
                <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Record
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>