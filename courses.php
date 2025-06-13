<?php
// Database connection (same as enquiry.php)
$host = 'localhost';
$dbname = 'schoolfeesys';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission for adding/updating courses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Validation
    $requiredFields = ['section', 'type_of_course', 'programme_code', 'title', 'starting_date1', 'starting_date2', 'duration', 'fee','location'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
        }
    }

    if (!is_numeric($_POST['fee'])) {
        $errors[] = "Fee must be a numeric value.";
    }

    if (empty($errors)) {
        try {
            if (isset($_POST['add_course'])) {
                // Add new course
                $stmt = $pdo->prepare("INSERT INTO courses (section, type_of_course, programme_code, title, duration, fee, eligibility, location) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['section'],
                    $_POST['type_of_course'],
                    $_POST['programme_code'],
                    $_POST['title'],
                    $_POST['duration'],
                    $_POST['fee'],
                    $_POST['eligibility'],
                    $_POST['location']
                ]);
            } elseif (isset($_POST['update_course'])) {
                // Update course
                $stmt = $pdo->prepare("UPDATE courses SET 
                    section = ?,
                    type_of_course = ?,
                    programme_code = ?,
                    title = ?,
                    duration = ?,
                    fee = ?,
                    eligibility = ?,
                    location = ?
                    WHERE id = ?");
                $stmt->execute([
                    $_POST['section'],
                    $_POST['type_of_course'],
                    $_POST['programme_code'],
                    $_POST['title'],
                    $_POST['duration'],
                    $_POST['fee'],
                    $_POST['eligibility'],
                    $_POST['location'],
                    $_POST['id']
                ]);
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
}

// Handle delete operation
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: index.php?page=course.php");
    exit();
}

// Fetch all courses
$stmt = $pdo->query("SELECT * FROM courses");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get course data for editing
$editData = [];
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        /* Optional: Add custom styles here */
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <a href="index.php" class="btn btn-primary">Back</a>
        <h2 class="text-center mb-4">Course Management</h2>
        
        <!-- Course Form -->
        <form method="POST" class="mb-5">
            <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Section</label>
                    <input type="text" name="section" class="form-control" 
                           value="<?= $editData['section'] ?? '' ?>" required list="sectionList">
                    <datalist id="sectionList">
                        <option value="ELECTRONICS_SECTION">ELECTRONICS SECTION</option>
                        <option value="ELECTRICAL_&_SOLAR SECTION">ELECTRICAL & SOLAR SECTION</option>
                        <option value="COMPUTER_SECTION">COMPUTER SECTION</option>
                        <option value="PCB_SECTION">PCB_SECTION</option>
                        <option value="WORKSHOP">WORKSHOP</option>
                        <option value="MECHANICAL_SECTION">MECHANICAL SECTION</option>
                    </datalist>

                </div>
                <div class="col-md-4">
                    <label>Type of Course</label>
                    <select name="type_of_course" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="Skill Development Trainings for Employment" <?= ($editData['type_of_course'] ?? '') === 'Skill Development Trainings for Employment' ? 'selected' : '' ?>>Skill Development Trainings for Employment</option>
                        <option value="Skill Oriented Entrepreneur Development Trainings" <?= ($editData['type_of_course'] ?? '') === 'Skill Oriented Entrepreneur Development Trainings' ? 'selected' : '' ?>>Skill Oriented Entrepreneur Development Trainings</option>
                        <option value="Specialized Skill Upgradation Trainings" <?= ($editData['type_of_course'] ?? '') === 'Specialized Skill Upgradation Trainings' ? 'selected' : '' ?>>Specialized Skill Upgradation Trainings</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label>Programme Code</label>
                    <input type="text" name="programme_code" class="form-control" 
                           value="<?= $editData['programme_code'] ?? '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Title of the Programme</label>
                    <input type="text" name="title" class="form-control" 
                           value="<?= $editData['title'] ?? '' ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label>Duration (months)</label>
                    <input type="number" name="duration" class="form-control" 
                           value="<?= $editData['duration'] ?? '' ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Fee (₹)</label>
                    <input type="number" step="0.01" name="fee" class="form-control" 
                           value="<?= $editData['fee'] ?? '' ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label>Eligibility</label>
                    <textarea name="eligibility" class="form-control" rows="3"><?= $editData['eligibility'] ?? '' ?></textarea>
                </div>
                <div class="col-md-6">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" 
                           value="<?= $editData['location'] ?? '' ?>">
                </div>
            </div>

            <div class="mt-4">
                <?php if (isset($_GET['edit_id'])): ?>
                    <button type="submit" name="update_course" class="btn btn-warning">Update Course</button>
                <?php else: ?>
                    <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
                <?php endif; ?>
            </div>
        </form>

        <!-- Courses Table -->
        <table id="courseTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Programme Code</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Fee</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $index => $course): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= $course['programme_code'] ?></td>
                    <td><?= $course['title'] ?></td>
                    <td><?= $course['type_of_course'] ?></td>
                    <td><?= $course['duration'] ?> months</td>
                    <td>₹<?= number_format($course['fee'], 2) ?></td>
                    <td>
                        <a href="index.php?page=courses.php&edit_id=<?= $course['id'] ?>" 
                           class="btn btn-sm btn-info">Edit</a>
                        <a href="course.php?delete_id=<?= $course['id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

     <!-- Bootstrap JS and dependencies -->
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#courseTable').DataTable();
        });
    </script>
</body>
</html>