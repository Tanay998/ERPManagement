<?php
// Database connection
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




// Pagination settings
$recordsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Filter parameters
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Base SQL query
$sql = "SELECT SQL_CALC_FOUND_ROWS id, `First Name`, `Middle Name`, `Last Name`, `Father Name`, `Mother Name`, 
               Course, AdmissionDate, Amount, payfee, payfee1, balance 
        FROM record WHERE 1=1";

// Change from:
if (!empty($searchTerm)) {
    $sql .= " AND (`First Name` LIKE '%$searchTerm%' 
                  OR `Last Name` LIKE '%$searchTerm%'
                  OR `Father Name` LIKE '%$searchTerm%')";
}
// To (for exact match):
    if (!empty($searchTerm)) {
        $sql .= " AND (CONCAT(`First Name`, ' ', `Middle Name`, ' ', `Last Name`) = '$searchTerm'
                      OR `Father Name` = '$searchTerm')";
    }

// OR for case-insensitive exact match:
    if (!empty($searchTerm)) {
        $searchTermLower = strtolower($searchTerm);
        $sql .= " AND (LOWER(CONCAT(`First Name`, ' ', `Middle Name`, ' ', `Last Name`)) = '$searchTermLower'
                      OR LOWER(`Father Name`) = '$searchTermLower')";
    }
// Change from:
if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND AdmissionDate BETWEEN '$startDate' AND '$endDate'";
}


// To:
if (!empty($startDate) && !empty($endDate)) {
    // Convert dates to YYYY-MM-DD format (MySQL format)
    $startDateFormatted = date('Y-m-d', strtotime($startDate));
    $endDateFormatted = date('Y-m-d', strtotime($endDate));
    $sql .= " AND AdmissionDate BETWEEN '$startDateFormatted' AND '$endDateFormatted'";
}

// Add sorting and pagination
$sql .= " ORDER BY AdmissionDate DESC LIMIT $offset, $recordsPerPage";

$result = $conn->query($sql);
$totalRows = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
$totalPages = ceil($totalRows / $recordsPerPage);

// Output student cards
if ($result->num_rows > 0):
    while($row = $result->fetch_assoc()): ?>
        <div class="student-card">
            <!-- Your existing student card HTML here -->
            <div class="student-header">
                        <div class="student-name">
                        <?php 
                            $fullName = $row['First Name'] . ' ' . $row['Middle Name'] . ' ' . $row['Last Name'];
                        echo htmlspecialchars($fullName); 
                        ?>
                        </div>
                        <div class="action-buttons">
                            <a href="#" class="btn btn-view" onclick="viewStudent(<?php echo $row['id']; ?>)">View</a>
                            <a href="#" class="btn btn-edit" onclick="editStudent(<?php echo $row['id']; ?>)">Edit</a>
                            <a href="#" class="btn btn-delete" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</a>
                            <a href="#" class="btn btn-transfer" onclick="showTransferModal(<?php echo $row['id']; ?>)">Transfer</a>
                        </div>
                    </div>
                    <div class="student-details">
                        <div class="detail-group">
                            <div><span class="detail-label">Father's Name:</span> <?php echo htmlspecialchars($row['Father Name']); ?></div>
                            <div><span class="detail-label">Mother's Name:</span> <?php echo htmlspecialchars($row['Mother Name']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div><span class="detail-label">Course:</span> <?php echo htmlspecialchars($row['Course']); ?></div>
                            <div><span class="detail-label">Admission Date:</span> <?php echo htmlspecialchars($row['AdmissionDate']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div><span class="detail-label">Fee:</span><?php echo htmlspecialchars($row['Amount']); ?></div>
                            <div><span class="detail-label">Paid Fee 1:</span> <?php echo htmlspecialchars($row['payfee']); ?></div>
                            <div><span class="detail-label">Paid Fee 2:</span> <?php echo htmlspecialchars($row['payfee1']); ?></div>
                            <div><span class="detail-label">Balance:</span> <?php echo htmlspecialchars($row['balance']); ?></div>
                        </div>
                    </div>
            <!-- Rest of your student card -->
        </div>
    <?php endwhile;
else: ?>
    <p>No students found matching your criteria.</p>
<?php endif;

echo "<!--PAGINATION_SPLIT-->";

// Output pagination
if ($totalPages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage-1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif;
            
            for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor;
            
            if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage+1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif;

$conn->close();
?>