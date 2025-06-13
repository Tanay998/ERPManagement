<?php
// database.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "schoolfeesys";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fee Records</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Student Fee Records</h2>
        <table id="studentTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Reg No</th>
                    <th>Admission Date</th>
                    <th>Course</th>
                    <th>Student Name</th>
                    <th>Profile Picture</th>
                    <th>Total Amount</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT 
                        CONCAT('AdmissionNo/', AdmissionNo1, '/', AdmissionNo2, '/', RegNo) AS AdmissionNo,
                        RegNo,
                        AdmissionDate,
                        Course,
                        CONCAT(`First Name`, ' ', `Middle Name`, ' ', `Last Name`) AS FullName,
                        `Profile Picture`,
                        total_fixed
                        FROM record";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['AdmissionNo']}</td>
                                <td>{$row['RegNo']}</td>
                                <td>{$row['AdmissionDate']}</td>
                                <td>{$row['Course']}</td>
                                <td>{$row['FullName']}</td>
                                <td><img src='{$row['Profile Picture']}' alt='Profile' style='height: 50px;'></td>
                                <td>₹" . number_format($row['total_fixed'] ?? 0, 2) . "</td>
                                <td><button class='btn btn-sm btn-primary view-details' data-id='{$row['RegNo']}'>View</button></td>
                              </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery and DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#studentTable').DataTable();

            // Handle details button click
            $('#studentTable tbody').on('click', '.view-details', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var regNo = $(this).data('id');

                if (row.child.isShown()) {
                    row.child.hide();
                } else {
                    // Fetch fee details via AJAX
                    $.ajax({
                        url: 'get_fee_details.php',
                        method: 'POST',
                        data: { regNo: regNo },
                        success: function(response) {
                            row.child(response).show();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>