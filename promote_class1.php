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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Personal details
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
    $exams = $_POST['exam_name'];
    $marks = $_POST['marks'];
    $subjects = $_POST['subjects'];
    $schools = $_POST['school'];
    $universities = $_POST['university'];

    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Insert personal details
        $stmt = $conn->prepare("INSERT INTO applicants (
            course_name, admission_date, father_name, dob, 
            aadhaar_no, address, mobile, email, category
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssssssss", 
            $course_name, $admission_date, $father_name, $dob,
            $aadhaar, $address, $mobile, $email, $category
        );
        
        $stmt->execute();
        $applicant_id = $stmt->insert_id;
        $stmt->close();
        
        // Insert educational details
        $stmt_edu = $conn->prepare("INSERT INTO educational_details (
            applicant_id, exam_name, marks_obtained, subjects, 
            school_college, university_body
        ) VALUES (?, ?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < count($exams); $i++) {
            $stmt_edu->bind_param("isssss", 
                $applicant_id, $exams[$i], $marks[$i], $subjects[$i],
                $schools[$i], $universities[$i]
            );
            $stmt_edu->execute();
        }
        
        $stmt_edu->close();
        $conn->commit();
        $success_message = "Admission submitted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Admission Form</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?= $success_message ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?= $error_message ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <!-- Personal Details Section -->
                            <div class="form-section">
                                <h3 class="section-title">Personal Details</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Course Name</label>
                                        <input type="text" class="form-control" name="course_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Admission Date</label>
                                        <input type="date" class="form-control" name="admission_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Father's Name</label>
                                        <input type="text" class="form-control" name="father_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" name="dob" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Aadhaar Card No</label>
                                        <input type="text" class="form-control" name="aadhaar" pattern="\d{12}" title="12-digit Aadhaar number" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Category</label>
                                        <select class="form-select" name="category" required>
                                            <option value="">Select</option>
                                            <option value="General">General</option>
                                            <option value="OBC">OBC</option>
                                            <option value="SC">SC</option>
                                            <option value="ST">ST</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Postal Address</label>
                                        <textarea class="form-control" name="address" rows="3" required></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mobile No</label>
                                        <input type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" title="10-digit mobile number" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required>
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
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><input type="text" class="form-control" name="exam_name[]" required></td>
                                                <td><input type="number" step="0.01" class="form-control" name="marks[]" required></td>
                                                <td><input type="text" class="form-control" name="subjects[]" required></td>
                                                <td><input type="text" class="form-control" name="school[]" required></td>
                                                <td><input type="text" class="form-control" name="university[]" required></td>
                                                <td><button type="button" class="btn btn-danger btn-sm" disabled>Remove</button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-primary btn-add-row" id="add-row">
                                        + Add Row
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
            
            // Add event listener to new remove button
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
    </script>
</body>
</html>