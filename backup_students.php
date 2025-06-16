<?php
// backup_students.php
require_once 'config.php';
$page = 'backup_students.php';

// Handle delete action (if needed)
if (isset($_GET['delete'])) {
    if (!isset($_SESSION['user_role'])) {
        header("Location: login.php");
        exit();
    }
    
    $id = $_GET['delete'];
    $table = $_GET['table'] ?? '';
    
    if ($table === 'polyregis_backup') {
        $sql = "DELETE FROM polyregis_backup WHERE RollNo = ?";
    } elseif ($table === 'estcregis_backup') {
        $sql = "DELETE FROM estcregis_backup WHERE id = ?";
    } else {
        header("Location: index.php?page=$page&error=Invalid table");
        exit();
    }
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $message = "Record deleted successfully";
        } else {
            $message = "No record found to delete";
        }
    } catch (Exception $e) {
        $message = "Error deleting record: " . $e->getMessage();
    }
    
    header("Location: index.php?page=$page&message=" . urlencode($message));
    exit();
}

// Initialize filter variables
$selectedTable = $_POST['student_type'] ?? 'polytechnic';
$filters = [
    'admission_type' => $_POST['admission_type'] ?? '',
    'semester' => $_POST['semester'] ?? '',
    'branch' => $_POST['branch'] ?? '',
    'payment_status' => $_POST['payment_status'] ?? 'all',
    'from_date' => $_POST['from_date'] ?? '',
    'to_date' => $_POST['to_date'] ?? ''
];

