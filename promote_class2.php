<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "schoolfeesys";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$success_message = $error_message = "";
$edit_mode = false;
$applicant_data = [];
$educational_data = [];

// Handle transfer from studentList.php
if (isset($_GET['transfer_id'])) {
    $transfer_id = $_GET['transfer_id'];
    
    try {
        // Fetch student data from estcregis
        $stmt = $conn->prepare("SELECT * FROM estcregis WHERE id = ?");
        $stmt->bind_param("s", $transfer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Student not found in estcregis table");
        }
        
        $student_data = $result->fetch_assoc();
        $stmt->close();
        
        // Set the data for the form
        $edit_mode = true;
        $applicant_data = [
            'id' => $student_data['id'],
            'applicant_name' => $student_data['applicant_name'],
            'course_name' => $student_data['course_list'],
            'admission_date' => date('Y-m-d'), // current date
            'father_name' => '', // not available in estcregis
            'dob' => '', // not available in estcregis
            'aadhaar_no' => '', // not available in estcregis
            'address' => '', // not available in estcregis
            'mobile' => '', // not available in estcregis
            'email' => '', // not available in estcregis
            'category' => '' // not available in estcregis
        ];
        
        // You might want to pre-fill some educational data based on course_list
        $educational_data = [
            [
                'exam_name' => 'Previous Qualification',
                'marks_obtained' => '',
                'subjects' => $student_data['course_list'],
                'school_college' => '',
                'university_body' => ''
            ]
        ];
        
    } catch (Exception $e) {
        $error_message = "Transfer failed: " . $e->getMessage();
    }
}

// Handle educational detail deletion
if (isset($_GET['delete_edu_id'])) {
    $delete_id = $_GET['delete_edu_id'];
    $id = $_GET['id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM educational_details WHERE educational_detail_id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $success_message = "Educational record deleted successfully!";
        } else {
            $error_message = "Error deleting educational record";
        }
        $stmt->close();
        
        // Redirect to edit mode
        header("Location: promote_class2.php?edit_id=$id");
        exit();
    } catch (Exception $e) {
        $error_message = "Error deleting record: " . $e->getMessage();
    }
}

// Handle record deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $conn->begin_transaction();
        
        // Delete educational details first
        $stmt = $conn->prepare("DELETE FROM educational_details WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete applicant
        $stmt = $conn->prepare("DELETE FROM applicants WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        $success_message = "Record deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error deleting record: " . $e->getMessage();
    }
}

// Handle edit mode initialization
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_mode = true;
    
    // Fetch applicant data
    $stmt = $conn->prepare("SELECT * FROM applicants WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $applicant_data = $result->fetch_assoc();
    $stmt->close();
    
    // Fetch educational data
    $stmt = $conn->prepare("SELECT * FROM educational_details WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $educational_data[] = $row;
    }
    $stmt->close();
}

