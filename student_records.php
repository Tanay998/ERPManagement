<?php
// Database connection
require_once 'config.php';
$conn = new mysqli('localhost', DB_USER, DB_PASS, 'schoolfeesys');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete operation
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM record WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Record deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting record";
    }
    
    header("Location: student_records.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Records</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .action-btns .btn {
            margin-right: 5px;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 4px;
            padding: 4px 8px;
            border: 1px solid #ced4da;
        }
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Student Records</h3>
                <div class="float-end">
                    <a href="index.php?page=new_admission2.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Add New Record
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table id="recordsTable" class="table table-striped table-bordered" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Admission No</th>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Semester</th>
                                <th>Admission Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id, AdmissionNo1, `First Name`, `Middle Name`, `Last Name`, 
                                   Course, Semester, AdmissionDate 
                                   FROM record ORDER BY AdmissionDate DESC";
                            $result = $conn->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $fullName = $row['First Name'] . ' ' . $row['Middle Name'] . ' ' . $row['Last Name'];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= htmlspecialchars($row['AdmissionNo1']) ?></td>
                                        <td><?= htmlspecialchars($fullName) ?></td>
                                        <td><?= htmlspecialchars($row['Course']) ?></td>
                                        <td><?= htmlspecialchars($row['Semester']) ?></td>
                                        <td><?= htmlspecialchars($row['AdmissionDate']) ?></td>
                                        <td class="action-btns">
                                            <a href="index.php?page=view_student11.php&id=<?= $row['id'] ?>" 
                                               class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?page=edit_student11.php&id=<?= $row['id'] ?>" 
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="student_records.php?delete_id=<?= $row['id'] ?>" 
                                               class="btn btn-danger btn-sm" title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this record?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Bootstrap JS, DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#recordsTable').DataTable({
                "responsive": true,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "order": [[0, "desc"]],
                "language": {
                    "search": "_INPUT_",
                    "searchPlaceholder": "Search records...",
                    "lengthMenu": "Show _MENU_ records per page",
                    "zeroRecords": "No matching records found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ records",
                    "infoEmpty": "No records available",
                    "infoFiltered": "(filtered from _MAX_ total records)"
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>