// Get distinct values for filters
$distinctValues = [];
try {
    // Admission Types (from backup table)
    $sql = "SELECT DISTINCT TRIM(admissionType) AS admissionType FROM polyregis_backup";
    $result = $db->query($sql);
    $distinctValues['admission_types'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Semesters (from backup table)
    $sql = "SELECT DISTINCT TRIM(semester) AS semester FROM polyregis_backup ORDER BY semester";
    $result = $db->query($sql);
    $distinctValues['semesters'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Branches (from backup table)
    $sql = "SELECT DISTINCT TRIM(branch) AS branch FROM polyregis_backup ORDER BY branch";
    $result = $db->query($sql);
    $distinctValues['branches'] = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Error fetching distinct values: " . $e->getMessage();
}

// Get backup table data with filters
$tableData = [];
$summary = [
    'by_admission' => [],
    'by_semester' => [],
    'by_branch' => [],
    'total_fee' => 0,
    'record_count' => 0
];

try {
    if ($selectedTable === 'polytechnic') {
        $sql = "SELECT branch, applicantName, fatherName, state, cdistrict, dob, admissionType, course, RollNo, semester, RegistrationDate, RegistrationFee, TransactionID FROM polyregis_backup WHERE 1=1";
        // Apply filters same as studentList.php
        $params = [];
        $types = '';

        if (!empty($filters['admission_type'])) {
            $sql .= " AND admissionType = ?";
            $params[] = $filters['admission_type'];
            $types .= 's';
        }
        
        if (!empty($filters['semester'])) {
            $sql .= " AND semester = ?";
            $params[] = $filters['semester'];
            $types .= 's';
        }

        if (!empty($filters['branch'])) {
            $sql .= " AND branch = ?";
            $params[] = $filters['branch'];
            $types .= 's';
        }
        
        if ($filters['payment_status'] === 'paid') {
            $sql .= " AND RegistrationFee = 200";
        } elseif ($filters['payment_status'] === 'not_paid') {
            $sql .= " AND (RegistrationFee IS NULL OR RegistrationFee != 200)";
        }
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND RegistrationDate >= ?";
            $params[] = $filters['from_date'];
            $types .= 's';
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND RegistrationDate <= ?";
            $params[] = $filters['to_date'];
            $types .= 's';
        }
        
        // Execute query
        $stmt = $db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tableData[] = $row;
                // Update summary data
                $fee = (float)($row['RegistrationFee'] ?? 0);
                $summary['total_fee'] += $fee;
                $summary['record_count']++;
                
                $admissionType = trim($row['admissionType'] ?? 'Unknown');
                $admissionType = $admissionType === '' ? 'Unknown' : $admissionType;

                $semester = trim($row['semester'] ?? '');
                $semester = $semester === '' ? 'Unknown' : $semester;
                $semester = preg_replace('/^Sem\s*/i', '', $semester);

                $branch = preg_replace('/\s+/', ' ', trim($row['branch'] ?? ''));
                $branch = $branch === '' ? 'Unknown' : $branch;
                
                if (!isset($summary['by_admission'][$admissionType])) {
                    $summary['by_admission'][$admissionType] = 0;
                }
                $summary['by_admission'][$admissionType] += $fee;
                
                if (!isset($summary['by_semester'][$semester])) {
                    $summary['by_semester'][$semester] = 0;
                }
                $summary['by_semester'][$semester] += $fee;
                
                if (!isset($summary['by_branch'][$branch])) {
                    $summary['by_branch'][$branch] = 0;
                }
                $summary['by_branch'][$branch] += $fee;
            }
        }
    } elseif ($selectedTable === 'non-polytechnic') {
        $sql = "SELECT id, course_type, courseLevel, course_list, applicant_name, employment_status, photo_path, registration_fee, transaction_id FROM estcregis_backup";
        $result = $db->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tableData[] = $row;
            }
        }
    }
} catch (Exception $e) {
    $error = "Error fetching data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferred Students | ERP Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same styles as studentList.php */
        .table-container {
            overflow-x: auto;
            max-width: 100%;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .table-responsive {
            min-width: 1000px;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .photo-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-card {
            background-color: #e9ecef;
            border-left: 4px solid #0d6efd;
        }
        .summary-item {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4 mt-4">
        <div class="card shadow" style="width:100%;">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0">Transferred Students</h2>
                    <a href="index.php?page=studentList.php" class="btn btn-warning float-end">BACK TO ACTIVE</a>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="post" class="mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="student_type" class="col-form-label">Registration Type:</label>
                        </div>
                        <div class="col-auto">
                            <select name="student_type" id="student_type" class="form-select" onchange="this.form.submit()">
                                <option value="polytechnic" <?= $selectedTable === 'polytechnic' ? 'selected' : '' ?>>Polytechnic</option>
                                <option value="non-polytechnic" <?= $selectedTable === 'non-polytechnic' ? 'selected' : '' ?>>Non-Polytechnic</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- FILTER SECTION -->
                <div class="filter-section">
                    <h5>Filter Transferred Students</h5>
                    <form method="post">
                        <input type="hidden" name="student_type" value="<?= $selectedTable ?>">
                        
                        <div class="row g-3">
                            <?php if ($selectedTable === 'polytechnic'): ?>
                                <div class="card summary-card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Fee Summary (Filtered Results)</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6>By Admission Type:</h6>
                                                <?php foreach ($summary['by_admission'] as $type => $amount): ?>
                                                    <div class="summary-item">
                                                        <span class="fw-bold"><?= $type ?>:</span> 
                                                        ₹<?= number_format($amount, 2) ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <h6>By Semester:</h6>
                                                <?php foreach ($summary['by_semester'] as $sem => $amount): ?>
                                                    <div class="summary-item">
                                                        <span class="fw-bold">Sem <?= $sem ?>:</span> 
                                                        ₹<?= number_format($amount, 2) ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <h6>By Branch:</h6>
                                                <?php foreach ($summary['by_branch'] as $branch => $amount): ?>
                                                    <div class="summary-item">
                                                        <span class="fw-bold"><?= $branch ?>:</span> 
                                                        ₹<?= number_format($amount, 2) ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="mt-3 pt-2 border-top">
                                            <h5>Total Registration Fees: 
                                                <span class="text-primary">₹<?= number_format($summary['total_fee'], 2) ?></span>
                                                <span class="fs-6 text-muted">(<?= $summary['record_count'] ?> records)</span>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            <div class="col-md-3">
                                <label class="form-label">Admission Type</label>
                                <select name="admission_type" class="form-select">
                                    <option value="">All Types</option>
                                    <?php foreach ($distinctValues['admission_types'] as $type): ?>
                                        <option value="<?= $type['admissionType'] ?>" 
                                            <?= $filters['admission_type'] === $type['admissionType'] ? 'selected' : '' ?>>
                                            <?= $type['admissionType'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Semester</label>
                                <select name="semester" class="form-select">
                                    <option value="">All Semesters</option>
                                    <?php foreach ($distinctValues['semesters'] as $sem): ?>
                                        <option value="<?= $sem['semester'] ?>" 
                                            <?= $filters['semester'] === $sem['semester'] ? 'selected' : '' ?>>
                                            <?= $sem['semester'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Branch</label>
                                <select name="branch" class="form-select">
                                    <option value="">All Branches</option>
                                    <?php foreach ($distinctValues['branches'] as $br): ?>
                                        <option value="<?= $br['branch'] ?>" 
                                            <?= $filters['branch'] === $br['branch'] ? 'selected' : '' ?>>
                                            <?= $br['branch'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-md-2">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-select">
                                    <option value="all" <?= $filters['payment_status'] === 'all' ? 'selected' : '' ?>>All</option>
                                    <option value="paid" <?= $filters['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid (₹200)</option>
                                    <option value="not_paid" <?= $filters['payment_status'] === 'not_paid' ? 'selected' : '' ?>>Not Paid</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">From Date</label>
                                <input type="date" name="from_date" class="form-control" 
                                       value="<?= $filters['from_date'] ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">To Date</label>
                                <input type="date" name="to_date" class="form-control" 
                                       value="<?= $filters['to_date'] ?>">
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                                <a href="index.php?page=<?= $page ?>" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="btn-group">
                    <a href="export_excel_backup.php?type=<?= $selectedTable ?>" class="btn btn-primary">
                        <i class="fas fa-file-export"></i> Export Excel
                    </a>
                </div>

                <div class="table-container mt-4 p-2">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered" id="tableSelect">
                            <thead class="table-dark">
                                <tr>
                                    <?php if ($selectedTable === 'polytechnic'): ?>
                                        <th>Branch</th>
                                        <th>Applicant Name</th>
                                        <th>Father's Name</th>
                                        <th>Admission Type</th>
                                        <th>Semester</th>
                                        <th>Reg. Fee</th>
                                        <th>Actions</th>
                                    <?php else: ?>
                                        <th>Course Type</th>
                                        <th>Course Level</th>
                                        <th>Course</th>
                                        <th>Applicant Name</th>
                                        <th>Employment</th>
                                        <th>Photo</th>
                                        <th>Reg. Fee</th>
                                        <th>Transaction ID</th>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tableData)): ?>
                                    <?php foreach ($tableData as $row): ?>
                                        <tr>
                                            <?php if ($selectedTable === 'polytechnic'): ?>
                                                <td><?= htmlspecialchars($row['branch']) ?></td>
                                                <td><?= htmlspecialchars($row['applicantName']) ?></td>
                                                <td><?= htmlspecialchars($row['fatherName']) ?></td>
                                                <td><?= htmlspecialchars($row['admissionType']) ?></td>
                                                <td><?= htmlspecialchars($row['semester']) ?></td>
                                                <td><?= htmlspecialchars($row['RegistrationFee']) ?></td>
                                                <td class="action-buttons">
                                                    <a href="view_student.php?table=polyregis_backup&id=<?= urlencode($row['RollNo']) ?>" class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="studentList.php?delete=<?= $row['RollNo'] ?>&table=polyregis_backup" 
                                                       class="btn btn-sm btn-danger" 
                                                       title="Delete"
                                                       onclick="return confirm('Are you sure you want to permanently delete this record?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            <?php else: ?>
                                                <td><?= htmlspecialchars($row['course_type']) ?></td>
                                                <td><?= htmlspecialchars($row['courseLevel']) ?></td>
                                                <td><?= htmlspecialchars($row['course_list']) ?></td>
                                                <td><?= htmlspecialchars($row['applicant_name']) ?></td>
                                                <td><?= htmlspecialchars($row['employment_status']) ?></td>
                                                <td>
                                                    <?php if (!empty($row['photo_path'])): ?>
                                                        <img src="<?= htmlspecialchars($row['photo_path']) ?>" alt="Photo" class="photo-thumbnail">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['registration_fee']) ?></td>
                                                <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                                                <td class="action-buttons">
                                                    <a href="view_student.php?table=estcregis_backup&id=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="studentList.php?delete=<?= $row['id'] ?>&table=estcregis_backup" 
                                                       class="btn btn-sm btn-danger" 
                                                       title="Delete"
                                                       onclick="return confirm('Are you sure you want to permanently delete this record?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= $selectedTable === 'polytechnic' ? 7 : 9 ?>" class="text-center text-muted py-4">
                                            No transferred records found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card-footer text-muted">
                <div class="d-flex justify-content-between">
                    <div>
                        Total Transferred Records: <strong><?= count($tableData) ?></strong>
                    </div>
                    <div>
                        <small>ERP Management System &copy; <?= date('Y') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tableSelect').DataTable();
        });
    </script>
</body>
</html>