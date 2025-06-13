<?php  // Start output buffering ?>

<?php
require 'vendor/autoload.php';

// Database connection
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

// Handle Excel Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_excel'])) {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        
        $file = $_FILES['excel_file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        // Remove header row
        array_shift($rows);
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($rows as $row) {
            // Skip empty rows
            if (empty(array_filter($row))) continue;
            
            // Prepare data (adjust indexes based on your Excel structure)
            $data = [
                'session' => $row[1] ?? '',
                'firstName' => $row[2] ?? '',
                'middleName' => $row[3] ?? '',
                'lastName' => $row[4] ?? '',
                'fatherName' => $row[5] ?? '',
                'qualification' => $row[6] ?? '',
                'state' => $row[7] ?? '',
                'state_name' => $row[8] ?? '',
                'district' => $row[9] ?? '',
                'city' => $row[10] ?? '',
                'mobile' => $row[11] ?? '',
                'email' => $row[12] ?? '',
                'course' => $row[13] ?? ''
            ];
            
            // Basic validation
            if (empty($data['firstName']) || empty($data['lastName']) || 
                empty($data['mobile']) || !preg_match('/^\d{10}$/', $data['mobile']) ||
                empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ||
                empty($data['course'])) {
                $skipped++;
                continue;
            }
            
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO enquiry (session, firstName, middleName, lastName, fatherName, 
                                    qualification, state, state_name, district, city, mobile, email, course) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(array_values($data));
            $imported++;
        }
        
        echo "<script>
            alert('Import completed!\\nImported: $imported records\\nSkipped: $skipped records');
            window.location.href='index.php?page=enquiry.php';
        </script>";
    } else {
        echo "<script>alert('Error uploading file!');</script>";
    }
}


