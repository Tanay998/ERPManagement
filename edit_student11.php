<?php
ob_start();
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'schoolfeesys');

// Get student ID
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM record WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    $_SESSION['error'] = "Student record not found";
    echo "<script>
        window.location.href='index.php?student_records.php'
    </script>";
    // header("Location: student_records.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    // Collect and sanitize data
    $data = [
        'admission_no' => $conn->real_escape_string($_POST['admission_no']),
        'first_name' => $conn->real_escape_string($_POST['first_name']),
        'middle_name' => $conn->real_escape_string($_POST['middle_name']),
        'last_name' => $conn->real_escape_string($_POST['last_name']),
        // Add all other fields similarly
        'course' => $conn->real_escape_string($_POST['course']),
        'semester' => $conn->real_escape_string($_POST['semester'])
    ];

    // Update query
    $sql = "UPDATE record SET 
            AdmissionNo1 = ?,
            `First Name` = ?,
            `Middle Name` = ?,
            `Last Name` = ?,
            Course = ?,
            Semester = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", 
        $data['admission_no'],
        $data['first_name'],
        $data['middle_name'],
        $data['last_name'],
        $data['course'],
        $data['semester'],
        $student_id
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Record updated successfully";
        // Proper redirect with output buffering
        ob_end_clean(); // Clean existing buffer
        echo "<script>
            window.location.href='index.php?page=view_student11.php&id = .$student_id'
        </script>";
        // header("Location: index.php?page=view_student11.php?id=" . $student_id);
        exit();
    } else {
        $_SESSION['error'] = "Error updating record: " . $conn->error;
        // Redirect back to edit page
        ob_end_clean();
        echo "<script>
            window.location.href='index.php?page=edit_student.php&id=.$student_id'
        </script>";
        // header("Location: edit_student.php?id=" . $student_id);
        exit();
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .section-title {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>Edit Student Record</h3>
                <a href="index.php?page=student_records.php" class="btn btn-light btn-sm">← Back to Records</a>
            </div>

            <div class="card-body">
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h4 class="section-title">Personal Information</h4>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Admission Number</label>
                                <input type="text" name="admission_no" class="form-control" 
                                       value="<?= htmlspecialchars($student['AdmissionNo1']) ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="<?= htmlspecialchars($student['First Name']) ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" 
                                       value="<?= htmlspecialchars($student['Middle Name']) ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" 
                                       value="<?= htmlspecialchars($student['Last Name']) ?>" required>
                            </div>
                            
                            <!-- Add other personal info fields -->
                        </div>
                    </div>

                    <!-- Course Information -->
                    <div class="form-section">
                        <h4 class="section-title">Academic Information</h4>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Course</label>
                                <select name="course" class="form-select" required>
                                    <option value="Diploma in Mechanical Engineering" <?= $student['Course'] === 'Diploma in Mechanical Engineering' ? 'selected' : '' ?>>Mechanical Engineering</option>
                                    <option value="Diploma in Electrical Engineering" <?= $student['Course'] === 'Diploma in Electrical Engineering' ? 'selected' : '' ?>>Electrical Engineering</option>
                                    <option value="Diploma in Computer Science Engineering" <?= $student['Course'] === 'Diploma in Computer Science Engineering' ? 'selected' : '' ?>>Computer Science Engineering</option>
                                    <!-- Add other course options -->
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label>Semester</label>
                                <select name="semester" class="form-select" required>
                                    <option value="Sem1" <?= $student['Semester'] === 'Sem1' ? 'selected' : '' ?>>Semester 1</option>
                                    <option value="Sem2" <?= $student['Semester'] === 'Sem2' ? 'selected' : '' ?>>Semester 2</option>
                                    <option value="Sem3" <?= $student['Semester'] === 'Sem3' ? 'selected' : '' ?>>Semester 3</option>
                                    <option value="Sem4" <?= $student['Semester'] === 'Sem4' ? 'selected' : '' ?>>Semester 4</option>
                                    <option value="Sem5" <?= $student['Semester'] === 'Sem5' ? 'selected' : '' ?>>Semester 5</option>
                                    <option value="Sem6" <?= $student['Semester'] === 'Sem6' ? 'selected' : '' ?>>Semester 6</option>
                                    <!-- Add other semester options -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Add other form sections (Contact, Address, etc.) -->

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Update Record</button>
                        <a href="student_records.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close();
ob_end_flush();
?>