// Process form submission (both create and update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Personal details
    $applicant_name = $_POST['applicant_name'];
    $course_name = $_POST['course_name'];
    $admission_date = $_POST['admission_date'];
    $father_name = $_POST['father_name'];
    $dob = $_POST['dob'];
    $aadhaar = $_POST['aadhaar'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $category = $_POST['category'];
    
    // Educational details
    $exams = $_POST['exam_name'] ?? [];
    $marks = $_POST['marks'] ?? [];
    $subjects = $_POST['subjects'] ?? [];
    $schools = $_POST['school'] ?? [];
    $universities = $_POST['university'] ?? [];

    try {
        // Start transaction
        $conn->begin_transaction();
        
        if (isset($_POST['id']) && $edit_mode) {
            // UPDATE EXISTING RECORD
            $id = $_POST['id'];
            
            // Update applicant
            $stmt = $conn->prepare("UPDATE applicants SET 
                applicant_name = ?, 
                course_name = ?, 
                admission_date = ?, 
                father_name = ?, 
                dob = ?, 
                aadhaar_no = ?, 
                address = ?, 
                mobile = ?, 
                email = ?, 
                category = ? 
                WHERE id = ?");
            
            $stmt->bind_param("ssssssssssi", 
                $applicant_name, $course_name, $admission_date, $father_name, $dob,
                $aadhaar, $address, $mobile, $email, $category, $id
            );
            $stmt->execute();
            $stmt->close();
            
            // Delete existing educational records
            $stmt = $conn->prepare("DELETE FROM educational_details WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        } else {
            // INSERT NEW RECORD
            $stmt = $conn->prepare("INSERT INTO applicants (
                applicant_name, course_name, admission_date, father_name, dob, 
                aadhaar_no, address, mobile, email, category
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssssssssss", 
                $applicant_name, $course_name, $admission_date, $father_name, $dob,
                $aadhaar, $address, $mobile, $email, $category
            );
            $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();
        }
        
        // Insert educational details
        $stmt_edu = $conn->prepare("INSERT INTO educational_details (
            id, exam_name, marks_obtained, subjects, 
            school_college, university_body
        ) VALUES (?, ?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < count($exams); $i++) {
            $stmt_edu->bind_param("isssss", 
                $id, $exams[$i], $marks[$i], $subjects[$i],
                $schools[$i], $universities[$i]
            );
            $stmt_edu->execute();
        }
        
        $stmt_edu->close();
        $conn->commit();
        $success_message = "Admission " . ($edit_mode ? "updated" : "submitted") . " successfully!";
        $edit_mode = false; // Exit edit mode after update
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch all records for viewing
$records = [];
$sql = "SELECT * FROM applicants ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Non-Polytechnic Admission Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .form-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .section-title {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #0d6efd;
        }
        .table-section {
            overflow-x: auto;
        }
        .btn-add-row {
            margin: 15px 0;
        }
        .record-card {
            transition: transform 0.2s;
        }
        .record-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .action-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .edu-row {
            position: relative;
        }
        .edu-actions {
            position: absolute;
            top: 5px;
            right: 5px;
            display: none;
        }
        .edu-row:hover .edu-actions {
            display: block;
        }
        .transfer-banner {
            background-color: #d4edda;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h2 class="text-center mb-0"><?= $edit_mode ? 'Edit Non-Polytechnic Admission' : 'Non-Polytechnic Admission Form' ?></h2>
                        <a href="index.php?page=promote_class2.php&view=records" class="btn btn-light">
                            <i class="bi bi-list-ul"></i> View Records
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_GET['transfer_id'])): ?>
                            <div class="transfer-banner">
                                <h4><i class="bi bi-info-circle"></i> Student Transferred from Registration</h4>
                                <p>Please complete the admission process by filling in the missing details below.</p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?= $success_message ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?= $error_message ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['view']) && $_GET['view'] == 'records'): ?>
                            <!-- Records View Section -->
                            <div class="mb-4">
                                <h3 class="section-title">Non-Polytechnic Admission Records</h3>
                                <a href="index.php?page=promote_class2.php" class="btn btn-success mb-3">
                                    <i class="bi bi-arrow-left"></i> Back to Form
                                </a>
                                
                                <div class="row">
                                    <?php if (count($records) > 0): ?>
                                        <?php foreach ($records as $record): ?>
                                            <div class="col-md-6 mb-4">
                                                <div class="card record-card h-100">
                                                    <div class="card-header bg-light d-flex justify-content-between">
                                                        <h5 class="mb-0"><?= htmlspecialchars($record['applicant_name']) ?></h5>
                                                        <span class="badge bg-success"><?= $record['course_name'] ?></span>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="mb-1"><strong>Admission Date:</strong> <?= $record['admission_date'] ?></p>
                                                        <p class="mb-1"><strong>Father's Name:</strong> <?= htmlspecialchars($record['father_name']) ?></p>
                                                        <p class="mb-1"><strong>DOB:</strong> <?= $record['dob'] ?></p>
                                                        <p class="mb-1"><strong>Mobile:</strong> <?= htmlspecialchars($record['mobile']) ?></p>
                                                    </div>
                                                    <div class="card-footer bg-white action-buttons">
                                                        <a href="index.php?page=promote_class2.php&edit_id=<?= $record['id'] ?>" class="btn btn-sm btn-warning">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a>
                                                        <a href="?delete_id=<?= $record['id'] ?>&view=records" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this record?');">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-info view-details" 
                                                                data-id="<?= $record['id'] ?>">
                                                            <i class="bi bi-eye"></i> View Details
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="alert alert-info">No admission records found.</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Admission Form Section -->
                            <form method="POST" action="">
                                <?php if ($edit_mode): ?>
                                    <input type="hidden" name="id" value="<?= $applicant_data['id'] ?>">
                                <?php endif; ?>
                                
                                <!-- Personal Details Section -->
                                <div class="form-section">
                                    <h3 class="section-title">Personal Details</h3>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Applicant Name</label>
                                            <input type="text" class="form-control" name="applicant_name" 
                                                value="<?= $edit_mode ? htmlspecialchars($applicant_data['applicant_name']) : '' ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Course Name</label>
                                            <input type="text" class="form-control" name="course_name" 
                                                value="<?= $edit_mode ? htmlspecialchars($applicant_data['course_name']) : '' ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Admission Date</label>
                                            <input type="date" class="form-control" name="admission_date" 
                                                value="<?= $edit_mode ? $applicant_data['admission_date'] : date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Father's Name</label>
                                            <input type="text" class="form-control" name="father_name" 
                                                value="<?= $edit_mode ? htmlspecialchars($applicant_data['father_name']) : '' ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control" name="dob" 
                                                value="<?= $edit_mode ? $applicant_data['dob'] : '' ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Aadhaar Card No</label>
                                            <input type="text" class="form-control" name="aadhaar" 
                                                value="<?= $edit_mode ? htmlspecialchars($applicant_data['aadhaar_no']) : '' ?>" 
                                                pattern="\d{12}" title="12-digit Aadhaar number" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Category</label>
                                            <select class="form-select" name="category" required>
                                                <option value="">Select</option>
                                                <?php
                                                $categories = ['General', 'OBC', 'SC', 'ST', 'Other'];
                                                foreach ($categories as $cat) {
                                                    $selected = ($edit_mode && $applicant_data['category'] == $cat) ? 'selected' : '';
                                                    echo "<option value=\"$cat\" $selected>$cat</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Postal Address</label>
                                            <textarea class="form-control" name="address" rows="3" required><?= $edit_mode ? htmlspecialchars($applicant_data['address']) : '' ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Mobile No</label>
                                            <input type="tel" class="form-control" name="mobile" 
                                                value="<?= $edit_mode ? htmlspecialchars($applicant_data['mobile']) : '' ?>" 
                                                pattern="[0-9]{10}" title="10-digit mobile number" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" 
                                                value="<?= $edit_mode ? htmlspecialchars($applicant_data['email']) : '' ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Educational Details Section -->
                                <div class="form-section">
                                    <h3 class="section-title">Educational Qualifications</h3>
                                    <div class="table-section">
                                        <table class="table table-bordered" id="edu-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Exam Passed</th>
                                                    <th>% Marks Obtained</th>
                                                    <th>Subjects</th>
                                                    <th>School/College</th>
                                                    <th>University/Board</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($edit_mode && count($educational_data) > 0): ?>
                                                    <?php foreach ($educational_data as $edu): ?>
                                                        <tr class="edu-row">
                                                            <td><input type="text" class="form-control" name="exam_name[]" value="<?= htmlspecialchars($edu['exam_name']) ?>" required></td>
                                                            <td><input type="number" step="0.01" class="form-control" name="marks[]" value="<?= htmlspecialchars($edu['marks_obtained']) ?>" required></td>
                                                            <td><input type="text" class="form-control" name="subjects[]" value="<?= htmlspecialchars($edu['subjects']) ?>" required></td>
                                                            <td><input type="text" class="form-control" name="school[]" value="<?= htmlspecialchars($edu['school_college']) ?>" required></td>
                                                            <td><input type="text" class="form-control" name="university[]" value="<?= htmlspecialchars($edu['university_body']) ?>" required></td>
                                                            <td>
                                                                <div class="edu-actions">
                                                                    <a href="?delete_edu_id=<?= $edu['educational_detail_id'] ?>&id=<?= $applicant_data['id'] ?>" 
                                                                       class="btn btn-danger btn-sm"
                                                                       onclick="return confirm('Are you sure you want to delete this educational record?');">
                                                                        <i class="bi bi-trash"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="exam_name[]" required></td>
                                                        <td><input type="number" step="0.01" class="form-control" name="marks[]" required></td>
                                                        <td><input type="text" class="form-control" name="subjects[]" required></td>
                                                        <td><input type="text" class="form-control" name="school[]" required></td>
                                                        <td><input type="text" class="form-control" name="university[]" required></td>
                                                        <td>
                                                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-success btn-add-row" id="add-row">
                                            + Add Row
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <?= $edit_mode ? 'Update Admission' : 'Submit Admission' ?>
                                    </button>
                                    <?php if ($edit_mode): ?>
                                        <a href="index.php?page=promote_class2.php" class="btn btn-secondary mt-2">Cancel Edit</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Applicant Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Applicant Name:</strong> <span id="detail-name"></span></p>
                            <p><strong>Course:</strong> <span id="detail-course"></span></p>
                            <p><strong>Admission Date:</strong> <span id="detail-admission-date"></span></p>
                            <p><strong>Father's Name:</strong> <span id="detail-father"></span></p>
                            <p><strong>Date of Birth:</strong> <span id="detail-dob"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Aadhaar No:</strong> <span id="detail-aadhaar"></span></p>
                            <p><strong>Address:</strong> <span id="detail-address"></span></p>
                            <p><strong>Mobile:</strong> <span id="detail-mobile"></span></p>
                            <p><strong>Email:</strong> <span id="detail-email"></span></p>
                            <p><strong>Category:</strong> <span id="detail-category"></span></p>
                        </div>
                    </div>
                    
                    <h5 class="mt-4">Educational Qualifications</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Marks</th>
                                <th>Subjects</th>
                                <th>School/College</th>
                                <th>University/Board</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="education-details">
                            <!-- Filled by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add/Remove educational rows
        document.getElementById('add-row').addEventListener('click', function() {
            const table = document.getElementById('edu-table').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();
            
            newRow.innerHTML = `
                <td><input type="text" class="form-control" name="exam_name[]" required></td>
                <td><input type="number" step="0.01" class="form-control" name="marks[]" required></td>
                <td><input type="text" class="form-control" name="subjects[]" required></td>
                <td><input type="text" class="form-control" name="school[]" required></td>
                <td><input type="text" class="form-control" name="university[]" required></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
            `;
            
            newRow.querySelector('.remove-row').addEventListener('click', function() {
                table.deleteRow(newRow.rowIndex - 1);
            });
        });

        // Add event listeners to existing remove buttons
        document.querySelectorAll('.remove-row').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('tr').remove();
            });
        });

        // View details functionality
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                fetch(`get_applicant_details.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        // Personal details
                        document.getElementById('detail-name').textContent = data.applicant.applicant_name;
                        document.getElementById('detail-course').textContent = data.applicant.course_name;
                        document.getElementById('detail-admission-date').textContent = data.applicant.admission_date;
                        document.getElementById('detail-father').textContent = data.applicant.father_name;
                        document.getElementById('detail-dob').textContent = data.applicant.dob;
                        document.getElementById('detail-aadhaar').textContent = data.applicant.aadhaar_no;
                        document.getElementById('detail-address').textContent = data.applicant.address;
                        document.getElementById('detail-mobile').textContent = data.applicant.mobile;
                        document.getElementById('detail-email').textContent = data.applicant.email;
                        document.getElementById('detail-category').textContent = data.applicant.category;
                        
                        // Educational details
                        const eduBody = document.getElementById('education-details');
                        eduBody.innerHTML = '';
                        
                        data.educational.forEach(edu => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${edu.exam_name}</td>
                                <td>${edu.marks_obtained}</td>
                                <td>${edu.subjects}</td>
                                <td>${edu.school_college}</td>
                                <td>${edu.university_body}</td>
                                <td>
                                    <a href="?delete_edu_id=${edu.educational_detail_id}&id=${data.applicant.id}" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this educational record?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            `;
                            eduBody.appendChild(row);
                        });
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                        modal.show();
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>