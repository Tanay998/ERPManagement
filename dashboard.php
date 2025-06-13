<?php
    // Include database configuration
    require_once 'config.php';

    // Create database connection
    $conn = new mysqli('localhost', DB_USER, DB_PASS, 'schoolfeesys'); // Add your database name

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Calculate current academic year based on month
    $currentYear = date('Y');
    $currentMonth = date('n'); // Numeric month (1-12)

    // April or later = current year - next year
    // January-March = previous year - current year
    if ($currentMonth >= 4) {
        $defaultSession = $currentYear . '-' . ($currentYear + 1);
    } else {
        $defaultSession = ($currentYear - 1) . '-' . $currentYear;
    }

    
    // Fetch sessions from database
    $sql = "SELECT Session FROM session ORDER BY Session DESC";
    $result = $conn->query($sql);
    $sessions = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sessions[] = $row['Session'];
        }
    }

    // Function to count admissions for current session
    function countAdmissionsBySession($conn, $currentSession) {
        // Extract the year part from session (e.g., "2024" from "2024-2025")
        $sessionYear = explode('-', $currentSession)[0];
        
        $sql = "SELECT COUNT(*) as admission_count FROM record 
                WHERE AdmissionNo1 LIKE ? OR AdmissionDate LIKE ?";
        $stmt = $conn->prepare($sql);
        
        // Search for admission numbers starting with session year (e.g., "2024%")
        $admissionPattern = $sessionYear . '%';
        $datePattern = $sessionYear . '%';
        
        $stmt->bind_param("ss", $admissionPattern, $datePattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['admission_count'] ?? 0;
    }

// Get admission count for current session
$admissionCount = countAdmissionsBySession($conn, $defaultSession);

    // Insert default session if it doesn't exist
    if (!in_array($defaultSession, $sessions)) {
        $insertSql = "INSERT INTO session (Session) VALUES (?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("s", $defaultSession);
        $stmt->execute();
        array_unshift($sessions, $defaultSession); // Add to beginning of array
    }
    $conn->close();
?>
<div class="row">
    <div class="col-md-4">
        <img src="images/header.jpg" alt="" class="img-fluid" style="height: 70px; width: 70px;">
    </div>
    <div class="col-md-4">
        <span>Session: <span id="session1"><?= htmlspecialchars($defaultSession) ?></span></span>
    </div>
    <hr style="color:teal;height:5px;">
</div>
<div class="row">
    <div class="col-md-12 d-flex align-items-center justify-content-center">
        <span> Session
        <select name="session" id="session" onchange="shown()">
            <option value="">---Select Year---</option>
            <?php foreach ($sessions as $session): ?>
                <option value="<?= htmlspecialchars($session) ?>" <?= ($session === $defaultSession) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($session) ?>
                </option>
            <?php endforeach; ?>
            </select>
        </span>
    </div>
</div>

<!-- Dashboard Cards -->
<div class="row mt-4">
    <!-- Enquiry Card -->
<a href="index.php?page=enquiry.php" class="col-md-3 mb-4 text-decoration-none">
    <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-3x text-primary mb-3"></i>
            <h5 class="card-title">Enquiry</h5>
            <p class="card-text" id="enquiryCount">0</p> <!-- Changed line -->
        </div>
    </div>
</a>

    <!-- Admission Card -->
    <a href="index.php?page=student_records.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                <h5 class="card-title">Admission</h5>
                <p class="card-text" id="admissionCount"><?= $admissionCount ?></p>
            </div>
        </div>
    </a>

    <!-- Student Details -->
    <a href="index.php?page=studentDetails.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-info mb-3"></i>
                <h5 class="card-title">Student Details</h5>
                <p class="card-text">250</p> <!-- Example value -->
            </div>
        </div>
    </a>

    <!-- Manage Users -->
    <a href="index.php?page=manage_users.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-users-cog fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Manage Users</h5>
                <p class="card-text">10</p> <!-- Example value -->
            </div>
        </div>
    </a>

    <!-- Exam Notifications -->
    <a href="index.php?page=exam_notifications.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-bell fa-3x text-danger mb-3"></i>
                <h5 class="card-title">Exam Notifications</h5>
            </div>
        </div>
    </a>

    <!-- Time Table -->
    <a href="index.php?page=time_table.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-calendar-alt fa-3x text-secondary mb-3"></i>
                <h5 class="card-title">Time Table</h5>
            </div>
        </div>
    </a>

    <!-- Attendance -->
    <a href="index.php?page=attendance.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Attendance</h5>
                <p class="card-text">95%</p> <!-- Example value -->
            </div>
        </div>
    </a>

    <!-- HR -->
    <a href="index.php?page=hr.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-users-cog fa-3x text-success mb-3"></i>
                <h5 class="card-title">HR</h5>
            </div>
        </div>
    </a>

    <!-- Finance -->
    <a href="index.php?page=account.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-wallet fa-3x text-info mb-3"></i>
                <h5 class="card-title">Finance</h5>
            </div>
        </div>
    </a>

    <!-- Hostel -->
    <a href="index.php?page=hostel.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-bed fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Hostel</h5>
            </div>
        </div>
    </a>

    <!-- Franchise -->
    <a href="index.php?page=franchise.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-store fa-3x text-danger mb-3"></i>
                <h5 class="card-title">Franchise</h5>
            </div>
        </div>
    </a>

    <!-- Library -->
    <a href="index.php?page=library.php" class="col-md-3 mb-4 text-decoration-none">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-book-open fa-3x text-secondary mb-3"></i>
                <h5 class="card-title">Library</h5>
            </div>
        </div>
    </a>
</div>

<script>
   function shown(){
    document.getElementById('session1').innerHTML = document.getElementById('session').value;
   }
</script>
<script>
function shown() {
    const sessionSelect = document.getElementById('session');
    const sessionDisplay = document.getElementById('session1');
    const selectedSession = sessionSelect.value;
    
    // Update display
    sessionDisplay.textContent = selectedSession || '<?= htmlspecialchars($defaultSession) ?>';
    
    // Fetch counts only if a session is selected
    if (selectedSession) {
        // Fetch enquiry count
        fetch(`get_enquiry_count.php?session=${encodeURIComponent(selectedSession)}`)
            .then(response => response.text())
            .then(count => {
                document.getElementById('enquiryCount').textContent = count;
            })
            .catch(error => console.error('Error:', error));
            
        // Fetch admission count
        fetch(`get_admission_count.php?session=${encodeURIComponent(selectedSession)}`)
            .then(response => response.text())
            .then(count => {
                document.getElementById('admissionCount').textContent = count;
            })
            .catch(error => console.error('Error:', error));
    } else {
        // Reset to default counts
        document.getElementById('enquiryCount').textContent = '0';
        document.getElementById('admissionCount').textContent = '<?= $admissionCount ?>';
    }
}

// Initialize with default session on page load
document.addEventListener('DOMContentLoaded', function() {
    shown();
});
</script>