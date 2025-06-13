<?php
require_once 'db_connection.php';
$page = 'view_student.php';
// Validate parameters
if (!isset($_GET['table']) || !isset($_GET['id'])) {
    header("Location: studentList.php?error=Invalid parameters");
    exit();
}

$table = $_GET['table'];
$id = $_GET['id'];

try {
    if ($table === 'polyregis') {
        $sql = "SELECT * FROM polyregis WHERE RollNo = ?";
    } elseif ($table === 'estcregis') {
        $sql = "SELECT * FROM estcregis WHERE id = ?";
    } else {
        header("Location: studentList.php?error=Invalid table specified");
        exit();
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: studentList.php?error=Student record not found");
        exit();
    }
    
    $student = $result->fetch_assoc();
} catch (Exception $e) {
    header("Location: studentList.php?error=Database error: " . urlencode($e->getMessage()));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detail-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        .student-photo {
            max-width: 200px;
            max-height: 200px;
            border: 2px solid #dee2e6;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="card detail-card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        Student Details
                    </h2>
                    <a href="index.php?page=studentList.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <?php if ($table === 'polyregis'): ?>
                    <!-- Polytechnic Student View -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <span class="detail-label">Roll Number:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['RollNo']) ?></p>
                            </div>
                            <div class="mb-3">
                                <span class="detail-label">Full Name:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['applicantName']) ?></p>
                            </div>
                            <div class="mb-3">
                                <span class="detail-label">Father's Name:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['fatherName']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <span class="detail-label">Branch:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['branch']) ?></p>
                            </div>
                            <div class="mb-3">
                                <span class="detail-label">Course:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['course']) ?></p>
                            </div>
                            <div class="mb-3">
                                <span class="detail-label">Semester:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['semester']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <span class="detail-label">State:</span>
                                <p><?= htmlspecialchars($student['state']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <span class="detail-label">District:</span>
                                <p><?= htmlspecialchars($student['cdistrict']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <span class="detail-label">Date of Birth:</span>
                                <p><?= htmlspecialchars($student['dob']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Non-Polytechnic Student View -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <span class="detail-label">Course Type:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['course_type']) ?></p>
                            </div>
                            <div class="mb-3">
                                <span class="detail-label">Course Level:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['courseLevel']) ?></p>
                            </div>
                            <div class="mb-3">
                                <span class="detail-label">Selected Course:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['course_list']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <span class="detail-label">Full Name:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['applicant_name']) ?></p>
                            </div>
                            <div class="mb-3">
                                <span class="detail-label">Employment Status:</span>
                                <p class="fs-5"><?= htmlspecialchars($student['employment_status']) ?></p>
                            </div>
                            <?php if (!empty($student['photo_path'])): ?>
                                <div class="mb-3">
                                    <span class="detail-label">Photo:</span>
                                    <div class="mt-2">
                                        <img src="<?= htmlspecialchars($student['photo_path']) ?>" 
                                             alt="Student Photo" 
                                             class="student-photo">
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <hr>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <span class="detail-label">Registration Fee:</span>
                            <p class="fs-5"><?= htmlspecialchars($table === 'polyregis' ? $student['RegistrationFee'] : $student['registration_fee']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <span class="detail-label">Transaction ID:</span>
                            <p class="fs-5"><?= htmlspecialchars($table === 'polyregis' ? $student['TransactionID'] : $student['transaction_id']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer text-end">
                <a href="edit_student.php?table=<?= $table ?>&id=<?= $id ?>" class="btn btn-warning me-2">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="studentList.php" class="btn btn-primary">
                    <i class="fas fa-list me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>