// Handle Excel Export
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
     // Clean output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    // Set headers for Excel download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="enquiries_' . date('Ymd') . '.xlsx"');
    
    // Create Excel Writer
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Add headers
    $headers = ['ID', 'Session', 'First Name', 'Middle Name', 'Last Name', 'Father Name', 
                'Qualification', 'State', 'District', 'City', 'Mobile', 'Email', 'Course'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Fetch data
    $stmt = $pdo->query("SELECT `id`, `session`, `firstName`, `middleName`, `lastName`, `fatherName`, `qualification`, `state_name`, `district`, `city`, `mobile`, `email`, `course` FROM `enquiry`");
    $enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add data rows
    $row = 2;
    foreach ($enquiries as $enquiry) {
        $sheet->fromArray(array_values($enquiry), null, "A{$row}");
        $row++;
    }
    
    // Output Excel file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}




// Calculate the current academic year dynamically (e.g., 2024-2025)
$currentYear = date('Y');
$currentMonth = date('n'); // Numeric month (1-12)

// If current month is April (4) or later, default to "YYYY-(YYYY+1)"
// Otherwise, default to "(YYYY-1)-YYYY"
if ($currentMonth >= 4) { // April or later
    $defaultSession = $currentYear . '-' . ($currentYear + 1);
} else { // Before July
    $defaultSession = ($currentYear - 1) . '-' . $currentYear;
}

// Example outputs:
// - If today is March 2024 → "2023-2024"
// - If today is August 2024 → "2024-2025"

// Fetch sessions from the session table
$stmtSessions = $pdo->query("SELECT Session FROM session");
$sessions = $stmtSessions->fetchAll(PDO::FETCH_COLUMN, 0);

// Calculate the current academic year
$currentYear = date('Y');
$currentMonth = date('n');
$defaultSession = ($currentMonth >= 4 ? "$currentYear-" . ($currentYear + 1) : ($currentYear - 1) . "-$currentYear");

// Insert the default session if it doesn't exist
if (!in_array($defaultSession, $sessions)) {
    $insertStmt = $pdo->prepare("INSERT INTO session (Session) VALUES (?)");
    $insertStmt->execute([$defaultSession]);
    $sessions[] = $defaultSession; // Add to the current list
}

// Fetch states from the database
$stmt = $pdo->query("SELECT id, name FROM states ORDER BY name ASC");
$states = $stmt->fetchAll(PDO::FETCH_ASSOC);


//Fetch courses from the database
$stmt1 = $pdo->query("SELECT * FROM courses");
$courses = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for adding or updating enquiry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Add session validation
    if (empty($_POST['session'])) {
        $errors[] = "Session selection is required.";
    }

    // Validate inputs
    if (empty($_POST['firstName'])) {
        $errors[] = "First Name is required.";
    }
    if (empty($_POST['lastName'])) {
        $errors[] = "Last Name is required.";
    }
    if (empty($_POST['mobile']) || !preg_match('/^\d{10}$/', $_POST['mobile'])) {
        $errors[] = "Mobile number must be 10 digits.";
    }
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    if (empty($_POST['course'])) {
        $errors[] = "Course is required.";
    }

    // If no errors, proceed with database operations
    if (empty($errors)) {

        if (isset($_POST['add_enquiry'])) {
            // Add new enquiry
            $stmt = $pdo->prepare("INSERT INTO enquiry (session,firstName, middleName, lastName, fatherName, qualification, state, state_name,district, city, mobile, email, course) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
            $stmt->execute([
                $_POST["session"],
                $_POST['firstName'],
                $_POST['middleName'],
                $_POST['lastName'],
                $_POST['fatherName'],
                $_POST['qualification'],
                $_POST['state'],
                $_POST['state_name'], 
                $_POST['district'],
                $_POST['city'],
                $_POST['mobile'],
                $_POST['email'],
                $_POST['course']
            ]);
        } elseif (isset($_POST['update_enquiry'])) {
            // Update existing enquiry
            $stmt = $pdo->prepare("UPDATE enquiry SET session = ?,firstName = ?, middleName = ?, lastName = ?, fatherName = ?, qualification = ?, state = ?, state_name = ?, district = ?, city = ?, mobile = ?, email = ?, course = ? WHERE id = ?");
            $stmt->execute([
                $_POST["session"],
                $_POST['firstName'],
                $_POST['middleName'],
                $_POST['lastName'],
                $_POST['fatherName'],
                $_POST['qualification'],
                $_POST['state'],
                $_POST['state_name'],
                $_POST['district'],
                $_POST['city'],
                $_POST['mobile'],
                $_POST['email'],
                $_POST['course'],
                $_POST['id']
            ]);
           
        }
        echo "<script>
            window.location.href='index.php?page=enquiry.php'
        </script>";
    } else {
        // Display validation errors
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
}

// Handle delete operation
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM enquiry WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    
    // Instead of header redirect, use JavaScript to reload
    echo '<script>window.location.href = "index.php?page=enquiry.php";</script>';
    exit();
}

// Fetch all enquiries
$stmt = $pdo->query("SELECT * FROM enquiry");
$enquiry = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If editing, fetch the current enquiry
$enquiryRow = [];
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM enquiry WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $enquiryRow = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiry Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container mt-5">
        <a href="index.php" class="btn btn-primary">Back</a>
        <h2 class="text-center mb-4">Enquiry Management</h2>
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload"></i> Import Excel
            </button>
            <a href="enquiry.php?export=excel" class="btn btn-primary">
                <i class="bi bi-download"></i> Export Excel
            </a>
        </div>
        <!-- Import Excel Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Enquiries from Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Select Excel File</label>
                        <input class="form-control" type="file" id="excel_file" name="excel_file" accept=".xlsx, .xls, .csv" required>
                        <small class="form-text text-muted">
                            Supported formats: .xlsx, .xls, .csv. 
                            <a href="#" id="downloadTemplate">Download template</a>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="import_excel" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Add/Edit Enquiry Form -->
        <form method="POST" class="mb-5" onsubmit="return validateForm()">
            <input type="hidden" name="id" value="<?= $enquiryRow['id'] ?? '' ?>">
            <div class="row">
                <div class="col-md-6">
                    <label>Session</label>
                    <select name="session" class="form-control" required>
    <option value="">Select Session</option>
    <?php foreach ($sessions as $session): ?>
        <option value="<?= htmlspecialchars($session) ?>"
            <?php 
            // Select the default session for new entries, or retain the existing value during edits
            if ((!isset($_GET['edit_id']) && $session === $defaultSession) || 
                (!empty($enquiryRow) && $enquiryRow['session'] === $session)) {
                echo 'selected';
            }
            ?>
        >
            <?= htmlspecialchars($session) ?>
        </option>
    <?php endforeach; ?>
</select>
                </div>
                <div class="col-md-4">
                    <label>First Name</label>
                    <input type="text" name="firstName" class="form-control" value="<?= $enquiryRow['firstName'] ?? '' ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Middle Name</label>
                    <input type="text" name="middleName" class="form-control" value="<?= $enquiryRow['middleName'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label>Last Name</label>
                    <input type="text" name="lastName" class="form-control" value="<?= $enquiryRow['lastName'] ?? '' ?>" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Father's Name</label>
                    <input type="text" name="fatherName" class="form-control" value="<?= $enquiryRow['fatherName'] ?? '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Qualification</label>
                    <input type="text" name="qualification" class="form-control" value="<?= $enquiryRow['qualification'] ?? '' ?>" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <label>State</label>
                    <select name="state" id="state" class="form-control" onchange="fetchDistricts1(this.value)" required>
                        <option value="">Select State</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?= htmlspecialchars($state['id']) ?>" 
                                data-name="<?= htmlspecialchars($state['name']) ?>"
                                <?= (!empty($enquiryRow) && $enquiryRow['state'] == $state['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($state['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="state_name" id="state_name" value="<?= $enquiryRow['state_name'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label>District</label>
                    <select name="district" id="district" class="form-control" required>
                        <option value="">Select District</option>
                        <?php if (!empty($enquiryRow)): ?>
                            <option value="<?= $enquiryRow['district'] ?>" selected><?= $enquiryRow['district'] ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>City</label>
                    <input type="text" name="city" class="form-control" value="<?= $enquiryRow['city'] ?? '' ?>" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Contact No</label>
                    <input type="text" name="mobile" class="form-control" value="<?= $enquiryRow['mobile'] ?? '' ?>" required pattern="\d{10}" title="Mobile number must be 10 digits.">
                </div>
                <div class="col-md-6">
                    <label>Email ID</label>
                    <input type="email" name="email" class="form-control" value="<?= $enquiryRow['email'] ?? '' ?>" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <label>Course Interested</label>
                    <input type="text" name="course" class="form-control" value="<?= $enquiryRow['course'] ?? '' ?>" list="courses" required>
                    <datalist id="courses">
                    <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['title'] ?>" 
                                <?= (!empty($enquiryRow) && $enquiryRow['course'] == $course['id']) ? 'selected' : '' ?>>
                                <?= $course['title'] ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>
            <div class="mt-3">
                <?php if (isset($_GET['edit_id'])): ?>
                    <button type="submit" name="update_enquiry" class="btn btn-warning">Update Enquiry</button>
                <?php else: ?>
                    <button type="submit" name="add_enquiry" class="btn btn-primary">Add Enquiry</button>
                <?php endif; ?>
            </div>
        </form>

        <!-- Display enquiries in a Table -->
        <table id="enquiryTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Father Name</th>
                    <th>Contact No</th>
                    <th>Qualification</th>
                    <th>Course Interested</th>
                    <th width="50%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enquiry as $index => $enquiries): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $enquiries['firstName']. ' '.$enquiries['middleName'].' '.$enquiries['lastName'] ?></td>
                        <td><?= $enquiries['fatherName'] ?></td>
                        <td><?= $enquiries['mobile'] ?></td>
                        <td><?= $enquiries['qualification'] ?></td>
                        <td><?= $enquiries['course'] ?></td>
                        <td>
                            <a href="index.php?page=enquiry.php&edit_id=<?= $enquiries['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                            <a href="index.php?page=enquiry.php&delete_id=<?= $enquiries['id'] ?>" 
                                class="btn btn-sm btn-danger" 
                                onclick="return confirm('Are you sure?')">Delete</a>
                            <button onclick="handleTransfer(<?= $enquiries['id'] ?>)" 
                                class="btn btn-sm btn-success">Transfer</button>
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
            $('#enquiryTable').DataTable();
        });

        // Load districts when state changes
        $('#state').change(function() {
            var stateId = $(this).val();
            var stateName = $(this).find('option:selected').data('name');
            $('#state_name').val(stateName);
            
            if (stateId) {
                $.ajax({
                    url: 'get_districts.php',
                    type: 'POST',
                    data: { state_id: stateId },
                    dataType: 'json',
                    success: function(data) {
                        $('#district').empty().append('<option value="">Select District</option>');
                        $.each(data, function(key, value) {
                            $('#district').append('<option value="' + value.name + '">' + value.name + '</option>');
                        });
                        <?php if (!empty($enquiryRow)): ?>
                            $('#district').val('<?= $enquiryRow['district'] ?>');
                        <?php endif; ?>
                    }
                });
            } else {
                $('#district').empty().append('<option value="">Select District</option>');
            }
        });

        // Trigger change if editing
        <?php if (!empty($enquiryRow)): ?>
            $('#state').trigger('change');
        <?php endif; ?>
    </script>
    <script>
        function handleTransfer(enquiryId) {
            const transferType = prompt("Select transfer type:\n1. Enter 'poly' for Polytechnic\n2. Enter 'non-poly' for Non-Poly");
            
            if (!transferType) return;  // User cancelled
            
            const normalized = transferType.toLowerCase();
            if (normalized === 'poly') {
                window.location.href = `index.php?page=new_admission.php&enquiry_id=${enquiryId}`;
            } else if (normalized === 'non-poly') {
                window.location.href = `index.php?page=promote_class.php&enquiry_id=${enquiryId}`;
            } else {
                alert('Invalid transfer type! Please enter "poly" or "non-poly"');
            }
        }
    </script>

    <script>
        function fetchDistricts1(stateValue) {
            console.log("State selected:", stateId);
            if (stateValue) {
                var parts = stateValue.split('|');
                var stateId = parts[0]; 
                console.log("State selected:", stateId);
                // Store the state name in the hidden field
                $('#cstate1').val(parts[1]);
                $.ajax({
                    url: 'fetch_districts1.php', // PHP script to fetch districts
                    type: 'GET',
                    data: { state_id: stateId },
                    success: function(response) {
                        console.log("Raw response:", response); // Add this line
                        var districts = JSON.parse(response);
                        var districtDropdown = $('#cdistrict');
                        districtDropdown.empty(); // Clear existing options
                        districtDropdown.append('<option value="">Select District</option>'); // Add default option

                        // Populate the district dropdown
                        districts.forEach(function(district) {
                            districtDropdown.append('<option value="' + district.id + '">' + district.name + '</option>');
                        });
                        
                 $('#state')       // Add change event handler to update hidden field
                        districtDropdown.change(function() {
                            var districtName = $(this).find('option:selected').text();
                            $('#ccdistrict').val(districtName);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching districts: " + error);
                    }
                });
            } else {
                $('#cdistrict').empty().append('<option value="">Select District</option>'); // Reset if no state is selected
                $('#ccdistrict').val(''); // Clear hidden field when no state is selected
            }
        }
    </script>
    <script>
document.getElementById('downloadTemplate').addEventListener('click', function(e) {
    e.preventDefault();
    // Create a simple template with headers
    const headers = ['ID (Leave Empty)', 'Session', 'First Name', 'Middle Name', 'Last Name', 
                    'Father Name', 'Qualification', 'State', 'State Name', 'District', 
                    'City', 'Mobile', 'Email', 'Course'];
    
    // Create CSV content
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += headers.join(",") + "\r\n";
    
    // Create download link
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "enquiry_template.csv");
    document.body.appendChild(link);
    
    // Trigger download
    link.click();
    document.body.removeChild(link);
});
</script>

</body>
</html>

<?php  // End output buffering and flush the output ?>